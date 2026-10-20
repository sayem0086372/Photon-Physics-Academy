import React from 'react';
import imgHero from '../assets/images/hero_physics_classroom_1792301360099.jpg';
import { Phone, ArrowRight, MapPin, Compass, BookOpen, Sparkles, CheckCircle2 } from 'lucide-react';
import { Logo } from './Logo';

interface HeroProps {
  onOpenInquiry: (program?: string) => void;
}

export const Hero: React.FC<HeroProps> = ({ onOpenInquiry }) => {
  return (
    <section id="home" className="relative pt-8 pb-16 md:pt-14 md:pb-24 overflow-hidden bg-gradient-to-b from-sky-50/60 via-white to-slate-50/80">
      {/* Background delicate scientific grid & subtle sky ambient light */}
      <div className="absolute inset-0 physics-grid opacity-60 pointer-events-none" />
      <div className="absolute top-10 right-10 w-96 h-96 bg-sky-200/40 blur-[120px] rounded-full pointer-events-none" />
      <div className="absolute bottom-10 left-10 w-80 h-80 bg-blue-100/50 blur-[100px] rounded-full pointer-events-none" />

      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        {/* Unboxed Metadata Trust Kicker */}
        <div className="flex flex-wrap items-center gap-2 text-xs text-slate-500 mb-6">
          <span className="flex items-center gap-1.5 text-blue-700 font-semibold bg-sky-100/80 px-2.5 py-1 rounded-md">
            <MapPin className="w-3.5 h-3.5" />
            ১০, জিলা স্কুল রোড, ময়মনসিংহ
          </span>
          <span aria-hidden="true" className="text-slate-300">·</span>
          <span>কনসেপ্টভিত্তিক পদার্থবিজ্ঞান একাডেমি</span>
          <span aria-hidden="true" className="text-slate-300">·</span>
          <span>নবম ও দশম শ্রেণি অ্যাকাডেমিক কেয়ার</span>
        </div>

        {/* Hero Split Grid */}
        <div className="grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 items-center">
          {/* Left Column: Headings, Value Proposition & Actions */}
          <div className="lg:col-span-7 space-y-6">
            <div className="space-y-3">
              <span className="text-xs uppercase tracking-widest text-blue-700 font-bold flex items-center gap-2">
                <Sparkles className="w-3.5 h-3.5" />
                PHOTON PHYSICS ACADEMY
              </span>
              
              <h1 className="text-3xl sm:text-4xl lg:text-5xl font-extrabold text-slate-900 tracking-tight leading-tight">
                Feel the Power of <span className="text-transparent bg-clip-text bg-gradient-to-r from-blue-700 via-sky-600 to-blue-800">Physics...</span>
              </h1>
              
              <h2 className="text-2xl sm:text-3xl font-bangla font-bold text-slate-800 leading-snug">
                পদার্থবিজ্ঞানের ধারণা হোক সহজ ও আনন্দময়
              </h2>
            </div>

            <p className="text-base sm:text-lg text-slate-600 font-normal leading-relaxed max-w-2xl font-bangla">
              Physics learning in Mymensingh with a focus on concepts, practice and confidence.
              মুখস্থের পরিবর্তে প্রতিটি সূত্রের গভীর বাস্তবতা, যৌক্তিক বিশ্লেষণ ও নিয়মিত অনুশীলনের মাধ্যমে পদার্থবিজ্ঞানে নিখুঁত পারদর্শিতা অর্জন।
            </p>

            {/* Direct Action Buttons */}
            <div className="flex flex-wrap items-center gap-3 pt-2">
              <a
                href="#programs"
                className="px-6 py-3.5 text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-700 hover:to-sky-700 rounded-lg shadow-md shadow-blue-500/20 hover:shadow-blue-500/30 transition-all duration-200 flex items-center gap-2"
              >
                <span>Explore Programs</span>
                <ArrowRight className="w-4 h-4" />
              </a>

              <a
                href="#contact"
                className="px-6 py-3.5 text-sm font-semibold text-slate-800 bg-white hover:bg-sky-50 border border-slate-300 rounded-lg transition-colors flex items-center gap-2 shadow-xs"
              >
                <span>যোগাযোগ করুন</span>
              </a>

              <button
                onClick={() => onOpenInquiry()}
                className="px-5 py-3.5 text-sm font-semibold text-blue-900 bg-sky-100/90 hover:bg-sky-200/90 border border-sky-300 rounded-lg transition-colors"
              >
                ভর্তি অনুসন্ধান
              </button>
            </div>

            {/* Clickable Phone Number Bar */}
            <div className="pt-4 border-t border-slate-200">
              <div className="text-xs text-slate-500 mb-2 font-medium">সরাসরি কথা বলুন (Click to Call):</div>
              <div className="flex flex-wrap items-center gap-3 text-sm font-medium">
                <a
                  href="tel:+8801312364499"
                  className="flex items-center gap-2 text-slate-800 hover:text-blue-700 py-1.5 px-3 rounded-lg bg-white border border-slate-200 hover:border-sky-300 shadow-2xs transition-colors"
                >
                  <Phone className="w-4 h-4 text-blue-600" />
                  <span className="font-mono tabular-nums font-semibold">+880 1312-364499</span>
                </a>

                <a
                  href="tel:01722003995"
                  className="flex items-center gap-2 text-slate-800 hover:text-blue-700 py-1.5 px-3 rounded-lg bg-white border border-slate-200 hover:border-sky-300 shadow-2xs transition-colors"
                >
                  <Phone className="w-4 h-4 text-blue-600" />
                  <span className="font-mono tabular-nums font-semibold">01722-003995</span>
                </a>
              </div>
            </div>
          </div>

          {/* Right Column: Authentic Classroom Photo Showcase & Official Brand Identity */}
          <div className="lg:col-span-5 space-y-5">
            {/* Real Academy Classroom Image Frame */}
            <div className="relative group rounded-2xl overflow-hidden border border-sky-100 bg-white shadow-xl">
              <div className="aspect-[16/10] w-full overflow-hidden bg-slate-100">
                <img
                  src={imgHero}
                  alt="Photon Physics Academy Classroom Learning in Mymensingh"
                  referrerPolicy="no-referrer"
                  loading="eager"
                  className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                />
              </div>
              <div className="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-transparent to-transparent pointer-events-none" />
              <div className="absolute bottom-3 left-4 right-4 flex items-center justify-between text-xs text-white">
                <div className="flex items-center gap-2 font-bangla font-medium">
                  <span className="w-2 h-2 rounded-full bg-sky-400" />
                  <span>ক্লাসরুম শিখন পরিবেশ · ময়মনসিংহ</span>
                </div>
                <span className="text-[11px] font-mono text-sky-200">Kinematics & Waves</span>
              </div>
            </div>

            {/* Official Logo Brand Card */}
            <div className="rounded-2xl border border-sky-100 bg-white p-5 shadow-lg flex items-center gap-5">
              <Logo size="xl" showText={false} />
              <div className="space-y-1">
                <div className="text-base font-extrabold text-blue-950 font-sans tracking-tight">
                  PHOTON PHYSICS ACADEMY
                </div>
                <div className="text-xs font-semibold text-blue-700 font-bangla">
                  পদার্থবিজ্ঞানের ধারণা হোক সহজ ও আনন্দময়
                </div>
                <div className="text-[11px] text-slate-500 font-bangla flex items-center gap-1 pt-1">
                  <CheckCircle2 className="w-3.5 h-3.5 text-emerald-600 shrink-0" />
                  <span>ময়মনসিংহের বিশ্বস্ত পদার্থবিজ্ঞান অ্যাকাডেমিক কেয়ার</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        {/* 4-Item Proof & Values Strip Below Split */}
        <div className="mt-12 pt-8 border-t border-slate-200/80 grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
          <div className="p-4 rounded-xl bg-white border border-sky-100 shadow-xs">
            <div className="flex items-center gap-2 text-blue-600 mb-1">
              <Compass className="w-4 h-4" />
              <span className="text-xs uppercase tracking-wider text-slate-500 font-semibold">মেথডোলজি</span>
            </div>
            <div className="text-sm font-semibold text-slate-900 font-bangla">কনসেপ্টভিত্তিক পাঠদান</div>
            <div className="text-xs text-slate-500 font-bangla mt-0.5">গাণিতিক ও যৌক্তিক বিশ্লেষণ</div>
          </div>

          <div className="p-4 rounded-xl bg-white border border-sky-100 shadow-xs">
            <div className="flex items-center gap-2 text-blue-600 mb-1">
              <BookOpen className="w-4 h-4" />
              <span className="text-xs uppercase tracking-wider text-slate-500 font-semibold">রিসোর্স</span>
            </div>
            <div className="text-sm font-semibold text-slate-900 font-bangla">নিজস্ব কনসেপ্ট বুক</div>
            <div className="text-xs text-slate-500 font-bangla mt-0.5">জুয়েল স্যার রচিত ও সম্পাদিত</div>
          </div>

          <div className="p-4 rounded-xl bg-white border border-sky-100 shadow-xs">
            <div className="flex items-center gap-2 text-blue-600 mb-1">
              <Sparkles className="w-4 h-4" />
              <span className="text-xs uppercase tracking-wider text-slate-500 font-semibold">প্র্যাকটিস</span>
            </div>
            <div className="text-sm font-semibold text-slate-900 font-bangla">নিয়মিত প্রবলেম সলভিং</div>
            <div className="text-xs text-slate-500 font-bangla mt-0.5">অধ্যায়ভিত্তিক মূল্যায়ন পরীক্ষা</div>
          </div>

          <div className="p-4 rounded-xl bg-white border border-sky-100 shadow-xs">
            <div className="flex items-center gap-2 text-blue-600 mb-1">
              <MapPin className="w-4 h-4" />
              <span className="text-xs uppercase tracking-wider text-slate-500 font-semibold">অবস্থান</span>
            </div>
            <div className="text-sm font-semibold text-slate-900 font-bangla">ময়মনসিংহ শহর</div>
            <div className="text-xs text-slate-500 font-bangla mt-0.5">সহজ যোগাযোগ ও ক্লাসরুম সুবিধা</div>
          </div>
        </div>
      </div>
    </section>
  );
};
