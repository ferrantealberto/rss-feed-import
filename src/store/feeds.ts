import { create } from 'zustand';
import { persist } from 'zustand/middleware';

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
    (set) => ({
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
      }))
      importFeed: async (id) => {
        const state = get();
        const feed = state.feeds.find(f => f.id === id);
        
        if (!feed) {
          throw new Error('Feed not found');
        }

        try {
          // Fetch the RSS feed
          const response = await fetch(feed.url);
          const text = await response.text();
          const parser = new DOMParser();
          const xml = parser.parseFromString(text, 'text/xml');
          
          // Extract items
          const items = Array.from(xml.querySelectorAll('item'));
          
          // Process each item
          for (const item of items) {
            const title = item.querySelector('title')?.textContent;
            const link = item.querySelector('link')?.textContent;
            const content = item.querySelector('description')?.textContent;
            const pubDate = item.querySelector('pubDate')?.textContent;
            
            if (title && content) {
              // Here you would typically save to your database
              console.log('Imported:', { title, link, pubDate });
            }
          }
          
          // Update feed status
          state.updateFeed(id, {
            lastImport: new Date().toISOString(),
            nextImport: calculateNextImport(feed.frequency)
          });

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