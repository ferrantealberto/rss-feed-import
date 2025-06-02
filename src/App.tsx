import { BrowserRouter } from 'react-router-dom';
import { AppRoutes } from './routes';
import { AuthProvider } from './contexts/AuthContext';
import { AuthButton } from './components/AuthButton';
import './App.css';

function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <AuthButton />
        <AppRoutes />
      </BrowserRouter>
    </AuthProvider>
  );
}

export default App;