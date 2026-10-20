-- READ-ONLY PREFLIGHT ONLY. Safe to run before deciding whether to apply the
-- v1.1.7 payment transaction migration. These queries do not change data.

-- Confirm the current database contains the expected tables and record counts.
SELECT 'students' AS table_name, count(*) AS row_count FROM public.students
UNION ALL
SELECT 'fee_payments', count(*) FROM public.fee_payments;

-- Inspect the existing monthly uniqueness constraints without exposing records.
SELECT c.conname, pg_get_constraintdef(c.oid) AS definition
FROM pg_constraint c
JOIN pg_class t ON t.oid = c.conrelid
JOIN pg_namespace n ON n.oid = t.relnamespace
WHERE n.nspname = 'public'
  AND t.relname = 'fee_payments'
  AND c.contype IN ('u', 'p');

-- Inspect policy names and expressions. Verify the UID is the actual admin UID.
SELECT schemaname, tablename, policyname, roles, cmd, qual, with_check
FROM pg_policies
WHERE schemaname = 'public'
  AND tablename IN ('students', 'fee_payments')
ORDER BY tablename, policyname;

-- Confirm project reference by checking the Supabase dashboard URL separately;
-- SQL alone should not be treated as proof that this is the production project.
