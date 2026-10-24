import React from 'react';
import imgTeacher from '../assets/images/teacher_mentorship_physics_1792301361137.jpg';
import { Target, CheckCircle2, Lightbulb, Users, ArrowRight } from 'lucide-react';

interface AboutProps {
  onOpenInquiry: () => void;
}

export const About: React.FC<AboutProps> = ({ onOpenInquiry }) => {
  return (
    <section id="about" className="py-16 md:py-24 bg-white border-y border-slate-200 relative">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Section Header */}
        <div className="max-w-3xl mb-12">
          <div className="text-xs uppercase tracking-widest text-blue-700 font-bold mb-2">
            ABOUT PHOTON — আমাদের সম্পর্কে
          </div>
          <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold text-slate-900 tracking-tight font-bangla">
            পদার্থবিজ্ঞানের আনন্দময় ও স্বচ্ছ ধারণার অভিযাত্রা
          </h2>
          <p className="mt-3 text-slate-600 font-bangla text-base sm:text-lg leading-relaxed">
            Photon Physics Academy focuses on making physics understandable, enjoyable, and concept-based for students in Mymensingh.
            আমরা বিশ্বাস করি, পদার্থবিজ্ঞান কোনো ভীতি বা মুখস্থের বিষয় নয়—এটি যুক্তি, পর্যবেক্ষণ ও চিন্তাশীলতার বিজ্ঞান।
          </p>
        </div>

        {/* Content & Visual Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
          {/* Left: Authentic Classroom Mentorship Image */}
          <div className="lg:col-span-5">
            <div className="relative rounded-2xl overflow-hidden border border-sky-100 shadow-xl bg-white group">
              <div className="aspect-[4/3] w-full overflow-hidden bg-slate-100">
                <img
                  src={imgTeacher}
                  alt="Physics Conceptual Teaching at Photon Physics Academy Mymensingh"
                  referrerPolicy="no-referrer"
                  loading="lazy"
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                />
              </div>
              <div className="p-4 bg-white border-t border-slate-100">
                <div className="text-xs font-bold text-slate-900 font-bangla">
                  বোর্ডে বাস্তব প্রপঞ্চ ও সমীকরণের চিত্ররূপ
                </div>
                <div className="text-[11px] text-slate-500 mt-0.5 font-bangla">
                  পদার্থবিজ্ঞানকে সহজভাবে হৃদয়াঙ্গম করানোর বাস্তবসম্মত পাঠদান পদ্ধতি।
                </div>
              </div>
            </div>
          </div>

          {/* Right: Core Educational Philosophy */}
          <div className="lg:col-span-7 space-y-6">
            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div className="p-5 rounded-xl bg-white border border-slate-200/90 hover:border-blue-400 hover:shadow-md transition-all">
                <div className="w-10 h-10 rounded-lg bg-sky-50 border border-sky-100 flex items-center justify-center text-blue-600 mb-3">
                  <Lightbulb className="w-5 h-5" />
                </div>
                <h3 className="text-base font-bold text-slate-900 font-bangla">কনসেপ্ট ও থিওরির স্পষ্টতা</h3>
                <p className="mt-1.5 text-xs sm:text-sm text-slate-600 font-bangla leading-relaxed">
                  মুখস্থ নির্ভরতার বিপরীতে প্রতিটি সূত্রের উৎপত্তি, তাৎপর্য ও ব্যবহারিক প্রয়োগের পুঙ্খানুপুঙ্খ ব্যাখ্যা।
                </p>
              </div>

              <div className="p-5 rounded-xl bg-white border border-slate-200/90 hover:border-blue-400 hover:shadow-md transition-all">
                <div className="w-10 h-10 rounded-lg bg-sky-50 border border-sky-100 flex items-center justify-center text-blue-600 mb-3">
                  <Target className="w-5 h-5" />
                </div>
                <h3 className="text-base font-bold text-slate-900 font-bangla">গাণিতিক সমস্যা সমাধান</h3>
                <p className="mt-1.5 text-xs sm:text-sm text-slate-600 font-bangla leading-relaxed">
                  সৃজনশীল প্রশ্ন (CQ) এবং বহুনির্বাচনী (MCQ)-এর জন্য পর্যায়ক্রমিক সমাধান পদ্ধতি ও ক্যালকুলেশন দক্ষতা।
                </p>
              </div>

              <div className="p-5 rounded-xl bg-white border border-slate-200/90 hover:border-blue-400 hover:shadow-md transition-all">
                <div className="w-10 h-10 rounded-lg bg-sky-50 border border-sky-100 flex items-center justify-center text-blue-600 mb-3">
                  <Users className="w-5 h-5" />
                </div>
                <h3 className="text-base font-bold text-slate-900 font-bangla">ব্যক্তিগত যত্ন ও পরামর্শ</h3>
                <p className="mt-1.5 text-xs sm:text-sm text-slate-600 font-bangla leading-relaxed">
                  শিক্ষার্থীদের ব্যক্তিগত জিজ্ঞাসা নিরসন ও নিয়মিত অগ্রগতি পর্যবেক্ষণের মাধ্যমে আত্মবিশ্বাস তৈরি।
                </p>
              </div>

              <div className="p-5 rounded-xl bg-white border border-slate-200/90 hover:border-blue-400 hover:shadow-md transition-all">
                <div className="w-10 h-10 rounded-lg bg-sky-50 border border-sky-100 flex items-center justify-center text-blue-600 mb-3">
                  <CheckCircle2 className="w-5 h-5" />
                </div>
                <h3 className="text-base font-bold text-slate-900 font-bangla">পরিমিত ও নিবেদিত পরিবেশ</h3>
                <p className="mt-1.5 text-xs sm:text-sm text-slate-600 font-bangla leading-relaxed">
                  একটি সুশৃঙ্খল ও আন্তরিক ক্লাসরুম যেখানে প্রশ্ন করতে শিক্ষার্থীরা কখনোই দ্বিধাবোধ করে না।
                </p>
              </div>
            </div>

            {/* Academy Inquiry Strip */}
            <div className="p-4 rounded-xl bg-white border border-sky-200/80 text-xs text-slate-600 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-xs">
              <span className="font-bangla">
                ময়মনসিংহে অবস্থিত এই একাডেমির বর্তমান ব্যাচসমূহ ও পাঠদান কার্যক্রম সম্পর্কে বিস্তারিত জানতে সরাসরি যোগাযোগ করুন।
              </span>
              <button
                onClick={onOpenInquiry}
                className="shrink-0 px-4 py-2 text-xs font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors flex items-center gap-1.5 shadow-xs"
              >
                <span>যোগাযোগ করুন</span>
                <ArrowRight className="w-3.5 h-3.5" />
              </button>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};
