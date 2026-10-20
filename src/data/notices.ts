import { Notice } from '../types';

/**
 * Notice Board Data for Photon Physics Academy, Mymensingh.
 * Note: These entries are structured sample notices for the Step 1 static frontend demo.
 * In Step 2, this will be connected to a live database or backend feed.
 */
export const NOTICES_DATA: Notice[] = [
  {
    id: 'notice-01',
    title: 'Admission Inquiries Open for New Batches',
    banglaTitle: 'নতুন ব্যাচে ভর্তি সংক্রান্ত অনুসন্ধান ও যোগাযোগ বিজ্ঞপ্তি',
    date: '১০ অক্টোবর ২০২৬',
    category: 'ভর্তি বিজ্ঞপ্তি',
    shortDescription: 'ময়মনসিংহে ফিজিক্সের বেসিক কনসেপ্ট ও সমস্যা সমাধানের জন্য নতুন সেশনে শিক্ষার্থীদের আগ্রহ প্রকাশ ও যোগাযোগ চলছে।',
    fullContent: 'ফোটন ফিজিক্স একাডেমি, ময়মনসিংহ-এ নবম ও দশম শ্রেণির শিক্ষার্থীদের জন্য পদার্থবিজ্ঞান বিষয়ক কনসেপ্ট ক্লাসের অনুসন্ধান গ্রহণ করা হচ্ছে। বিস্তারিত আসন সংখ্যা ও সময়সূচি নিশ্চিতকরণের জন্য সরাসরি যোগাযোগ করুন।\n\nযোগাযোগ:\n• 01312-364499\n• 01722-003995',
    isSample: true,
  },
  {
    id: 'notice-02',
    title: 'Kinematics & Dynamics Concept Evaluation Test (Demo Schedule)',
    banglaTitle: 'গতিবিদ্যা ও নিউটনীয় বলবিদ্যা কনসেপ্ট যাচাই পরীক্ষা (নমুনা সময়সূচি)',
    date: '০৫ অক্টোবর ২০২৬',
    category: 'পরীক্ষার সময়সূচি',
    shortDescription: 'নমুনা নোটিশ: অধ্যায়ভিত্তিক প্রস্তুতি পরীক্ষার সময়সূচি সংক্রান্ত কাঠামোগত নোটিশ ডেমো।',
    fullContent: 'শিক্ষার্থীদের কনসেপ্ট ক্লিয়ারিং মূল্যায়নের জন্য নিয়মিত রিটেন ও এমসিকিউ টেস্ট আয়োজন করা হবে। অফিশিয়াল পরীক্ষার সূচি ও হলরুম সংক্রান্ত চূড়ান্ত নোটিশ একাডেমির নোটিশ বোর্ডে হালনাগাদ করা হবে।',
    isSample: true,
    pdfAttachment: {
      filename: 'Sample_Exam_Routine_Physics.pdf',
      size: '240 KB',
    },
  },
  {
    id: 'notice-03',
    title: 'Weekly Routine & Problem Solving Session Information',
    banglaTitle: 'সাপ্তাহিক ক্লাস রুটিন ও ডাউট ক্লিয়ারিং সেশন (নমুনা)',
    date: '২৮ সেপ্টেম্বর ২০২৬',
    category: 'ক্লাসের সময়সূচি',
    shortDescription: 'থিওরি ক্লাসের পাশাপাশি প্রতি সপ্তাহে বিশেষ প্রবলেম সলভিং ও প্রশ্নোত্তর পর্বের প্রস্তাবিত সময়সূচি।',
    fullContent: 'পদার্থবিজ্ঞান কেবল মুখস্থ করার বিষয় নয়, এটি গভীরভাবে অনুভব ও প্রয়োগ করার বিজ্ঞান। নিয়মিত ক্লাসের পাশাপাশি শিক্ষার্থীদের সমস্যা সমাধান ও কনসেপ্ট বুকের অনুশীলনী সমাধানের জন্য বিশেষ ডাউট সেশন পরিচালিত হয়।',
    isSample: true,
  },
  {
    id: 'notice-04',
    title: 'Physical Quantities & Measurement Concept Book Distribution Note',
    banglaTitle: 'ভৌত রাশি ও পরিমাপ কনসেপ্ট বুক সংক্রান্ত তথ্য',
    date: '১৫ সেপ্টেম্বর ২০২৬',
    category: 'জরুরি ঘোষণা',
    shortDescription: 'একাডেমির কনসেপ্ট বুক শিক্ষার্থীদের হাতে পৌঁছানো ও অধ্যয়ন নির্দেশিকা সম্পর্কিত তথ্য।',
    fullContent: 'একাডেমির তৈরি কনসেপ্ট বুক সম্পর্কে বিস্তারিত জানতে বা সংগ্রহ প্রক্রিয়া সম্পর্কে সরাসরি একাডেমি অফিসে যোগাযোগ করার অনুরোধ জানানো হচ্ছে।',
    isSample: true,
  },
  {
    id: 'notice-05',
    title: 'Monthly Evaluation Assessment System Notice',
    banglaTitle: 'মাসিক মূল্যায়ন ও ফিডব্যাক পদ্ধতি সংক্রান্ত সাধারণ নির্দেশনা',
    date: '০১ সেপ্টেম্বর ২০২৬',
    category: 'ফলাফল প্রকাশ',
    shortDescription: 'শিক্ষার্থীদের ব্যক্তিগত অগ্রগতি ট্র্যাকিং ও গার্ডিয়ানদের সাথে ফিডব্যাক সেশনের রূপরেখা।',
    fullContent: 'অফিশিয়াল পরীক্ষার ফলাফল ও ব্যাচভিত্তিক পারফরম্যান্স একাডেমি থেকে শিক্ষার্থীদের কাছে সরাসরি প্রদান করা হয়। কোনো ভুয়া বা আনুমানিক ফলাফল এখানে প্রদর্শন করা হয় না।',
    isSample: true,
  },
];
