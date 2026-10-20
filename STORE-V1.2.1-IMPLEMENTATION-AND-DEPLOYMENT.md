# v1.2.1 Store Implementation & Deployment Notes

## What is included in this package
- `src/components/BookStore.tsx`: integrated responsive store UI, database-backed catalogue, search/category filter, cart, Supabase Auth sign-in/sign-up, checkout form, COD/manual mobile-payment choice, and RPC checkout request.
- `src/db/migrations/v1.2.1-store-proposal.sql`: a proposed additive schema and checkout RPC. It is intentionally labelled a review proposal; it has not been run against production.
- `CHANGELOG-v1.2.1.md`: version scope and known gaps.

## Before enabling production
1. Take a current Supabase schema/backup and review the existing live policies, grants, extensions, and current admin identity.
2. Review the SQL proposal in a staging Supabase project first. It adds new store tables and does not alter academy tables, but still needs SQL review and tests before production use.
3. Verify the admin account directly in Supabase Auth. Insert that verified user ID into `public.store_admins` through a trusted administrative session only after approval. Do not guess or copy an ID from an old schema file.
4. Add and test storage policies for book cover uploads. The current proposal stores cover URLs but does not create a Storage bucket.
5. Configure public `VITE_SUPABASE_URL` and `VITE_SUPABASE_ANON_KEY` in Netlify. Never put a service-role key or SMS provider secret in a `VITE_` variable.
6. Configure bKash/Nagad instructions and delivery fees through a reviewed admin-only path before accepting orders. The UI deliberately does not claim a payment has succeeded.
7. Verify the existing `sms-gateway` before extending it. OTP support, queued notifications, idempotent retries, and delivery logs are not yet wired into the store UI/function.
8. Only after staging tests and explicit production approval should the migration be applied and Netlify deployed.

## Known limitations in this v1.2.1 foundation
This is not yet the entire requested production store. The following are still required before production launch:
- Admin UI for book/category CRUD, image upload, low-stock alerts, order status transitions, payment verification/refunds, delivery settings, coupons, reports, CSV export, invoices, and audit review.
- Customer order history/tracking and address-book UI.
- OTP SMS flow, asynchronous SMS queue/worker, delivery retry policy, and duplicate-message handling.
- A dedicated reviewed admin RPC for order transitions/payment verification/refunds/cancellation stock return and audit writes.
- Cancellation/return policy, coupon validation, full phone normalization, rate limits, abuse protection, and operational monitoring.
- SQL syntax review plus automated tests for simultaneous checkout, duplicate retries, stock race conditions, row-level security, payment verification, and order-total invariants.

## Checkout contract
The UI calls `place_store_order` with the signed-in customer's delivery details, payment method, optional transaction reference, and book IDs/quantities. The server function calculates prices, discounts, delivery fee, locks stock rows, reserves stock, records snapshots, and always starts payment as `pending`. The returned order number is not a payment confirmation. If the migration is not installed, checkout is expected to fail safely.

## Build
Requires Node.js 20.19+ per `package.json`.

```bash
npm install
npm run lint
npm run build
```

Do not claim the release passed build/tests until these commands have actually completed successfully in the deployment environment.
