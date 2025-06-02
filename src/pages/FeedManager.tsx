import { useState } from 'react';
import { useSitesStore } from '../store/sites';
import { useFeedsStore } from '../store/feeds';
import { useOpenRouterStore } from '../store/openrouter';
import { useFeedImportsStore } from '../store/feedImports';
import { useScheduledPostsStore, ScheduledPost } from '../store/scheduledPosts';
import { CSVImport } from '../components/CSVImport';

interface ImportedFeed {
  categoria_feed: string;
  nome_fonte: string;
  url_rss: string;
  descrizione_feed: string;
  frequenza: string;
  tipo_contenuto: string;
  priorita: number;
  status: string;
}

interface Feed {
  id: string;
  name: string;
  url: string;
  frequency: string;
  status: string;
  siteId?: string;
  description?: string;
  category?: string;
  contentType?: string;
  priority?: number;
}

interface EditingFeed extends Feed {
  postStatus: 'draft' | 'publish' | 'pending' | 'private';
  scheduledTime?: string;
  scheduledDays?: string[];
}

export function FeedManager() {
  const { sites } = useSitesStore();
  const { feeds, addFeeds, updateFeed, deleteFeed } = useFeedsStore();
  const { rewriteContent } = useOpenRouterStore();
  const { posts: scheduledPosts, addPost, removePost, togglePostStatus, reschedulePost } = useScheduledPostsStore();
  const { imports, addImport, importFeed, scheduleImport } = useFeedImportsStore();
  const [editingFeed, setEditingFeed] = useState<EditingFeed | null>(null);
  const [lastPostTime, setLastPostTime] = useState<{[key: string]: number}>({});
  const [selectedSite, setSelectedSite] = useState<string>('');

  const handleEditFeed = (feed: Feed) => {
    setEditingFeed({
      ...feed,
      postStatus: 'draft',
      scheduledTime: '09:00',
      scheduledDays: ['Mon', 'Wed', 'Fri']
    });
  };

  const handleSaveEdit = () => {
    if (!editingFeed) return;

    // Add or update import schedule
    const existingImport = imports.find(imp => imp.feedId === editingFeed.id);
    if (!existingImport) {
      addImport(editingFeed.id, editingFeed.name);
    }
    scheduleImport(editingFeed.id, editingFeed.frequency);

    updateFeed(editingFeed.id, {
      ...editingFeed
    });

    // Schedule posts if needed
    if (editingFeed.scheduledTime && editingFeed.scheduledDays?.length) {
      const site = sites.find(s => s.id === editingFeed.siteId);
      if (site) {
        editingFeed.scheduledDays.forEach(day => {
          // Create a scheduled post for each selected day
          const scheduledDate = getNextDayTime(day, editingFeed.scheduledTime!);
          addPost({
            feedId: editingFeed.id,
            feedName: editingFeed.name,
            title: `Scheduled post from ${editingFeed.name}`,
            content: '', // Will be fetched when publishing
            siteId: editingFeed.siteId!,
            siteName: site.name,
            scheduledDate: scheduledDate,
            rewriteTone: 'professional',
          });
        });
      }
    }

    setEditingFeed(null);
  };

  const getNextDayTime = (day: string, time: string): string => {
    const days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    const today = new Date();
    const targetDay = days.indexOf(day);
    const currentDay = today.getDay();
    
    let daysToAdd = targetDay - currentDay;
    if (daysToAdd <= 0) daysToAdd += 7;
    
    const targetDate = new Date(today);
    targetDate.setDate(today.getDate() + daysToAdd);
    const [hours, minutes] = time.split(':');
    targetDate.setHours(parseInt(hours), parseInt(minutes), 0, 0);
    
    return targetDate.toISOString().slice(0, 16);
  };

  const handleCSVImport = async (importedFeeds: ImportedFeed[]) => {
    try {
      const newFeeds = importedFeeds.map(feed => ({
        id: crypto.randomUUID(),
        name: feed.nome_fonte,
        url: feed.url_rss,
        frequency: feed.frequenza,
        status: feed.status,
        description: feed.descrizione_feed,
        category: feed.categoria_feed,
        contentType: feed.tipo_contenuto,
        priority: feed.priorita
      }));
      
      addFeeds(newFeeds);
      alert(`Successfully imported ${newFeeds.length} feeds`);
    } catch (error) {
      console.error('Error importing feeds:', error);
      alert('Failed to import feeds');
    }
  };

  const handleDeleteFeed = (feedId: string) => {
    if (window.confirm('Are you sure you want to delete this feed? This will also remove all scheduled posts from this feed.')) {
      deleteFeed(feedId);
      // Remove all scheduled posts from this feed
      scheduledPosts
        .filter(post => post.feedId === feedId)
        .forEach(post => removePost(post.id));
    }
  };

  const handleImportNow = async (feedId: string) => {
    try {
      await importFeed(feedId);
      alert('Feed import started successfully');
    } catch (error) {
      alert('Failed to import feed: ' + (error instanceof Error ? error.message : 'Unknown error'));
    }
  };

  const handlePublishToSite = async (post: any) => {
    const site = sites.find(s => s.id === post.siteId);
    
    if (!site) {
      alert('Please select a valid WordPress site first');
      return;
    }

    if (!site.url || !site.username || !site.password) {
      alert('Selected site is missing required credentials. Please check site settings.');
      return;
    }

    // Check if we've posted to this site recently from this feed
    const siteKey = `${post.siteId}-${post.feedId}`;
    const lastTime = lastPostTime[siteKey] || 0;
    const now = Date.now();
    
    if (now - lastTime < 3600000) { // 1 hour minimum between posts from same feed
      alert(`Please wait at least 1 hour between posts from the same feed to ${site.name}`);
      return;
    }

    try {
      // Rewrite content before publishing
      const { content: rewrittenContent, seo } = await rewriteContent(post.content);

      // Fallback to original title if SEO title is not available
      const postTitle = post.title || seo?.title || 'Untitled Post';

      // Prepara i dati del post
      const postData = {
        title: postTitle,
        content: rewrittenContent,
        excerpt: seo?.description || '',
        status: 'draft',
        categories: seo?.categories || [],
        tags: seo?.tags || [],
        meta: {
          _yoast_wpseo_metadesc: seo?.description || '',
          _yoast_wpseo_focuskw: seo?.keywords?.join(', ') || ''
        }
      };

      // Effettua la richiesta all'API REST di WordPress
      const response = await fetch(`${site.url}/wp-json/wp/v2/posts`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Authorization': `Basic ${btoa(site.username + ':' + site.password)}`,
          'Accept': 'application/json',
          'X-WP-Nonce': site.nonce || ''
        },
        body: JSON.stringify(postData)
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(`WordPress API Error: ${errorData.message || response.statusText}`);
      }
      
      // Aggiorna l'orario dell'ultimo post
      setLastPostTime({
        ...lastPostTime,
        [siteKey]: now
      });
      
      alert(`Post creato come bozza su ${site.name}`);
      
    } catch (error) {
      console.error('Error publishing post:', error);
      alert(`Impossibile pubblicare il post: ${error instanceof Error ? error.message : 'Errore sconosciuto'}`);
    }
  };

  return (
    <div>
      <h1 className="card-title">Feed Manager</h1>

      <div className="card">
        <h2 className="card-title">Add New Feed</h2>
        <form>
          <div className="form-group">
            <label className="form-label">Feed Name</label>
            <input type="text" className="form-input" placeholder="Enter feed name" />
          </div>
          <div className="form-group">
            <label className="form-label">Feed URL</label>
            <input type="url" className="form-input" placeholder="Enter feed URL" />
          </div>
          <div className="form-group">
            <label className="form-label">Import Frequency</label>
            <select className="form-input">
              <option value="hourly">Ogni ora</option>
              <option value="daily">Giornaliero</option>
              <option value="weekly">Settimanale</option>
            </select>
          </div>
          <div className="form-group">
            <label className="form-label">Sito di destinazione</label>
            <select 
              className="form-input"
              value={selectedSite}
              onChange={(e) => setSelectedSite(e.target.value)}
            >
              <option value="">Seleziona un sito</option>
              {sites.map(site => (
                <option key={site.id} value={site.id}>
                  {site.name}
                </option>
              ))}
            </select>
          </div>
          <button type="submit" className="button button-primary">Aggiungi Feed</button>
        </form>
        
        <div style={{ marginTop: '20px', padding: '20px', borderTop: '1px solid #eee' }}>
          <CSVImport onImport={handleCSVImport} />
        </div>
      </div>

      <div className="card">
        <h2 className="card-title">Managed Feeds</h2>
        {feeds.length === 0 ? (
          <p>No feeds configured yet. Add your first feed above.</p>
        ) : (
        <table className="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>URL</th>
              <th>Frequency</th>
              <th>Target Site</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            {feeds.map(feed => (
            <tr key={feed.id}>
              <td>{feed.name}</td>
              <td>{feed.url}</td>
              <td>{feed.frequency}</td>
              <td>
                <select 
                  className="form-input"
                  value={feed.siteId || ''}
                  onChange={(e) => {
                    updateFeed(feed.id, { siteId: e.target.value });
                  }}
                >
                  <option value="">Select a site</option>
                  {sites.map(site => (
                    <option key={site.id} value={site.id}>
                      {site.name}
                    </option>
                  ))}
                </select>
              </td>
              <td>{feed.status}</td>
              <td>
                <div className="button-group">
                  <button 
                    className="button button-primary"
                    onClick={() => handleImportNow(feed.id)}
                  >
                    Import Now
                  </button>
                  <button 
                    className="button button-secondary"
                    onClick={() => handlePublishToSite({
                      feedId: feed.id,
                      content: 'Sample content',
                      title: feed.name,
                      siteId: feed.siteId
                    })}
                  >
                    Test Post
                  </button>
                  <button 
                    className="button button-secondary"
                    onClick={() => handleEditFeed(feed)}
                  >
                    Edit
                  </button>
                  <button 
                    className="button button-secondary"
                    onClick={() => handleDeleteFeed(feed.id)}
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
            ))}
          </tbody>
        </table>)}
      </div>
      
      {/* Edit Feed Modal */}
      {editingFeed && (
        <div className="modal" style={{ display: 'block' }}>
          <div className="modal-content">
            <h3>Edit Feed: {editingFeed.name}</h3>
            
            <div className="form-group">
              <label>Post Status</label>
              <select
                value={editingFeed.postStatus}
                onChange={(e) => setEditingFeed({
                  ...editingFeed,
                  postStatus: e.target.value as 'draft' | 'publish' | 'pending' | 'private'
                })}
              >
                <option value="draft">Draft</option>
                <option value="publish">Published</option>
                <option value="pending">Pending Review</option>
                <option value="private">Private</option>
              </select>
            </div>

            <div className="form-group">
              <label>Schedule Posts</label>
              <div>
                <label>Time:</label>
                <input
                  type="time"
                  value={editingFeed.scheduledTime}
                  onChange={(e) => setEditingFeed({
                    ...editingFeed,
                    scheduledTime: e.target.value
                  })}
                />
              </div>
              
              <div style={{ marginTop: '10px' }}>
                <label>Days:</label>
                <div className="day-selector">
                  {['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'].map(day => (
                    <label key={day}>
                      <input
                        type="checkbox"
                        checked={editingFeed.scheduledDays?.includes(day)}
                        onChange={(e) => {
                          const days = editingFeed.scheduledDays || [];
                          const newDays = e.target.checked
                            ? [...days, day]
                            : days.filter(d => d !== day);
                          setEditingFeed({
                            ...editingFeed,
                            scheduledDays: newDays
                          });
                        }}
                      />
                      {day}
                    </label>
                  ))}
                </div>
              </div>
            </div>
            
            <div className="button-group" style={{ marginTop: '20px' }}>
              <button 
                className="button button-primary"
                onClick={handleSaveEdit}
              >
                Save Changes
              </button>
              <button
                className="button button-secondary"
                onClick={() => setEditingFeed(null)}
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      )}

      <div className="card">
        <h2 className="card-title">Scheduled Posts</h2>
        {scheduledPosts.length === 0 ? (
          <p>No posts scheduled.</p>
        ) : (
          <table className="table">
            <thead>
              <tr>
                <th>Title</th>
                <th>Feed</th>
                <th>Target Site</th>
                <th>Scheduled For</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              {scheduledPosts.map(post => (
                <tr key={post.id}>
                  <td>{post.title}</td>
                  <td>{post.feedName}</td>
                  <td>{post.siteName}</td>
                  <td>
                    <input 
                      type="datetime-local" 
                      value={post.scheduledDate}
                      onChange={(e) => reschedulePost(post.id, e.target.value)}
                      disabled={post.status === 'published'}
                    />
                  </td>
                  <td>{post.status}</td>
                  <td>
                    <div className="button-group">
                      <button
                        className="button button-secondary"
                        onClick={() => togglePostStatus(post.id)}
                        disabled={post.status === 'published'}
                      >
                        {post.status === 'scheduled' ? 'Pause' : 'Resume'}
                      </button>
                      <button
                        className="button button-secondary"
                        onClick={() => removePost(post.id)}
                        disabled={post.status === 'published'}
                      >
                        Remove
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
      
      <div className="card">
        <h2 className="card-title">Import History</h2>
        {imports.length === 0 ? (
          <p>No imports yet.</p>
        ) : (
          <table className="table">
            <thead>
              <tr>
                <th>Feed</th>
                <th>Last Import</th>
                <th>Next Import</th>
                <th>Status</th>
                <th>Import Count</th>
              </tr>
            </thead>
            <tbody>
              {imports.map(imp => (
                <tr key={imp.id}>
                  <td>{imp.feedName}</td>
                  <td>{imp.lastImport ? new Date(imp.lastImport).toLocaleString() : 'Never'}</td>
                  <td>{imp.nextImport ? new Date(imp.nextImport).toLocaleString() : 'Not scheduled'}</td>
                  <td>
                    <span className={`status-${imp.status}`}>
                      {imp.status}
                      {imp.error && <span title={imp.error}>⚠️</span>}
                    </span>
                  </td>
                  <td>{imp.importCount}</td>
                </tr>
              ))}
            </tbody>
          </table>
        )}
      </div>
    </div>
  );
}