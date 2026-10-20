# Photon Physics Academy — v1.1.0

## Changes
- Removed the Bengali payment-summary subtitles while retaining the English labels.
- Removed the Supabase connection-status indicator from the admin header.
- Changed the admin login heading and public navbar admin action to English (`Admin Login` / `Admin`).
- Reworked the public navbar breakpoints and added a compact admin shortcut in the mobile top bar so the action stays within the header.
- Corrected combined batch-time data: every scheduled time is now its own batch record in demo data and fresh-install seed data.
- Added `src/db/update-v1.1.0-batch-times.sql` to split the combined time strings in an existing Supabase database while keeping existing student links on the first listed time.

## Database update required for existing deployments
Run `src/db/update-v1.1.0-batch-times.sql` in Supabase SQL Editor. It is designed to be re-runnable and adds any missing separate-time rows. Review the proposed time-to-batch mapping before running it if your current schedule has changed.

## Verification
Source and ZIP structure checks are performed for this release. A production build and live Supabase migration are not confirmed unless separately run in the target environment.
