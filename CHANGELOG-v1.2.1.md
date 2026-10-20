# Photon Physics Academy v1.2.1 — Integrated Book Store foundation

## Included
- Added a responsive Book Store section to the existing academy page and public navigation.
- Added search, category filtering, lazy-loaded cover images, discount display, stock display, cart quantity controls, subtotal calculation, and checkout form.
- Added Supabase Auth sign-in/sign-up entry point for customers.
- Added a checkout call to a server-side `place_store_order` RPC contract. The UI does not mark manual bKash/Nagad payments as paid.
- Added a proposed store schema migration and deployment checklist.
- Preserved the existing academy sections and admin login flow.

## Important status
This is a **pre-production v1.2.1 foundation**, not a claim that the full store is production-ready. The migration is a proposal and has NOT been applied to the live Supabase project. Until the migration is reviewed and deployed, the store will show a setup message and checkout will not work. Customer OTP SMS, delivery-fee configuration, admin store CRUD, payment review UI, refund processing, coupons, reports, CSV exports, invoices, SMS delivery logs/retries, and end-to-end concurrency/security tests remain follow-up implementation work.

## Safety
- No production database was changed.
- No service-role key or SMS credential is included.
- Do not deploy the store migration until the verified admin identity and RLS policy design are reviewed.
