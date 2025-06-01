import { useState } from 'react';
import { useSitesStore } from '../store/sites';
import { useOpenRouterStore } from '../store/openrouter';
import { CSVImport } from '../components/CSVImport';

export function FeedManager() {
  const { sites } = useSitesStore();
  const { rewriteContent } = useOpenRouterStore();
  const [selectedSite, setSelectedSite] = useState('');
  const [lastPostTime, setLastPostTime] = useState<{[key: string]: number}>({});

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

  const handleCSVImport = async (feeds: any[]) => {
    try {
      // Here you would typically batch process the feeds
      for (const feed of feeds) {
        // Add validation and processing logic here
        console.log('Importing feed:', feed);
      }
      alert(`Successfully processed ${feeds.length} feeds`);
    } catch (error) {
      console.error('Error importing feeds:', error);
      alert('Failed to import feeds');
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
            <tr>
              <td>Tech News</td>
              <td>https://example.com/feed</td>
              <td>Daily</td>
              <td>
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
              </td>
              <td>Active</td>
              <td>
                <div className="button-group">
                  <button 
                    className="button button-secondary"
                    onClick={() => handlePublishToSite({
                      feedId: 'tech-news',
                      content: 'Sample content'
                    })}
                  >
                    Test Post
                  </button>
                  <button className="button button-secondary">Edit</button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  );
}