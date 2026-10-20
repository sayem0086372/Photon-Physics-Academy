# Photon Physics Academy v1.3.1 — Guest Cash on Delivery Checkout

## Store checkout
- Removed the customer sign-in/sign-up requirement from bookstore checkout.
- Added guest checkout fields for full name, phone, full delivery address, area/thana, and district/city.
- Cash on Delivery is the only active payment method. bKash and Nagad remain visible as disabled “Upcoming” options.
- Added server-side guest Cash on Delivery order creation with price/stock/delivery revalidation, stock reservation, order items, payment event, and admin audit record.
- Store Admin Orders now displays guest customer details, delivery location, ordered books, payment method, coupon discount, delivery fee, and total.
- Coupons continue to work for guest checkout, with per-customer usage limits associated with the phone number.

## Database migration
- Review and apply `src/db/migrations/store_guest_cod_v1_3_1.sql` to the intended Supabase project before deploying this frontend.
- The migration allows guest orders (`user_id` nullable), adds `area` and checkout idempotency fields, and exposes only the COD guest checkout RPC to anonymous/authenticated customers.
- The legacy wallet-capable checkout RPC is revoked so wallet payments cannot be submitted through the old checkout path. Existing orders, books, batches, and Store Admin membership are preserved.
- This ZIP is source code; it does not automatically apply the migration or deploy the live Netlify site.
