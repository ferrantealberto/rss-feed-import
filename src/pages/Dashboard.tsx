export function Dashboard() {
  const stats = {
    activeFeeds: 5,
    postsImported: 128,
    imagesImported: 96,
    scheduledImports: 3
  };

  return (
    <div>
      <h1 className="card-title">Dashboard</h1>
      
      <div className="stats-grid">
        <div className="stat-card">
          <h2 className="stat-value">{stats.activeFeeds}</h2>
          <p className="stat-label">Active Feeds</p>
        </div>
        <div className="stat-card">
          <h2 className="stat-value">{stats.postsImported}</h2>
          <p className="stat-label">Posts Imported</p>
        </div>
        <div className="stat-card">
          <h2 className="stat-value">{stats.imagesImported}</h2>
          <p className="stat-label">Images Imported</p>
        </div>
        <div className="stat-card">
          <h2 className="stat-value">{stats.scheduledImports}</h2>
          <p className="stat-label">Scheduled Imports</p>
        </div>
      </div>

      <div className="card">
        <h2 className="card-title">Recent Activity</h2>
        <table className="table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Feed</th>
              <th>Action</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>2024-01-15</td>
              <td>Tech News</td>
              <td>Import</td>
              <td>Success</td>
            </tr>
            <tr>
              <td>2024-01-15</td>
              <td>Blog Feed</td>
              <td>Import</td>
              <td>Success</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  );
}