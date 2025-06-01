export function FeedManager() {
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
          <button type="submit" className="button button-primary">Add Feed</button>
        </form>
      </div>

      <div className="card">
        <h2 className="card-title">Managed Feeds</h2>
        <table className="table">
          <thead>
            <tr>
              <th>Name</th>
              <th>URL</th>
              <th>Frequency</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Tech News</td>
              <td>https://example.com/feed</td>
              <td>Daily</td>
              <td>Active</td>
              <td>
                <button className="button button-secondary">Edit</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  );
}