# Photon Physics Academy v1.3.4

## Store and homepage updates
- English store banner at the top of the homepage, directly under the shared navigation: “Find Your Next Book”, “Explore concept books and other learning resources, and order online.” and “Browse & Buy Books”.
- Store destination defaults to `/store`; configure an external URL with `VITE_STORE_URL` at build time.
- Compact classroom gallery styling; old dedicated classroom slideshow is not mounted by the homepage.
- Dedicated product detail routes (`/store/:bookId`), cart (`/cart`), checkout (`/checkout`), and order confirmation (`/order-confirmation/:orderNumber`).
- Cart contents persist in browser local storage between pages.
- Checkout shows Cash on Delivery as available and supplied bKash/Nagad logos as Coming Soon.
- Existing Supabase store book query and guest order RPC are retained; no production database migration is included or applied.

## Deployment notes
- Netlify SPA fallback is configured in `netlify.toml`.
- Build verification must be completed in a Node.js environment meeting the package engine requirement (Node >=20.19; Netlify config uses Node 22).
- Checkout requires the existing `place_guest_store_order_with_coupon` RPC and compatible schema to already be installed. The optional notes field is currently collected in the UI but the existing RPC does not accept notes, so notes are not persisted.

## v1.3.5 payment-method update
- Updated the bKash logo URL to the newly supplied JPG.
- Enabled bKash and Nagad as selectable manual wallet payment methods alongside Cash on Delivery.
- Wallet checkout instructs customers to send the order total to 01312364499, requires a transaction ID, and optionally collects the sender mobile number.
- Added `src/db/migrations/store_wallet_manual_verification_v1_3_5.sql`. Apply it only after `store_guest_cod_v1_3_1.sql` and the store coupon migration; it adds `store_orders.sender_mobile` and updates the guest checkout RPC to store payment method, transaction ID and sender number. It leaves wallet payments pending for manual admin verification.
- Store Management displays transaction ID and sender mobile number on order records. Payments must be verified against the actual wallet transaction before moving orders into processing.
- The frontend package is not deployed automatically; the matching database migration must be applied before wallet checkout will work.
