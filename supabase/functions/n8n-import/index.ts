import { serve } from "https://deno.land/std@0.168.0/http/server.ts";
import { createClient } from 'npm:@supabase/supabase-js@2.39.3';

const corsHeaders = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
  'Access-Control-Allow-Methods': 'POST, OPTIONS'
};

const N8N_WEBHOOK_URL = 'https://n8n.weblabfactory.it/webhook/rss-import';

serve(async (req) => {
  if (req.method === 'OPTIONS') {
    return new Response('ok', { headers: corsHeaders });
  }

  try {
    // Get feed data from request
    const { feedId, feedUrl } = await req.json();
    
    if (!feedId || !feedUrl) {
      throw new Error('Feed ID and URL are required');
    }

    // Initialize Supabase client
    const supabaseClient = createClient(
      Deno.env.get('SUPABASE_URL') ?? '',
      Deno.env.get('SUPABASE_ANON_KEY') ?? '',
      { auth: { persistSession: false } }
    );

    // Get feed details from database
    const { data: feed, error: feedError } = await supabaseClient
      .from('feeds')
      .select('*')
      .eq('id', feedId)
      .single();

    if (feedError || !feed) {
      throw new Error('Feed not found');
    }

    // Send request to n8n webhook
    const response = await fetch(N8N_WEBHOOK_URL, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        feedId,
        feedUrl,
        feedName: feed.name,
        siteId: feed.site_id,
        frequency: feed.frequency,
        category: feed.category,
        contentType: feed.content_type,
        priority: feed.priority
      })
    });

    if (!response.ok) {
      throw new Error(`n8n webhook error: ${response.statusText}`);
    }

    const result = await response.json();

    // Update feed with import status
    await supabaseClient
      .from('feeds')
      .update({
        last_import: new Date().toISOString(),
        next_import: calculateNextImport(feed.frequency)
      })
      .eq('id', feedId);

    return new Response(
      JSON.stringify({ success: true, result }),
      { 
        headers: {
          ...corsHeaders,
          'Content-Type': 'application/json'
        }
      }
    );

  } catch (error) {
    console.error('n8n import error:', error);
    
    return new Response(
      JSON.stringify({ 
        error: error instanceof Error ? error.message : 'Unknown error'
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

function calculateNextImport(frequency: string): string {
  const now = new Date();
  
  switch (frequency) {
    case 'hourly':
      now.setHours(now.getHours() + 1);
      break;
    case 'daily':
      now.setDate(now.getDate() + 1);
      break;
    case 'weekly':
      now.setDate(now.getDate() + 7);
      break;
    default:
      now.setHours(now.getHours() + 1);
  }
  
  return now.toISOString();
}