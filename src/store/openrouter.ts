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
  isLoading: boolean;
  error: string | null;
  setApiKey: (key: string) => void;
  setSelectedModel: (modelId: string) => void;
  setAvailableModels: (models: AIModel[]) => void;
  setError: (error: string | null) => void;
  verifyApiKey: () => Promise<boolean>;
  fetchModels: () => Promise<void>;
}

export const useOpenRouterStore = create<OpenRouterStore>()(
  persist(
    (set, get) => ({
      apiKey: '',
      selectedModel: null,
      availableModels: [],
      isLoading: false,
      error: null,
      
      setApiKey: (key) => set({ apiKey: key }),
      setSelectedModel: (modelId) => set({ selectedModel: modelId }),
      setAvailableModels: (models) => set({ availableModels: models }),
      setError: (error) => set({ error }),
      
      verifyApiKey: async () => {
        const { apiKey } = get();
        set({ isLoading: true, error: null });
        
        try {
          const response = await fetch('https://openrouter.ai/api/v1/models', {
            headers: {
              'Authorization': `Bearer ${apiKey}`,
              'HTTP-Referer': window.location.origin,
            }
          });
          
          if (!response.ok) {
            throw new Error('Invalid API key');
          }
          
          const models = await response.json();
          set({ availableModels: models.data });
          return true;
        } catch (error) {
          set({ error: error instanceof Error ? error.message : 'Failed to verify API key' });
          console.error('API key verification failed:', error);
          return false;
        } finally {
          set({ isLoading: false });
        }
      }
    }),
    {
      name: 'openrouter-settings'
    }
  )
);