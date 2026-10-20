-- Photon v1.0.5 — run ONCE in Supabase > SQL Editor on your existing project (safe to re-run).
-- 1) Link students to batches
-- 2) Remove the two old demo batches
-- 3) Add the real batch list (skips any that already exist)

ALTER TABLE public.students
  ADD COLUMN IF NOT EXISTS batch_id UUID REFERENCES public.batches(id) ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_students_batch ON public.students(batch_id);

DELETE FROM public.batches
WHERE batch_name IN (
  'Class 9 — Vector & Dynamics Batch A',
  'Class 10 — Board CQ & MCQ Master Batch'
);

INSERT INTO public.batches (batch_name, class_level, schedule_days, time_slot, room_number, max_capacity, enrolled_students, instructor_name)
SELECT v.*
FROM (VALUES
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
('দশম শ্রেণি · মেয়েদের ব্যাচ', 'Class 10', 'রবি · মঙ্গল · বৃহস্পতি', '৫:৩০ PM', '', 35, 0, 'Rafiqul Islam (Jewel Sir)')
) AS v(batch_name, class_level, schedule_days, time_slot, room_number, max_capacity, enrolled_students, instructor_name)
WHERE NOT EXISTS (
  SELECT 1 FROM public.batches b
  WHERE b.batch_name = v.batch_name AND b.schedule_days = v.schedule_days AND b.time_slot = v.time_slot
);
