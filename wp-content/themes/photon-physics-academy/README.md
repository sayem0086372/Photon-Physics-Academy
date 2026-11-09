# Photon Physics Academy — WordPress Theme

This is the first visual/theme foundation for the WordPress migration.

## Current scope
- Custom PHP theme shell, responsive header/footer, typography and design tokens.
- Homepage sections based on the current React website.
- The homepage keeps the store banner first, then the hero, then the notice board as requested.
- WordPress custom post type for academy notices.
- WooCommerce theme support and a matching content wrapper.
- Existing academy logo and original image assets are referenced from the source repository while the migration is in progress.

## Install (after testing)
1. In WordPress, go to **Appearance → Themes → Add New → Upload Theme**.
2. Upload the packaged theme ZIP.
3. Activate only on a staging/test WordPress site first.
4. Set the WordPress homepage to a static page and select that page as the homepage if required by the WordPress Reading settings.
5. Create a Primary Navigation menu and assign it to the Primary Navigation location.
6. Configure the WooCommerce shop page slug as `store` so the existing `/store/` route is retained.

## Important migration boundary
This theme does not yet migrate the existing student records, payment history, SMS gateway, admin workflows, or Supabase data. Do not remove or disconnect Supabase and do not delete production records as part of installing this theme. Those workflows require a separate, access-controlled WordPress plugin and a verified migration plan.

The theme is under active development on the `wordpress-migration` branch. It is not yet approved for production installation.
