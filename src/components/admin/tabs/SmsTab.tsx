import React, { useMemo, useState } from 'react';
import { MessageSquare, Send, Users, Wallet, AlertTriangle, CheckCircle2, AlertCircle, ExternalLink, RefreshCw } from 'lucide-react';
import type { AdminStudent, AdminBatchRecord } from '../../../types/admin';
import { supabase } from '../../../lib/supabase';

interface Props { students: AdminStudent[]; batches: AdminBatchRecord[] }
export const SmsTab: React.FC<Props> = ({ students, batches }) => {
  const [classFilter, setClassFilter] = useState('All');
  const [batchFilter, setBatchFilter] = useState('All');
  const [message, setMessage] = useState('');
  const [includeStudent, setIncludeStudent] = useState(false);
  const [includeGuardian, setIncludeGuardian] = useState(true);
  const [onlyActive, setOnlyActive] = useState(true);
  const [sending, setSending] = useState(false);
  const [feedback, setFeedback] = useState('');
  const [feedbackError, setFeedbackError] = useState(false);
  const [balance, setBalance] = useState<string | null>(null);
  const [balanceBusy, setBalanceBusy] = useState(false);
  const [balanceError, setBalanceError] = useState('');

  const checkBalance = async () => {
    if (!supabase) { setBalanceError('Supabase is not configured.'); return; }
    setBalanceBusy(true);
    setBalanceError('');
    try {
      const { data, error } = await supabase.functions.invoke('sms-gateway', { body: { action: 'balance' } });
      if (error) {
        const context = (error as any).context;
        let detail = '';
        try { if (context && typeof context.json === 'function') { const body = await context.json(); detail = body?.msg || body?.error || ''; } } catch { /* response body may already be consumed */ }
        throw new Error([error.message, detail].filter(Boolean).join(' — '));
      }
      if (data?.error !== 0 && Number(data?.error) !== 0) throw new Error(data?.msg || String(data?.error ?? 'Gateway returned an error.'));
      const value = data?.data?.balance ?? data?.balance ?? data?.data?.credits ?? data?.credits;
      if (value === undefined || value === null || value === '') throw new Error('The gateway did not return a readable balance. Check the sms-gateway function logs.');
      setBalance(String(value));
    } catch (e) { setBalanceError(e instanceof Error ? e.message : 'Could not check SMS balance.'); }
    finally { setBalanceBusy(false); }
  };

  const eligible = useMemo(() => students.filter((s) =>
    (classFilter === 'All' || s.classLevel === classFilter) &&
    (batchFilter === 'All' || s.batchId === batchFilter) &&
    (!onlyActive || s.status === 'active')
  ), [students, classFilter, batchFilter, onlyActive]);

  const recipients = useMemo(() => {
    const numbers = new Set<string>();
    for (const student of eligible) {
      if (includeStudent && student.studentPhone.trim()) numbers.add(student.studentPhone.trim());
      if (includeGuardian && student.guardianPhone.trim()) numbers.add(student.guardianPhone.trim());
    }
    return [...numbers];
  }, [eligible, includeStudent, includeGuardian]);

  const send = async () => {
    setFeedback(''); setFeedbackError(false);
    if (!supabase) { setFeedback('Supabase is not configured.'); setFeedbackError(true); return; }
    if (!message.trim()) { setFeedback('Write a message first.'); setFeedbackError(true); return; }
    if (!recipients.length) { setFeedback('No recipients have the selected phone numbers. Check student and guardian contacts.'); setFeedbackError(true); return; }
    if (!window.confirm(`Send this SMS to ${recipients.length} unique phone number(s)? SMS charges may apply.`)) return;
    setSending(true);
    try {
      const { data, error } = await supabase.functions.invoke('sms-gateway', { body: { action: 'send', to: recipients, msg: message.trim() } });
      if (error) throw new Error(error.message);
      if (data?.error) throw new Error(data.error);
      setFeedback(`Gateway response: ${data.sent ?? 0} accepted, ${data.failed ?? 0} failed. No SMS history was saved. Check the gateway result for individual errors.`);
      setFeedbackError(Boolean(data.failed));
    } catch (e) {
      setFeedback(e instanceof Error ? e.message : 'Could not send SMS. Check the Supabase function and gateway configuration.');
      setFeedbackError(true);
    } finally { setSending(false); }
  };

  return <div className="space-y-6">
    <div className="rounded-2xl border border-blue-100 bg-blue-50 p-5 flex gap-3">
      <MessageSquare className="w-6 h-6 text-blue-700 shrink-0" />
      <div><h2 className="font-bold text-slate-900">SMS Management</h2><p className="text-sm text-slate-600 mt-1">Send individual or batch-wide messages through sms.bd. Sent-message history is not stored in the database.</p></div>
    </div>

    <section className="rounded-2xl border border-slate-200 bg-white p-5 space-y-4">
      <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-start gap-3">
          <div className="rounded-xl bg-emerald-50 p-3"><Wallet className="h-5 w-5 text-emerald-700" /></div>
          <div><h3 className="font-bold text-slate-900">SMS Balance</h3><p className="mt-1 text-sm text-slate-500">Check your current sms.bd account balance without leaving the academy dashboard.</p></div>
        </div>
        <button type="button" onClick={checkBalance} disabled={balanceBusy} className="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-bold text-white hover:bg-blue-700 disabled:cursor-not-allowed disabled:opacity-50"><RefreshCw className={`h-4 w-4 ${balanceBusy ? 'animate-spin' : ''}`} />{balanceBusy ? 'Checking balance…' : balance === null ? 'Check SMS Balance' : 'Refresh Balance'}</button>
      </div>
      {balance !== null && !balanceError && <div className="flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800"><CheckCircle2 className="h-5 w-5 shrink-0" /><span>Current gateway balance: <strong>{balance}</strong></span></div>}
      {balanceError && <div className="flex items-start gap-2 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800"><AlertCircle className="mt-0.5 h-5 w-5 shrink-0" /><span>{balanceError}</span></div>}
      <div className="border-t border-slate-100 pt-4">
        <p className="text-sm text-slate-600">Need to manage your SMS account or check it directly with the provider?</p>
        <a href="https://portal.sms.net.bd/login/" target="_blank" rel="noopener noreferrer" className="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-blue-700 underline decoration-blue-300 underline-offset-4 hover:text-blue-900">Open official SMS gateway portal <ExternalLink className="h-4 w-4" /></a>
      </div>
    </section>

    {feedback && <div className={`rounded-xl border px-4 py-3 text-sm ${feedbackError ? 'border-red-200 bg-red-50 text-red-800' : 'border-emerald-200 bg-emerald-50 text-emerald-800'}`}>{feedback}</div>}
    <div className="bg-white rounded-2xl border border-slate-200 p-5 space-y-5">
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <label className="text-sm font-semibold text-slate-700">Class
          <select value={classFilter} onChange={e=>setClassFilter(e.target.value)} className="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm"><option>All</option><option>Class 8</option><option>Class 9</option><option>Class 10</option></select>
        </label>
        <label className="text-sm font-semibold text-slate-700">Batch
          <select value={batchFilter} onChange={e=>setBatchFilter(e.target.value)} className="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm"><option value="All">All batches</option>{batches.map(b=><option key={b.id} value={b.id}>{b.name} — {b.timeSlot}</option>)}</select>
        </label>
      </div>
      <label className="flex items-center gap-2 text-sm text-slate-700"><input type="checkbox" checked={onlyActive} onChange={e=>setOnlyActive(e.target.checked)} className="rounded"/> Only active students</label>
      <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
        <label className="flex items-start gap-3 border border-slate-200 rounded-xl p-3"><input type="checkbox" checked={includeStudent} onChange={e=>setIncludeStudent(e.target.checked)} className="mt-1 rounded"/><span><strong className="block text-sm">Student mobile numbers</strong><small className="text-slate-500">Optional contact field; blank numbers are skipped.</small></span></label>
        <label className="flex items-start gap-3 border border-slate-200 rounded-xl p-3"><input type="checkbox" checked={includeGuardian} onChange={e=>setIncludeGuardian(e.target.checked)} className="mt-1 rounded"/><span><strong className="block text-sm">Guardian mobile numbers</strong><small className="text-slate-500">Enabled by default so guardians receive notices.</small></span></label>
      </div>
      <div className="rounded-xl bg-slate-50 p-3 flex items-center gap-3"><Users className="w-5 h-5 text-slate-500"/><div><div className="text-sm font-bold text-slate-800">{recipients.length} unique recipient(s)</div><div className="text-xs text-slate-500">From {eligible.length} matching student records; duplicate phone numbers are sent once.</div></div></div>
      <label className="block text-sm font-semibold text-slate-700">Message
        <textarea value={message} onChange={e=>setMessage(e.target.value)} rows={5} maxLength={1000} placeholder="Write your SMS in Bangla or English..." className="mt-1 block w-full rounded-xl border border-slate-200 bg-slate-50 p-3 text-sm font-normal"/>
      </label>
      <div className="flex flex-col sm:flex-row justify-between gap-3 items-start sm:items-center"><span className="text-xs text-slate-500">{message.length}/1000 characters. Gateway charges may apply. No message history is retained.</span><button disabled={sending || !recipients.length || !message.trim()} onClick={send} className="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-bold text-white hover:bg-blue-700 disabled:opacity-50"><Send className="w-4 h-4"/>{sending ? 'Sending…' : 'Send SMS'}</button></div>
    </div>
    <div className="rounded-xl border border-amber-200 bg-amber-50 p-4 flex gap-3 text-sm text-amber-900"><AlertTriangle className="w-5 h-5 shrink-0"/><p>WhatsApp numbers are saved separately as contact details. sms.bd sends SMS only; WhatsApp messages require a separate WhatsApp Business API integration.</p></div>
  </div>;
};
