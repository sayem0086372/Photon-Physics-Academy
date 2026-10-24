import React, { useState } from 'react';
import { Maximize2, Camera, ShieldCheck } from 'lucide-react';
import { GALLERY_ITEMS } from '../data/gallery';
import { LightboxModal } from './LightboxModal';

export const Gallery: React.FC = () => {
  const [lightboxIndex, setLightboxIndex] = useState<number | null>(null);

  const openLightbox = (index: number) => {
    setLightboxIndex(index);
  };

  const closeLightbox = () => {
    setLightboxIndex(null);
  };

  const handlePrev = () => {
    if (lightboxIndex !== null) {
      setLightboxIndex((lightboxIndex - 1 + GALLERY_ITEMS.length) % GALLERY_ITEMS.length);
    }
  };

  const handleNext = () => {
    if (lightboxIndex !== null) {
      setLightboxIndex((lightboxIndex + 1) % GALLERY_ITEMS.length);
    }
  };

  return (
    <section id="gallery" className="py-5 md:py-7 bg-slate-100 border-y border-slate-200">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Section Header */}
        <div className="flex flex-col md:flex-row md:items-end justify-between mb-4 pb-3 border-b border-slate-200 gap-4">
          <div>
            <div className="text-xs uppercase tracking-widest text-blue-700 font-bold mb-2">
              OUR CLASSROOMS — আমাদের ক্লাসরুম
            </div>
            <h2 className="text-xl sm:text-2xl md:text-3xl font-bold text-slate-900 tracking-tight font-bangla">
              শিক্ষা ও অনুশীলনের দৃশ্যপট
            </h2>
            <p className="mt-2 text-slate-600 font-bangla text-sm sm:text-base">
              ক্লাসরুম লেকচার, শিক্ষার্থীদের কনসেপ্ট বিশ্লেষণ এবং আন্তরিক শিক্ষক দিকনির্দেশনা।
            </p>
          </div>

          <div className="flex items-center gap-2 text-xs text-slate-600 bg-white px-3 py-1.5 rounded-lg border border-slate-200 shadow-2xs">
            <ShieldCheck className="w-4 h-4 text-blue-600" />
            <span>বাস্তব ক্লাসরুম পরিবেশের চিত্রায়ণ</span>
          </div>
        </div>

        {/* Gallery Grid */}
        <div className="grid grid-cols-2 md:grid-cols-4 gap-2 sm:gap-3">
          {GALLERY_ITEMS.map((item, index) => (
            <div
              key={item.id}
              onClick={() => openLightbox(index)}
              className="group relative rounded-xl overflow-hidden border border-slate-200 bg-white cursor-pointer shadow-xs hover:shadow-xl hover:border-blue-400 transition-all duration-300 flex flex-col"
            >
              <div className="aspect-[5/3] w-full overflow-hidden bg-slate-100 relative">
                <img
                  src={item.imageUrl}
                  alt={item.title}
                  referrerPolicy="no-referrer"
                  loading="lazy"
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                />

                {/* Hover overlay with zoom icon */}
                <div className="absolute inset-0 bg-slate-900/40 opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                  <div className="p-2.5 rounded-full bg-white text-blue-600 shadow-md">
                    <Maximize2 className="w-5 h-5" />
                  </div>
                </div>
              </div>

              {/* Caption */}
              <div className="p-2.5 bg-white flex-1 flex flex-col justify-between">
                <p className="text-xs sm:text-sm font-semibold text-slate-800 font-bangla leading-relaxed line-clamp-2">
                  {item.banglaCaption}
                </p>
                <div className="mt-2 pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-500">
                  <span className="flex items-center gap-1">
                    <Camera className="w-3 h-3 text-blue-600" />
                    <span>গ্যালারি</span>
                  </span>
                  <span className="text-blue-700 font-medium text-[11px]">বড় করে দেখুন</span>
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      {/* Lightbox Modal */}
      <LightboxModal
        items={GALLERY_ITEMS}
        currentIndex={lightboxIndex ?? 0}
        isOpen={lightboxIndex !== null}
        onClose={closeLightbox}
        onPrev={handlePrev}
        onNext={handleNext}
      />
    </section>
  );
};
