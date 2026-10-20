# Photon Physics Academy v1.2.2 — Store Management Admin

- Added a Store Management item to the existing admin sidebar.
- Added authenticated, store-admin-only catalog management for adding and editing books, pricing, discounts, inventory thresholds, visibility, and cover image URLs.
- Added order listing, manual bKash/Nagad payment verification using the database RPC, and guarded order status progression.
- Added delivery fee, free-delivery threshold, and wallet instruction settings.
- Added low-stock and pending-payment indicators.
- Added the database RPC migration `src/db/migrations/store_admin_order_status_workflow_v1.sql` to enforce order-status progression and audit it.

## Setup notes

- The signed-in user must be enrolled in `public.store_admins`; the interface does not grant admin rights.
- Cover images currently use public image URLs. Storage uploads require a separate bucket/policy setup and are not enabled by this release.
- Payment verification is manual; only mark a wallet payment verified after checking the real transaction in the official account.
- Order cancellation/refunds are not implemented in this admin UI.
- Set `VITE_SUPABASE_URL` and `VITE_SUPABASE_ANON_KEY` in the build environment.
