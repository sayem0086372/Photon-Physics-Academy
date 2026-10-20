-- Photon Physics Academy v1.0.9 — repair RLS policies across ALL admin tables.
-- Run once in Supabase Dashboard > SQL Editor > New query.
-- IMPORTANT: Verify the admin UUID against Authentication > Users before running.
-- This migration replaces all existing RLS policies on the five application tables.
-- Public visitors can only read published notices and active batches. All writes and
-- private student/payment/result data are restricted to the configured admin user.

BEGIN;

ALTER TABLE public.students ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.fee_payments ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.notices ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.batches ENABLE ROW LEVEL SECURITY;
ALTER TABLE public.exam_results ENABLE ROW LEVEL SECURITY;

DO $$
DECLARE
  table_name text;
  policy_row record;
BEGIN
  FOREACH table_name IN ARRAY ARRAY['students', 'fee_payments', 'notices', 'batches', 'exam_results']
  LOOP
    FOR policy_row IN
      SELECT policyname
      FROM pg_policies
      WHERE schemaname = 'public' AND tablename = table_name
    LOOP
      EXECUTE format('DROP POLICY IF EXISTS %I ON public.%I', policy_row.policyname, table_name);
    END LOOP;
  END LOOP;
END
$$;

-- Public, read-only access for content intentionally shown on the public website.
CREATE POLICY "Public can read published notices"
ON public.notices FOR SELECT TO anon, authenticated
USING (is_published = true);

CREATE POLICY "Public can read active batches"
ON public.batches FOR SELECT TO anon, authenticated
USING (status = 'active');

-- Admin-only access to all records and all operations.
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

COMMIT;
