import React from 'react';
import { BookOpen, User, CheckCircle, MessageSquare } from 'lucide-react';
import { CONCEPT_BOOKS_DATA } from '../data/books';
import { ConceptBook } from '../types';

interface ConceptBooksProps {
  onSelectBook: (bookTitle: string) => void;
}

export const ConceptBooks: React.FC<ConceptBooksProps> = ({ onSelectBook }) => {
  return (
    <section id="books" className="py-16 md:py-24 relative bg-white">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {/* Section Header */}
        <div className="max-w-3xl mb-12">
          <div className="text-xs uppercase tracking-widest text-blue-700 font-bold mb-2">
            CONCEPT BOOKS — সেরা কনসেপ্ট বুক
          </div>
          <h2 className="text-2xl sm:text-3xl md:text-4xl font-bold text-slate-900 tracking-tight font-bangla">
            পদার্থবিজ্ঞানের ভিত্তি মজবুত করার সহায়ক পুস্তক
          </h2>
          <p className="mt-3 text-slate-600 font-bangla text-base sm:text-lg leading-relaxed">
            পদার্থবিজ্ঞানের ভৌত রাশি ও পরিমাপ, গতিবিদ্যা এবং বল সংক্রান্ত মূল ধারণাগুলোকে সহজ ও প্রাঞ্জল উপস্থাপনায় শিক্ষার্থীদের সামনে তুলে ধরতে প্রণীত বিশেষ প্রকাশনা।
          </p>
        </div>

        {/* Books Grid - 3 Column Featured Showcase */}
        <div className="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-6xl mx-auto">
          {CONCEPT_BOOKS_DATA.map((book: ConceptBook) => (
            <div
              key={book.id}
              className="flex flex-col justify-between rounded-2xl border border-slate-200 bg-white hover:border-blue-400 hover:shadow-xl transition-all duration-300 p-5 shadow-xs group"
            >
              <div>
                {/* Book Cover Visual with 3:4 aspect ratio */}
                <div className="aspect-[3/4] w-full rounded-xl overflow-hidden bg-slate-100 border border-slate-200 mb-4 relative shadow-md">
                  {book.coverImage ? (
                    <img
                      src={book.coverImage}
                      alt={book.title}
                      referrerPolicy="no-referrer"
                      loading="lazy"
                      className="w-full h-full object-contain bg-white group-hover:scale-[1.02] transition-transform duration-300"
                    />
                  ) : (
                    <div className="w-full h-full flex flex-col items-center justify-center p-4 text-center">
                      <BookOpen className="w-10 h-10 text-blue-600 mb-2 opacity-60" />
                      <span className="text-xs text-slate-500 font-bangla">{book.banglaTitle}</span>
                    </div>
                  )}

                  {/* Clean unboxed tag */}
                  <div className="absolute bottom-2 left-2 right-2 bg-white/95 backdrop-blur-sm border border-slate-200/80 rounded-lg p-1.5 text-center shadow-xs">
                    <span className="text-xs font-bold text-blue-900 font-bangla">
                      {book.targetClass}
                    </span>
                  </div>
                </div>

                <h3 className="text-base font-bold text-slate-900 font-bangla group-hover:text-blue-700 transition-colors">
                  {book.banglaTitle}
                </h3>
                <div className="text-xs font-mono text-slate-500 mt-0.5 mb-2.5">{book.title}</div>

                {/* Author Attribution */}
                {book.authorOrEditor && (
                  <div className="flex items-center gap-1.5 text-xs text-slate-700 font-bangla bg-sky-50 p-2.5 rounded-lg border border-sky-100 mb-3">
                    <User className="w-3.5 h-3.5 text-blue-600 shrink-0" />
                    <span>
                      <strong className="text-slate-900">রচয়িতা ও সম্পাদক:</strong> {book.authorOrEditor}
                    </span>
                  </div>
                )}

                <p className="text-xs text-slate-600 font-bangla leading-relaxed mb-4">
                  {book.description}
                </p>

                {/* Features */}
                <ul className="space-y-1.5 mb-5">
                  {book.features.map((feat, idx) => (
                    <li key={idx} className="flex items-start gap-1.5 text-xs text-slate-600 font-bangla">
                      <CheckCircle className="w-3.5 h-3.5 text-blue-600 mt-0.5 shrink-0" />
                      <span>{feat}</span>
                    </li>
                  ))}
                </ul>
              </div>

              {/* Inquiry Action */}
              <div className="pt-3.5 border-t border-slate-100">
                <button
                  onClick={() => onSelectBook(book.banglaTitle)}
                  className="w-full py-2.5 px-3 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-xs transition-colors flex items-center justify-center gap-1.5"
                >
                  <MessageSquare className="w-3.5 h-3.5" />
                  <span>বই সম্পর্কে জানতে যোগাযোগ করুন</span>
                </button>
              </div>
            </div>
          ))}
        </div>

        {/* Note on genuine attribution & pricing */}
        <div className="mt-8 p-4 rounded-xl bg-sky-50 border border-sky-200/80 text-xs text-slate-600 text-center font-bangla">
          কনসেপ্ট বুকসমূহ একাডেমির শিক্ষার্থীদের অভ্যন্তরীণ পাঠদানের সহায়ক উপকরণ হিসেবে ব্যবহৃত হয়। সংগ্রহ ও কপি প্রাপ্তি সম্পর্কে জানতে একাডেমির হেল্পলাইনে যোগাযোগ করুন।
        </div>
      </div>
    </section>
  );
};
