# Photon Physics Academy v1.3.1 — Deployment checklist

## Release contents
- Guest bookstore checkout with no customer account or sign-up.
- Cash on Delivery is the only enabled payment method. bKash and Nagad are shown as disabled “Upcoming” options.
- Customer form: name, phone, full address, area/thana, and district/city.
- Store Admin order cards show customer contact, delivery details, items/quantities, coupon discount, delivery charge, total, and order status.
- Coupon code support remains available.

## Supabase migration
1. Confirm the base store schema and the v1.3.0 coupon migration are already applied to the intended Supabase project. For a new project, apply the store foundation first, then `src/db/migrations/store_coupons_advanced_v1.sql`.
2. Review `src/db/migrations/store_guest_cod_v1_3_1.sql` before applying it. It changes the store orders schema to permit guest orders, adds delivery area and checkout idempotency fields, enables the guest Cash on Delivery checkout RPC, and revokes the old wallet-capable checkout RPC.
3. Apply the v1.3.1 migration in Supabase SQL Editor or through the approved migration workflow.
4. Deploy this source to Netlify promptly after the migration. The old v1.3.0 checkout may stop working between migration and frontend deployment because the old checkout RPC is revoked.

## Frontend deployment
1. Set `VITE_SUPABASE_URL` and `VITE_SUPABASE_ANON_KEY` as Netlify environment variables.
2. Deploy the project root using the existing `netlify.toml` build configuration (`npm run build`, publish `dist`).
3. Test a Cash on Delivery order using a test catalog item. Confirm the order appears in Admin → Store Management → Orders with contact details and line items, and verify the stock count is reduced once.
4. Test an invalid coupon, a valid coupon, an unavailable book, and the bKash/Nagad disabled display.

## Important
- This ZIP does not apply the Supabase migration or deploy the live website automatically.
- No existing student data, academy batches, existing orders, books, or Store Admin membership is intentionally deleted.
- Customer addresses and phone numbers are private data; only Store Admin users should access order records.
