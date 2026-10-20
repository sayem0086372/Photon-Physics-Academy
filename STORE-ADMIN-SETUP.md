# Store Management — deployment and access setup

## What changed

The existing admin portal now has a **Store Management** sidebar option with:
- Add/edit books, authors, categories, descriptions, prices, discounts, stock and low-stock thresholds.
- Publish/hide books from the public catalog.
- Search the catalog and see low-stock alerts.
- Review orders and their delivery details.
- Record manual bKash/Nagad payment verification through the admin-only Supabase function.
- Advance order status through placed → confirmed → processing → shipped → delivered.
- Configure delivery fee, free-delivery threshold, and customer-facing wallet instructions.

## Deploy

1. Unzip this release.
2. Set `VITE_SUPABASE_URL` and `VITE_SUPABASE_ANON_KEY` in Netlify site environment variables.
3. Deploy the project root to Netlify, or commit the source to the connected Git repository and let its build run.
4. Open the existing admin portal using the website's Admin link (the route is `#admin`).
5. Sign in with the existing Supabase Auth account.

## Store administrator enrollment

The store screen checks `public.is_store_admin()` and does not grant access based on login alone. A verified project owner must enroll the intended account in `public.store_admins` after checking its exact UUID in Supabase Dashboard → Authentication → Users.

Use the following SQL only after replacing the placeholder with the verified Auth user UUID:

```sql
insert into public.store_admins (user_id)
values ('REPLACE_WITH_VERIFIED_AUTH_USER_UUID'::uuid)
on conflict (user_id) do nothing;
```

Do not use a UUID based on memory or guesswork. The store admin table intentionally has no public read policy; the UI uses the protected `is_store_admin()` function.

## Limitations

- Book covers currently use a public image URL; direct Storage upload is not enabled yet.
- Payment verification is manual and must only be recorded after checking the real transaction.
- Cancellation/refund controls are not included in this UI.
- The build could not be verified in this environment because `npm install` timed out and Vite was not available. Run `npm install` and `npm run build` in your deployment environment before publishing.


## v1.3.0 coupons and advanced store tools
- Apply `src/db/migrations/store_coupons_advanced_v1.sql` before deploying v1.3.0. It creates coupon and redemption tables, adds coupon snapshots to orders, and installs secure preview/checkout RPCs.
- The migration is additive to academy tables and preserves batches, existing store orders, and `store_admins`. Existing orders get `coupon_discount_bdt = 0`.
- Coupon codes are uppercase; supports percentage/fixed discounts, minimum subtotal, optional maximum discount, campaign dates, total usage cap, and per-user usage cap. Checkout validates and redeems the code server-side.
- The Reports & Export panel downloads the currently loaded/filtered orders as CSV. It is limited to the latest 300 loaded orders and is not a complete accounting ledger. Exported customer contact/address data must be kept private.
- This version is source-only until the migration is applied and the ZIP is deployed to Netlify.


## v1.3.1 guest checkout and Cash on Delivery
- Apply `src/db/migrations/store_guest_cod_v1_3_1.sql` to the correct Supabase project, then deploy the v1.3.1 source to Netlify.
- The checkout form is available without signup/login. Customers provide name, phone, address, area/thana, and district/city. Only COD is selectable; bKash and Nagad are visible as disabled “Upcoming” options.
- The Admin → Store Management → Orders panel displays customer contact/address, ordered books, coupon, delivery charge, and total.
- Do not assume the live site is updated until the Netlify deployment completes and a test COD order is visible in Admin. Test with a low-risk/test catalog item first.
