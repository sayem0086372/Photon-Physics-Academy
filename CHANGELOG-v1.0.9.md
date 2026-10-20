# Photon Physics Academy — v1.0.9

## Fixed
- Added one migration to normalize Row Level Security policies for students, fee payments, notices, batches, and exam results.
- Restricted all admin table writes and private data to the configured Supabase Auth administrator UUID.
- Preserved public read-only access to published notices and active batches.
- Updated admin error handling so RLS errors on any table point to the comprehensive migration.
- Aligned the base schema and package version with v1.0.9.

## Required before use
Run `src/db/fix-v1.0.9-all-tables-rls.sql` in Supabase SQL Editor. First verify the admin UUID in the SQL file matches the actual account under Authentication → Users. This migration replaces existing policies on the five application tables.

## Verification
ZIP structure and migration text checked. Build and live Supabase behavior were not verified in this environment; run `npm install` and `npm run build`, then test the migration and CRUD operations in the target Supabase project.
