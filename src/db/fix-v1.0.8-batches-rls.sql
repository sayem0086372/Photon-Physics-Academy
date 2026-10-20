-- Photon Physics Academy v1.0.8 — repair batch-management RLS policies.
-- Run once in Supabase Dashboard > SQL Editor > New query.
-- Keeps public access read-only for active batches and restricts all batch
-- writes (and admin-only reads of inactive batches) to the known admin user.
-- Safe to re-run: existing policies on public.batches are replaced.

ALTER TABLE public.batches ENABLE ROW LEVEL SECURITY;

DO $$
DECLARE policy_row RECORD;
BEGIN
  FOR policy_row IN
    SELECT policyname
    FROM pg_policies
    WHERE schemaname = 'public' AND tablename = 'batches'
  LOOP
    EXECUTE format('DROP POLICY IF EXISTS %I ON public.batches', policy_row.policyname);
  END LOOP;
END
$$;

CREATE POLICY "Public can read active batches"
ON public.batches
FOR SELECT
TO anon, authenticated
USING (status = 'active');

CREATE POLICY "Academy admin manages batches"
ON public.batches
FOR ALL
TO authenticated
USING (auth.uid() = '67270b87-8169-4b2e-aa41-cc397ba2bdcd'::uuid)
WITH CHECK (auth.uid() = '67270b87-8169-4b2e-aa41-cc397ba2bdcd'::uuid);
