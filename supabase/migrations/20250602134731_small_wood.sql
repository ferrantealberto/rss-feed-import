/*
  # RSS Feed Importer Schema

  1. New Tables
    - `feeds`
      - `id` (uuid, primary key)
      - `name` (text)
      - `url` (text)
      - `frequency` (text)
      - `status` (text)
      - `site_id` (text, nullable)
      - `description` (text, nullable)
      - `category` (text, nullable)
      - `content_type` (text, nullable)
      - `priority` (integer, nullable)
      - `last_import` (timestamptz, nullable)
      - `next_import` (timestamptz, nullable)
      - `total_imported` (integer)
      - `created_at` (timestamptz)
      - `updated_at` (timestamptz)

    - `imported_posts`
      - `id` (uuid, primary key)
      - `feed_id` (uuid, references feeds)
      - `title` (text)
      - `content` (text)
      - `original_url` (text)
      - `published_at` (timestamptz)
      - `status` (text)
      - `created_at` (timestamptz)
      - `updated_at` (timestamptz)

  2. Security
    - Enable RLS on both tables
    - Add policies for authenticated users
*/

-- Create feeds table
CREATE TABLE IF NOT EXISTS feeds (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  name text NOT NULL,
  url text NOT NULL,
  frequency text NOT NULL,
  status text NOT NULL DEFAULT 'active',
  site_id text,
  description text,
  category text,
  content_type text,
  priority integer,
  last_import timestamptz,
  next_import timestamptz,
  total_imported integer DEFAULT 0,
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Create imported_posts table
CREATE TABLE IF NOT EXISTS imported_posts (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  feed_id uuid REFERENCES feeds ON DELETE CASCADE,
  title text NOT NULL,
  content text NOT NULL,
  original_url text,
  published_at timestamptz NOT NULL,
  status text NOT NULL DEFAULT 'pending',
  created_at timestamptz DEFAULT now(),
  updated_at timestamptz DEFAULT now()
);

-- Enable RLS
ALTER TABLE feeds ENABLE ROW LEVEL SECURITY;
ALTER TABLE imported_posts ENABLE ROW LEVEL SECURITY;

-- Create policies for feeds
CREATE POLICY "Enable read access for authenticated users" ON feeds
  FOR SELECT TO authenticated USING (true);

CREATE POLICY "Enable insert access for authenticated users" ON feeds
  FOR INSERT TO authenticated WITH CHECK (true);

CREATE POLICY "Enable update access for authenticated users" ON feeds
  FOR UPDATE TO authenticated USING (true);

CREATE POLICY "Enable delete access for authenticated users" ON feeds
  FOR DELETE TO authenticated USING (true);

-- Create policies for imported_posts
CREATE POLICY "Enable read access for authenticated users" ON imported_posts
  FOR SELECT TO authenticated USING (true);

CREATE POLICY "Enable insert access for authenticated users" ON imported_posts
  FOR INSERT TO authenticated WITH CHECK (true);

CREATE POLICY "Enable update access for authenticated users" ON imported_posts
  FOR UPDATE TO authenticated USING (true);

CREATE POLICY "Enable delete access for authenticated users" ON imported_posts
  FOR DELETE TO authenticated USING (true);

-- Create indexes for better performance
CREATE INDEX IF NOT EXISTS feeds_status_idx ON feeds(status);
CREATE INDEX IF NOT EXISTS feeds_next_import_idx ON feeds(next_import);
CREATE INDEX IF NOT EXISTS imported_posts_feed_id_idx ON imported_posts(feed_id);
CREATE INDEX IF NOT EXISTS imported_posts_original_url_idx ON imported_posts(original_url);
CREATE INDEX IF NOT EXISTS imported_posts_status_idx ON imported_posts(status);