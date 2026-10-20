# Photon Physics Academy v1.3.0 — Store Coupons & Advanced Management

## Added
- Store Admin Coupon & Promotions tab with percent/fixed discounts, minimum basket value, optional maximum discount, total-use limits, per-customer limits, scheduling, activation/pause, and campaign descriptions.
- Checkout coupon application and server-side coupon preview. The final order RPC recalculates prices, validates coupon windows and limits, locks coupon/stock rows, reserves inventory, snapshots prices, records redemptions, and applies the discount transactionally.
- Order-level coupon code and discount snapshots. Existing orders default to zero coupon discount.
- Store reports panel with loaded-order summaries and CSV export including customer/delivery, payment and coupon fields.
- Coupon change audit entries in the existing store admin audit table.
- Checkout idempotency request UUID wired to the server RPC.

## Migration / deployment
1. Review and apply `src/db/migrations/store_coupons_advanced_v1.sql` in the intended Supabase project before deploying this source version.
2. Deploy the updated app source to Netlify.
3. Test coupon creation, date/usage limits, checkout totals, and a failed/expired code using a test account.

This package does not automatically modify the live Supabase schema or deploy to Netlify. The coupon feature will remain unavailable until the migration and matching frontend are both deployed. Existing academy tables, batches, and Store Admin membership are not changed by this migration.
