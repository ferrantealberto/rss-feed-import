import { useForm, Controller } from 'react-hook-form';
import { useOpenRouterStore } from '../store/openrouter';
import { useSitesStore, WordPressSite } from '../store/sites';
import { v4 as uuidv4 } from 'uuid';
import { useState } from 'react';

export function Settings() {
  const { apiKey, setApiKey, verifyApiKey, fetchModels, availableModels, selectedModel, setSelectedModel } = useOpenRouterStore();
  const { sites, addSite, updateSite, deleteSite } = useSitesStore();
  const [isVerifying, setIsVerifying] = useState(false);
  const [searchTerm, setSearchTerm] = useState('');

  const handleVerifyKey = async () => {
    setIsVerifying(true);
    try {
      const isValid = await verifyApiKey();
      if (isValid) {
        await fetchModels();
      }
    } finally {
      setIsVerifying(false);
    }
  };

  const filteredModels = availableModels.filter(model => 
    model.name.toLowerCase().includes(searchTerm.toLowerCase())
  );

  const addNewSite = () => {
    const newSite: WordPressSite = {
      id: uuidv4(),
      name: '',
      url: '',
      username: '',
      password: '',
      schedule: {
        enabled: false,
        frequency: 'daily'
      }
    };
    addSite(newSite);
  };

  return (
    <div>
      <div className="settings-header">
        <h1>Settings</h1>
      </div>

      {/* API Settings */}
      <div className="settings-section">
        <h2>OpenRouter API Settings</h2>
        <div className="api-key-section">
          <label>API Key</label>
          <div className="api-key-input">
            <input
              type="password"
              value={apiKey}
              onChange={(e) => setApiKey(e.target.value)}
              placeholder="Enter your OpenRouter API key"
            />
            <button 
              onClick={handleVerifyKey}
              disabled={isVerifying || !apiKey}
            >
              {isVerifying ? 'Verifying...' : 'Verify'}
            </button>
          </div>
          <small>Your API key is encrypted and never shared</small>
        </div>

        {/* Model Selection */}
        {availableModels.length > 0 && (
          <div className="model-selection">
            <h3>AI Model Selection</h3>
            <input
              type="text"
              placeholder="Search models..."
              value={searchTerm}
              onChange={(e) => setSearchTerm(e.target.value)}
              className="model-search"
            />
            <div className="models-grid">
              {filteredModels.map(model => (
                <div 
                  key={model.id}
                  className={`model-card ${selectedModel === model.id ? 'selected' : ''}`}
                  onClick={() => setSelectedModel(model.id)}
                >
                  <h4>{model.name}</h4>
                  <div className="model-details">
                    <span>Context: {model.context}</span>
                    <span>Prompt: {model.promptCost}</span>
                    <span>Completion: {model.completionCost}</span>
                  </div>
                </div>
              ))}
            </div>
          </div>
        )}
      </div>

      {/* WordPress Sites */}
      <div className="settings-section">
        <div className="sites-header">
          <h2>WordPress Sites</h2>
          <button onClick={addNewSite}>Add New Site</button>
        </div>

        <div className="sites-grid">
          {sites.map(site => (
            <div key={site.id} className="site-card">
              <input
                type="text"
                value={site.name}
                onChange={(e) => updateSite(site.id, { name: e.target.value })}
                placeholder="Site Name"
              />
              <input
                type="url"
                value={site.url}
                onChange={(e) => updateSite(site.id, { url: e.target.value })}
                placeholder="WordPress URL"
              />
              <input
                type="text"
                value={site.username}
                onChange={(e) => updateSite(site.id, { username: e.target.value })}
                placeholder="Username"
              />
              <input
                type="password"
                value={site.password}
                onChange={(e) => updateSite(site.id, { password: e.target.value })}
                placeholder="Application Password"
              />

              <div className="schedule-settings">
                <label>
                  <input
                    type="checkbox"
                    checked={site.schedule.enabled}
                    onChange={(e) => updateSite(site.id, {
                      schedule: { ...site.schedule, enabled: e.target.checked }
                    })}
                  />
                  Enable Scheduling
                </label>

                {site.schedule.enabled && (
                  <div className="schedule-options">
                    <select
                      value={site.schedule.frequency}
                      onChange={(e) => updateSite(site.id, {
                        schedule: { ...site.schedule, frequency: e.target.value }
                      })}
                    >
                      <option value="hourly">Hourly</option>
                      <option value="daily">Daily</option>
                      <option value="weekly">Weekly</option>
                    </select>

                    {site.schedule.frequency === 'daily' && (
                      <input
                        type="time"
                        value={site.schedule.time}
                        onChange={(e) => updateSite(site.id, {
                          schedule: { ...site.schedule, time: e.target.value }
                        })}
                      />
                    )}

                    {site.schedule.frequency === 'weekly' && (
                      <div className="day-selector">
                        {['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].map(day => (
                          <label key={day}>
                            <input
                              type="checkbox"
                              checked={site.schedule.days?.includes(day)}
                              onChange={(e) => {
                                const days = site.schedule.days || [];
                                const newDays = e.target.checked
                                  ? [...days, day]
                                  : days.filter(d => d !== day);
                                updateSite(site.id, {
                                  schedule: { ...site.schedule, days: newDays }
                                });
                              }}
                            />
                            {day}
                          </label>
                        ))}
                      </div>
                    )}
                  </div>
                )}
              </div>

              <button 
                onClick={() => deleteSite(site.id)}
                className="delete-site"
              >
                Delete Site
              </button>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}