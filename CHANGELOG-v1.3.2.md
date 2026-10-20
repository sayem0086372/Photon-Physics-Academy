# Photon Physics Academy v1.3.2 — Store UX & Classroom Slideshow

## Store experience
- Added a confirmation popup after a book is successfully added to the cart, including the book title, Continue Shopping, and View Cart actions.
- Updated customer-facing payment text to use the full name **Cash on Delivery** instead of the abbreviation.
- Retained bKash and Nagad as visible but unavailable, marked Upcoming.
- Retained guest checkout, coupons, stock revalidation, and server-side order pricing from v1.3.1.

## Homepage slideshow
- Added the homepage section **“ক্লাসরুম শিখন পরিবেশ · ময়মনসিংহ”** with automatic transitions, previous/next controls, dots, pause/play, responsive layout, and reduced-motion support.
- The slideshow uses admin-managed public image URLs when configured, and falls back to the existing built-in classroom gallery images otherwise.
- Added Settings → Homepage Slideshow tools to add/edit image URLs, titles and captions, preview images, reorder slides, publish/hide slides, and remove URL entries.
- Supabase stores only URL and presentation metadata. It does not store or upload the image files.
- Public visitors can read active slide URLs; only enrolled store admins can manage the slide list.

## Database migrations
- `src/db/migrations/store_guest_cod_v1_3_1.sql` enables guest checkout and Cash on Delivery-only order submission.
- `src/db/migrations/homepage_slideshow_urls_v1_3_2.sql` adds the URL-only slideshow configuration table and RLS policies.
- Apply migrations in order after the existing store core, security, order workflow, and coupon migrations.

## Deployment note
- This ZIP is source code. It does not apply Supabase migrations or deploy the live Netlify website automatically.
- Validate the production build and test guest checkout and slideshow administration before publishing.
