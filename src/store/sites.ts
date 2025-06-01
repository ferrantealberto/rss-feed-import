import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export interface WordPressSite {
  id: string;
  name: string;
  url: string;
  username: string;
  password: string;
  importSettings: {
    contentLength: 'full' | 'excerpt';
    excerptLength: number;
    importImages: boolean;
    useFirstImageAsFeatured: boolean;
  };
  schedule: {
    enabled: boolean;
    frequency: 'hourly' | 'daily' | 'weekly';
    time?: string;
    days?: string[];
  };
}

const defaultImportSettings = {
  contentLength: 'full' as const,
  excerptLength: 150,
  importImages: true,
  useFirstImageAsFeatured: true,
};

const defaultSchedule = {
  enabled: false,
  frequency: 'daily' as const,
};

interface SitesStore {
  sites: WordPressSite[];
  addSite: (site: WordPressSite) => void;
  updateSite: (id: string, site: Partial<WordPressSite>) => void;
  deleteSite: (id: string) => void;
}

export const useSitesStore = create<SitesStore>()(
  persist(
    (set) => ({
      sites: [],
      addSite: (site) => set((state) => ({ 
        sites: [...state.sites, {
          ...site,
          importSettings: {
            ...defaultImportSettings,
            ...site.importSettings
          },
          schedule: {
            ...defaultSchedule,
            ...site.schedule
          }
        }] 
      })),
      updateSite: (id, updates) => set((state) => ({
        sites: state.sites.map(site => 
          site.id === id ? {
            ...site,
            ...updates,
            importSettings: {
              ...site.importSettings,
              ...(updates.importSettings || {})
            },
            schedule: {
              ...site.schedule,
              ...(updates.schedule || {})
            }
          } : site
        )
      })),
      deleteSite: (id) => set((state) => ({
        sites: state.sites.filter(site => site.id !== id)
      }))
    }),
    {
      name: 'wordpress-sites',
      merge: (persistedState: any, currentState: SitesStore) => {
        const mergedState = {
          ...currentState,
          ...persistedState,
          sites: (persistedState as SitesStore).sites.map((site: WordPressSite) => ({
            ...site,
            importSettings: {
              ...defaultImportSettings,
              ...site.importSettings
            },
            schedule: {
              ...defaultSchedule,
              ...site.schedule
            }
          }))
        };
        return mergedState;
      }
    }
  )
);