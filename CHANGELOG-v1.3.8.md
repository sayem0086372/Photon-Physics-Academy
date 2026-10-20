# Photon Physics Academy v1.3.8 — Payment Workflow & Scale Readiness

## Included in this package
- Student data loader now uses paginated range requests so the initial Supabase response cap does not silently truncate the student list.
- Storefront and store-admin product/order/coupon fetch windows increased to 1,000 rows as an interim improvement.
- Existing manual bKash/Nagad payment verification flow already opens an SMS confirmation dialog after the admin verifies payment; retained.
- Package version updated to 1.3.8.

## Requirements still needing backend work before production use
- COD order-submitted SMS and bKash/Nagad order-submitted SMS require a secure server-side trigger/workflow. Do not expose the SMS provider key or bypass the admin-only SMS gateway from the public browser.
- The payment-confirmation popup currently appears after payment is marked verified; SMS delivery remains an explicit admin action. A durable idempotency/audit mechanism for SMS retries should be added before claiming duplicate delivery prevention.
- Storefront/admin lists currently fetch up to 1,000 records rather than implementing full UI pagination; this is an improvement, not an unlimited-record guarantee.
- The requested student fee categories ("Sheet বাবদ" and "ভর্তি ফি") and complete receipts/reports require a reviewed schema/UI implementation and migration. No database migration is included or automatically applied in this package.

## Deployment
- Use Node.js >=20.19.
- Run `npm install`, then `npm run build`.
- Deploy the project root to Netlify.
- Keep Supabase URL and public anon/publishable key in Netlify environment variables; never put service-role keys or SMS provider secrets in frontend code.
