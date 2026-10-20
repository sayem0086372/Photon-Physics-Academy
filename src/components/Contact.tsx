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
    <section id="contact" className="py-16 md:py-24 bg-sky-50/50 border-t border-sky-100/80 relative">
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
                  <MessageCircle className="w-4 h-4" />
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

          {/* Right Column: Inquiry Form with Transparent Feedback */}
          <div className="lg:col-span-7">
            <div className="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8 shadow-sm">
              <div className="mb-6">
                <h3 className="text-lg font-bold text-slate-900 font-bangla">
                  অনলাইন অনুসন্ধান ও বার্তা প্রেরণ
                </h3>
                <p className="text-xs text-slate-500 font-bangla mt-1">
                  ফর্মটি পূরণ করে সরাসরি তথ্য দিন। কোনো অপ্রয়োজনীয় ব্যক্তিগত তথ্য সংগ্রহ করা হয় না।
                </p>
              </div>

              {!isSubmitted ? (
                <form onSubmit={handleSubmit} className="space-y-4" noValidate>
                  {/* Name field */}
                  <div>
                    <label className="block text-xs font-semibold text-slate-700 font-bangla mb-1">
                      শিক্ষার্থী বা অভিভাবকের নাম <span className="text-blue-600">*</span>
                    </label>
                    <input
                      type="text"
                      value={name}
                      onChange={(e) => setName(e.target.value)}
                      placeholder="যেমন: তানভীর আহমেদ / অভিভাবকের নাম"
                      className={`w-full px-4 py-3 rounded-xl bg-slate-50 border ${
                        errors.name ? 'border-rose-500 bg-rose-50/20' : 'border-slate-200'
                      } text-sm text-slate-900 placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 transition-colors`}
                    />
                    {errors.name && (
                      <p className="text-xs text-rose-600 mt-1 font-bangla">{errors.name}</p>
                    )}
                  </div>

                  {/* Phone field */}
                  <div>
                    <label className="block text-xs font-semibold text-slate-700 font-bangla mb-1">
                      মোবাইল নম্বর <span className="text-blue-600">*</span>
                    </label>
                    <input
                      type="tel"
                      value={phone}
                      onChange={(e) => setPhone(e.target.value)}
                      placeholder="017XXXXXXXX বা 013XXXXXXXX"
                      className={`w-full px-4 py-3 rounded-xl bg-slate-50 border ${
                        errors.phone ? 'border-rose-500 bg-rose-50/20' : 'border-slate-200'
                      } text-sm text-slate-900 font-mono placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 transition-colors`}
                    />
                    {errors.phone && (
                      <p className="text-xs text-rose-600 mt-1 font-bangla">{errors.phone}</p>
                    )}
                  </div>

                  {/* Program / Class select (focused on active programs) */}
                  <div>
                    <label className="block text-xs font-semibold text-slate-700 font-bangla mb-1">
                      আগ্রহী শ্রেণি বা প্রোগ্রাম
                    </label>
                    <select
                      value={program}
                      onChange={(e) => setProgram(e.target.value)}
                      className="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-900 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 transition-colors font-bangla"
                    >
                      <option value="অষ্টম শ্রেণি বিজ্ঞান">অষ্টম শ্রেণি বিজ্ঞান (Class 8)</option>
                      <option value="নবম শ্রেণি পদার্থবিজ্ঞান">নবম শ্রেণি পদার্থবিজ্ঞান (Class 9)</option>
                      <option value="দশম শ্রেণি পদার্থবিজ্ঞান">দশম শ্রেণি পদার্থবিজ্ঞান (Class 10)</option>
                      <option value="কনসেপ্ট বুক সংগ্রহ সংক্রান্ত">কনসেপ্ট বুক সংগ্রহ সংক্রান্ত</option>
                      <option value="সাধারণ অনুসন্ধান">সাধারণ অনুসন্ধান ও অন্যান্য</option>
                    </select>
                  </div>

                  {/* Message field */}
                  <div>
                    <label className="block text-xs font-semibold text-slate-700 font-bangla mb-1">
                      আপনার জিজ্ঞাসা বা বার্তা (ঐচ্ছিক)
                    </label>
                    <textarea
                      rows={3}
                      value={message}
                      onChange={(e) => setMessage(e.target.value)}
                      placeholder="আপনার কোনো নির্দিষ্ট জিজ্ঞাসা থাকলে এখানে লিখুন..."
                      className="w-full px-4 py-3 rounded-xl bg-slate-50 border border-slate-200 text-sm text-slate-900 placeholder-slate-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 transition-colors font-bangla resize-none"
                    />
                  </div>

                  {/* Submit Button */}
                  <button
                    type="submit"
                    className="w-full py-3.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm flex items-center justify-center gap-2 shadow-sm transition-colors"
                  >
                    <Send className="w-4 h-4" />
                    <span>অনুসন্ধান বার্তা পাঠান</span>
                  </button>
                </form>
              ) : (
                /* Honest submission feedback */
                <div className="py-4 space-y-4">
                  <div className="p-5 rounded-xl bg-sky-50 border border-sky-200 text-left">
                    <div className="flex items-center gap-2 text-blue-900 font-bold font-bangla text-base mb-1">
                      <CheckCircle2 className="w-5 h-5 text-blue-600" />
                      <span>আপনার অনুসন্ধানের বিবরণ প্রস্তুত হয়েছে!</span>
                    </div>
                    <p className="text-xs text-slate-700 font-bangla leading-relaxed">
                      নাম: <strong className="text-slate-900">{name}</strong> · ফোন:{' '}
                      <strong className="text-slate-900">{phone}</strong> · বিষয়:{' '}
                      <strong className="text-slate-900">{program}</strong>
                    </p>
                  </div>

                  {/* Transparent Step 1 explanation */}
                  <div className="p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-600 font-bangla flex items-start gap-2.5 leading-relaxed">
                    <AlertCircle className="w-4 h-4 text-blue-600 shrink-0 mt-0.5" />
                    <span>
                      <strong>স্বচ্ছতা নিশ্চিতকরণ:</strong> এটি স্টেপ ১ এর স্ট্যাটিক ফ্রন্টএন্ড ভার্সন (কোনো ব্যাকএন্ড ডাটাবেস নেই)। নিশ্চিত তাৎক্ষণিক যোগাযোগের জন্য আপনার এই বার্তাটি এক ক্লিকেই সরাসরি একাডেমির অফিশিয়াল হোয়াটসঅ্যাপে প্রেরণ করুন অথবা সরাসরি ফোন করুন।
                    </span>
                  </div>

                  {/* 1-Click WhatsApp Trigger with pre-filled message */}
                  <div className="space-y-2 pt-2">
                    <a
                      href={generateWhatsAppUrl()}
                      target="_blank"
                      rel="noopener noreferrer"
                      className="w-full py-3.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm flex items-center justify-center gap-2 shadow-sm transition-colors"
                    >
                      <MessageCircle className="w-4 h-4" />
                      <span>হোয়াটসঅ্যাপে এই বার্তাটি এখনই পাঠান</span>
                    </a>

                    <div className="grid grid-cols-2 gap-2 text-xs">
                      <a
                        href="tel:+8801312364499"
                        className="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 border border-slate-200 flex items-center justify-center gap-1.5 transition-colors font-semibold"
                      >
                        <Phone className="w-3.5 h-3.5 text-blue-600" />
                        <span>সরাসরি কল করুন</span>
                      </a>
                      <button
                        onClick={() => {
                          setIsSubmitted(false);
                          setName('');
                          setPhone('');
                          setMessage('');
                        }}
                        className="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 hover:text-slate-900 border border-slate-200 transition-colors font-medium"
                      >
                        নতুন বার্তা লিখুন
                      </button>
                    </div>
                  </div>
                </div>
              )}
            </div>
          </div>
        </div>
      </div>
    </section>
  );
};
