export function Settings() {
  return (
    <div>
      <h1 className="card-title">Settings</h1>

      <div className="card">
        <h2 className="card-title">Import Settings</h2>
        <form>
          <div className="form-group">
            <label className="form-label">Max Posts per Import</label>
            <input type="number" className="form-input" min="1" max="100" defaultValue="10" />
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

          <button type="submit" className="button button-primary">Save Settings</button>
        </form>
      </div>

      <div className="card">
        <h2 className="card-title">System Information</h2>
        <table className="table">
          <tbody>
            <tr>
              <td>Version</td>
              <td>1.0.0</td>
            </tr>
            <tr>
              <td>Last Import</td>
              <td>2024-01-15 15:30</td>
            </tr>
            <tr>
              <td>Next Scheduled Import</td>
              <td>2024-01-15 16:30</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  );
}