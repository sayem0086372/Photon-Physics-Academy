import React from 'react';
import { BookOpen, Compass, MessageCircle, Info, Sparkles } from 'lucide-react';
import { PROGRAMS_DATA } from '../data/programs';
import { Program } from '../types';

interface ProgramsProps {
  onSelectProgram: (programName: string) => void;
}

export const Programs: React.FC<ProgramsProps> = ({ onSelectProgram }) => {
  return (
    <section id="programs" className="py-16 md:py-24 relative bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Section Header */}
        <div className="flex flex-col md:flex-row md:items-end justify-between mb-10 pb-6 border-b border-slate-200 gap-4">
          <div className="max-w-3xl">
            <div className="text-xs uppercase tracking-widest text-blue-700 font-bold mb-2 flex items-center gap-2">
              <Sparkles className="w-3.5 h-3.5" />
              LEARNING PATHWAYS — শিক্ষা কার্যক্রম
            </div>
            <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold text-slate-900 tracking-tight font-bangla">
              শ্রেণিভিত্তিক অ্যাকাডেমিক কার্যক্রম
            </h2>
            <p className="mt-2 text-slate-600 font-bangla text-sm sm:text-base">
              নবম ও দশম শ্রেণির শিক্ষার্থীদের জন্য পদার্থবিজ্ঞানের সমন্বিত ও নিবিড় পাঠদান রূপরেখা।
            </p>
          </div>

          {/* Important Transparency Notice Banner */}
          <div className="bg-sky-50 border border-sky-200 rounded-xl p-3.5 text-xs text-slate-700 flex items-start gap-2.5 max-w-md shadow-2xs">
            <Info className="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
            <div className="font-bangla leading-relaxed">
              <span className="font-semibold text-slate-900">অ্যাকাডেমিক নোটিশ:</span> বর্তমান সক্রিয় ব্যাচ ও রুটিন নিশ্চিতকরণের জন্য সরাসরি একাডেমি অফিসে যোগাযোগ করার অনুরোধ রইল।
            </div>
          </div>
        </div>

        {/* 2-Card Prominent Grid for Class 8, 9 and 10 */}
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
          {PROGRAMS_DATA.map((prog: Program) => (
            <div
              key={prog.id}
              className="flex flex-col justify-between rounded-2xl border border-sky-100 bg-gradient-to-b from-white to-sky-50/30 hover:border-blue-400 hover:shadow-xl transition-all duration-300 p-7 sm:p-8 shadow-sm group"
            >
              <div>
                <div className="flex items-center justify-between mb-5">
                  <div className="w-12 h-12 rounded-xl bg-sky-100 border border-sky-200 flex items-center justify-center group-hover:scale-105 transition-transform">
                    <BookOpen className="w-6 h-6 text-blue-700" />
                  </div>
                  <span className="text-xs font-semibold text-blue-800 bg-sky-100 border border-sky-200 px-3 py-1 rounded-full">
                    {prog.name}
                  </span>
                </div>

                <h3 className="text-2xl font-bold text-slate-900 font-bangla group-hover:text-blue-700 transition-colors">
                  {prog.banglaName}
                </h3>
                <p className="text-sm text-slate-500 font-bangla mt-1 mb-5">
                  {prog.subtitle}
                </p>

                {/* Target Audience */}
                <div className="text-xs text-slate-700 pb-3 mb-4 border-b border-slate-100 bg-white p-3 rounded-lg border border-slate-200/80">
                  <span className="text-slate-500">উপযুক্ত শিক্ষার্থী:</span>{' '}
                  <span className="font-semibold text-slate-900 font-bangla">{prog.targetAudience}</span>
                </div>

                {/* Features List */}
                <ul className="space-y-2.5 mb-8">
                  {prog.features.map((feature, idx) => (
                    <li key={idx} className="flex items-start gap-2.5 text-xs sm:text-sm text-slate-700 font-bangla">
                      <span className="w-2 h-2 rounded-full bg-blue-600 mt-1.5 shrink-0" />
                      <span>{feature}</span>
                    </li>
                  ))}
                </ul>
              </div>

              {/* Card Footer: Status and Direct Action */}
              <div className="pt-5 border-t border-slate-100 space-y-3.5">
                <div className="text-xs text-slate-500 font-bangla flex items-center gap-1.5">
                  <Compass className="w-3.5 h-3.5 text-blue-600 shrink-0" />
                  <span>{prog.statusLabelBangla}</span>
                </div>

                <button
                  onClick={() => onSelectProgram(prog.banglaName)}
                  className="w-full py-3 px-4 text-xs sm:text-sm font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-colors flex items-center justify-center gap-2"
                >
                  <MessageCircle className="w-4 h-4" />
                  <span>ব্যাচ ও সময়সূচি জানতে যোগাযোগ</span>
                </button>
              </div>
            </div>
          ))}
        </div>

        {/* Note */}
        <div className="mt-10 text-center text-xs text-slate-500 font-bangla max-w-xl mx-auto">
          প্রোগ্রামের বিস্তারিত ফি ও ব্যাচের নতুন সময়সূচি একাডেমির অফিশিয়াল নোটিশ বোর্ডে প্রকাশ করা হবে।
        </div>
      </div>
    </section>
  );
};
