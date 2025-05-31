import { useState } from 'react';
import { Outlet, useNavigate } from 'react-router-dom';

export function Layout() {
  const [mobileOpen, setMobileOpen] = useState(false);
  const navigate = useNavigate();

  return (
    <div className="layout">
      <nav className="sidebar">
        <button onClick={() => navigate('/dashboard')}>Dashboard</button>
        <button onClick={() => navigate('/feeds')}>Feed Manager</button>
        <button onClick={() => navigate('/posts')}>Imported Posts</button>
        <button onClick={() => navigate('/settings')}>Settings</button>
      </nav>
      
      <main className="main-content">
        <Outlet />
      </main>
    </div>
  );
}