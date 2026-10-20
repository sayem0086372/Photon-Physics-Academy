import React, { useEffect, useMemo, useState } from 'react';
import { ChevronLeft, ChevronRight, Images, Pause, Play } from 'lucide-react';
import { supabase } from '../lib/supabase';
import { GALLERY_ITEMS } from '../data/gallery';

type Slide = { id: string; image_url: string; title: string | null; caption: string | null; display_order: number };
const fallbackSlides: Slide[] = GALLERY_ITEMS.map((item, index) => ({
  id: item.id, image_url: item.imageUrl, title: item.title, caption: item.banglaCaption, display_order: index + 1,
}));

export const ClassroomSlideshow: React.FC = () => {
  const [slides, setSlides] = useState<Slide[]>(fallbackSlides);
  const [index, setIndex] = useState(0);
  const [paused, setPaused] = useState(false);
  const [failed, setFailed] = useState<Set<string>>(new Set());

  useEffect(() => {
    let cancelled = false;
    const load = async () => {
      if (!supabase) return;
      const { data, error } = await supabase.from('homepage_slideshow_images')
        .select('id,image_url,title,caption,display_order')
        .eq('is_active', true).order('display_order', { ascending: true }).limit(30);
      if (!cancelled && !error && data?.length) {
        setSlides(data as Slide[]);
        setIndex(0);
      }
    };
    void load();
    return () => { cancelled = true; };
  }, []);

  const visibleSlides = useMemo(() => slides.filter(s => !failed.has(s.id)), [slides, failed]);
  useEffect(() => {
    if (paused || visibleSlides.length < 2) return;
    const timer = window.setInterval(() => setIndex(current => (current + 1) % visibleSlides.length), 5000);
    return () => window.clearInterval(timer);
  }, [paused, visibleSlides.length]);
  useEffect(() => { if (index >= visibleSlides.length) setIndex(0); }, [index, visibleSlides.length]);

  const active = visibleSlides[index];
  const move = (direction: number) => setIndex(current => (current + direction + visibleSlides.length) % visibleSlides.length);

  return <section aria-label="ক্লাসরুম শিখন পরিবেশ" className="overflow-hidden border-y border-sky-100 bg-gradient-to-b from-sky-50 via-white to-blue-50 py-16 sm:py-20">
    <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div className="mb-8 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div><p className="mb-2 text-xs font-bold uppercase tracking-[0.22em] text-blue-700">Photon Physics Academy · Mymensingh</p><h2 className="text-2xl font-black tracking-tight text-slate-950 sm:text-3xl md:text-4xl">ক্লাসরুম শিখন পরিবেশ · ময়মনসিংহ</h2><p className="mt-3 max-w-2xl text-sm leading-6 text-slate-600 sm:text-base">শ্রেণিকক্ষ, শিক্ষাদান ও শেখার মুহূর্তগুলোর এক ঝলক।</p></div>
        <div className="inline-flex w-fit items-center gap-2 rounded-full border border-slate-200 bg-white/90 px-3 py-2 text-xs font-semibold text-slate-600 shadow-sm"><Images className="h-4 w-4 text-blue-700"/> {visibleSlides.length} photos</div>
      </div>
      {active ? <div className="group relative overflow-hidden rounded-3xl border border-white bg-slate-950 shadow-2xl shadow-sky-900/10" onMouseEnter={() => setPaused(true)} onMouseLeave={() => setPaused(false)}>
        <div className="relative aspect-[16/9] min-h-[260px] w-full sm:min-h-[360px] lg:min-h-[470px]">
          <img key={active.id} src={active.image_url} alt={active.title || 'Photon Physics Academy classroom'} onError={() => setFailed(prev => new Set(prev).add(active.id))} referrerPolicy="no-referrer" className="slideshow-image absolute inset-0 h-full w-full object-cover" />
          <div className="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-slate-950/10 to-transparent" />
          <div className="absolute inset-x-0 bottom-0 p-5 text-white sm:p-8 md:p-10"><p className="text-xs font-bold uppercase tracking-[0.2em] text-sky-200">Learning environment</p>{active.title && <h3 className="mt-2 text-xl font-black sm:text-2xl md:text-3xl">{active.title}</h3>}{active.caption && <p className="mt-2 max-w-2xl text-sm leading-6 text-white/90 sm:text-base">{active.caption}</p>}</div>
        </div>
        {visibleSlides.length > 1 && <><button onClick={() => move(-1)} aria-label="Previous photo" className="absolute left-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/30 bg-black/35 text-white backdrop-blur transition hover:bg-black/65 sm:left-5 sm:h-12 sm:w-12"><ChevronLeft className="h-6 w-6"/></button><button onClick={() => move(1)} aria-label="Next photo" className="absolute right-3 top-1/2 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full border border-white/30 bg-black/35 text-white backdrop-blur transition hover:bg-black/65 sm:right-5 sm:h-12 sm:w-12"><ChevronRight className="h-6 w-6"/></button><div className="absolute right-4 top-4 flex items-center gap-1.5 sm:right-6 sm:top-6"><button onClick={() => setPaused(v => !v)} aria-label={paused ? 'Play slideshow' : 'Pause slideshow'} className="flex h-9 w-9 items-center justify-center rounded-full border border-white/30 bg-black/35 text-white backdrop-blur hover:bg-black/60">{paused ? <Play className="h-4 w-4"/> : <Pause className="h-4 w-4"/>}</button></div></>}
        {visibleSlides.length > 1 && <div className="flex items-center justify-center gap-2 bg-slate-950 px-4 py-4">{visibleSlides.map((slide, dotIndex) => <button key={slide.id} onClick={() => setIndex(dotIndex)} aria-label={`Show photo ${dotIndex + 1}`} aria-current={dotIndex === index} className={`h-2 rounded-full transition-all duration-300 ${dotIndex === index ? 'w-8 bg-sky-300' : 'w-2 bg-white/40 hover:bg-white/70'}`}/>)}</div>}
      </div> : <div className="rounded-2xl border border-dashed border-slate-300 bg-white p-10 text-center text-sm text-slate-600">No classroom photos are available right now.</div>}
    </div>
  </section>;
};
