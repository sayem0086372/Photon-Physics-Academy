import React, { useState } from 'react';
import { ShieldCheck, Wallet, CheckCircle2, AlertCircle, Settings as SettingsIcon } from 'lucide-react';
import { supabase } from '../../../lib/supabase';
import { SlideshowSettings } from './SlideshowSettings';

export const SettingsTab: React.FC = () => {
  const [balance, setBalance] = useState<string | null>(null);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const checkBalance = async () => {
    setBusy(true); setError(''); setBalance(null);
    try {
      if (!supabase) throw new Error('Supabase is not configured.');
      const { data, error: invokeError } = await supabase.functions.invoke('sms-gateway', { body: { action: 'balance' } });
      if (invokeError) {
        const context = (invokeError as any).context;
        let detail = '';
        try { if (context && typeof context.json === 'function') { const body = await context.json(); detail = body?.msg || body?.error || ''; } } catch { /* response may already be consumed */ }
        throw new Error([invokeError.message, detail].filter(Boolean).join(' — '));
      }
      if (Number(data?.error) !== 0) throw new Error(data?.msg || String(data?.error ?? 'Gateway returned an error.'));
      const value = data?.data?.balance ?? data?.balance ?? data?.data?.credits ?? data?.credits;
      if (value === undefined || value === null || value === '') throw new Error('sms.bd responded successfully but did not include a readable balance. Check the gateway response in Supabase Function Logs.');
      setBalance(String(value));
    } catch (e) { setError(e instanceof Error ? e.message : 'Could not check balance.'); }
    finally { setBusy(false); }
  };
  return <div className="space-y-6">
    <div><h2 className="text-lg font-bold text-slate-900">Settings</h2><p className="text-sm text-slate-500 mt-1">Configure integrations and academy preferences.</p></div>
    <section className="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
      <div className="flex items-start gap-3"><div className="rounded-xl bg-blue-50 p-3"><SettingsIcon className="w-5 h-5 text-blue-700"/></div><div><h3 className="font-bold text-slate-900">sms.bd Gateway</h3><p className="text-sm text-slate-500">Credentials are stored securely as Supabase Edge Function secrets—not in the browser or database.</p></div></div>
      <div className="rounded-xl bg-slate-50 p-4 text-sm space-y-2"><div className="flex items-center gap-2 font-semibold"><ShieldCheck className="w-4 h-4 text-emerald-600"/> Secure configuration required</div><p>In Supabase, deploy the <code>sms-gateway</code> Edge Function and set these secrets:</p><pre className="whitespace-pre-wrap rounded-lg bg-slate-900 p-3 text-xs text-slate-100">SMSBD_API_KEY=your_new_rotated_key{ '\n' }PPA_ADMIN_USER_ID=your_admin_auth_user_uuid{ '\n' }APP_ORIGIN=https://your-netlify-site.netlify.app</pre><p className="text-xs text-slate-500">Do not paste the API key into this page or frontend code. The key previously shared in chat should be rotated before use.</p></div>
      <button onClick={checkBalance} disabled={busy} className="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-50"><Wallet className="w-4 h-4"/>{busy ? 'Checking…' : 'Check SMS Balance'}</button>
      {balance !== null && <p className="flex items-center gap-2 text-sm text-emerald-700"><CheckCircle2 className="w-4 h-4"/>Current gateway balance: {balance}</p>}
      {error && <p className="flex items-center gap-2 text-sm text-red-700"><AlertCircle className="w-4 h-4"/>{error}</p>}
    </section>
    <SlideshowSettings />
    <section className="rounded-2xl border border-slate-200 bg-white p-5"><h3 className="font-bold text-slate-900">SMS privacy</h3><p className="mt-1 text-sm text-slate-600">The application does not create a sent-SMS history table or save message bodies and recipient lists. The provider may keep its own request/report records according to its policies.</p></section>
  </div>;
};
