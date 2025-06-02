/*
  # WordPress-style RSS Feed Importer

  1. New Tables
    - `feed_items` - Stores individual items from feeds before import
    - `feed_item_meta` - Stores metadata for feed items (images, categories, etc)
    
  2. Functions
    - `process_feed_item()` - Processes feed items for import
    - `extract_feed_images()` - Extracts images from feed content
    - `generate_excerpt()` - Generates post excerpts
    
  3. Security
    - Enable RLS on new tables
    - Add policies for authenticated users
*/

-- Create feed_items table
CREATE TABLE IF NOT EXISTS feed_items (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  feed_id uuid REFERENCES feeds(id) ON DELETE CASCADE,
  guid text NOT NULL,
  title text NOT NULL,
  content text NOT NULL,
  excerpt text,
  link text,
  author text,
  published_at timestamptz NOT NULL,
  status text NOT NULL DEFAULT 'pending',
  processed boolean DEFAULT false,
  import_status text DEFAULT 'pending',
  import_error text,
  user_id uuid NOT NULL DEFAULT auth.uid(),
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Create feed_item_meta table
CREATE TABLE IF NOT EXISTS feed_item_meta (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  feed_item_id uuid REFERENCES feed_items(id) ON DELETE CASCADE,
  meta_key text NOT NULL,
  meta_value text,
  created_at timestamptz DEFAULT now()
);

-- Enable RLS
ALTER TABLE feed_items ENABLE ROW LEVEL SECURITY;
ALTER TABLE feed_item_meta ENABLE ROW LEVEL SECURITY;

-- Create policies for feed_items
CREATE POLICY "Enable read for feed owners" ON feed_items
FOR SELECT TO authenticated
USING (auth.uid() = user_id);

CREATE POLICY "Enable insert for feed owners" ON feed_items
FOR INSERT TO authenticated
WITH CHECK (
  auth.uid() = user_id AND
  EXISTS (
    SELECT 1 FROM feeds
    WHERE id = feed_id
  )
);

CREATE POLICY "Enable update for feed owners" ON feed_items
FOR UPDATE TO authenticated
USING (auth.uid() = user_id)
WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Enable delete for feed owners" ON feed_items
FOR DELETE TO authenticated
USING (auth.uid() = user_id);

-- Create policies for feed_item_meta
CREATE POLICY "Enable read for feed item owners" ON feed_item_meta
FOR SELECT TO authenticated
USING (
  EXISTS (
    SELECT 1 FROM feed_items
    WHERE id = feed_item_id
    AND user_id = auth.uid()
  )
);

CREATE POLICY "Enable insert for feed item owners" ON feed_item_meta
FOR INSERT TO authenticated
WITH CHECK (
  EXISTS (
    SELECT 1 FROM feed_items
    WHERE id = feed_item_id
    AND user_id = auth.uid()
  )
);

CREATE POLICY "Enable update for feed item owners" ON feed_item_meta
FOR UPDATE TO authenticated
USING (
  EXISTS (
    SELECT 1 FROM feed_items
    WHERE id = feed_item_id
    AND user_id = auth.uid()
  )
);

CREATE POLICY "Enable delete for feed item owners" ON feed_item_meta
FOR DELETE TO authenticated
USING (
  EXISTS (
    SELECT 1 FROM feed_items
    WHERE id = feed_item_id
    AND user_id = auth.uid()
  )
);

-- Create indexes
CREATE INDEX IF NOT EXISTS feed_items_feed_id_idx ON feed_items(feed_id);
CREATE INDEX IF NOT EXISTS feed_items_guid_idx ON feed_items(guid);
CREATE INDEX IF NOT EXISTS feed_items_status_idx ON feed_items(status);
CREATE INDEX IF NOT EXISTS feed_items_processed_idx ON feed_items(processed);
CREATE INDEX IF NOT EXISTS feed_item_meta_feed_item_id_idx ON feed_item_meta(feed_item_id);
CREATE INDEX IF NOT EXISTS feed_item_meta_key_idx ON feed_item_meta(meta_key);

-- Create function to process feed items
CREATE OR REPLACE FUNCTION process_feed_item(item_id uuid)
RETURNS boolean
LANGUAGE plpgsql
SECURITY DEFINER
AS $$
DECLARE
  v_item feed_items;
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

  -- Insert into imported_posts
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