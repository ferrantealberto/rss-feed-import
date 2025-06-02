import { useForm, Controller } from 'react-hook-form';
import { useOpenRouterStore } from '../store/openrouter';
import { useSitesStore, WordPressSite, SiteUpdates } from '../store/sites';
import { v4 as uuidv4 } from 'uuid';
import { useState, useRef } from 'react';

export function Settings() {
  const { 
    apiKey, 
    setApiKey, 
    verifyApiKey,
    availableModels,
    selectedModel,
    setSelectedModel,
    rewriteTone,
    setRewriteTone,
    isLoading,
    error
  } = useOpenRouterStore();
  const { sites, addSite, updateSite, deleteSite } = useSitesStore();
  const [searchTerm, setSearchTerm] = useState('');
  const [modifiedSites, setModifiedSites] = useState<Set<string>>(new Set());
  const originalSites = useRef<{[key: string]: WordPressSite}>({});

  const handleSiteChange = (siteId: string, updates: Partial<WordPressSite>) => {
    if (!originalSites.current[siteId]) {
      originalSites.current[siteId] = sites.find(s => s.id === siteId) || {} as WordPressSite;
    }
    
    updateSite(siteId, updates);
    setModifiedSites(prev => new Set(prev).add(siteId));
  };

  const handleSaveSite = async (siteId: string) => {
    try {
      const site = sites.find(s => s.id === siteId);
      if (!site) return;

      // Test connection before saving
      const response = await fetch(`${site.url}/wp-json/wp/v2/posts`, {
        method: 'GET',
        headers: {
          'Authorization': `Basic ${btoa(site.username + ':' + site.password)}`,
        }
      });

      if (!response.ok) {
        throw new Error('Failed to connect to WordPress site');
      }

      // Clear modified state for this site
      setModifiedSites(prev => {
        const next = new Set(prev);
        next.delete(siteId);
        return next;
      });
      delete originalSites.current[siteId];

      alert('Site settings saved successfully!');
    } catch (error) {
      alert(`Error saving site settings: ${error instanceof Error ? error.message : 'Unknown error'}`);
    }
  };

  const handleCancelSite = (siteId: string) => {
    const originalSite = originalSites.current[siteId];
    if (originalSite) {
      updateSite(siteId, originalSite);
      setModifiedSites(prev => {
        const next = new Set(prev);
        next.delete(siteId);
        return next;
      });
      delete originalSites.current[siteId];
    }
  };

  const handleVerifyKey = async () => {
    await verifyApiKey();
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
      importSettings: {
        contentLength: 'full',
        excerptLength: 150,
        importImages: true,
        useFirstImageAsFeatured: true
      },
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
              disabled={isLoading || !apiKey}
              className={isLoading ? 'loading' : ''}
            >
              {isLoading ? 'Verifying...' : 'Verify'}
            </button>
          </div>
          {error && (
            <p className="error-message" style={{ color: 'red', marginTop: '8px' }}>
              {error}
            </p>
          )}
          <small>Your API key is encrypted and never shared</small>
        </div>

        {/* Model Selection */}
        {availableModels.length > 0 && (
          <div className="model-selection">
            <h3>AI Model Selection</h3>
            
            <div className="rewrite-tone" style={{ marginBottom: '20px' }}>
              <label>Content Rewrite Tone:</label>
              <select 
                value={rewriteTone}
                onChange={(e) => setRewriteTone(e.target.value as any)}
                style={{ marginLeft: '10px' }}
              >
                <option value="professional">Professional</option>
                <option value="casual">Casual</option>
                <option value="academic">Academic</option>
                <option value="journalistic">Journalistic</option>
                <option value="creative">Creative</option>
              </select>
            </div>
            
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
                onChange={(e) => handleSiteChange(site.id, { name: e.target.value })}
                placeholder="Site Name"
              />
              <input
                type="url"
                value={site.url}
                onChange={(e) => handleSiteChange(site.id, { url: e.target.value })}
                placeholder="WordPress URL"
              />
              <input
                type="text"
                value={site.username}
                onChange={(e) => handleSiteChange(site.id, { username: e.target.value })}
                placeholder="Username"
              />
              <input
                type="password"
                value={site.password}
                onChange={(e) => handleSiteChange(site.id, { password: e.target.value })}
                placeholder="Application Password"
              />
              
              {modifiedSites.has(site.id) && (
                <div style={{ display: 'flex', gap: '10px', marginTop: '10px' }}>
                  <button
                    className="button button-primary"
                    onClick={() => handleSaveSite(site.id)}
                  >
                    Save Changes
                  </button>
                  <button
                    className="button button-secondary"
                    onClick={() => handleCancelSite(site.id)}
                  >
                    Cancel
                  </button>
                </div>
              )}

              <div className="import-settings" style={{ marginTop: '15px', padding: '15px', border: '1px solid #ddd', borderRadius: '4px' }}>
                <h4 style={{ margin: '0 0 10px 0' }}>Import Settings</h4>
                
                <div style={{ marginBottom: '10px' }}>
                  <label style={{ display: 'block', marginBottom: '5px' }}>Content Length:</label>
                  <select
                    value={site.importSettings.contentLength}
                    onChange={(e) => updateSite(site.id, {
                      importSettings: { ...site.importSettings, contentLength: e.target.value as 'full' | 'excerpt' }
                    })}
                  >
                    <option value="full">Full Content</option>
                    <option value="excerpt">Excerpt Only</option>
                  </select>
                </div>
                
                {site.importSettings.contentLength === 'excerpt' && (
                  <div style={{ marginBottom: '10px' }}>
                    <label style={{ display: 'block', marginBottom: '5px' }}>Excerpt Length (words):</label>
                    <input
                      type="number"
                      min="50"
                      max="500"
                      value={site.importSettings.excerptLength}
                      onChange={(e) => updateSite(site.id, {
                        importSettings: { ...site.importSettings, excerptLength: parseInt(e.target.value) }
                      })}
                    />
                  </div>
                )}
                
                <div style={{ marginBottom: '10px' }}>
                  <label>
                    <input
                      type="checkbox"
                      checked={site.importSettings.importImages}
                      onChange={(e) => updateSite(site.id, {
                        importSettings: { ...site.importSettings, importImages: e.target.checked }
                      })}
                    />
                    Import Images
                  </label>
                </div>
                
                {site.importSettings.importImages && (
                  <div>
                    <label>
                      <input
                        type="checkbox"
                        checked={site.importSettings.useFirstImageAsFeatured}
                        onChange={(e) => updateSite(site.id, {
                          importSettings: { ...site.importSettings, useFirstImageAsFeatured: e.target.checked }
                        })}
                      />
                      Use First Image as Featured Image
                    </label>
                  </div>
                )}
              </div>

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