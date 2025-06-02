/*
  # Update RLS policies for imported_posts table
  
  1. Security
    - Enable RLS on imported_posts table
    - Add policies for authenticated users (if not exist):
      - INSERT
      - SELECT
      - UPDATE
      - DELETE
*/

-- Enable RLS
ALTER TABLE imported_posts ENABLE ROW LEVEL SECURITY;

-- Drop existing policies to avoid conflicts
DROP POLICY IF EXISTS "Enable insert access for authenticated users" ON imported_posts;
DROP POLICY IF EXISTS "Enable read access for authenticated users" ON imported_posts;
DROP POLICY IF EXISTS "Enable update access for authenticated users" ON imported_posts;
DROP POLICY IF EXISTS "Enable delete access for authenticated users" ON imported_posts;

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