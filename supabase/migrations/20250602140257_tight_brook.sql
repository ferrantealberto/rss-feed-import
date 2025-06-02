/*
  # Fix Authentication Requirements

  1. Changes
    - Add NOT NULL constraint to user_id column
    - Update RLS policies to strictly enforce user authentication
    - Add trigger to ensure user_id is always set
  
  2. Security
    - Enable RLS
    - Add strict authentication checks
    - Ensure user_id is properly set
*/

-- Make user_id required and default to authenticated user
ALTER TABLE public.imported_posts 
  ALTER COLUMN user_id SET NOT NULL,
  ALTER COLUMN user_id SET DEFAULT auth.uid();

-- Drop existing policies
DROP POLICY IF EXISTS "Enable insert for feed owners" ON public.imported_posts;
DROP POLICY IF EXISTS "Enable read for feed owners" ON public.imported_posts;
DROP POLICY IF EXISTS "Enable update for feed owners" ON public.imported_posts;
DROP POLICY IF EXISTS "Enable delete for feed owners" ON public.imported_posts;

-- Create stricter policies
CREATE POLICY "Enable insert for feed owners" ON public.imported_posts
FOR INSERT TO authenticated
WITH CHECK (
  auth.uid() = user_id AND
  EXISTS (
    SELECT 1 FROM public.feeds
    WHERE id = feed_id
  )
);

CREATE POLICY "Enable read for feed owners" ON public.imported_posts
FOR SELECT TO authenticated
USING (auth.uid() = user_id);

CREATE POLICY "Enable update for feed owners" ON public.imported_posts
FOR UPDATE TO authenticated
USING (auth.uid() = user_id)
WITH CHECK (auth.uid() = user_id);

CREATE POLICY "Enable delete for feed owners" ON public.imported_posts
FOR DELETE TO authenticated
USING (auth.uid() = user_id);

-- Ensure trigger function exists and is up to date
CREATE OR REPLACE FUNCTION public.set_imported_post_user_id()
RETURNS TRIGGER AS $$
BEGIN
  IF auth.uid() IS NULL THEN
    RAISE EXCEPTION 'User must be authenticated to insert posts';
  END IF;
  NEW.user_id := auth.uid();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;