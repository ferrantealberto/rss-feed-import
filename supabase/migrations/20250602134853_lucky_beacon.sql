/*
  # Update RLS policies for imported_posts table

  1. Changes
    - Add RLS policy for authenticated users to insert posts
    - Add RLS policy for authenticated users to read posts
    - Add RLS policy for authenticated users to update posts
    - Add RLS policy for authenticated users to delete posts

  2. Security
    - Enable RLS on imported_posts table
    - Add policies for CRUD operations
*/

-- Enable RLS
ALTER TABLE imported_posts ENABLE ROW LEVEL SECURITY;

-- Create policies for authenticated users
CREATE POLICY "Enable insert access for authenticated users"
ON imported_posts
FOR INSERT
TO authenticated
WITH CHECK (true);

CREATE POLICY "Enable read access for authenticated users"
ON imported_posts
FOR SELECT
TO authenticated
USING (true);

CREATE POLICY "Enable update access for authenticated users"
ON imported_posts
FOR UPDATE
TO authenticated
USING (true)
WITH CHECK (true);

CREATE POLICY "Enable delete access for authenticated users"
ON imported_posts
FOR DELETE
TO authenticated
USING (true);