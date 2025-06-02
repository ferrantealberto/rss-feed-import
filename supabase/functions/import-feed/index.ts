// @deno-types="npm:@types/xml2js"
import { parseString } from 'npm:xml2js';
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
    
    // Parse XML using xml2js
    const items = await new Promise((resolve, reject) => {
      parseString(text, (err, result) => {
        if (err) {
          reject(new Error('Failed to parse XML: ' + err.message));
          return;
        }

        try {
          // Handle both RSS and Atom formats
          const entries = [];
          
          if (result.rss?.channel?.[0]?.item) {
            // RSS format
            entries.push(...result.rss.channel[0].item);
          } else if (result.feed?.entry) {
            // Atom format
            entries.push(...result.feed.entry);
          }

          const parsedItems = entries.map(entry => ({
            title: getFirstValue(entry.title),
            link: getFirstValue(entry.link) || entry.link?.[0]?.['$']?.href,
            description: getFirstValue(entry.description) || getFirstValue(entry['content:encoded']) || getFirstValue(entry.content),
            pubDate: getFirstValue(entry.pubDate) || getFirstValue(entry.published) || new Date().toISOString()
          })).filter(item => item.title && item.description);

          resolve(parsedItems);
        } catch (error) {
          reject(new Error('Failed to process feed entries: ' + error.message));
        }
      });
    });

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

// Helper function to safely get the first value from an array or string
function getFirstValue(value: unknown): string {
  if (Array.isArray(value)) {
    return value[0]?._ || value[0] || '';
  }
  return value?.toString() || '';
}