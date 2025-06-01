import { useForm } from 'react-hook-form';
import { useSettingsStore } from '../store/settings';

const models = [
  { value: 'anthropic/claude-2', label: 'Claude 2' },
  { value: 'google/palm-2-chat-bison', label: 'PaLM 2 Chat' },
  { value: 'meta-llama/llama-2-70b-chat', label: 'Llama 2 70B' },
  { value: 'openai/gpt-3.5-turbo', label: 'GPT-3.5 Turbo' },
  { value: 'openai/gpt-4', label: 'GPT-4' }
];

const tones = [
  { value: 'professional', label: 'Professional' },
  { value: 'casual', label: 'Casual' },
  { value: 'academic', label: 'Academic' },
  { value: 'journalistic', label: 'Journalistic' },
  { value: 'creative', label: 'Creative' }
];

export function Settings() {
  const settings = useSettingsStore();
  const { register, handleSubmit } = useForm({
    defaultValues: settings
  });

  const onSubmit = (data: any) => {
    settings.updateSettings(data);
  };

  return (
    <div>
      <h1 className="card-title">Settings</h1>

      <form onSubmit={handleSubmit(onSubmit)}>
        <div className="card">
          <h2 className="card-title">WordPress Connection</h2>
          <div className="form-group">
            <label className="form-label">WordPress Site URL</label>
            <input 
              type="url" 
              className="form-input"
              placeholder="https://your-wordpress-site.com"
              {...register('wordpressUrl')}
            />
          </div>
          
          <div className="form-group">
            <label className="form-label">WordPress Username</label>
            <input 
              type="text" 
              className="form-input"
              {...register('wordpressUsername')}
            />
          </div>

          <div className="form-group">
            <label className="form-label">WordPress Password</label>
            <input 
              type="password" 
              className="form-input"
              {...register('wordpressPassword')}
            />
            <p className="form-help">Use an application password for better security</p>
          </div>
        </div>

        <div className="card">
          <h2 className="card-title">OpenRouter Configuration</h2>
          <div className="form-group">
            <label className="form-label">API Key</label>
            <input 
              type="password" 
              className="form-input"
              {...register('openrouterApiKey')}
            />
          </div>

          <div className="form-group">
            <label className="form-label">AI Model</label>
            <select className="form-input" {...register('selectedModel')}>
              {models.map(model => (
                <option key={model.value} value={model.value}>
                  {model.label}
                </option>
              ))}
            </select>
          </div>

          <div className="form-group">
            <label className="form-label">Rewriting Tone</label>
            <select className="form-input" {...register('rewriteTone')}>
              {tones.map(tone => (
                <option key={tone.value} value={tone.value}>
                  {tone.label}
                </option>
              ))}
            </select>
          </div>
        </div>

        <div className="card">
          <h2 className="card-title">Import Settings</h2>
          <div className="form-group">
            <label className="form-label">Max Posts per Import</label>
            <input 
              type="number" 
              className="form-input" 
              min="1" 
              max="100" 
              defaultValue="10"
            />
          </div>
          
          <div className="form-group">
            <label className="form-label">Default Post Status</label>
            <select className="form-input">
              <option value="draft">Draft</option>
              <option value="publish">Published</option>
              <option value="pending">Pending Review</option>
            </select>
          </div>

          <div className="form-group">
            <label>
              <input type="checkbox" defaultChecked />
              Import Images Automatically
            </label>
          </div>
        </div>

        <button type="submit" className="button button-primary">
          Save Settings
        </button>
      </form>
    </div>
  );
}