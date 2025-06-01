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
}

interface FeedsStore {
  feeds: Feed[];
  addFeed: (feed: Omit<Feed, 'id'>) => void;
  addFeeds: (feeds: Omit<Feed, 'id'>[]) => void;
  updateFeed: (id: string, updates: Partial<Feed>) => void;
  deleteFeed: (id: string) => void;
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
    }),
    {
      name: 'feeds-storage'
    }
  )
);