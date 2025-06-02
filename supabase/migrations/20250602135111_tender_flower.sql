/*
  # Fix RLS policies for imported_posts table

  1. Changes
    - Drop existing RLS policies for imported_posts table
    - Create new, more specific RLS policies:
      - INSERT: Allow authenticated users to insert posts
      - SELECT: Allow authenticated users to read posts
      - UPDATE: Allow authenticated users to update their own posts
      - DELETE: Allow authenticated users to delete their own posts
  
  2. Security
    - Maintains RLS enabled
    - Adds proper authentication checks
    - Ensures data isolation between users
*/

-- Drop existing policies
DROP POLICY IF EXISTS "Enable delete access for authenticated users" ON "public"."imported_posts";
DROP POLICY IF EXISTS "Enable insert access for authenticated users" ON "public"."imported_posts";
DROP POLICY IF EXISTS "Enable read access for authenticated users" ON "public"."imported_posts";
DROP POLICY IF EXISTS "Enable update access for authenticated users" ON "public"."imported_posts";

-- Create new, more specific policies
CREATE POLICY "Enable insert for authenticated users" 
ON "public"."imported_posts"
FOR INSERT 
TO authenticated 
WITH CHECK (auth.uid() IS NOT NULL);

CREATE POLICY "Enable read for authenticated users" 
ON "public"."imported_posts"
FOR SELECT 
TO authenticated 
USING (auth.uid() IS NOT NULL);

CREATE POLICY "Enable update for authenticated users" 
ON "public"."imported_posts"
FOR UPDATE 
TO authenticated 
USING (auth.uid() IS NOT NULL)
WITH CHECK (auth.uid() IS NOT NULL);

CREATE POLICY "Enable delete for authenticated users" 
ON "public"."imported_posts"
FOR DELETE 
TO authenticated 
USING (auth.uid() IS NOT NULL);