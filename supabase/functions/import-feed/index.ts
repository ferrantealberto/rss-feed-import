import { serve } from 'https://deno.land/std@0.168.0/http/server.ts'
import { createClient } from 'npm:@supabase/supabase-js@2.39.3'

const corsHeaders = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
}

serve(async (req) => {
  // Handle CORS preflight requests
  if (req.method === 'OPTIONS') {
    return new Response('ok', { headers: corsHeaders })
  }

  try {
    const { feedUrl } = await req.json()
    
    if (!feedUrl) {
      throw new Error('Feed URL is required')
    }

    // Fetch the RSS feed
    const response = await fetch(feedUrl, {
      headers: {
        'Accept': 'application/xml, application/rss+xml, text/xml',
        'User-Agent': 'RSS Feed Importer/1.0'
      }
    })

    if (!response.ok) {
      throw new Error(`Failed to fetch feed: ${response.status}`)
    }

    const text = await response.text()
    
    // Parse XML using DOMParser
    const parser = new DOMParser()
    const xml = parser.parseFromString(text, 'text/xml')
    
    // Extract items
    const items = Array.from(xml.querySelectorAll('item')).map(item => ({
      title: item.querySelector('title')?.textContent || '',
      link: item.querySelector('link')?.textContent || '',
      description: item.querySelector('description')?.textContent || '',
      pubDate: item.querySelector('pubDate')?.textContent || ''
    }))

    return new Response(
      JSON.stringify({ items }),
      { 
        headers: {
          ...corsHeaders,
          'Content-Type': 'application/json'
        }
      }
    )

  } catch (error) {
    return new Response(
      JSON.stringify({ error: error.message }),
      { 
        status: 400,
        headers: {
          ...corsHeaders,
          'Content-Type': 'application/json'
        }
      }
    )
  }
})