# Photon Physics Academy — v1.0.8

## Fixed
- Added an idempotent Supabase migration to repair `public.batches` RLS policies.
- Batch writes are restricted to the configured academy admin Auth user ID; public visitors can only read active batches.
- Improved the admin-panel error message for batch RLS failures so the required migration and admin-ID check are clear.
- Updated the package version to 1.0.8 and aligned the base schema's batch policies.

## Required before use
Run `src/db/fix-v1.0.8-batches-rls.sql` in Supabase SQL Editor. Verify the admin Auth user's UUID matches the UUID in the migration. See `README.md`.

## Verification
Not build-tested: dependency installation could not complete in this environment. Run `npm install` and `npm run build` before deploying.
