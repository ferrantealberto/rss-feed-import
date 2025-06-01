import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export interface AIModel {
  id: string;
  name: string;
  context: string;
  promptCost: string;
  completionCost: string;
}

export type RewriteTone = 'professional' | 'casual' | 'academic' | 'journalistic' | 'creative';

interface OpenRouterStore {
  apiKey: string;
  selectedModel: string | null;
  rewriteTone: RewriteTone;
  availableModels: AIModel[]; 
  isLoading: boolean;
  error: string | null;
  setApiKey: (key: string) => void;
  setSelectedModel: (modelId: string) => void;
  setRewriteTone: (tone: RewriteTone) => void;
  setAvailableModels: (models: AIModel[]) => void;
  setError: (error: string | null) => void;
  verifyApiKey: () => Promise<boolean>;
  fetchModels: () => Promise<void>;
  rewriteContent: (content: string) => Promise<string>;
}

export const useOpenRouterStore = create<OpenRouterStore>()(
  persist(
    (set, get) => ({
      apiKey: '',
      selectedModel: null,
      rewriteTone: 'professional',
      availableModels: [],
      isLoading: false,
      error: null,
      
      setApiKey: (key) => set({ apiKey: key }),
      setSelectedModel: (modelId) => set({ selectedModel: modelId }),
      setRewriteTone: (tone) => set({ rewriteTone: tone }),
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
      },
      
      rewriteContent: async (content: string) => {
        const { apiKey, selectedModel, rewriteTone } = get();
        
        if (!apiKey || !selectedModel) {
          throw new Error('API key or model not configured');
        }
        
        try {
          const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
            method: 'POST',
            headers: {
              'Authorization': `Bearer ${apiKey}`,
              'Content-Type': 'application/json',
              'HTTP-Referer': window.location.origin
            },
            body: JSON.stringify({
              model: selectedModel,
              messages: [
                {
                  role: 'system',
                  content: `Rewrite the following content in a ${rewriteTone} tone while maintaining key information and making it unique.`
                },
                {
                  role: 'user',
                  content
                }
              ]
            })
          });

          if (!response.ok) {
            throw new Error('Failed to rewrite content');
          }

          const data = await response.json();
          return data.choices[0].message.content;
          
        } catch (error) {
          console.error('Error rewriting content:', error);
          throw error;
        }
      }
    }),
    {
      name: 'openrouter-settings'
    }
  )
);