import { create } from 'zustand';
import { persist } from 'zustand/middleware';

interface Settings {
  wordpressUrl: string;
  wordpressUsername: string;
  wordpressPassword: string;
  openrouterApiKey: string;
  selectedModel: string;
  rewriteTone: string;
}

interface SettingsStore extends Settings {
  updateSettings: (settings: Partial<Settings>) => void;
}

export const useSettingsStore = create<SettingsStore>()(
  persist(
    (set) => ({
      wordpressUrl: '',
      wordpressUsername: '',
      wordpressPassword: '',
      openrouterApiKey: '',
      selectedModel: 'anthropic/claude-2',
      rewriteTone: 'professional',
      updateSettings: (newSettings) => set((state) => ({ ...state, ...newSettings })),
    }),
    {
      name: 'rss-importer-settings',
    }
  )
);