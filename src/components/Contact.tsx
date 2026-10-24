import React, { useState } from 'react';
import { Phone, MessageCircle, MapPin, Send, CheckCircle2, Clock, AlertCircle } from 'lucide-react';

interface ContactProps {
  prefilledProgram?: string;
}

export const Contact: React.FC<ContactProps> = ({ prefilledProgram = '' }) => {
  const [name, setName] = useState('');
  const [phone, setPhone] = useState('');
  const [program, setProgram] = useState(prefilledProgram || 'নবম শ্রেণি পদার্থবিজ্ঞান');
  const [message, setMessage] = useState('');
  const [errors, setErrors] = useState<{ [key: string]: string }>({});
  const [isSubmitted, setIsSubmitted] = useState(false);

  // Synchronize when prefilledProgram changes externally (e.g. from program card or book card)
  React.useEffect(() => {
    if (prefilledProgram) {
      setProgram(prefilledProgram);
      const contactSection = document.getElementById('contact');
      if (contactSection) {
        contactSection.scrollIntoView({ behavior: 'smooth' });
      }
    }
  }, [prefilledProgram]);

  // Bangladeshi phone number validation
  const validatePhone = (num: string) => {
    const cleaned = num.replace(/[\s-]/g, '');
    const banglaPattern = /^(?:\+8801|01)[3-9]\d{8}$/;
    return banglaPattern.test(cleaned);
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const newErrors: { [key: string]: string } = {};

    if (!name.trim()) {
      newErrors.name = 'শিক্ষার্থী বা অভিভাবকের নাম প্রদান করুন';
    }

    if (!phone.trim()) {
      newErrors.phone = 'ফোন নম্বর প্রদান করুন';
    } else if (!validatePhone(phone)) {
      newErrors.phone = 'সঠিক বাংলাদেশি ফোন নম্বর দিন (যেমন: 017XXXXXXXX বা +88013XXXXXXXX)';
    }

    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      return;
    }

    setErrors({});
    setIsSubmitted(true);
  };

  // Generate direct WhatsApp link with prefilled parameters
  const generateWhatsAppUrl = () => {
    const text = encodeURIComponent(
      `আসসালামু আলাইকুম। ফোটন ফিজিক্স একাডেমি ভর্তি ও কোর্স সম্পর্কে জানতে চাই।\n` +
      `নাম: ${name}\n` +
      `ফোন: ${phone}\n` +
      `আগ্রহী কোর্স/শ্রেণি: ${program}\n` +
      (message ? `বার্তা: ${message}` : '')
    );
    return `https://wa.me/8801312364499?text=${text}`;
  };

  return (
    <section id="contact" className="py-16 md:py-24 bg-slate-100 border-t border-slate-200 relative">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Section Header */}
        <div className="max-w-3xl mb-12">
          <div className="text-xs uppercase tracking-widest text-blue-700 font-bold mb-2 flex items-center gap-2">
            <MessageCircle className="w-3.5 h-3.5" />
            START A CONVERSATION — যোগাযোগ
          </div>
          <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold text-slate-900 tracking-tight font-bangla">
            ভর্তি সংক্রান্ত তথ্যের জন্য যোগাযোগ করুন
          </h2>
          <p className="mt-3 text-slate-600 font-bangla text-base sm:text-lg leading-relaxed">
            কোর্সের সময়সূচি, আসন সংখ্যা বা কনসেপ্ট বুক সম্পর্কে বিস্তারিত জানতে সরাসরি আমাদের ফোন নম্বরে যোগাযোগ করুন অথবা অনুসন্ধান ফর্মটি পূরণ করুন।
          </p>
        </div>

        <div className="grid grid-cols-1 lg:grid-cols-12 gap-10">
          {/* Left Column: Direct Phone, WhatsApp & Location */}
          <div className="lg:col-span-5 space-y-6">
            {/* Direct Phone Numbers Card */}
            <div className="rounded-2xl border border-slate-200 bg-white p-6 sm:p-7 shadow-sm">
              <h3 className="text-xs font-bold text-blue-900 uppercase tracking-wider mb-4 flex items-center gap-2">
                <Phone className="w-4 h-4 text-blue-600" />
                <span>সরাসরি হেল্পলাইন (Click to Call)</span>
              </h3>

              <div className="space-y-3">
                <a
                  href="tel:+8801312364499"
                  className="flex items-center justify-between p-4 rounded-xl bg-sky-50/70 border border-sky-100 hover:border-blue-400 hover:bg-sky-50 transition-colors group"
                >
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white group-hover:scale-105 transition-transform shadow-xs">
                      <Phone className="w-4 h-4" />
                    </div>
                    <div>
                      <div className="text-xs text-slate-500 font-bangla">প্রধান হেল্পলাইন ও হোয়াটসঅ্যাপ</div>
                      <div className="text-sm font-mono font-bold text-slate-900 tabular-nums">+880 1312-364499</div>
                    </div>
                  </div>
                  <span className="text-xs font-bold text-blue-700 bg-white border border-sky-200 px-3 py-1 rounded-md shadow-2xs">
                    কল করুন
                  </span>
                </a>

                <a
                  href="tel:01722003995"
                  className="flex items-center justify-between p-4 rounded-xl bg-sky-50/70 border border-sky-100 hover:border-blue-400 hover:bg-sky-50 transition-colors group"
                >
                  <div className="flex items-center gap-3">
                    <div className="w-10 h-10 rounded-lg bg-blue-600 flex items-center justify-center text-white group-hover:scale-105 transition-transform shadow-xs">
                      <Phone className="w-4 h-4" />
                    </div>
                    <div>
                      <div className="text-xs text-slate-500 font-bangla">বিকল্প যোগাযোগ নম্বর</div>
                      <div className="text-sm font-mono font-bold text-slate-900 tabular-nums">01722-003995</div>
                    </div>
                  </div>
                  <span className="text-xs font-bold text-blue-700 bg-white border border-sky-200 px-3 py-1 rounded-md shadow-2xs">
                    কল করুন
                  </span>
                </a>
              </div>

              {/* Direct WhatsApp Action Button */}
              <div className="mt-5 pt-5 border-t border-slate-100">
                <a
                  href="https://wa.me/8801312364499?text=আসসালামু%20আলাইকুম,%20ফোটন%20ফিজিক্স%20একাডেমি%20সম্পর্কে%20জানতে%20চাই।"
                  target="_blank"
                  rel="noopener noreferrer"
                  className="w-full py-3.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs sm:text-sm flex items-center justify-center gap-2 shadow-sm transition-colors"
                >
                  <img src="https://cdn.phototourl.com/member/2026-10-24-a2556322-ef98-4816-9826-cb1deec479d3.png" alt="WhatsApp" className="w-5 h-5 object-contain" />
                  <span>হোয়াটসঅ্যাপে চ্যাট করুন (+880 1312-364499)</span>
                </a>
              </div>
            </div>

            {/* Location & Visiting Hours */}
            <div className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
              <div className="flex items-start gap-3">
                <div className="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center text-blue-700 shrink-0">
                  <MapPin className="w-5 h-5" />
                </div>
                <div>
                  <h4 className="text-sm font-bold text-slate-900 font-bangla">একাডেমির অফিশিয়াল ঠিকানা</h4>
                  <p className="text-xs text-slate-800 font-semibold font-bangla mt-1 leading-relaxed">
                    ১০, জিলা স্কুল রোড (জিলা স্কুলের বিপরীত গলি), ময়মনসিংহ
                  </p>
                  <p className="text-[11px] text-slate-500 font-bangla mt-0.5">
                    10, Zilla School Road (Opposite lane of Zilla School), Mymensingh
                  </p>
                </div>
              </div>

              <div className="flex items-start gap-3 pt-3 border-t border-slate-100">
                <div className="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center text-blue-700 shrink-0">
                  <Clock className="w-5 h-5" />
                </div>
                <div>
                  <h4 className="text-sm font-bold text-slate-900 font-bangla">পরামর্শ ও যোগাযোগের সময়</h4>
                  <p className="text-xs text-slate-600 font-bangla mt-1">
                    সকাল ৯:০০ টা – রাত ৯:০০ টা (প্রতিদিন)
                  </p>
                </div>
              </div>
            </div>
          </div>

          {/* Right Column: Direct WhatsApp Contact */}
          <div className="lg:col-span-7 flex items-start">
            <div className="w-full rounded-2xl border border-emerald-200 bg-white p-8 sm:p-10 shadow-sm">
              <div className="w-16 h-16 rounded-2xl bg-[#25D366] flex items-center justify-center mb-5 shadow-sm p-2.5">
                <img src="https://cdn.phototourl.com/member/2026-10-24-a2556322-ef98-4816-9826-cb1deec479d3.png" alt="WhatsApp logo" className="w-full h-full object-contain" />
              </div>
              <h3 className="text-2xl font-bold text-slate-900 mb-3">
                WhatsApp Us
              </h3>
              <p className="text-sm sm:text-base text-slate-600 leading-relaxed mb-7">
                Have a question about admissions, class schedules, or concept books? Chat with Photon Physics Academy directly on WhatsApp.
              </p>
              <a
                href="https://wa.me/8801312364499?text=Assalamu%20Alaikum%2C%20I%20would%20like%20to%20know%20more%20about%20Photon%20Physics%20Academy."
                target="_blank"
                rel="noopener noreferrer"
                className="w-full sm:w-auto py-4 px-7 rounded-xl bg-[#25D366] hover:bg-[#1ebe5d] text-white font-bold text-sm sm:text-base inline-flex items-center justify-center gap-2.5 shadow-sm transition-colors"
              >
                <img src="https://cdn.phototourl.com/member/2026-10-24-a2556322-ef98-4816-9826-cb1deec479d3.png" alt="" className="w-5 h-5 object-contain" />
                <span>WhatsApp Us</span>
              </a>
              <p className="mt-4 text-xs text-slate-500">
                +880 1312-364499
              </p>
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};
