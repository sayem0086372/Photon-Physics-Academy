import React, { useEffect, useRef, useState } from 'react';
import type { Session } from '@supabase/supabase-js';
import { LoaderCircle } from 'lucide-react';
import { supabase } from '../../lib/supabase';
import { AdminLayout } from './AdminLayout';

interface Props {
  onExitAdmin: () => void;
}

/** Keeps the entire admin application behind a confirmed Supabase session. */
export const AdminGate: React.FC<Props> = ({ onExitAdmin }) => {
  const [session, setSession] = useState<Session | null>(null);
  const [checking, setChecking] = useState(true);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);
  const [loggingOut, setLoggingOut] = useState(false);
  const [toast, setToast] = useState('');
  const authEventReceived = useRef(false);

  useEffect(() => {
    if (!supabase) {
      setChecking(false);
      return;
    }

    let active = true;
    const { data: { subscription } } = supabase.auth.onAuthStateChange((_event, nextSession) => {
      authEventReceived.current = true;
      if (active) setSession(nextSession);
    });

    supabase.auth.getSession()
      .then(({ data, error: sessionError }) => {
        if (!active) return;
        // An auth event takes precedence over this initial asynchronous read.
        if (!authEventReceived.current) setSession(data.session);
        if (sessionError) setError(sessionError.message);
        setChecking(false);
      })
      .catch((sessionError: unknown) => {
        if (!active) return;
        setSession(null);
        setError(sessionError instanceof Error ? sessionError.message : 'Unable to verify your login. Please try again.');
        setChecking(false);
      });

    return () => {
      active = false;
      subscription.unsubscribe();
    };
  }, []);

  useEffect(() => {
    if (!toast) return;
    const timer = window.setTimeout(() => setToast(''), 3500);
    return () => window.clearTimeout(timer);
  }, [toast]);

  const handleLogout = async () => {
    if (!supabase || loggingOut) return;
    setLoggingOut(true);
    setError('');
    const { error: signOutError } = await supabase.auth.signOut();
    if (signOutError) {
      setError(signOutError.message);
      setLoggingOut(false);
      return;
    }
    // The auth listener also clears the session; setting it here makes the
    // transition to the login-only screen immediate.
    setSession(null);
    setToast('Logged out successfully');
    setLoggingOut(false);
  };

  const handleLogin = async (event: React.FormEvent) => {
    event.preventDefault();
    if (!supabase || busy) return;
    setBusy(true);
    setError('');
    const { error: loginError } = await supabase.auth.signInWithPassword({ email, password });
    if (loginError) setError(loginError.message);
    setBusy(false);
  };

  if (checking) {
    return (
      <div className="min-h-screen bg-slate-50 flex items-center justify-center" role="status" aria-label="Checking admin session">
        <LoaderCircle className="w-8 h-8 text-blue-600 animate-spin" />
      </div>
    );
  }

  // AdminLayout is deliberately never mounted without a confirmed session.
  if (session && supabase) {
    return <AdminLayout onExitAdmin={onExitAdmin} onLogout={handleLogout} session={session} />;
  }

  return (
    <div className="min-h-screen bg-slate-50 flex items-center justify-center p-4 font-sans">
      <form onSubmit={handleLogin} className="w-full max-w-sm bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-4">
        <div>
          <h1 className="text-xl font-bold text-slate-900">Admin Login</h1>
          <p className="text-sm text-slate-500">Photon Physics Academy — Admin Portal</p>
        </div>
        {!supabase && (
          <p className="text-sm text-red-600">Admin login is unavailable because Supabase is not configured.</p>
        )}
        <input
          type="email" required autoComplete="email" placeholder="Email"
          value={email} onChange={(event) => setEmail(event.target.value)}
          disabled={!supabase || busy}
          className="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-slate-100"
        />
        <input
          type="password" required autoComplete="current-password" placeholder="Password"
          value={password} onChange={(event) => setPassword(event.target.value)}
          disabled={!supabase || busy}
          className="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:bg-slate-100"
        />
        {error && <p className="text-sm text-red-600" role="alert">{error}</p>}
        <button
          type="submit" disabled={!supabase || busy}
          className="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white font-semibold rounded-lg py-2 text-sm"
        >
          {busy ? 'Signing in…' : 'Sign in'}
        </button>
        <button type="button" onClick={onExitAdmin} className="w-full text-sm text-slate-500 hover:text-slate-800">
          ← Back to website
        </button>
      </form>
      {toast && (
        <div className="fixed bottom-5 left-1/2 -translate-x-1/2 rounded-lg bg-slate-900 px-4 py-2.5 text-sm font-medium text-white shadow-lg" role="status">
          {toast}
        </div>
      )}
    </div>
  );
};
