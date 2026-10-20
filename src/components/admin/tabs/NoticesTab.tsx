import React, { useState } from 'react';
import { Bell, Plus, Trash2, Calendar, Tag, Check, Eye, X } from 'lucide-react';
import { AdminNoticeRecord } from '../../../types/admin';
import { EmptyState } from '../ui/EmptyState';
import { SectionToolbar } from '../ui/SectionToolbar';
import { StatusBadge } from '../ui/StatusBadge';

interface NoticesTabProps {
  notices: AdminNoticeRecord[];
  onAddNotice: (notice: Omit<AdminNoticeRecord, 'id'>) => void;
  onDeleteNotice: (id: string) => void;
  onTogglePublish: (id: string) => void;
}

export const NoticesTab: React.FC<NoticesTabProps> = ({
  notices,
  onAddNotice,
  onDeleteNotice,
  onTogglePublish,
}) => {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [newNotice, setNewNotice] = useState({
    title: '',
    banglaTitle: '',
    category: 'ভর্তি বিজ্ঞপ্তি' as AdminNoticeRecord['category'],
    shortDescription: '',
    fullContent: '',
    date: '18 Oct 2026',
    isPublished: true,
    hasAttachment: false,
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newNotice.banglaTitle.trim()) {
      alert('নোটিশের বাংলা শিরোনাম প্রদান করুন।');
      return;
    }
    onAddNotice(newNotice);
    setIsModalOpen(false);
    setNewNotice({
      title: '',
      banglaTitle: '',
      category: 'ভর্তি বিজ্ঞপ্তি',
      shortDescription: '',
      fullContent: '',
      date: '18 Oct 2026',
      isPublished: true,
      hasAttachment: false,
    });
  };

  return (
    <div className="space-y-6">
      <SectionToolbar
        title="Notice Management"
        description="ওয়েবসাইটে প্রদর্শিত ভর্তি, পরীক্ষা ও ক্লাস রুটিন সম্পর্কিত ঘোষণা নিয়ন্ত্রণ করুন।"
      >
        <button onClick={() => setIsModalOpen(true)} className="px-3.5 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs flex items-center gap-1.5 transition-colors whitespace-nowrap">
          <Plus className="w-4 h-4" />
          <span>New Notice</span>
        </button>
      </SectionToolbar>

      {/* Notices Grid */}
      {notices.length === 0 ? (
        <div className="bg-white rounded-xl border border-slate-200">
          <EmptyState
            icon={Bell}
            title="No notices yet"
            description="প্রথম নোটিশটি প্রকাশ করলে তা ওয়েবসাইটের নোটিশ বোর্ডে দেখা যাবে।"
            action={{ label: 'New Notice', onClick: () => setIsModalOpen(true), icon: Plus }}
          />
        </div>
      ) : (
      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
        {notices.map((n) => (
          <div
            key={n.id}
            className="p-5 rounded-xl bg-white border border-slate-200 flex flex-col justify-between"
          >
            <div>
              <div className="flex items-center justify-between text-xs text-slate-500 mb-2">
                <span className="font-semibold text-blue-700 bg-sky-50 px-2 py-0.5 rounded border border-sky-100">
                  {n.category}
                </span>
                <span className="flex items-center gap-1 font-mono text-[11px]">
                  <Calendar className="w-3 h-3 text-slate-400" />
                  {n.date}
                </span>
              </div>

              <h3 className="text-base font-bold text-slate-900 font-bangla leading-snug">
                {n.banglaTitle}
              </h3>
              {n.title && <p className="text-[11px] font-mono text-slate-400 mt-0.5 mb-2">{n.title}</p>}

              <p className="text-xs text-slate-600 font-bangla leading-relaxed line-clamp-3 mb-4">
                {n.shortDescription}
              </p>
            </div>

            <div className="pt-3 border-t border-slate-100 flex items-center justify-between text-xs">
              <button
                onClick={() => onTogglePublish(n.id)}
                className={`px-2.5 py-1 rounded-md text-[11px] font-semibold flex items-center gap-1 transition-colors ${
                  n.isPublished
                    ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                    : 'bg-slate-100 text-slate-600 border border-slate-200'
                }`}
              >
                <Check className="w-3 h-3" />
                <span>{n.isPublished ? 'Published' : 'Draft'}</span>
              </button>

              <button
                onClick={() => {
                  if (confirm('আপনি কি এই নোটিশটি মুছে ফেলতে চান?')) {
                    onDeleteNotice(n.id);
                  }
                }}
                className="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition-colors"
                title="Delete Notice"
              >
                <Trash2 className="w-4 h-4" />
              </button>
            </div>
          </div>
        ))}
      </div>
      )}

      {/* Add Notice Modal */}
      {isModalOpen && (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
          <div className="relative max-w-lg w-full bg-white rounded-xl border border-slate-200 shadow-2xl p-6">
            <button
              onClick={() => setIsModalOpen(false)}
              className="absolute top-5 right-5 p-1.5 rounded-lg text-slate-400 hover:text-slate-800 bg-slate-100"
            >
              <X className="w-5 h-5" />
            </button>

            <h3 className="text-lg font-bold text-slate-900 font-bangla mb-1">
              Create Notice
            </h3>
            <p className="text-xs text-slate-500 mb-5">
              ওয়েবসাইটের পাবলিক নোটিশ বোর্ডে তাৎক্ষণিক আপডেট হবে।
            </p>

            <form onSubmit={handleSubmit} className="space-y-4">
              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  বাংলা শিরোনাম *
                </label>
                <input
                  type="text"
                  placeholder="যেমন: গতিবিদ্যা অধ্যায় মূল্যায়ন পরীক্ষার সময়সূচি"
                  value={newNotice.banglaTitle}
                  onChange={(e) => setNewNotice({ ...newNotice, banglaTitle: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bangla text-slate-900"
                  required
                />
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  ইংরেজি শিরোনাম (ঐচ্ছিক)
                </label>
                <input
                  type="text"
                  placeholder="e.g. Kinematics Chapter Evaluation Exam Routine"
                  value={newNotice.title}
                  onChange={(e) => setNewNotice({ ...newNotice, title: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                />
              </div>

              <div className="grid grid-cols-2 gap-3">
                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    ক্যাটাগরি
                  </label>
                  <select
                    value={newNotice.category}
                    onChange={(e) =>
                      setNewNotice({
                        ...newNotice,
                        category: e.target.value as AdminNoticeRecord['category'],
                      })
                    }
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs text-slate-900"
                  >
                    <option value="ভর্তি বিজ্ঞপ্তি">ভর্তি বিজ্ঞপ্তি</option>
                    <option value="পরীক্ষার সময়সূচি">পরীক্ষার সময়সূচি</option>
                    <option value="ক্লাসের সময়সূচি">ক্লাসের সময়সূচি</option>
                    <option value="জরুরি ঘোষণা">জরুরি ঘোষণা</option>
                    <option value="ফলাফল প্রকাশ">ফলাফল প্রকাশ</option>
                  </select>
                </div>

                <div>
                  <label className="block text-xs font-semibold text-slate-700 mb-1">
                    প্রকাশের তারিখ
                  </label>
                  <input
                    type="text"
                    value={newNotice.date}
                    onChange={(e) => setNewNotice({ ...newNotice, date: e.target.value })}
                    className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono text-slate-900"
                  />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-slate-700 mb-1">
                  সংক্ষিপ্ত বিবরণ *
                </label>
                <textarea
                  rows={2}
                  value={newNotice.shortDescription}
                  onChange={(e) => setNewNotice({ ...newNotice, shortDescription: e.target.value })}
                  className="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-bangla text-slate-900 resize-none"
                  placeholder="নোটিশের মূল বক্তব্য সংক্ষেপে লিখুন..."
                  required
                />
              </div>

              <div className="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                <button
                  type="button"
                  onClick={() => setIsModalOpen(false)}
                  className="px-4 py-2 text-xs font-medium text-slate-600 hover:text-slate-900 bg-slate-100 rounded-lg"
                >
                  Cancel
                </button>
                <button
                  type="submit"
                  className="px-5 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm"
                >
                  Publish Notice
                </button>
              </div>
            </form>
          </div>
        </div>
      )}
    </div>
  );
};
