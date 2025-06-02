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
  totalImported?: number;
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
          const { data: { session }, error: authError } = await supabase.auth.getSession();
          if (authError || !session) {
            throw new Error('User must be authenticated to import feeds');
          }

          const functionUrl = `${import.meta.env.VITE_SUPABASE_URL}/functions/v1/n8n-import`;
          const response = await fetch(functionUrl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${session.access_token}`
            },
            body: JSON.stringify({ 
              feedUrl: feed.url,
              feedId: feed.id
            })
          });
          
          const data = await response.json();
          
          if (!response.ok || data.error) {
            throw new Error(data.error || `HTTP error! status: ${response.status}`);
          }
          
          state.updateFeed(id, {
            lastImport: new Date().toISOString(),
            nextImport: calculateNextImport(feed.frequency),
            totalImported: (feed.totalImported || 0) + (data.result?.imported || 0)
          });

          return data.result?.imported || 0;
        } catch (error) {
          const errorMessage = error instanceof Error ? error.message : 'Unknown error';
          console.error('Import failed:', errorMessage);
          throw new Error(`Failed to import feed: ${errorMessage}`);
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