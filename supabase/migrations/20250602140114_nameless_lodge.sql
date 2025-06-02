/*
  # Fix RLS policies for imported_posts table

  1. Changes
    - Update INSERT policy for imported_posts to properly handle authenticated users
    - Ensure user_id is set correctly during insert operations
    - Add proper check conditions for INSERT operations

  2. Security
    - Maintain RLS enabled on imported_posts table
    - Allow authenticated users to insert posts with their user_id
    - Keep existing policies for other operations (SELECT, UPDATE, DELETE)
*/

-- First drop the existing INSERT policy if it exists
DO $$ 
BEGIN
  IF EXISTS (
    SELECT 1 FROM pg_policies 
    WHERE schemaname = 'public' 
    AND tablename = 'imported_posts' 
    AND policyname = 'Enable insert for feed owners'
  ) THEN
    DROP POLICY "Enable insert for feed owners" ON public.imported_posts;
  END IF;
END $$;

-- Create new INSERT policy with proper conditions
CREATE POLICY "Enable insert for feed owners" ON public.imported_posts
FOR INSERT TO authenticated
WITH CHECK (
  auth.uid() IS NOT NULL AND (
    EXISTS (
      SELECT 1
      FROM public.feeds
      WHERE id = imported_posts.feed_id
    )
  )
);

-- Ensure the user_id column is properly set during insert
CREATE OR REPLACE FUNCTION public.set_imported_post_user_id()
RETURNS TRIGGER AS $$
BEGIN
  NEW.user_id = auth.uid();
  RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Drop the trigger if it exists
DO $$ 
BEGIN
  IF EXISTS (
    SELECT 1 FROM pg_trigger 
    WHERE tgname = 'set_user_id_on_post_insert'
  ) THEN
    DROP TRIGGER IF EXISTS set_user_id_on_post_insert ON public.imported_posts;
  END IF;
END $$;

-- Create trigger to automatically set user_id
CREATE TRIGGER set_user_id_on_post_insert
  BEFORE INSERT ON public.imported_posts
  FOR EACH ROW
  EXECUTE FUNCTION public.set_imported_post_user_id();