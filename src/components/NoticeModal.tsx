import React, { useEffect } from 'react';
import { X, Calendar, Tag, FileText, Phone, Download, AlertTriangle } from 'lucide-react';
import { Notice } from '../types';

interface NoticeModalProps {
  notice: Notice | null;
  isOpen: boolean;
  onClose: () => void;
}

export const NoticeModal: React.FC<NoticeModalProps> = ({ notice, isOpen, onClose }) => {
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      if (isOpen && e.key === 'Escape') onClose();
    };
    window.addEventListener('keydown', handleKeyDown);
    return () => window.removeEventListener('keydown', handleKeyDown);
  }, [isOpen, onClose]);

  if (!isOpen || !notice) return null;

  return (
    <div
      role="dialog"
      aria-modal="true"
      aria-labelledby="modal-notice-title"
      className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 sm:p-6"
    >
      <div className="relative max-w-2xl w-full rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-2xl max-h-[90vh] overflow-y-auto">
        {/* Close Button */}
        <button
          onClick={onClose}
          className="absolute top-5 right-5 p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-900 transition-colors"
          aria-label="Close Notice"
        >
          <X className="w-5 h-5" />
        </button>

        {/* Demo indicator note */}
        {notice.isSample && (
          <div className="mb-4 inline-flex items-center gap-1.5 text-xs font-semibold text-amber-800 bg-amber-50 border border-amber-200 px-3 py-1 rounded-md">
            <AlertTriangle className="w-4 h-4 text-amber-600" />
            <span>নমুনা নোটিশ কাঠামো · ফ্রন্টএন্ড স্টেপ ১ প্রদর্শনী</span>
          </div>
        )}

        {/* Metadata */}
        <div className="flex flex-wrap items-center gap-3 text-xs text-slate-500 mb-3">
          <span className="flex items-center gap-1.5 text-blue-700 font-semibold">
            <Tag className="w-3.5 h-3.5" />
            <span>{notice.category}</span>
          </span>
          <span aria-hidden="true" className="text-slate-300">·</span>
          <span className="flex items-center gap-1.5 font-medium">
            <Calendar className="w-3.5 h-3.5 text-slate-400" />
            <span>{notice.date}</span>
          </span>
        </div>

        {/* Title */}
        <h3 id="modal-notice-title" className="text-xl sm:text-2xl font-bold text-slate-900 font-bangla mb-1">
          {notice.banglaTitle}
        </h3>
        <p className="text-xs font-mono text-slate-500 mb-6">{notice.title}</p>

        {/* Full Notice Content */}
        <div className="p-4 rounded-xl bg-slate-50 border border-slate-200 text-sm sm:text-base text-slate-800 font-bangla whitespace-pre-line leading-relaxed mb-6">
          {notice.fullContent || notice.shortDescription}
        </div>

        {/* Optional PDF Download */}
        {notice.pdfAttachment && (
          <div className="p-3.5 rounded-xl bg-sky-50 border border-sky-200 flex items-center justify-between gap-3 mb-6">
            <div className="flex items-center gap-2.5 text-xs text-slate-800">
              <FileText className="w-4 h-4 text-blue-600" />
              <span className="font-semibold">{notice.pdfAttachment.filename}</span>
              {notice.pdfAttachment.size && (
                <span className="text-slate-500">({notice.pdfAttachment.size})</span>
              )}
            </div>
            <button
              onClick={() => {
                alert('স্টেপ ১ স্ট্যাটিক ফ্রন্টএন্ড ডেমো: অফিশিয়াল পিডিএফ ফাইল স্টেপ ২-এ ব্যাকএন্ড সংযুক্তির পর ডাউনলোডের জন্য উন্মুক্ত হবে।');
              }}
              className="px-3.5 py-1.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg flex items-center gap-1.5 transition-colors shadow-xs"
            >
              <Download className="w-3.5 h-3.5" />
              <span>ডাউনলোড</span>
            </button>
          </div>
        )}

        {/* Action / Help Footer */}
        <div className="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
          <div className="flex items-center gap-2 text-xs text-slate-600 font-medium">
            <Phone className="w-3.5 h-3.5 text-blue-600" />
            <span>জরুরি জিজ্ঞাসা: 01312-364499</span>
          </div>

          <button
            onClick={onClose}
            className="w-full sm:w-auto px-4 py-2 text-xs font-semibold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors"
          >
            বন্ধ করুন
          </button>
        </div>
      </div>
    </div>
  );
};
