import React, { useEffect, useState } from 'react';
import type { Session } from '@supabase/supabase-js';
import { supabase } from '../../lib/supabase';
import { AdminLayout } from './AdminLayout';

interface Props {
  onExitAdmin: () => void;
}

/** Requires a Supabase login before showing the admin panel (RLS needs an authenticated user). */
export const AdminGate: React.FC<Props> = ({ onExitAdmin }) => {
  const [session, setSession] = useState<Session | null>(null);
  const [checking, setChecking] = useState(true);
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');
  const [busy, setBusy] = useState(false);

  useEffect(() => {
    if (!supabase) return;
    supabase.auth.getSession().then(({ data }) => {
      setSession(data.session);
      setChecking(false);
    });
    const { data: sub } = supabase.auth.onAuthStateChange((_e, s) => setSession(s));
    return () => sub.subscription.unsubscribe();
  }, []);

  const handleExit = async () => {
    await supabase?.auth.signOut();
    onExitAdmin();
  };

  const handleLogin = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!supabase) return;
    setBusy(true);
    setError('');
    const { error } = await supabase.auth.signInWithPassword({ email, password });
    if (error) setError(error.message);
    setBusy(false);
  };

  // Supabase not configured → fall back to the offline demo panel
  if (!supabase) return <AdminLayout onExitAdmin={onExitAdmin} session={null} />;
  if (checking) return <div className="min-h-screen bg-slate-50" />;
  if (session) return <AdminLayout onExitAdmin={handleExit} session={session} />;

  return (
    <div className="min-h-screen bg-slate-50 flex items-center justify-center p-4 font-sans">
      <form onSubmit={handleLogin} className="w-full max-w-sm bg-white border border-slate-200 rounded-2xl shadow-sm p-6 space-y-4">
        <div>
          <h1 className="text-xl font-bold text-slate-900">Admin Login</h1>
          <p className="text-sm text-slate-500">Photon Physics Academy — Admin Portal</p>
        </div>
        <input
          type="email" required autoComplete="email" placeholder="Email"
          value={email} onChange={(e) => setEmail(e.target.value)}
          className="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        <input
          type="password" required autoComplete="current-password" placeholder="Password"
          value={password} onChange={(e) => setPassword(e.target.value)}
          className="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
        />
        {error && <p className="text-sm text-red-600">{error}</p>}
        <button
          type="submit" disabled={busy}
          className="w-full bg-blue-600 hover:bg-blue-700 disabled:opacity-60 text-white font-semibold rounded-lg py-2 text-sm"
        >
          {busy ? 'Signing in…' : 'Sign in'}
        </button>
        <button type="button" onClick={onExitAdmin} className="w-full text-sm text-slate-500 hover:text-slate-800">
          ← Back to website
        </button>
      </form>
    </div>
  );
};
