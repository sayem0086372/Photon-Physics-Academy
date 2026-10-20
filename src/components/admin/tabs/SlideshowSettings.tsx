import React, { useCallback, useEffect, useState } from 'react';
import { AlertCircle, ArrowDown, ArrowUp, CheckCircle2, Eye, EyeOff, ImagePlus, Pencil, RefreshCw, Save, Trash2, X } from 'lucide-react';
import { supabase } from '../../../lib/supabase';

type Slide = { id: string; image_url: string; title: string | null; caption: string | null; display_order: number; is_active: boolean; created_at: string };
const blank = { image_url: '', title: '', caption: '' };
const field = 'w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100';

export const SlideshowSettings: React.FC = () => {
  const [slides, setSlides] = useState<Slide[]>([]);
  const [form, setForm] = useState(blank);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [userId, setUserId] = useState('');
  const [isAdmin, setIsAdmin] = useState(false);
  const [loading, setLoading] = useState(true);
  const [busy, setBusy] = useState(false);
  const [error, setError] = useState('');
  const [notice, setNotice] = useState('');

  const load = useCallback(async () => {
    if (!supabase) { setError('Supabase is not configured.'); setLoading(false); return; }
    setLoading(true); setError('');
    try {
      const { data: auth, error: authError } = await supabase.auth.getUser();
      if (authError) throw authError;
      if (!auth.user) throw new Error('Sign in to the admin panel again.');
      setUserId(auth.user.id);
      const { data: admin, error: adminError } = await supabase.rpc('is_store_admin');
      if (adminError) throw adminError;
      if (admin !== true) { setIsAdmin(false); throw new Error('Store administrator access is required to manage homepage slideshow images.'); }
      setIsAdmin(true);
      const { data, error: queryError } = await supabase.from('homepage_slideshow_images').select('*').order('display_order', { ascending: true }).limit(100);
      if (queryError) {
        if (/relation .* does not exist|schema cache/i.test(queryError.message)) throw new Error('Run src/db/migrations/homepage_slideshow_urls_v1_3_2.sql in Supabase before managing slideshow URLs.');
        throw queryError;
      }
      setSlides((data ?? []) as Slide[]);
    } catch (e) { setError(e instanceof Error ? e.message : 'Could not load slideshow settings.'); }
    finally { setLoading(false); }
  }, []);
  useEffect(() => { void load(); }, [load]);

  const reset = () => { setForm(blank); setEditingId(null); };
  const save = async (event: React.FormEvent) => {
    event.preventDefault();
    if (!supabase || !isAdmin) return;
    const url = form.image_url.trim();
    try { const parsed = new URL(url); if (!['http:', 'https:'].includes(parsed.protocol)) throw new Error(); }
    catch { setError('Enter a valid image URL starting with https:// or http://.'); return; }
    if (form.title.trim().length > 160 || form.caption.trim().length > 500) { setError('Title must be 160 characters or fewer and caption 500 characters or fewer.'); return; }
    setBusy(true); setError(''); setNotice('');
    const payload = { image_url: url, title: form.title.trim() || null, caption: form.caption.trim() || null, updated_at: new Date().toISOString() };
    const result = editingId
      ? await supabase.from('homepage_slideshow_images').update(payload).eq('id', editingId)
      : await supabase.from('homepage_slideshow_images').insert({ ...payload, display_order: (Math.max(0, ...slides.map(s => s.display_order)) + 1), is_active: true, created_by: userId || null });
    if (result.error) setError(result.error.message);
    else { setNotice(editingId ? 'Slideshow image updated.' : 'Image URL added to the slideshow.'); reset(); await load(); }
    setBusy(false);
  };

  const edit = (slide: Slide) => { setEditingId(slide.id); setForm({ image_url: slide.image_url, title: slide.title ?? '', caption: slide.caption ?? '' }); setNotice(''); setError(''); };
  const toggle = async (slide: Slide) => {
    if (!supabase) return;
    setBusy(true); setError(''); setNotice('');
    const { error: updateError } = await supabase.from('homepage_slideshow_images').update({ is_active: !slide.is_active, updated_at: new Date().toISOString() }).eq('id', slide.id);
    if (updateError) setError(updateError.message); else { setNotice(`Image ${slide.is_active ? 'hidden' : 'published'}.`); await load(); }
    setBusy(false);
  };
  const remove = async (slide: Slide) => {
    if (!supabase || !window.confirm('Remove this image URL from the homepage slideshow? The original image file will not be deleted.')) return;
    setBusy(true); setError(''); setNotice('');
    const { error: deleteError } = await supabase.from('homepage_slideshow_images').delete().eq('id', slide.id);
    if (deleteError) setError(deleteError.message); else { setNotice('Image URL removed. The original image was not changed.'); if (editingId === slide.id) reset(); await load(); }
    setBusy(false);
  };
  const reorder = async (slide: Slide, delta: number) => {
    if (!supabase) return;
    const current = slides.findIndex(s => s.id === slide.id); const target = current + delta;
    if (target < 0 || target >= slides.length) return;
    setBusy(true); setError(''); setNotice('');
    const other = slides[target];
    const { error: firstError } = await supabase.from('homepage_slideshow_images').update({ display_order: other.display_order, updated_at: new Date().toISOString() }).eq('id', slide.id);
    const { error: secondError } = firstError ? { error: null } : await supabase.from('homepage_slideshow_images').update({ display_order: slide.display_order, updated_at: new Date().toISOString() }).eq('id', other.id);
    if (firstError || secondError) setError(firstError?.message || secondError?.message || 'Could not reorder images.');
    else { setNotice('Slideshow order updated.'); await load(); }
    setBusy(false);
  };

  return <section className="rounded-2xl border border-slate-200 bg-white p-5 sm:p-6">
    <div className="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between"><div className="flex items-start gap-3"><div className="rounded-xl bg-sky-50 p-3"><ImagePlus className="h-5 w-5 text-sky-700"/></div><div><h3 className="font-bold text-slate-900">Homepage Slideshow</h3><p className="mt-1 max-w-2xl text-sm text-slate-500">Manage the photos in “ক্লাসরুম শিখন পরিবেশ · ময়মনসিংহ”. Supabase stores only the image URL and its text/order settings—not image files.</p></div></div><button onClick={() => void load()} disabled={loading || busy} className="inline-flex items-center justify-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-700"><RefreshCw className="h-4 w-4"/>Refresh</button></div>
    {error && <div role="alert" className="mt-4 flex gap-2 rounded-xl border border-red-200 bg-red-50 p-3 text-sm text-red-800"><AlertCircle className="mt-0.5 h-4 w-4 shrink-0"/>{error}</div>}
    {notice && <div role="status" className="mt-4 flex gap-2 rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm text-emerald-800"><CheckCircle2 className="h-4 w-4 shrink-0"/>{notice}</div>}
    {isAdmin && <form onSubmit={save} className="mt-5 grid gap-3 rounded-xl bg-slate-50 p-4">
      <div className="flex items-center justify-between gap-3"><h4 className="font-bold text-slate-900">{editingId ? 'Edit slideshow image' : 'Add image URL'}</h4>{editingId && <button type="button" onClick={reset} className="inline-flex items-center gap-1 text-xs font-semibold text-slate-600"><X className="h-3.5 w-3.5"/>Cancel edit</button>}</div>
      <label className="text-xs font-semibold text-slate-600">Image URL *<input required maxLength={2048} type="url" value={form.image_url} onChange={e => setForm({ ...form, image_url: e.target.value })} placeholder="https://example.com/classroom-photo.jpg" className={`${field} mt-1.5`}/></label>
      <div className="grid gap-3 sm:grid-cols-2"><label className="text-xs font-semibold text-slate-600">Title (optional)<input maxLength={160} value={form.title} onChange={e => setForm({ ...form, title: e.target.value })} placeholder="আমাদের শ্রেণিকক্ষ" className={`${field} mt-1.5`}/></label><label className="text-xs font-semibold text-slate-600">Caption (optional)<input maxLength={500} value={form.caption} onChange={e => setForm({ ...form, caption: e.target.value })} placeholder="আধুনিক পরিবেশে পদার্থবিজ্ঞান শিক্ষা" className={`${field} mt-1.5`}/></label></div>
      {form.image_url.trim() && <div className="overflow-hidden rounded-xl border border-slate-200 bg-white"><img src={form.image_url} alt="Slideshow image preview" onError={e => { e.currentTarget.style.display = 'none'; }} onLoad={e => { e.currentTarget.style.display = 'block'; }} className="max-h-52 w-full object-contain"/><p className="px-3 py-2 text-xs text-slate-500">Preview only. The image stays hosted at the URL above.</p></div>}
      <button disabled={busy || loading} className="inline-flex w-fit items-center justify-center gap-2 rounded-lg bg-blue-700 px-4 py-2.5 text-sm font-bold text-white disabled:opacity-50"><Save className="h-4 w-4"/>{busy ? 'Saving…' : editingId ? 'Save changes' : 'Add to slideshow'}</button>
    </form>}
    <div className="mt-5"><h4 className="mb-3 font-bold text-slate-900">Slideshow images ({slides.length})</h4>{loading ? <div className="h-24 animate-pulse rounded-xl bg-slate-100"/> : slides.length === 0 ? <div className="rounded-xl border border-dashed border-slate-300 p-6 text-center text-sm text-slate-500">No custom URLs added. The homepage will show the built-in classroom photos until you add your own.</div> : <div className="space-y-3">{slides.map((slide, index) => <article key={slide.id} className="flex flex-col gap-3 rounded-xl border border-slate-200 p-3 sm:flex-row sm:items-center"><div className="h-24 w-full shrink-0 overflow-hidden rounded-lg bg-slate-100 sm:w-36"><img src={slide.image_url} alt={slide.title || 'Slideshow preview'} className="h-full w-full object-cover" onError={e => { e.currentTarget.style.display = 'none'; }}/></div><div className="min-w-0 flex-1"><div className="flex flex-wrap items-center gap-2"><p className="font-bold text-slate-900">{slide.title || `Image ${index + 1}`}</p><span className={`rounded-full px-2 py-0.5 text-[10px] font-bold ${slide.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'}`}>{slide.is_active ? 'Published' : 'Hidden'}</span></div>{slide.caption && <p className="mt-1 text-xs text-slate-600">{slide.caption}</p>}<p className="mt-1 break-all text-[11px] text-slate-400">{slide.image_url}</p></div><div className="flex flex-wrap items-center gap-1 sm:justify-end"><button disabled={busy || index === 0} onClick={() => void reorder(slide, -1)} aria-label="Move image up" title="Move up" className="rounded-lg border p-2 disabled:opacity-30"><ArrowUp className="h-4 w-4"/></button><button disabled={busy || index === slides.length - 1} onClick={() => void reorder(slide, 1)} aria-label="Move image down" title="Move down" className="rounded-lg border p-2 disabled:opacity-30"><ArrowDown className="h-4 w-4"/></button><button disabled={busy} onClick={() => edit(slide)} aria-label="Edit image" title="Edit" className="rounded-lg border p-2"><Pencil className="h-4 w-4"/></button><button disabled={busy} onClick={() => void toggle(slide)} aria-label={slide.is_active ? 'Hide image' : 'Publish image'} title={slide.is_active ? 'Hide' : 'Publish'} className="rounded-lg border p-2">{slide.is_active ? <EyeOff className="h-4 w-4"/> : <Eye className="h-4 w-4"/>}</button><button disabled={busy} onClick={() => void remove(slide)} aria-label="Remove image URL" title="Remove URL" className="rounded-lg border border-red-200 p-2 text-red-700"><Trash2 className="h-4 w-4"/></button></div></article>)}</div>}</div>
    <p className="mt-4 text-xs leading-5 text-slate-500">Use image URLs that are publicly viewable over HTTPS. Removing a URL only removes it from this slideshow; it never deletes the hosted image. Avoid private or expiring links.</p>
  </section>;
};
