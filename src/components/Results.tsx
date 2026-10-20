import React from 'react';
import { Award, FileCheck, PhoneCall } from 'lucide-react';

interface ResultsProps {
  onOpenInquiry: () => void;
}

export const Results: React.FC<ResultsProps> = ({ onOpenInquiry }) => {
  return (
    <section id="results" className="py-16 md:py-24 relative bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Section Header */}
        <div className="max-w-3xl mb-12">
          <div className="text-xs uppercase tracking-widest text-blue-700 font-bold mb-2 flex items-center gap-2">
            <Award className="w-3.5 h-3.5" />
            RESULTS — আমাদের ফলাফল
          </div>
          <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold text-slate-900 tracking-tight font-bangla">
            যাচাইকৃত ফলাফল ও সাফল্যের প্রতিশ্রুতি
          </h2>
          <p className="mt-3 text-slate-600 font-bangla text-base sm:text-lg leading-relaxed">
            ফোটন ফিজিক্স একাডেমি শিক্ষার্থীদের বাস্তব মেধা, সততা ও পরিশ্রমের মূল্যায়নে বিশ্বাসী।
          </p>
        </div>

        {/* Mandatory Transparency Box: "ফলাফল ও অর্জনের তথ্য শীঘ্রই প্রকাশ করা হবে।" */}
        <div className="rounded-2xl border border-sky-200 bg-gradient-to-br from-white via-sky-50/30 to-blue-50/40 p-8 sm:p-10 shadow-lg relative overflow-hidden">
          <div className="relative z-10 max-w-2xl">
            <div className="inline-flex items-center gap-2 text-xs font-bold text-blue-900 bg-sky-100 border border-sky-200 px-3 py-1 rounded-full mb-4">
              <FileCheck className="w-3.5 h-3.5 text-blue-700" />
              <span>সত্যনিষ্ঠ ফলাফল প্রকাশ নীতি</span>
            </div>

            {/* Prompt's required exact text */}
            <h3 className="text-xl sm:text-2xl font-bold text-slate-900 font-bangla leading-snug mb-3">
              ফলাফল ও অর্জনের তথ্য শীঘ্রই প্রকাশ করা হবে।
            </h3>

            <p className="text-slate-600 font-bangla text-sm sm:text-base leading-relaxed mb-6">
              একাডেমির অফিশিয়াল বোর্ড পরীক্ষা ও পদার্থবিজ্ঞান মূল্যায়নের যথাযথ যাচাইকৃত ফলাফল প্রস্তুত সাপেক্ষে এখানে নিয়মিত হালনাগাদ করা হবে। কোনো আনুমানিক পরিসংখ্যান বা অপ্রমাণিত দাবি এখানে প্রচার করা হয় না।
            </p>

            {/* How verified results will be structured */}
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8 pt-4 border-t border-sky-100 text-xs">
              <div className="bg-white border border-sky-100 rounded-xl p-4 shadow-2xs">
                <div className="text-blue-800 font-bold mb-1 font-bangla text-sm">নবম ও দশম শ্রেণি মূল্যায়ন</div>
                <div className="text-slate-600 font-bangla">অধ্যায়ভিত্তিক অগ্রগতি ও কনসেপ্ট যাচাই পরীক্ষার রেকর্ড</div>
              </div>

              <div className="bg-white border border-sky-100 rounded-xl p-4 shadow-2xs">
                <div className="text-blue-800 font-bold mb-1 font-bangla text-sm">এসএসসি বোর্ড ফলাফল</div>
                <div className="text-slate-600 font-bangla">মাধ্যমিক পরীক্ষার অফিশিয়াল জিপিএ ও এ+ প্রাপ্তির স্বীকৃতি</div>
              </div>
            </div>

            {/* Direct Contact Button */}
            <div className="flex flex-wrap items-center gap-3">
              <button
                onClick={onOpenInquiry}
                className="px-5 py-2.5 text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-colors flex items-center gap-2"
              >
                <PhoneCall className="w-4 h-4" />
                <span>ফলাফল ও মূল্যায়ন পদ্ধতি সম্পর্কে জানুন</span>
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};
