import imgBookBol from '../assets/images/book_bol_authentic_cover_1792302614714.jpg';
import imgBookGati from '../assets/images/book_gati_authentic_cover_1792302615144.jpg';
import imgBookQuantities from '../assets/images/book_quantities_measurement_cover_1792302614659.jpg';
import { ConceptBook } from '../types';

export const CONCEPT_BOOKS_DATA: ConceptBook[] = [
  {
    id: 'book-bol',
    title: 'Bol (বল) — Concept Book',
    banglaTitle: 'মাধ্যমিক পদার্থবিজ্ঞান: বল',
    topic: 'নিউটনের গতিসূত্র, ঘর্ষণ বল, ভরবেগ ও জড়তার ব্যবহারিক প্রয়োগ',
    targetClass: 'নবম ও দশম শ্রেণি',
    authorOrEditor: 'রফিকুল ইসলাম (জুয়েল স্যার)',
    verifiedStatus: 'একাডেমির অফিশিয়াল কনসেপ্ট বুক',
    coverImage: imgBookBol,
    description: 'বলবিদ্যা ও নিউটনের গতিসূত্রের বাস্তবসম্মত রূপরেখা। চলনশীল বস্তুর ওপর বলের প্রভাব, স্থির বস্তুর ঘর্ষণ বল এবং বাস্তব জীবনের গাণিতিক সমস্যার নিখুঁত বিশ্লেষণ।',
    features: [
      'নিউটনের ৩টি গতিসূত্রের স্পষ্ট চিত্রভিত্তিক প্রমাণ (F = ma)',
      'ঘর্ষণ বল (f), লব্ধি বল ও ত্বরনের সম্পর্ক বিশ্লেষণ',
      'অধ্যায়ভিত্তিক সৃজনশীল প্রশ্ন (CQ) ও গাণিতিক উদাহরণ',
    ],
  },
  {
    id: 'book-gati',
    title: 'Gati (গতি) — Concept Book',
    banglaTitle: 'পদার্থবিজ্ঞান সহজপাঠ: গতি',
    topic: 'দূরত্ব, সরণ, বেগ, ত্বরণ, প্রজেক্টাইল ও গতির ৪টি মৌলিক সমীকরণ',
    targetClass: 'SSC 27 & 28 পরীক্ষার্থীদের জন্য সেরা কনসেপ্ট বুক',
    authorOrEditor: 'রফিকুল ইসলাম (জুয়েল স্যার)',
    verifiedStatus: 'একাডেমির অফিশিয়াল কনসেপ্ট বুক',
    coverImage: imgBookGati,
    description: 'গতির সমীকরণ ও নিউটনের বলবিদ্যার বাস্তবসম্মত প্রয়োগ। সময়-দূরত্ব ও বেগ-সময় লেখচিত্রের (Graphs) সাহায্যে গতি সংক্রান্ত জটিল গাণিতিক সমস্যার সহজ ও নিখুঁত সমাধান।',
    features: [
      'গতির ৪টি মৌলিক সমীকরণের পুঙ্খানুপুঙ্খ প্রতিপাদন',
      'গ্রাফের ঢাল ও ক্ষেত্রফল দ্বারা সরণ ও ত্বরণ নির্ণয়',
      'বোর্ড পরীক্ষার সৃজনশীল প্রশ্নাবলির বিশেষ অনুশীলন',
    ],
  },
  {
    id: 'book-quantities-measurement',
    title: 'Physical Quantities and Measurement',
    banglaTitle: 'পদার্থবিজ্ঞান: ভৌত রাশি ও পরিমাপ',
    topic: 'মৌলিক ও লব্ধ রাশি, ভার্নিয়ার ক্যালিপার্স ও স্ক্রু গজ পরিমাপ',
    targetClass: 'নবম ও দশম শ্রেণি (SSC)',
    authorOrEditor: 'রফিকুল ইসলাম (জুয়েল স্যার)',
    verifiedStatus: 'একাডেমির অফিশিয়াল কনসেপ্ট বুক',
    coverImage: imgBookQuantities,
    description: 'পদার্থবিজ্ঞানের ভিত্তিমূল পরিমাপ পদ্ধতি। ভার্নিয়ার স্কেল, রৈখিক স্কেল ও স্ক্রু গজের ব্যবহারিক পাঠ, পরিমাপের যান্ত্রিক ত্রুটি এবং মাত্রা বিশ্লেষণের সহজ পদ্ধতি।',
    features: [
      'ভার্নিয়ার ধ্রুবক (VC) ও পিচ গণনার নিয়মাবলি',
      'পরিমাপের সম্ভাব্য শতকরা ত্রুটি নির্ণয় পদ্ধতি',
      'অধ্যায়ভিত্তিক কনসেপ্ট ক্লিয়ারিং প্রশ্নব্যাংক',
    ],
  },
];
