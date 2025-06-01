export function ImportedPosts() {
  return (
    <div>
      <h1 className="card-title">Imported Posts</h1>

      <div className="card">
        <div className="form-group">
          <label className="form-label">Filter by Feed</label>
          <select className="form-input">
            <option value="">All Feeds</option>
            <option value="1">Tech News</option>
            <option value="2">Blog Feed</option>
          </select>
        </div>

        <table className="table">
          <thead>
            <tr>
              <th>Title</th>
              <th>Feed</th>
              <th>Status</th>
              <th>Import Date</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Sample Post Title</td>
              <td>Tech News</td>
              <td>Published</td>
              <td>2024-01-15</td>
              <td>
                <button className="button button-secondary">View</button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  );
}