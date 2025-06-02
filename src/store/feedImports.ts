import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export interface FeedImport {
  id: string;
  feedId: string;
  feedName: string;
  lastImport: string | null;
  nextImport: string | null;
  status: 'active' | 'paused' | 'error';
  error?: string;
  importCount: number;
}

interface FeedImportsStore {
  imports: FeedImport[];
  addImport: (feedId: string, feedName: string) => void;
  updateImport: (id: string, updates: Partial<FeedImport>) => void;
  removeImport: (id: string) => void;
  importFeed: (feedId: string) => Promise<void>;
  scheduleImport: (feedId: string, frequency: string) => void;
}

export const useFeedImportsStore = create<FeedImportsStore>()(
  persist(
    (set, get) => ({
      imports: [],
      
      addImport: (feedId, feedName) => set((state) => ({
        imports: [...state.imports, {
          id: crypto.randomUUID(),
          feedId,
          feedName,
          lastImport: null,
          nextImport: null,
          status: 'active',
          importCount: 0
        }]
      })),

      updateImport: (id, updates) => set((state) => ({
        imports: state.imports.map(imp => 
          imp.id === id ? { ...imp, ...updates } : imp
        )
      })),

      removeImport: (id) => set((state) => ({
        imports: state.imports.filter(imp => imp.id !== id)
      })),

      importFeed: async (feedId) => {
        const state = get();
        const feedImport = state.imports.find(imp => imp.feedId === feedId);
        
        if (!feedImport) return;

        try {
          // Update import status
          state.updateImport(feedImport.id, {
            lastImport: new Date().toISOString(),
            importCount: feedImport.importCount + 1,
            status: 'active',
            error: undefined
          });

        } catch (error) {
          state.updateImport(feedImport.id, {
            status: 'error',
            error: error instanceof Error ? error.message : 'Unknown error'
          });
        }
      },

      scheduleImport: (feedId, frequency) => {
        const state = get();
        const feedImport = state.imports.find(imp => imp.feedId === feedId);
        
        if (!feedImport) return;

        let nextImport = new Date();
        
        switch (frequency) {
          case 'hourly':
            nextImport.setHours(nextImport.getHours() + 1);
            break;
          case 'daily':
            nextImport.setDate(nextImport.getDate() + 1);
            break;
          case 'weekly':
            nextImport.setDate(nextImport.getDate() + 7);
            break;
        }

        state.updateImport(feedImport.id, {
          nextImport: nextImport.toISOString(),
          status: 'active'
        });
      }
    }),
    {
      name: 'feed-imports'
    }
  )
);