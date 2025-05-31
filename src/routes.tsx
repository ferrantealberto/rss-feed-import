import { Routes, Route, Navigate } from 'react-router-dom';
import { Layout } from './components/Layout';
import { Dashboard } from './pages/Dashboard';
import { FeedManager } from './pages/FeedManager';
import { Settings } from './pages/Settings';
import { ImportedPosts } from './pages/ImportedPosts';

export function AppRoutes() {
  return (
    <Routes>
      <Route path="/" element={<Layout />}>
        <Route index element={<Navigate to="/dashboard\" replace />} />
        <Route path="dashboard" element={<Dashboard />} />
        <Route path="feeds" element={<FeedManager />} />
        <Route path="posts" element={<ImportedPosts />} />
        <Route path="settings" element={<Settings />} />
      </Route>
    </Routes>
  );
}