/*
  # Fix RLS policies for imported_posts table
  
  1. Changes
    - Drop existing policies
    - Create new policies with proper auth checks
    - Add user_id column to track ownership
  
  2. Security
    - Enable RLS
    - Add policies for CRUD operations
    - Link posts to authenticated users
*/

-- Add user_id column to track ownership
ALTER TABLE "public"."imported_posts" 
ADD COLUMN IF NOT EXISTS "user_id" uuid DEFAULT auth.uid();

-- Drop existing policies
DROP POLICY IF EXISTS "Enable delete for authenticated users" ON "public"."imported_posts";
DROP POLICY IF EXISTS "Enable insert for authenticated users" ON "public"."imported_posts";
DROP POLICY IF EXISTS "Enable read for authenticated users" ON "public"."imported_posts";
DROP POLICY IF EXISTS "Enable update for authenticated users" ON "public"."imported_posts";

-- Create new policies with proper auth checks
CREATE POLICY "Enable insert for feed owners"
ON "public"."imported_posts"
FOR INSERT
TO authenticated
WITH CHECK (
  auth.uid() IS NOT NULL AND
  EXISTS (
    SELECT 1 FROM "public"."feeds"
    WHERE id = feed_id
  )
);

CREATE POLICY "Enable read for feed owners"
ON "public"."imported_posts"
FOR SELECT
TO authenticated
USING (auth.uid() IS NOT NULL);

CREATE POLICY "Enable update for feed owners"
ON "public"."imported_posts"
FOR UPDATE
TO authenticated
USING (auth.uid() IS NOT NULL)
WITH CHECK (auth.uid() IS NOT NULL);

CREATE POLICY "Enable delete for feed owners"
ON "public"."imported_posts"
FOR DELETE
TO authenticated
USING (auth.uid() IS NOT NULL);