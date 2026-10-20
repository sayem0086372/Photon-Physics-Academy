# Photon Physics Academy v1.3.3 — Multi-Page Store

## Multi-page navigation
- Added a dedicated `/store` route that renders the Book Store on its own page.
- Kept the shared academy Navbar and Footer on the Store page, matching the academy's existing branding.
- Updated navigation links so the Store opens at `/store`, while academy section links return to the homepage and its relevant section.
- Added a clear return-to-home link on the Store page.
- Kept the existing Netlify SPA fallback in `netlify.toml`; direct URL and refresh behavior for defined client-side routes is supported by that rewrite.
- Preserved the existing store component, checkout, coupon, slideshow settings, and Supabase integration. No database migrations are applied by this source package.

## Notes
- Existing academy content sections remain on the homepage; this version makes the Store a separate page as requested.
- `/test` is not added as a page because it was not part of the requested site structure.
- This ZIP is source code only. It does not deploy the site or change the production database.
