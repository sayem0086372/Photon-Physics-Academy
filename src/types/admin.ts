export type ClassLevel = 'Class 8' | 'Class 9' | 'Class 10';

export type AdminTab = 
  | 'overview'
  | 'students'
  | 'payments'
  | 'notices'
  | 'programs'
  | 'results'
  | 'sms'
  | 'store'
  | 'settings';

export interface AdminStudent {
  id: string;
  studentIdCode: string;
  fullName: string;
  banglaName?: string;
  classLevel: ClassLevel;
  studentPhone: string;
  guardianName: string;
  guardianPhone: string;
  whatsappPhone?: string;
  institution: string;
  batchTiming: string;
  batchId?: string | null;
  monthlyFeeBdt: number;
  paymentStatus: 'Paid' | 'Partial' | 'Due';
  status: 'active' | 'inactive';
  joinedDate: string;
}

export interface AdminPaymentRecord {
  id: string;
  studentId: string;
  studentName: string;
  studentIdCode: string;
  classLevel: ClassLevel;
  billingMonth: string;
  billingYear: number;
  payableAmount: number;
  /** Amount collected by this individual transaction. */
  paidAmount: number;
  /** Cumulative amount paid for the month after this transaction. */
  monthlyPaidAmount?: number;
  dueAmount: number;
  paymentStatus: 'Paid' | 'Partial' | 'Due';
  paymentMethod: 'Cash' | 'bKash' | 'Nagad';
  receiptNumber: string;
  paymentDate: string;
  /** Stable idempotency key reused when retrying the same payment attempt. */
  requestId?: string;
}

export interface AdminNoticeRecord {
  id: string;
  title: string;
  banglaTitle: string;
  category: 'ভর্তি বিজ্ঞপ্তি' | 'পরীক্ষার সময়সূচি' | 'ক্লাসের সময়সূচি' | 'জরুরি ঘোষণা' | 'ফলাফল প্রকাশ';
  shortDescription: string;
  fullContent?: string;
  date: string;
  isPublished: boolean;
  hasAttachment: boolean;
}

export interface AdminBatchRecord {
  id: string;
  name: string;
  classLevel: ClassLevel;
  scheduleDays: string;
  timeSlot: string;
  roomNumber: string;
  enrolledStudents: number;
  maxCapacity: number;
  instructor: string;
  status: 'active' | 'upcoming';
}

export interface AdminExamResult {
  id: string;
  studentIdCode: string;
  studentName: string;
  classLevel: ClassLevel;
  examTitle: string;
  chapter: string;
  totalMarks: number;
  obtainedMarks: number;
  highestMarks: number;
  grade: string;
  examDate: string;
}
