import React, { useEffect, useState } from 'react';
import { Bell, Calendar, ChevronRight, Filter, AlertCircle, Info } from 'lucide-react';
import { NOTICES_DATA } from '../data/notices';
import { Notice } from '../types';
import { isSupabaseConfigured } from '../lib/supabase';
import { loadPublishedNotices } from '../lib/db';
import { NoticeModal } from './NoticeModal';

export const NoticeBoard: React.FC = () => {
  const [selectedCategory, setSelectedCategory] = useState<string>('সব নোটিশ');
  const [activeModalNotice, setActiveModalNotice] = useState<Notice | null>(null);
  const live = isSupabaseConfigured();
  const [notices, setNotices] = useState<Notice[]>(live ? [] : NOTICES_DATA);

  useEffect(() => {
    if (!live) return;
    loadPublishedNotices()
      .then((rows) =>
        setNotices(
          rows.map((r) => ({
            id: r.id,
            title: r.title,
            banglaTitle: r.banglaTitle,
            date: new Date(r.date).toLocaleDateString('bn-BD', { day: '2-digit', month: 'long', year: 'numeric' }),
            category: r.category,
            shortDescription: r.shortDescription,
            fullContent: r.fullContent,
            isSample: false,
          }))
        )
      )
      .catch(() => setNotices(NOTICES_DATA));
  }, [live]);

  const categories: string[] = [
    'সব নোটিশ',
    'ভর্তি বিজ্ঞপ্তি',
    'পরীক্ষার সময়সূচি',
    'ক্লাসের সময়সূচি',
    'জরুরি ঘোষণা',
    'ফলাফল প্রকাশ',
  ];

  const filteredNotices =
    selectedCategory === 'সব নোটিশ'
      ? notices
      : notices.filter((n) => n.category === selectedCategory);

  return (
    <section id="notices" className="py-16 md:py-24 bg-slate-100 border-y border-slate-200 relative">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Section Header */}
        <div className="flex flex-col md:flex-row md:items-end justify-between mb-8 pb-6 border-b border-slate-200 gap-4">
          <div>
            <div className="text-xs uppercase tracking-widest text-blue-700 font-bold mb-2 flex items-center gap-2">
              <Bell className="w-3.5 h-3.5" />
              NOTICE BOARD — নোটিশ বোর্ড
            </div>
            <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold text-slate-900 tracking-tight font-bangla">
              একাডেমিক নোটিশ ও নির্দেশনা
            </h2>
            <p className="mt-2 text-slate-600 font-bangla text-sm sm:text-base">
              ভর্তি, পরীক্ষা ও ক্লাসরুটিনের তথ্য ও বিজ্ঞপ্তি।
            </p>
          </div>

          {!live && (
            <>
          {/* Demonstration Notice Disclaimer */}
          <div className="p-3 rounded-xl bg-amber-50 border border-amber-200 flex items-start gap-2.5 text-xs text-amber-900 max-w-sm shadow-2xs">
            <Info className="w-4 h-4 text-amber-600 shrink-0 mt-0.5" />
            <span className="font-bangla leading-relaxed">
              <strong>স্টেপ ১ ডেমো:</strong> নিচের নোটিশসমূহ কাঠামোগত নমুনা হিসেবে প্রদর্শিত। কোনো কাল্পনিক বা অসত্য তথ্য প্রচার করা হয়নি।
            </span>
          </div>
            </>
          )}
        </div>

        {/* Category Filters (Interactive Filter Tabs) */}
        <div className="flex items-center gap-2 overflow-x-auto pb-4 mb-6 scrollbar-none text-xs">
          <span className="text-slate-500 font-medium flex items-center gap-1 mr-1 shrink-0">
            <Filter className="w-3.5 h-3.5 text-blue-600" />
            <span>ফিল্টার:</span>
          </span>
          {categories.map((cat) => (
            <button
              key={cat}
              onClick={() => setSelectedCategory(cat)}
              className={`px-4 py-2 rounded-xl whitespace-nowrap transition-colors font-medium shadow-2xs ${
                selectedCategory === cat
                  ? 'bg-blue-600 text-white font-bold shadow-sm'
                  : 'bg-white text-slate-700 hover:bg-sky-50 hover:text-blue-700 border border-slate-200'
              }`}
            >
              {cat}
            </button>
          ))}
        </div>

        {/* Notices Cards Grid */}
        {filteredNotices.length > 0 ? (
          <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            {filteredNotices.map((notice) => (
              <div
                key={notice.id}
                className="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white hover:border-blue-400 hover:shadow-xl transition-all duration-300 p-6 shadow-xs group"
              >
                <div>
                  {/* Unboxed Metadata */}
                  <div className="flex items-center justify-between text-xs text-slate-500 mb-3">
                    <span className="text-blue-700 font-bold font-bangla">{notice.category}</span>
                    <span className="flex items-center gap-1 text-slate-500 font-mono text-[11px]">
                      <Calendar className="w-3.5 h-3.5 text-slate-400" />
                      {notice.date}
                    </span>
                  </div>

                  <h3 className="text-base font-bold text-slate-900 font-bangla group-hover:text-blue-700 transition-colors line-clamp-2">
                    {notice.banglaTitle}
                  </h3>
                  <p className="text-[11px] font-mono text-slate-400 mt-0.5 mb-3 line-clamp-1">
                    {notice.title}
                  </p>

                  <p className="text-xs sm:text-sm text-slate-600 font-bangla leading-relaxed line-clamp-3 mb-4">
                    {notice.shortDescription}
                  </p>
                </div>

                {/* Footer Action */}
                <div className="pt-3.5 border-t border-slate-100 flex items-center justify-between">
                  <button
                    onClick={() => setActiveModalNotice(notice)}
                    className="flex items-center gap-1 text-xs font-bold text-blue-700 hover:text-blue-800 transition-colors py-1.5 px-2.5 rounded-lg hover:bg-sky-50"
                  >
                    <span>বিস্তারিত দেখুন</span>
                    <ChevronRight className="w-3.5 h-3.5" />
                  </button>
                </div>
              </div>
            ))}
          </div>
        ) : (
          /* Empty State */
          <div className="p-12 text-center rounded-2xl border border-slate-200 bg-white shadow-xs">
            <AlertCircle className="w-10 h-10 text-slate-400 mx-auto mb-3" />
            <h4 className="text-base font-bold text-slate-900 font-bangla">এই বিভাগে কোনো নোটিশ নেই</h4>
            <p className="text-xs text-slate-500 mt-1 font-bangla">
              অনুগ্রহ করে অন্য কোনো ক্যাটাগরি নির্বাচন করুন অথবা সব নোটিশ দেখুন।
            </p>
            <button
              onClick={() => setSelectedCategory('সব নোটিশ')}
              className="mt-4 px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors shadow-xs"
            >
              সব নোটিশ দেখুন
            </button>
          </div>
        )}
      </div>

      {/* Notice Detail Modal */}
      <NoticeModal
        notice={activeModalNotice}
        isOpen={activeModalNotice !== null}
        onClose={() => setActiveModalNotice(null)}
      />
    </section>
  );
};
