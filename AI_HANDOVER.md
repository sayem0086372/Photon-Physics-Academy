# AI Handover — Photon Physics Academy

Last updated: 2026-11-11  
Repository: `oathwi1d/Photon-Physics-Academy`  
Active WordPress work branch: `wordpress-v2-restoration`

> This document is the working handover for the next AI/developer. Read it before making changes. Treat the repository code and current WordPress installation as the source of truth; this document summarizes project intent and known state, but some features have not yet been tested on the live site.

## 1. Mission and current direction

The project is Photon Physics Academy, a coaching/physics-learning center in Mymensingh, Bangladesh. The current migration target is **WordPress on Namecheap cPanel**, using the main domain:

- Public website: https://photonphysicsbd.com
- Repository: https://github.com/oathwi1d/Photon-Physics-Academy
- Current working branch: `wordpress-v2-restoration`
- Production/default branch: `main`

The user moved the current implementation from the earlier React/Supabase app to WordPress. Do not switch back to Netlify or Vercel. Cloudflare Pages was a previous React deployment target, not the current WordPress target.

**Critical release rule:** Work on `wordpress-v2-restoration` unless explicitly instructed otherwise. Do not merge to `main`, deploy, alter production, or run destructive database operations without the user's explicit approval.

## 2. User's design and collaboration expectations

- The WordPress site should closely reproduce the original React **main branch** design and functionality—not a generic WordPress theme.
- Visual quality must be polished, modern, professional, and mobile-first. Avoid cartoon-like layouts, cheap-looking components, generic AI/vibe-coded styling, purple/fuchsia gradients, and excessive animation.
- Preferred palette: white, deep navy, slate, and sky blue.
- Use subtle transitions (roughly 150–250 ms) and respect reduced-motion preferences.
- The site must be fast on inexpensive Android phones and mobile data.
- The user prefers concise, practical instructions in Bangla when guiding them through WordPress/cPanel tasks. Avoid unnecessary steps and don't claim work is complete unless verified.
- The user prefers direct GitHub commits and installable ZIPs over repeated version ZIPs for source-code changes.
- Keep the public website and the custom Academy Admin distinct from the WordPress Dashboard.

## 3. Public website requirements

- Keep the original Photon Physics Academy identity and original logo. Logo URL:
  https://cdn.phototourl.com/member/2026-10-26-00b8c0d9-38d9-4d60-a377-dfd8928013de.png
- Main navigation historically includes Home, About, Programs, Notices, Results, Resources, Gallery, and Contact.
- Brand/tagline used previously: “Feel the Power of Physics...”
- Course/program details should appear as compact details on the homepage; the user specifically does **not** want separate course detail pages for this content.
- Homepage order requirements previously specified:
  - Store promotional banner above the main hero/notice area.
  - Notice Board immediately below the Hero.
- Store banner copy:
  “Explore concept books and other learning resources, and order online.”
  Button: “Browse & Buy Books ↗”
- Footer credit:
  “Designed and Developed by oathwild (sayeem)”
  Make `oathwild` glow subtly. GitHub profile link must be https://github.com/oathwi1d (not the old username).
- Add an Admin login link at the bottom of the public website. It should lead to the custom Academy Admin login, not the WordPress login.

## 4. Custom Academy Admin

The admin interface is intended to be a **custom website-building/academy management panel**, not the WordPress Dashboard.

- Admin route: `/academyadmin/`
- It should visually match the original React main-branch admin as closely as possible.
- The original admin look used a dark navy sidebar with blue accents. Preserve the professional visual language and responsive/mobile behavior.
- Keep WordPress Dashboard access separate; don't redirect the user's normal admin workflow into wp-admin.
- The admin has sections including Programs & Batches, students, fees/payments, results, SMS management, settings, and store/order management as implemented in the branch.
- The user previously reported low contrast in stat cards and the visitor counter; check contrast and readability.
- Make admin tables, forms, notices, and mobile layouts consistent and usable.

## 5. Store

- Current WordPress store route is `/shop/` (older React version used `/store`; current WP route is the one to preserve unless user requests a change).
- The store should restore the previous polished look, including a hero with two floating book images.
- Use real book-cover images where available; avoid obviously AI-generated placeholders.
- Product cards should be visually substantial and readable.
- Product flow requested previously:
  - “Buy Now” opens a product detail page.
  - Add-to-cart should show a confirmation with “Continue Shopping” and “View Cart”.
  - Checkout should be a full form page; customer signup is not required.
- Store should not show the academy's normal navigation if the original store design hid it.
- Product and order resets must never delete private coaching batch data.

### Store payments and order messages

- bKash, Nagad, and Cash on Delivery (COD) were requested as available methods (not “upcoming”).
- bKash instructions use `*247#`; Nagad instructions use `*167#`.
- A payment number was supplied in prior project conversations. Retrieve it from the user's current instructions or verified project configuration if needed; do not invent or expose payment credentials.
- bKash/Nagad orders: send an order-submitted SMS first; send a separate payment-confirmed SMS only after an admin explicitly marks payment received.
- COD orders: send the order SMS.
- Do not include receipt numbers in store/order SMS.
- Do not claim SMS delivery unless the gateway response confirms it.

## 6. Student, batch, fee, payment, and result data

**Data preservation is mandatory.** Never reset, truncate, or delete existing student, fee, payment, exam-result, or private coaching data. Never use a broad cleanup query. Before any database migration, take/verify a backup and explain exactly what will change.

The current WordPress implementation uses MySQL through WordPress `$wpdb`, not Supabase. The user selected WordPress phpMyAdmin/MySQL. The old Supabase project was a source/reference for legacy data only; do not write to it.

Known custom table names (prefix may vary by installation; the user's known prefix was `wppn_`):
- `wppn_ppa_students`
- `wppn_ppa_batches`
- `wppn_ppa_fee_records`
- `wppn_ppa_payment_transactions`
- `wppn_ppa_exam_results`

Do not put database passwords, API keys, gateway credentials, WordPress passwords, or other secrets in this handover or in GitHub. Do not assume the table prefix or DB configuration without checking the live installation's configuration.

Other requirements from earlier work:
- Student ID format: `YYNNN` (example format: `26001`).
- Student phone number is optional; guardian phone is used for SMS. WhatsApp number is optional contact-only information, not an SMS destination.
- Fee/payment records should support previous months, not only a few hardcoded recent months.
- Student search should be available in Record Payment.
- Preserve duplicate receipt/student/month protections and handle existing records safely.
- Deleting a student with fee/payment history requires a deliberate, safe policy; never cascade-delete financial history without explicit approval.
- No room-number system.

## 7. SMS requirements

- Gateway used previously: sms.net.bd.
- SMS history must **not** be stored by the application.
- Guardian phone receives SMS.
- WhatsApp number is contact information only; the SMS gateway does not send WhatsApp messages.
- Monthly fee SMS template should include student, month, paid amount, and “Photon Physics Academy”.
- Never include a receipt number in monthly fee SMS.
- After an admin verifies a store payment, the payment-confirmed SMS may be sent according to the order flow above.
- SMS Management should show gateway balance and a login link to https://portal.sms.net.bd/login/.
- The user previously confirmed SMS sending worked after setting the app origin. Verify current configuration before changing it.

## 8. Database and environment notes

- Hosting: Namecheap cPanel WordPress on the main domain.
- Database engine: MySQL/MariaDB via WordPress.
- phpMyAdmin has been used for SQL backup/export.
- The user previously resolved DB access by assigning the DB user to the database with privileges.
- A full SQL backup was exported previously; ask the user to verify a fresh backup before any schema/data change.
- Do not repeat database credentials in chat, code comments, docs, or commits.
- No direct cPanel database connection is available through this GitHub workflow. A plugin can use WordPress `$wpdb` only after it is installed and activated on the site.
- Never claim a live database change was made unless it was actually executed and verified on the live WordPress site.

## 9. 13-batch restoration work — current status and caution

A batch importer was implemented on `wordpress-v2-restoration` to restore 13 legacy batch definitions and show them in Academy Admin.

The 13 definitions are:

1. Class 10, boys, Sat · Mon · Wed, 2:30 PM
2. Class 10, boys, Sat · Mon · Wed, 9:00 AM
3. Class 10, girls, Sat · Mon · Wed, 8:00 AM
4. Class 10, girls, Sun · Tue · Thu, 5:30 PM
5. Class 8, science, Sun · Tue · Thu, 6:30 PM
6. Class 9, boys, Sat · Mon · Wed, 11:00 AM
7. Class 9, boys, Sat · Mon · Wed, 3:30 PM
8. Class 9, boys, Sun · Tue · Thu, 8:00 AM
9. Class 9, girls, Sat · Mon · Wed, 4:30 PM
10. Class 9, girls, Sat · Mon · Wed, 5:30 PM
11. Class 9, girls, Sun · Tue · Thu, 9:00 AM
12. Class 9, girls, Sat · Mon · Wed, 12:30 PM
13. Class 9, girls, Sat · Mon · Wed, 10:00 AM

Source values previously reported: capacity 35, enrolled count 0, instructor “Rafiqul Islam (Jewel Sir)”, Active status, room blank. Do not add room numbers.

Implementation notes:
- Main importer file: `includes/batch-import.php`
- Main plugin file includes the importer.
- It creates/uses legacy `ppa_batch` CPT posts, sets batch metadata, checks for existing matches using a fingerprint, attempts to attach orphan MySQL rows, and calls `ppa_db_sync_record()`.
- Import action is intended to be admin-only with a nonce and `manage_options`.
- The action panel is placed in Academy Admin → Programs & Batches.
- The importer avoids rerunning schema installation as part of the import.
- **Not yet verified against the live WordPress database.** Do not click/run the import until a fresh backup exists and the importer has been reviewed against the actual current plugin/database code. Verify idempotency and that no existing student/payment/result records can be modified.

## 10. Latest generated installable packages

The latest package artifacts were downloaded and inspected on 2026-11-11:

- Theme ZIP: `photon-physics-academy-theme.zip`
  - Generated by successful theme packaging workflow run `42819330664`.
  - Artifact ID: `13765719080`.
  - Source branch SHA: `8260d71e62c07da1c20bffc18128fc7cb4a9831b`.
  - ZIP integrity test passed; contains `style.css` and the theme directory `photon-physics-academy/`.
- Plugin ZIP: `ppa-academy-core-plugin.zip`
  - Generated by successful plugin packaging workflow run `42819778611`.
  - Artifact ID: `13765819880`.
  - Source branch SHA: `838e1b17eb7852bbb3bb2ca889414d3059f9ad7a`.
  - ZIP integrity test passed; contains `ppa-academy-core/`, `ppa-academy-core.php`, and include files including `batch-import.php`.
  - Plugin PHP syntax validation passed in CI.
- The theme and plugin packages were separate downloads. They have not been installed on the live site by this handover.
- No production deployment, database import, or live-site verification occurred as part of package generation.

Current branch tip at handover:
`838e1b17eb7852bbb3bb2ca889414d3059f9ad7a`

Previous theme package source commit:
`8260d71e62c07da1c20bffc18128fc7cb4a9831b`

## 11. Suggested next steps for the next AI

1. Read this handover and inspect the current branch, especially the theme, plugin, importer, and packaging workflows.
2. Compare the WordPress output with the original React main-branch design and the user's screenshots/feedback where available.
3. Identify any mismatch or incomplete feature before making broad changes.
4. Review the importer and database sync logic; add tests or safe dry-run/preview capability before asking the user to run it.
5. Verify plugin activation and theme compatibility on a staging/backup environment first, if available. The user previously said they could not create a subdomain; do not assume staging exists.
6. Preserve all current MySQL data. Back up before any database operation.
7. Package theme and plugin separately, validate ZIP contents and PHP syntax, and provide clear install steps.
8. Keep changes on `wordpress-v2-restoration`; request explicit permission before production deployment or merging into `main`.

## 12. Important communication rules

- Be honest about what is implemented, built, tested, installed, and deployed. These are different states.
- Never say “done” for a live-site feature based only on code or a successful ZIP build.
- Do not ask the user to repeat details already documented here unless the current code/site contradicts them.
- When giving cPanel/WordPress instructions, use short numbered steps and Bangla if the user asks.
- Ask one focused clarification only when a decision blocks safe progress.
- Keep secrets out of GitHub. This handover intentionally omits passwords, API keys, and database credentials.
