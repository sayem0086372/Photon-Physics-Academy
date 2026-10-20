-- Photon Physics Academy v1.1.0
-- Split combined time strings into one database row per distinct batch time.
-- Run once in Supabase Dashboard > SQL Editor. Safe to re-run.
-- Existing students remain assigned to their original (first-time) batch.

BEGIN;

-- Keep the original row / linked students for the first listed time.
UPDATE public.batches
SET time_slot = '১০:০০ AM'
WHERE batch_name = 'নবম শ্রেণি · মেয়েদের ব্যাচ'
  AND class_level = 'Class 9'
  AND schedule_days = 'শনি · সোম · বুধ'
  AND time_slot = '১০:০০ AM · ১২:৩০ PM · ৪:৩০ PM · ৫:৩০ PM';

UPDATE public.batches
SET time_slot = '১১:০০ AM'
WHERE batch_name = 'নবম শ্রেণি · ছেলেদের ব্যাচ'
  AND class_level = 'Class 9'
  AND schedule_days = 'শনি · সোম · বুধ'
  AND time_slot = '১১:০০ AM · ৩:৩০ PM';

UPDATE public.batches
SET time_slot = '৯:০০ AM'
WHERE batch_name = 'দশম শ্রেণি · ছেলেদের ব্যাচ'
  AND class_level = 'Class 10'
  AND schedule_days = 'শনি · সোম · বুধ'
  AND time_slot = '৯:০০ AM · ২:৩০ PM';

-- Add the other times as separate batch rows, without duplicating existing ones.
WITH desired(batch_name, class_level, schedule_days, time_slot) AS (
  VALUES
    ('নবম শ্রেণি · মেয়েদের ব্যাচ', 'Class 9', 'শনি · সোম · বুধ', '১২:৩০ PM'),
    ('নবম শ্রেণি · মেয়েদের ব্যাচ', 'Class 9', 'শনি · সোম · বুধ', '৪:৩০ PM'),
    ('নবম শ্রেণি · মেয়েদের ব্যাচ', 'Class 9', 'শনি · সোম · বুধ', '৫:৩০ PM'),
    ('নবম শ্রেণি · ছেলেদের ব্যাচ', 'Class 9', 'শনি · সোম · বুধ', '৩:৩০ PM'),
    ('দশম শ্রেণি · ছেলেদের ব্যাচ', 'Class 10', 'শনি · সোম · বুধ', '২:৩০ PM')
)
INSERT INTO public.batches
  (batch_name, class_level, schedule_days, time_slot, room_number,
   max_capacity, enrolled_students, instructor_name, status)
SELECT
  d.batch_name, d.class_level, d.schedule_days, d.time_slot,
  COALESCE(base.room_number, ''),
  COALESCE(base.max_capacity, 35), 0,
  COALESCE(base.instructor_name, 'Rafiqul Islam (Jewel Sir)'),
  COALESCE(base.status, 'active')
FROM desired d
LEFT JOIN LATERAL (
  SELECT b.room_number, b.max_capacity, b.instructor_name, b.status
  FROM public.batches b
  WHERE b.batch_name = d.batch_name
    AND b.class_level = d.class_level
    AND b.schedule_days = d.schedule_days
  ORDER BY CASE WHEN b.time_slot = '১০:০০ AM' OR b.time_slot = '১১:০০ AM' OR b.time_slot = '৯:০০ AM' THEN 0 ELSE 1 END
  LIMIT 1
) base ON true
WHERE NOT EXISTS (
  SELECT 1 FROM public.batches existing
  WHERE existing.batch_name = d.batch_name
    AND existing.class_level = d.class_level
    AND existing.schedule_days = d.schedule_days
    AND existing.time_slot = d.time_slot
);

-- Refresh the text snapshot for students still linked to their original batch.
UPDATE public.students s
SET batch_timing = LEFT(b.schedule_days || ' · ' || b.time_slot, 100)
FROM public.batches b
WHERE s.batch_id = b.id;

COMMIT;
