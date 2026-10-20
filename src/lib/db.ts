import { supabase } from './supabase';
import {
  AdminStudent,
  AdminPaymentRecord,
  AdminNoticeRecord,
  AdminBatchRecord,
  AdminExamResult,
} from '../types/admin';

const sb = () => {
  if (!supabase) throw new Error('Supabase is not configured');
  return supabase;
};

async function loadAllRows<T>(query: any, pageSize = 500): Promise<T[]> {
  const rows: T[] = [];
  for (let offset = 0; ; offset += pageSize) {
    const { data, error } = await query.range(offset, offset + pageSize - 1);
    if (error) throw error;
    const page = (data ?? []) as T[];
    rows.push(...page);
    if (page.length < pageSize) return rows;
  }
}

const MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

// ---------- Row mappers ----------
const toStudent = (r: any, status: AdminStudent['paymentStatus'] = 'Due'): AdminStudent => ({
  id: r.id,
  studentIdCode: r.student_id_code,
  fullName: r.full_name,
  banglaName: r.bangla_name ?? undefined,
  classLevel: r.class_level,
  studentPhone: r.student_phone ?? '',
  guardianName: r.guardian_name ?? '',
  guardianPhone: r.guardian_phone ?? '',
  whatsappPhone: r.whatsapp_phone ?? '',
  institution: r.institution ?? '',
  batchTiming: r.batch_timing ?? '',
  batchId: r.batch_id ?? null,
  monthlyFeeBdt: Number(r.monthly_fee_bdt),
  paymentStatus: status,
  status: r.status,
  joinedDate: String(r.created_at).slice(0, 10),
});

const toPayment = (r: any): AdminPaymentRecord => ({
  id: r.id,
  studentId: r.student_id,
  studentName: r.student_name ?? r.students?.full_name ?? '',
  studentIdCode: r.student_id_code ?? r.students?.student_id_code ?? '',
  classLevel: r.class_level ?? r.students?.class_level ?? 'Class 9',
  billingMonth: r.billing_month,
  billingYear: r.billing_year,
  payableAmount: Number(r.payable_amount),
  paidAmount: Number(r.amount ?? r.paid_amount),
  monthlyPaidAmount: Number(r.monthly_paid_amount ?? r.paid_amount),
  dueAmount: Number(r.due_amount_after ?? r.due_amount),
  paymentStatus: r.payment_status,
  paymentMethod: r.payment_method,
  receiptNumber: r.receipt_number,
  paymentDate: r.payment_date,
  requestId: r.request_id,
});

export const toNotice = (r: any): AdminNoticeRecord => ({
  id: r.id,
  title: r.title,
  banglaTitle: r.bangla_title,
  category: r.category,
  shortDescription: r.short_description,
  fullContent: r.full_content ?? undefined,
  date: r.publication_date,
  isPublished: r.is_published,
  hasAttachment: Boolean(r.pdf_attachment_url),
});

const toBatch = (r: any): AdminBatchRecord => ({
  id: r.id,
  name: r.batch_name,
  classLevel: r.class_level,
  scheduleDays: r.schedule_days,
  timeSlot: r.time_slot,
  roomNumber: r.room_number ?? '',
  enrolledStudents: r.enrolled_students,
  maxCapacity: r.max_capacity,
  instructor: r.instructor_name,
  status: r.status,
});

const toResult = (r: any): AdminExamResult => ({
  id: r.id,
  studentIdCode: r.students?.student_id_code ?? '',
  studentName: r.students?.full_name ?? '',
  classLevel: r.students?.class_level ?? 'Class 9',
  examTitle: r.exam_title,
  chapter: r.chapter_topic,
  totalMarks: Number(r.total_marks),
  obtainedMarks: Number(r.obtained_marks),
  highestMarks: Number(r.highest_marks ?? 0),
  grade: r.grade ?? '',
  examDate: r.exam_date,
});

// ---------- Loaders ----------
export async function loadAll() {
  const c = sb();
  const [students, p, n, b, e] = await Promise.all([
    loadAllRows<any>(c.from('students').select('*').order('created_at', { ascending: false })),
    c.from('payment_transactions')
      .select('*')
      .order('created_at', { ascending: false }),
    c.from('notices').select('*').order('publication_date', { ascending: false }),
    c.from('batches').select('*').order('class_level'),
    c.from('exam_results')
      .select('*, students(full_name, student_id_code, class_level)')
      .order('exam_date', { ascending: false }),
  ]);
  for (const res of [p, n, b, e]) if (res.error) throw res.error;

  const payments = (p.data ?? []).map(toPayment);
  // A student's fee status = status of their most recent billing record
  const latest = new Map<string, AdminPaymentRecord>();
  for (const pay of payments) {
    const cur = latest.get(pay.studentId);
    const key = (x: AdminPaymentRecord) => x.billingYear * 12 + MONTHS.indexOf(x.billingMonth);
    if (!cur || key(pay) > key(cur)) latest.set(pay.studentId, pay);
  }
  return {
    students: students.map((r) => toStudent(r, latest.get(r.id)?.paymentStatus ?? 'Due')),
    payments,
    notices: (n.data ?? []).map(toNotice),
    batches: (b.data ?? []).map(toBatch),
    results: (e.data ?? []).map(toResult),
  };
}

/** Public (no login needed): published notices only — RLS enforces this too. */
export async function loadPublishedNotices(): Promise<AdminNoticeRecord[]> {
  const { data, error } = await sb()
    .from('notices').select('*').eq('is_published', true)
    .order('publication_date', { ascending: false });
  if (error) throw error;
  return (data ?? []).map(toNotice);
}

// ---------- Writers ----------
export async function insertStudent(s: Omit<AdminStudent, 'id' | 'joinedDate'>) {
  const { data, error } = await sb().from('students').insert({
    student_id_code: s.studentIdCode,
    full_name: s.fullName,
    bangla_name: s.banglaName || null,
    class_level: s.classLevel,
    student_phone: s.studentPhone || null,
    whatsapp_phone: s.whatsappPhone || null,
    guardian_name: s.guardianName,
    guardian_phone: s.guardianPhone || null,
    institution: s.institution,
    batch_timing: s.batchTiming,
    batch_id: s.batchId || null,
    monthly_fee_bdt: s.monthlyFeeBdt,
    status: s.status,
  }).select().single();
  if (error) throw error;
  return toStudent(data, s.paymentStatus);
}

export async function updateStudent(id: string, s: Omit<AdminStudent, 'id' | 'joinedDate'>) {
  const { data, error } = await sb().from('students').update({
    student_id_code: s.studentIdCode,
    full_name: s.fullName,
    bangla_name: s.banglaName || null,
    class_level: s.classLevel,
    student_phone: s.studentPhone || null,
    whatsapp_phone: s.whatsappPhone || null,
    guardian_name: s.guardianName,
    guardian_phone: s.guardianPhone || null,
    institution: s.institution,
    batch_timing: s.batchTiming,
    batch_id: s.batchId || null,
    monthly_fee_bdt: s.monthlyFeeBdt,
    status: s.status,
  }).eq('id', id).select().single();
  if (error) throw error;
  return toStudent(data, s.paymentStatus);
}

export async function removeStudent(id: string) {
  const { error } = await sb().from('students').delete().eq('id', id);
  if (error) throw error;
}

export async function insertPayment(p: Omit<AdminPaymentRecord, 'id'>) {
  const client = sb();
  const requestId = p.requestId || crypto.randomUUID();
  // The database function atomically updates the monthly summary and inserts
  // one immutable transaction. Reuse requestId for retries of the same attempt.
  const { data: result, error: rpcError } = await client.rpc('record_student_fee_payment', {
    p_student_id: p.studentId,
    p_billing_month: p.billingMonth,
    p_billing_year: p.billingYear,
    p_payment_amount: p.paidAmount,
    p_payment_method: p.paymentMethod,
    p_payment_date: p.paymentDate,
    p_request_id: requestId,
  });
  if (rpcError) throw rpcError;
  if (!result?.success || !result?.transaction_id) {
    throw new Error('The payment was not confirmed by the database. Refresh the Payments page before trying again.');
  }

  const { data, error } = await client.from('payment_transactions')
    .select('*').eq('id', result.transaction_id).single();
  if (error) throw error;
  return toPayment(data);
}

export async function insertNotice(n: Omit<AdminNoticeRecord, 'id'>) {
  const { data, error } = await sb().from('notices').insert({
    title: n.title,
    bangla_title: n.banglaTitle,
    category: n.category,
    short_description: n.shortDescription,
    full_content: n.fullContent ?? null,
    publication_date: n.date,
    is_published: n.isPublished,
  }).select().single();
  if (error) throw error;
  return toNotice(data);
}

export async function removeNotice(id: string) {
  const { error } = await sb().from('notices').delete().eq('id', id);
  if (error) throw error;
}

export async function setNoticePublished(id: string, isPublished: boolean) {
  const { error } = await sb().from('notices').update({ is_published: isPublished }).eq('id', id);
  if (error) throw error;
}

export async function insertBatch(b: Omit<AdminBatchRecord, 'id'>) {
  const { data, error } = await sb().from('batches').insert({
    batch_name: b.name,
    class_level: b.classLevel,
    schedule_days: b.scheduleDays,
    time_slot: b.timeSlot,
    room_number: b.roomNumber,
    max_capacity: b.maxCapacity,
    enrolled_students: b.enrolledStudents,
    instructor_name: b.instructor,
    status: b.status,
  }).select().single();
  if (error) throw error;
  return toBatch(data);
}

export async function updateBatch(id: string, b: Omit<AdminBatchRecord, 'id'>) {
  const { data, error } = await sb().from('batches').update({
    batch_name: b.name,
    class_level: b.classLevel,
    schedule_days: b.scheduleDays,
    time_slot: b.timeSlot,
    room_number: b.roomNumber,
    max_capacity: b.maxCapacity,
    instructor_name: b.instructor,
    status: b.status,
  }).eq('id', id).select().single();
  if (error) throw error;
  return toBatch(data);
}

export async function removeBatch(id: string) {
  const { error } = await sb().from('batches').delete().eq('id', id);
  if (error) throw error;
}

export async function insertResult(r: Omit<AdminExamResult, 'id'>, studentUuid: string) {
  const { data, error } = await sb().from('exam_results').insert({
    student_id: studentUuid,
    exam_title: r.examTitle,
    chapter_topic: r.chapter,
    total_marks: r.totalMarks,
    obtained_marks: r.obtainedMarks,
    highest_marks: r.highestMarks,
    grade: r.grade,
    exam_date: r.examDate,
  }).select('*, students(full_name, student_id_code, class_level)').single();
  if (error) throw error;
  return toResult(data);
}

export async function removeResult(id: string) {
  const { error } = await sb().from('exam_results').delete().eq('id', id);
  if (error) throw error;
}
