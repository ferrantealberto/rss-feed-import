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
        sites: [...state.sites, site] 
      })),
      updateSite: (id, updates) => set((state) => ({
        sites: state.sites.map(site => 
          site.id === id ? { ...site, ...updates } : site
        )
      })),
      deleteSite: (id) => set((state) => ({
        sites: state.sites.filter(site => site.id !== id)
      }))
    }),
    {
      name: 'wordpress-sites'
    }
  )
);