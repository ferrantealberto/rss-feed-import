import { NavLink, Outlet } from 'react-router-dom';

export function Layout() {
  return (
    <div className="app-container">
      <nav className="sidebar">
        <ul className="nav-list">
          <li className="nav-item">
            <NavLink to="/dashboard" className={({ isActive }) => 
              `nav-link ${isActive ? 'active' : ''}`
            }>
              Dashboard
            </NavLink>
          </li>
          <li className="nav-item">
            <NavLink to="/feeds" className={({ isActive }) => 
              `nav-link ${isActive ? 'active' : ''}`
            }>
              Feed Manager
            </NavLink>
          </li>
          <li className="nav-item">
            <NavLink to="/posts" className={({ isActive }) => 
              `nav-link ${isActive ? 'active' : ''}`
            }>
              Imported Posts
            </NavLink>
          </li>
          <li className="nav-item">
            <NavLink to="/settings" className={({ isActive }) => 
              `nav-link ${isActive ? 'active' : ''}`
            }>
              Settings
            </NavLink>
          </li>
        </ul>
      </nav>
      
      <main className="main-content">
        <Outlet />
      </main>
    </div>
  );
}