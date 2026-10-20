export type NoticeCategory = 
  | 'ভর্তি বিজ্ঞপ্তি'
  | 'পরীক্ষার সময়সূচি'
  | 'ক্লাসের সময়সূচি'
  | 'জরুরি ঘোষণা'
  | 'ফলাফল প্রকাশ';

export interface Notice {
  id: string;
  title: string;
  banglaTitle: string;
  date: string; // ISO date format or readable Bangladeshi format (e.g., '১৫ মার্চ ২০২৬')
  category: NoticeCategory;
  shortDescription: string;
  fullContent?: string;
  isSample: boolean; // Explicit flag indicating demonstration notice
  pdfAttachment?: {
    filename: string;
    size?: string;
  };
}

export interface Program {
  id: string;
  name: string;
  banglaName: string;
  subtitle: string;
  targetAudience: string;
  features: string[];
  status: 'active' | 'proposed' | 'upcoming';
  statusLabelBangla: string;
  statusLabelEnglish: string;
}

export interface ConceptBook {
  id: string;
  title: string;
  banglaTitle: string;
  topic: string;
  targetClass: string;
  authorOrEditor?: string;
  verifiedStatus: string;
  coverImage?: string;
  description: string;
  features: string[];
}

export interface GalleryItem {
  id: string;
  title: string;
  banglaCaption: string;
  imageUrl: string;
  category: 'classroom' | 'study' | 'mentorship';
}
