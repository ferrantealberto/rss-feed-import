import { useAuth } from '../contexts/AuthContext';
import { supabase } from '../lib/supabaseClient';

export function AuthButton() {
  const { user, signOut } = useAuth();

  const handleSignIn = async () => {
    await supabase.auth.signInWithOAuth({
      provider: 'github',
      options: {
        redirectTo: window.location.origin
      }
    });
  };

  return (
    <button 
      onClick={user ? signOut : handleSignIn}
      className="button button-primary"
      style={{ position: 'absolute', top: '1rem', right: '1rem' }}
    >
      {user ? 'Sign Out' : 'Connect to Supabase'}
    </button>
  );
}