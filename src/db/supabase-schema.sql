-- ==============================================================================
-- Photon Physics Academy (ময়মনসিংহ) — Supabase PostgreSQL Schema
-- Run this script in your Supabase Project: SQL Editor -> New query -> Run
-- ==============================================================================

-- 1. Enable UUID Extension
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- 2. Students Table
CREATE TABLE IF NOT EXISTS public.students (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    student_id_code VARCHAR(30) UNIQUE NOT NULL, -- e.g. PPA-26-09-01
    full_name VARCHAR(150) NOT NULL,
    bangla_name VARCHAR(150),
    class_level VARCHAR(20) NOT NULL,            -- 'Class 8', 'Class 9', 'Class 10'
    student_phone VARCHAR(20),
    guardian_name VARCHAR(150),
    guardian_phone VARCHAR(20),
    whatsapp_phone VARCHAR(20),
    batch_timing VARCHAR(100),
    institution VARCHAR(200),                    -- e.g. Mymensingh Zilla School
    status VARCHAR(20) DEFAULT 'active',         -- 'active', 'inactive'
    monthly_fee_bdt NUMERIC(10, 2) DEFAULT 1500.00,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- 3. Monthly Fee & Payment Records Table
CREATE TABLE IF NOT EXISTS public.fee_payments (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    student_id UUID REFERENCES public.students(id) ON DELETE CASCADE NOT NULL,
    billing_month VARCHAR(20) NOT NULL,          -- e.g. 'October'
    billing_year INT NOT NULL,                   -- 2026
    payable_amount NUMERIC(10, 2) NOT NULL,
    paid_amount NUMERIC(10, 2) DEFAULT 0.00,
    due_amount NUMERIC(10, 2) DEFAULT 0.00,
    payment_status VARCHAR(20) DEFAULT 'Due',    -- 'Paid', 'Partial', 'Due'
    payment_method VARCHAR(50) DEFAULT 'Cash',   -- 'Cash', 'bKash', 'Nagad'
    transaction_reference VARCHAR(100),
    receipt_number VARCHAR(50) UNIQUE NOT NULL,  -- e.g. RCP-2026-10-001
    payment_date DATE DEFAULT CURRENT_DATE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- 4. Academic Notices Table
CREATE TABLE IF NOT EXISTS public.notices (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    title VARCHAR(255) NOT NULL,
    bangla_title VARCHAR(255) NOT NULL,
    category VARCHAR(50) NOT NULL,               -- 'ভর্তি বিজ্ঞপ্তি', 'পরীক্ষার সময়সূচি', 'ক্লাসের সময়সূচি', 'জরুরি ঘোষণা', 'ফলাফল প্রকাশ'
    short_description TEXT NOT NULL,
    full_content TEXT,
    publication_date DATE DEFAULT CURRENT_DATE NOT NULL,
    pdf_attachment_url TEXT,
    is_published BOOLEAN DEFAULT true,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- 5. Programs & Batches Table
CREATE TABLE IF NOT EXISTS public.batches (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    batch_name VARCHAR(150) NOT NULL,
    class_level VARCHAR(20) NOT NULL,            -- 'Class 8', 'Class 9', 'Class 10'
    schedule_days VARCHAR(100) NOT NULL,         -- e.g. 'রবি · মঙ্গল · বৃহস্পতি'
    time_slot VARCHAR(50) NOT NULL,              -- e.g. '৪:০০ PM - ৫:৩০ PM'
    room_number VARCHAR(30) DEFAULT 'Room 101',
    max_capacity INT DEFAULT 35,
    enrolled_students INT DEFAULT 0,
    instructor_name VARCHAR(100) DEFAULT 'Rafiqul Islam (Jewel Sir)',
    status VARCHAR(20) DEFAULT 'active'          -- 'active', 'upcoming'
);

-- Link students to batches (enrolled count is computed from this)
ALTER TABLE public.students
  ADD COLUMN IF NOT EXISTS batch_id UUID REFERENCES public.batches(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_students_batch ON public.students(batch_id);

-- 6. Exam Results Table
CREATE TABLE IF NOT EXISTS public.exam_results (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    student_id UUID REFERENCES public.students(id) ON DELETE CASCADE NOT NULL,
    exam_title VARCHAR(150) NOT NULL,            -- e.g. 'Chapter 2: Kinematics Assessment'
    chapter_topic VARCHAR(150) NOT NULL,         -- e.g. 'গতি (Kinematics)'
    total_marks NUMERIC(5, 2) NOT NULL,
    obtained_marks NUMERIC(5, 2) NOT NULL,
    highest_marks NUMERIC(5, 2),
    grade VARCHAR(5),                            -- 'A+', 'A', etc.
    exam_date DATE DEFAULT CURRENT_DATE NOT NULL,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT timezone('utc'::text, now()) NOT NULL
);

-- ==============================================================================
-- Indexes for High Performance Queries
-- ==============================================================================
CREATE INDEX IF NOT EXISTS idx_students_class ON public.students(class_level);
CREATE INDEX IF NOT EXISTS idx_students_code ON public.students(student_id_code);
CREATE INDEX IF NOT EXISTS idx_fee_payments_student ON public.fee_payments(student_id);
CREATE INDEX IF NOT EXISTS idx_fee_payments_month ON public.fee_payments(billing_month, billing_year);
CREATE INDEX IF NOT EXISTS idx_notices_published ON public.notices(is_published, publication_date DESC);
CREATE INDEX IF NOT EXISTS idx_exam_results_student ON public.exam_results(student_id);

-- ==============================================================================
-- Row Level Security (RLS) Policies
-- ==============================================================================
ALTER TABLE public.students ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.fee_payments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.notices ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.batches ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.exam_results ENABLE ROW LEVEL SECURITY;

-- Public visitors can only read published notices and active batches.
CREATE POLICY "Public can read published notices"
ON public.notices FOR SELECT TO anon, authenticated USING (is_published = true);

CREATE POLICY "Public can read active batches"
ON public.batches FOR SELECT TO anon, authenticated USING (status = 'active');

-- Replace the UUID below with the actual admin user ID from Supabase Auth > Users.
CREATE POLICY "Academy admin manages students"
ON public.students FOR ALL TO authenticated
USING (auth.uid() = '67270b87-8169-4b2e-aa41-cc397ba2bdcd'::uuid)
WITH CHECK (auth.uid() = '67270b87-8169-4b2e-aa41-cc397ba2bdcd'::uuid);

CREATE POLICY "Academy admin manages fee payments"
ON public.fee_payments FOR ALL TO authenticated
USING (auth.uid() = '67270b87-8169-4b2e-aa41-cc397ba2bdcd'::uuid)
WITH CHECK (auth.uid() = '67270b87-8169-4b2e-aa41-cc397ba2bdcd'::uuid);

CREATE POLICY "Academy admin manages notices"
ON public.notices FOR ALL TO authenticated
USING (auth.uid() = '67270b87-8169-4b2e-aa41-cc397ba2bdcd'::uuid)
WITH CHECK (auth.uid() = '67270b87-8169-4b2e-aa41-cc397ba2bdcd'::uuid);

CREATE POLICY "Academy admin manages batches"
ON public.batches FOR ALL TO authenticated
USING (auth.uid() = '67270b87-8169-4b2e-aa41-cc397ba2bdcd'::uuid)
WITH CHECK (auth.uid() = '67270b87-8169-4b2e-aa41-cc397ba2bdcd'::uuid);

CREATE POLICY "Academy admin manages exam results"
ON public.exam_results FOR ALL TO authenticated
USING (auth.uid() = '67270b87-8169-4b2e-aa41-cc397ba2bdcd'::uuid)
WITH CHECK (auth.uid() = '67270b87-8169-4b2e-aa41-cc397ba2bdcd'::uuid);

-- ==============================================================================
-- Initial Seed Data (Optional Sample Records)
-- ==============================================================================
INSERT INTO public.batches (batch_name, class_level, schedule_days, time_slot, room_number, max_capacity, enrolled_students, instructor_name)
VALUES
('অষ্টম শ্রেণি · বিজ্ঞান ব্যাচ', 'Class 8', 'রবি · মঙ্গল · বৃহস্পতি', '৬:৩০ PM', '', 35, 0, 'Rafiqul Islam (Jewel Sir)'),
('নবম শ্রেণি · মেয়েদের ব্যাচ', 'Class 9', 'শনি · সোম · বুধ', '১০:০০ AM', '', 35, 0, 'Rafiqul Islam (Jewel Sir)'),
('নবম শ্রেণি · মেয়েদের ব্যাচ', 'Class 9', 'শনি · সোম · বুধ', '১২:৩০ PM', '', 35, 0, 'Rafiqul Islam (Jewel Sir)'),
('নবম শ্রেণি · মেয়েদের ব্যাচ', 'Class 9', 'শনি · সোম · বুধ', '৪:৩০ PM', '', 35, 0, 'Rafiqul Islam (Jewel Sir)'),
('নবম শ্রেণি · মেয়েদের ব্যাচ', 'Class 9', 'শনি · সোম · বুধ', '৫:৩০ PM', '', 35, 0, 'Rafiqul Islam (Jewel Sir)'),
('নবম শ্রেণি · ছেলেদের ব্যাচ', 'Class 9', 'শনি · সোম · বুধ', '১১:০০ AM', '', 35, 0, 'Rafiqul Islam (Jewel Sir)'),
('নবম শ্রেণি · ছেলেদের ব্যাচ', 'Class 9', 'শনি · সোম · বুধ', '৩:৩০ PM', '', 35, 0, 'Rafiqul Islam (Jewel Sir)'),
('নবম শ্রেণি · ছেলেদের ব্যাচ', 'Class 9', 'রবি · মঙ্গল · বৃহস্পতি', '৮:০০ AM', '', 35, 0, 'Rafiqul Islam (Jewel Sir)'),
('নবম শ্রেণি · মেয়েদের ব্যাচ', 'Class 9', 'রবি · মঙ্গল · বৃহস্পতি', '৯:০০ AM', '', 35, 0, 'Rafiqul Islam (Jewel Sir)'),
('দশম শ্রেণি · মেয়েদের ব্যাচ', 'Class 10', 'শনি · সোম · বুধ', '৮:০০ AM', '', 35, 0, 'Rafiqul Islam (Jewel Sir)'),
('দশম শ্রেণি · ছেলেদের ব্যাচ', 'Class 10', 'শনি · সোম · বুধ', '৯:০০ AM', '', 35, 0, 'Rafiqul Islam (Jewel Sir)'),
('দশম শ্রেণি · ছেলেদের ব্যাচ', 'Class 10', 'শনি · সোম · বুধ', '২:৩০ PM', '', 35, 0, 'Rafiqul Islam (Jewel Sir)'),
('দশম শ্রেণি · মেয়েদের ব্যাচ', 'Class 10', 'রবি · মঙ্গল · বৃহস্পতি', '৫:৩০ PM', '', 35, 0, 'Rafiqul Islam (Jewel Sir)');
