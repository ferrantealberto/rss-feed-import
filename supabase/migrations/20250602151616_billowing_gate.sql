/*
  # Add n8n integration settings

  1. Changes
    - Add n8n_workflow_id column to feeds table
    - Add n8n_settings JSONB column for workflow-specific settings
    - Add index on n8n_workflow_id for faster lookups
    
  2. Security
    - Maintain existing RLS policies
*/

-- Add n8n integration columns
ALTER TABLE public.feeds
ADD COLUMN IF NOT EXISTS n8n_workflow_id text,
ADD COLUMN IF NOT EXISTS n8n_settings jsonb DEFAULT '{}'::jsonb;

-- Add index for workflow lookups
CREATE INDEX IF NOT EXISTS feeds_n8n_workflow_id_idx ON public.feeds(n8n_workflow_id);

-- Update feed_items table to track n8n processing
ALTER TABLE public.feed_items
ADD COLUMN IF NOT EXISTS n8n_execution_id text,
ADD COLUMN IF NOT EXISTS n8n_execution_status text,
ADD COLUMN IF NOT EXISTS n8n_execution_error text;

-- Create index for n8n execution tracking
CREATE INDEX IF NOT EXISTS feed_items_n8n_execution_idx ON public.feed_items(n8n_execution_id);

-- Update process_feed_item function to handle n8n integration
CREATE OR REPLACE FUNCTION process_feed_item(item_id uuid)
RETURNS boolean
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_item feed_items;
  v_feed feeds;
  v_duplicate boolean;
BEGIN
  -- Get the feed item
  SELECT * INTO v_item
  FROM feed_items
  WHERE id = item_id
  AND processed = false
  FOR UPDATE;

  IF NOT FOUND THEN
    RETURN false;
  END IF;

  -- Get associated feed
  SELECT * INTO v_feed
  FROM feeds
  WHERE id = v_item.feed_id;

  -- Check for duplicates
  SELECT EXISTS (
    SELECT 1 FROM imported_posts
    WHERE original_url = v_item.link
    OR title = v_item.title
  ) INTO v_duplicate;

  IF v_duplicate THEN
    -- Mark as duplicate
    UPDATE feed_items
    SET processed = true,
        import_status = 'duplicate',
        updated_at = now()
    WHERE id = item_id;
    
    RETURN true;
  END IF;

  -- If feed has n8n workflow, mark for n8n processing
  IF v_feed.n8n_workflow_id IS NOT NULL THEN
    UPDATE feed_items
    SET processed = false,
        import_status = 'pending_n8n',
        updated_at = now()
    WHERE id = item_id;
    
    RETURN true;
  END IF;

  -- Otherwise process normally
  INSERT INTO imported_posts (
    feed_id,
    title,
    content,
    original_url,
    published_at,
    status,
    user_id
  )
  VALUES (
    v_item.feed_id,
    v_item.title,
    v_item.content,
    v_item.link,
    v_item.published_at,
    'pending',
    v_item.user_id
  );

  -- Mark as processed
  UPDATE feed_items
  SET processed = true,
      import_status = 'success',
      updated_at = now()
  WHERE id = item_id;

  RETURN true;
EXCEPTION WHEN OTHERS THEN
  -- Handle errors
  UPDATE feed_items
  SET processed = false,
      import_status = 'error',
      import_error = SQLERRM,
      updated_at = now()
  WHERE id = item_id;
  
  RETURN false;
END;
$$;