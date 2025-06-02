import { DOMParser } from 'npm:deno-dom';
import { serve } from "https://deno.land/std@0.168.0/http/server.ts";

const corsHeaders = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
  'Access-Control-Allow-Methods': 'POST, OPTIONS'
};

serve(async (req) => {
  if (req.method === 'OPTIONS') {
    return new Response('ok', { headers: corsHeaders });
  }

  try {
    const { feedUrl } = await req.json();
    
    if (!feedUrl) {
      throw new Error('Feed URL is required');
    }

    // Fetch the RSS feed with proper headers
    const response = await fetch(feedUrl, {
      headers: {
        'Accept': 'application/xml, application/rss+xml, text/xml',
        'User-Agent': 'RSS Feed Importer/1.0'
      }
    });

    if (!response.ok) {
      throw new Error(`Failed to fetch feed: ${response.status}`);
    }

    const text = await response.text();
    
    // Parse XML
    const parser = new DOMParser();
    const xml = parser.parseFromString(text, 'application/xml');
    
    if (!xml) {
      throw new Error('Failed to parse XML');
    }

    // Try to find items in both RSS and Atom formats
    const items = [];
    const entries = xml.querySelectorAll('item, entry');

    for (const entry of entries) {
      // Handle both RSS and Atom formats
      const item = {
        title: entry.querySelector('title')?.textContent || '',
        link: entry.querySelector('link')?.textContent || entry.querySelector('link')?.getAttribute('href') || '',
        description: entry.querySelector('description, content, summary')?.textContent || '',
        pubDate: entry.querySelector('pubDate, published, updated')?.textContent || new Date().toISOString()
      };

      // Clean up content
      item.description = item.description.trim();
      
      // Only add items with required fields
      if (item.title && item.description) {
        items.push(item);
      }
    }

    if (items.length === 0) {
      throw new Error('No valid items found in feed');
    }

    return new Response(
      JSON.stringify({ items }),
      { 
        headers: {
          ...corsHeaders,
          'Content-Type': 'application/json'
        }
      }
    );

  } catch (error) {
    console.error('Feed import error:', error);
    
    return new Response(
      JSON.stringify({ 
        error: error instanceof Error ? error.message : 'Unknown error',
        details: error instanceof Error ? error.stack : undefined
      }),
      { 
        status: 400,
        headers: {
          ...corsHeaders,
          'Content-Type': 'application/json'
        }
      }
    );
  }
});