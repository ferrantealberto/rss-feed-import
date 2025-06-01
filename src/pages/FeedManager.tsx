import { useState } from 'react';
import { useSitesStore } from '../store/sites';
import { useOpenRouterStore } from '../store/openrouter';
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

export function FeedManager() {
  const { sites } = useSitesStore();
  const { rewriteContent } = useOpenRouterStore();
  const { posts: scheduledPosts, addPost, removePost, togglePostStatus, reschedulePost } = useScheduledPostsStore();
  const [selectedSite, setSelectedSite] = useState('');
  const [lastPostTime, setLastPostTime] = useState<{[key: string]: number}>({});
  const [feeds, setFeeds] = useState<Feed[]>([]);

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
      
      setFeeds(prevFeeds => [...prevFeeds, ...newFeeds]);
      alert(`Successfully imported ${newFeeds.length} feeds`);
    } catch (error) {
      console.error('Error importing feeds:', error);
      alert('Failed to import feeds');
    }
  };

  const handleDeleteFeed = (feedId: string) => {
    if (window.confirm('Are you sure you want to delete this feed? This will also remove all scheduled posts from this feed.')) {
      setFeeds(feeds.filter(feed => feed.id !== feedId));
      // Remove all scheduled posts from this feed
      scheduledPosts
        .filter(post => post.feedId === feedId)
        .forEach(post => removePost(post.id));
    }
  };

  const handlePublishToSite = async (post: any) => {
    if (!selectedSite) {
      alert('Please select a site first');
      return;
    }

    // Check if we've posted to this site recently from this feed
    const siteKey = `${selectedSite}-${post.feedId}`;
    const lastTime = lastPostTime[siteKey] || 0;
    const now = Date.now();
    
    if (now - lastTime < 3600000) { // 1 hour minimum between posts from same feed
      alert('Please wait before posting another article from this feed to the same site');
      return;
    }

    try {
      // Rewrite content before publishing
      const { content: rewrittenContent, seo } = await rewriteContent(post.content);
      
      // Update last post time
      setLastPostTime({
        ...lastPostTime,
        [siteKey]: now
      });

      // TODO: Implement actual WordPress post creation
      console.log('Publishing to site:', selectedSite, {
        content: rewrittenContent,
        title: seo.title,
        excerpt: seo.description,
        categories: seo.categories,
        tags: seo.tags,
        meta: {
          _yoast_wpseo_metadesc: seo.description,
          _yoast_wpseo_focuskw: seo.keywords.join(', ')
        }
      });
      
    } catch (error) {
      console.error('Error publishing post:', error);
      alert('Failed to publish post');
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
              <option value="hourly">Hourly</option>
              <option value="daily">Daily</option>
              <option value="weekly">Weekly</option>
            </select>
          </div>
          <div className="form-group">
            <label className="form-label">Target Site</label>
            <select 
              className="form-input"
              value={selectedSite}
              onChange={(e) => setSelectedSite(e.target.value)}
            >
              <option value="">Select a site</option>
              {sites.map(site => (
                <option key={site.id} value={site.id}>
                  {site.name}
                </option>
              ))}
            </select>
          </div>
          <button type="submit" className="button button-primary">Add Feed</button>
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
                  value={selectedSite}
                  onChange={(e) => {
                    const updatedFeeds = feeds.map(f => 
                      f.id === feed.id ? { ...f, siteId: e.target.value } : f
                    );
                    setFeeds(updatedFeeds);
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
                    className="button button-secondary"
                    onClick={() => handlePublishToSite({
                      feedId: feed.id,
                      content: 'Sample content'
                    })}
                  >
                    Test Post
                  </button>
                  <button className="button button-secondary">Edit</button>
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
    </div>
  );
}