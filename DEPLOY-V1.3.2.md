# Deploy Photon Physics Academy v1.3.2

## Before deploying
1. Keep a copy of the currently deployed source and confirm you are using the intended Supabase project.
2. Verify that the store core, store security/indexes, store order workflow, and `store_coupons_advanced_v1` migrations have already been applied.
3. In Supabase SQL Editor, review and apply `src/db/migrations/store_guest_cod_v1_3_1.sql` first if it has not already been applied. This migration changes checkout to support guest orders and disables the old wallet-capable checkout RPC.
4. Then review and apply `src/db/migrations/homepage_slideshow_urls_v1_3_2.sql`.
5. Confirm `homepage_slideshow_images` exists, RLS is enabled, anonymous users can read active rows only, and only enrolled store admins can insert/update/delete.

## Netlify
1. Extract this ZIP and deploy the project folder as the site source, or connect the matching repository commit.
2. Set `VITE_SUPABASE_URL` and `VITE_SUPABASE_ANON_KEY` in Netlify environment variables. Never put a service-role key in frontend environment variables.
3. Build with Node.js 20.19+ and run `npm run build` before publishing.
4. Publish the resulting build and check the deployment logs.

## Test checklist
- Add a book: the confirmation popup names the book and offers Continue Shopping / View Cart.
- Checkout requires no account, sign-up, email, or password.
- Cash on Delivery is the only selectable payment option; bKash and Nagad are visible but disabled as Upcoming.
- Place a test guest order and verify customer name, phone, full address, area/thana, city/district, items, coupon discount, delivery fee, total, and order status in Admin → Store Management → Orders.
- In Admin → Settings → Homepage Slideshow, add a publicly accessible HTTPS image URL, title, and caption. Confirm it appears on the homepage after refresh.
- Test reordering, hide/show, edit, and remove. Removing an entry must not delete the hosted image.
- Verify a non-admin cannot manage slideshow URLs and that hidden slides are not shown publicly.

## Important
The frontend and migrations are separate deployment steps. Do not expect guest checkout or slideshow administration to work on the live site until the required migrations and matching frontend are deployed. Do not apply the guest checkout migration twice if it is already recorded as applied; inspect the schema/migration history first.
