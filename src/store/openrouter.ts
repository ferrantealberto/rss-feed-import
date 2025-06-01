import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export interface AIModel {
  id: string;
  name: string;
  context: string;
  promptCost: string;
  completionCost: string;
}

interface OpenRouterStore {
  apiKey: string;
  selectedModel: string | null;
  availableModels: AIModel[];
  setApiKey: (key: string) => void;
  setSelectedModel: (modelId: string) => void;
  setAvailableModels: (models: AIModel[]) => void;
  verifyApiKey: () => Promise<boolean>;
  fetchModels: () => Promise<void>;
}

export const useOpenRouterStore = create<OpenRouterStore>()(
  persist(
    (set, get) => ({
      apiKey: '',
      selectedModel: null,
      availableModels: [],
      
      setApiKey: (key) => set({ apiKey: key }),
      setSelectedModel: (modelId) => set({ selectedModel: modelId }),
      setAvailableModels: (models) => set({ availableModels: models }),
      
      verifyApiKey: async () => {
        const { apiKey } = get();
        try {
          const response = await fetch('https://openrouter.ai/api/v1/auth/verify', {
            headers: {
              'Authorization': `Bearer ${apiKey}`,
              'HTTP-Referer': window.location.origin,
            }
          });
          return response.ok;
        } catch (error) {
          console.error('API key verification failed:', error);
          return false;
        }
      },
      
      fetchModels: async () => {
        const { apiKey } = get();
        try {
          const response = await fetch('https://openrouter.ai/api/v1/models', {
            headers: {
              'Authorization': `Bearer ${apiKey}`,
              'HTTP-Referer': window.location.origin,
            }
          });
          
          if (!response.ok) throw new Error('Failed to fetch models');
          
          const models = await response.json();
          set({ availableModels: models });
        } catch (error) {
          console.error('Failed to fetch models:', error);
        }
      }
    }),
    {
      name: 'openrouter-settings'
    }
  )
);