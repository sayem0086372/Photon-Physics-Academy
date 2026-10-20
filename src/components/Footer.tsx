import React from 'react';
import { Phone, MapPin, ArrowUp, Shield } from 'lucide-react';
import { Logo } from './Logo';

interface FooterProps {
  onOpenAdmin?: () => void;
}

export const Footer: React.FC<FooterProps> = ({ onOpenAdmin }) => {
  const scrollToTop = () => {
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <footer className="bg-slate-900 border-t border-slate-800 text-slate-400 text-xs">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-16">
        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-12">
          {/* Brand & Mission Statement with Logo */}
          <div className="lg:col-span-5 space-y-4">
            <div className="bg-white/95 p-3 rounded-xl inline-block shadow-md">
              <Logo size="md" showText={true} />
            </div>

            <p className="text-slate-300 font-bangla text-xs sm:text-sm leading-relaxed max-w-sm">
              পদার্থবিজ্ঞানের ধারণা হোক সহজ ও আনন্দময়। মুখস্থের পরিবর্তে বাস্তব ধারণা, যৌক্তিক বিশ্লেষণ ও নিয়মিত অনুশীলনের মাধ্যমে ময়মনসিংহের নবম ও দশম শ্রেণির শিক্ষার্থীদের পদার্থবিজ্ঞানে আত্মবিশ্বাসী করে তোলা আমাদের লক্ষ্য।
            </p>

            <div className="text-[11px] font-mono text-sky-400 font-semibold">
              "Feel the Power of Physics..."
            </div>
          </div>

          {/* Quick Navigation Links */}
          <div className="lg:col-span-3 space-y-3">
            <h4 className="text-sm font-bold text-white uppercase tracking-wider font-bangla">
              প্রয়োজনীয় লিংক
            </h4>
            <ul className="space-y-2">
              <li>
                <a href="#home" className="hover:text-sky-400 transition-colors">
                  হোম পেজ
                </a>
              </li>
              <li>
                <a href="#about" className="hover:text-sky-400 transition-colors">
                  আমাদের সম্পর্কে (About Us)
                </a>
              </li>
              <li>
                <a href="#programs" className="hover:text-sky-400 transition-colors">
                  শিক্ষা কার্যক্রম (Programs)
                </a>
              </li>
              <li>
                <a href="#gallery" className="hover:text-sky-400 transition-colors">
                  ক্লাসরুম গ্যালারি (Classrooms)
                </a>
              </li>
              <li>
                <a href="/store" className="hover:text-sky-400 transition-colors">
                  বুক স্টোর (Book Store)
                </a>
              </li>
              <li>
                <a href="#notices" className="hover:text-sky-400 transition-colors">
                  নোটিশ বোর্ড (Notice Board)
                </a>
              </li>
              <li>
                <a href="#results" className="hover:text-sky-400 transition-colors">
                  ফলাফল নীতি (Results)
                </a>
              </li>
              {onOpenAdmin && (
                <li>
                  <button
                    onClick={onOpenAdmin}
                    className="hover:text-sky-400 transition-colors flex items-center gap-1.5 text-left font-bangla text-sky-400/90 font-medium pt-1"
                  >
                    <Shield className="w-3.5 h-3.5" />
                    <span>অ্যাডমিন ড্যাশবোর্ড (Admin Panel)</span>
                  </button>
                </li>
              )}
            </ul>
          </div>

          {/* Contact and Location */}
          <div className="lg:col-span-4 space-y-3">
            <h4 className="text-sm font-bold text-white uppercase tracking-wider font-bangla">
              যোগাযোগ ও ঠিকানা
            </h4>
            <div className="space-y-2.5">
              <div className="flex items-start gap-2.5 text-slate-300">
                <MapPin className="w-4 h-4 text-sky-400 shrink-0 mt-0.5" />
                <span className="font-bangla">১০, জিলা স্কুল রোড (জিলা স্কুলের বিপরীত গলি), ময়মনসিংহ</span>
              </div>

              <div className="flex items-center gap-2.5 text-slate-300">
                <Phone className="w-4 h-4 text-sky-400 shrink-0" />
                <a href="tel:+8801312364499" className="hover:text-sky-400 font-mono font-semibold">
                  +880 1312-364499
                </a>
              </div>

              <div className="flex items-center gap-2.5 text-slate-300">
                <Phone className="w-4 h-4 text-sky-400 shrink-0" />
                <a href="tel:01722003995" className="hover:text-sky-400 font-mono font-semibold">
                  01722-003995
                </a>
              </div>
            </div>

            <div className="pt-2 text-[11px] text-slate-400 font-bangla">
              ভর্তি সংক্রান্ত অনুসন্ধান ও বই সংক্রান্ত তথ্যের জন্য সকাল ৯টা থেকে রাত ৯টা পর্যন্ত যোগাযোগ করুন।
            </div>
          </div>
        </div>

        {/* Bottom Bar: Copyright & Back-to-Top */}
        <div className="mt-12 pt-6 border-t border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 text-slate-400">
          <div>
            © 2026 Photon Physics Academy. সর্বস্বত্ব সংরক্ষিত (All rights reserved).
          </div>

          <div className="flex items-center gap-4">
            <button
              onClick={scrollToTop}
              className="p-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white border border-slate-700 transition-colors flex items-center gap-1 text-[11px]"
              aria-label="Back to Top"
            >
              <span>উপরে যান</span>
              <ArrowUp className="w-3.5 h-3.5" />
            </button>
          </div>
        </div>
      </div>
    </footer>
  );
};
