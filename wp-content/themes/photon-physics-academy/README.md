# Photon Physics Academy — WordPress Theme

Version 2.0.0 — restoration work is in progress on the `wordpress-v2-restoration` branch.

## Version 2.0.0 work started
- Restored interactive notice category filters and notice detail modal.
- Restored homepage animated statistics.
- Added classroom gallery lightbox with previous/next navigation.
- Added admission inquiry form with course selection, secure nonce validation, honeypot field, WordPress inquiry records and email notification.
- Added footer Admin Login access.
- Added the two-floating-book animated bookstore hero on the WooCommerce `/store/` catalogue.
- Added a notice-category taxonomy and inquiry content type in the companion core plugin.

## Still to migrate from the original React main branch
The following are not yet complete and must not be considered available until implemented and tested:
- Original custom admin experience and its nine areas: Overview, Students, Programs & Batches, Payments & Fees, Notices, Results & Exams, SMS Management, Settings & Slideshow, Store Management.
- Student records, student IDs, guardian contacts and batch assignments.
- Batch CRUD and student enrollment.
- Monthly fee schedules, payment transactions, partial payments, due calculations and receipts.
- SMS gateway integration, guardian messaging, balance display and the original send-message workflows.
- Results and chapter-wise exam marks.
- Complete custom bookstore catalogue, cart/checkout flow, order tracking, coupons, delivery management and bKash/Nagad manual verification.
- Exact original logo asset and complete homepage visual parity.

## Install structure
- Theme: `wp-content/themes/photon-physics-academy/`
- Companion plugin: `wp-content/plugins/ppa-academy-core/`

Install the theme and companion plugin on a staging WordPress site first. Keep production backups and existing data intact. This branch is not approved for production deployment.
