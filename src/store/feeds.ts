import { create } from 'zustand';
import { persist } from 'zustand/middleware';
import { supabase } from '../lib/supabaseClient';

export interface Feed {
  id: string;
  name: string;
  url: string;
  frequency: string;
  status: string;
  siteId?: string;
  description?: string;
  category?: string;
  contentType?: string;
  priority?: number;
  lastImport?: string;
  nextImport?: string;
}

interface FeedsStore {
  feeds: Feed[];
  addFeed: (feed: Omit<Feed, 'id'>) => void;
  addFeeds: (feeds: Omit<Feed, 'id'>[]) => void;
  updateFeed: (id: string, updates: Partial<Feed>) => void;
  deleteFeed: (id: string) => void;
  importFeed: (id: string) => Promise<void>;
  scheduleImports: () => void;
}

export const useFeedsStore = create<FeedsStore>()(
  persist(
    (set, get) => ({
      feeds: [],
      addFeed: (feed) => set((state) => ({
        feeds: [...state.feeds, { ...feed, id: crypto.randomUUID() }]
      })),
      addFeeds: (newFeeds) => set((state) => ({
        feeds: [...state.feeds, ...newFeeds.map(feed => ({ ...feed, id: crypto.randomUUID() }))]
      })),
      updateFeed: (id, updates) => set((state) => ({
        feeds: state.feeds.map(feed => 
          feed.id === id ? { ...feed, ...updates } : feed
        )
      })),
      deleteFeed: (id) => set((state) => ({
        feeds: state.feeds.filter(feed => feed.id !== id)
      })),
      importFeed: async (id) => {
        const state = get();
        const feed = state.feeds.find(f => f.id === id);
        if (!feed) {
          throw new Error('Feed not found');
        }

        try {
          const response = await fetch(feed.url);
          const text = await response.text();
          const parser = new DOMParser();
          const xml = parser.parseFromString(text, 'text/xml');
          
          const items = Array.from(xml.querySelectorAll('item'));
          const importedCount = 0;
          
          for (const item of items) {
            const title = item.querySelector('title')?.textContent;
            const link = item.querySelector('link')?.textContent;
            const content = item.querySelector('description')?.textContent;
            const pubDate = item.querySelector('pubDate')?.textContent;
            
            if (title && content) {
              // Check for duplicates
              const { data: existing } = await supabase
                .from('imported_posts')
                .select('id')
                .eq('original_url', link)
                .single();
                
              if (!existing) {
                // Insert new post
                const { data, error } = await supabase
                  .from('imported_posts')
                  .insert({
                    feed_id: feed.id,
                    title: title,
                    content: content,
                    original_url: link,
                    published_at: pubDate ? new Date(pubDate).toISOString() : new Date().toISOString(),
                    status: 'pending'
                  });
                  
                if (!error) {
                  importedCount++;
                }
              }
            }
          }
          
          state.updateFeed(id, {
            lastImport: new Date().toISOString(),
            nextImport: calculateNextImport(feed.frequency),
            totalImported: (feed.totalImported || 0) + importedCount
          });

          return importedCount;
        } catch (error) {
          console.error('Import failed:', error);
          throw error;
        }
      },
      scheduleImports: () => {
        const state = get();
        
        // Check each feed
        state.feeds.forEach(feed => {
          if (feed.status !== 'active') return;
          
          const now = new Date();
          const nextImport = feed.nextImport ? new Date(feed.nextImport) : null;
          
          if (!nextImport || now >= nextImport) {
            // Time to import
            state.importFeed(feed.id).catch(console.error);
          }
        });
      }
    }),
    {
      name: 'feeds-storage'
    }
  )
);

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
      now.setHours(now.getHours() + 1); // Default to hourly
  }
  
  return now.toISOString();
}