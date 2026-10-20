# Photon Physics Academy — Mymensingh (Step 1 Static Frontend)

> **Feel the Power of Physics...**  
> **পদার্থবিজ্ঞানের ধারণা হোক সহজ ও আনন্দময়**

A professional, production-grade static website for **Photon Physics Academy**, located in Mymensingh, Bangladesh. Built with React 19, TypeScript, Vite, and Tailwind CSS.

---

## 🌟 Key Features

1. **Brand Identity & Bengali Localization**:
   - Bilingual support (Bangla & English) using `Hind Siliguri` and `Plus Jakarta Sans`.
   - Physics-inspired visual identity in deep navy, white, and electric cyan accents.
   - Interactive photon wave-particle simulation demonstrating $E = h \cdot f$ in real-time.

2. **Programs & Learning Pathways**:
   - Proposed learning tracks for Class 9, Class 10, SSC Preparation, HSC Physics, Physics Olympiad, and Foundation Programs.
   - Clear disclosure of proposed pathways with direct inquiry links.

3. **Classrooms Gallery & Lightbox**:
   - High-resolution photographs showcasing classroom teaching, vector diagrams, and peer study.
   - Accessible lightbox with keyboard navigation (`ESC`, `ArrowLeft`, `ArrowRight`).

4. **Concept Books**:
   - Showcases verified concept books (Physical Quantities and Measurement, Gati, Bol).
   - Accurate attribution to **Rafiqul Islam (Jewel Sir)**.
   - Direct inquiry buttons without fake prices or fake ISBN numbers.

5. **Notice Board (Step 1 Static Architecture)**:
   - Filterable categories: ভর্তি বিজ্ঞপ্তি, পরীক্ষার সময়সূচি, ক্লাসের সময়সূচি, জরুরি ঘোষণা, ফলাফল প্রকাশ.
   - Demonstration notices clearly marked as samples.
   - Notice detail modal with mock routine download preview.
   - Ready for Step 2 database/API integration via `src/data/notices.ts`.

6. **Results & Verified Policy**:
   - Transparent commitment: *"ফলাফল ও অর্জনের তথ্য শীঘ্রই প্রকাশ করা হবে।"*
   - Honest layout without fabricated percentages, fake testimonials, or fictional rank holders.

7. **Admission & Contact**:
   - Click-to-call numbers: `+880 1312-364499` and `01722-003995`.
   - Direct WhatsApp integration with pre-filled inquiry text.
   - Bangladeshi phone number validation (`01XXXXXXXXX` / `+8801XXXXXXXXX`).
   - Honest Step 1 feedback informing users of static frontend status and offering instant 1-click WhatsApp delivery.

---

## 🚀 Running Locally

```bash
# Install dependencies
npm install

# Start development server
npm run dev
```

The application will be accessible at `http://localhost:3000`.

---

## 📦 Production Build

```bash
# Build static production bundle
npm run build

# Preview production build locally
npm run preview
```

The output files will be in the `dist/` directory, ready to deploy to any static host (Cloudflare Pages, Vercel, Netlify, GitHub Pages, or Firebase Hosting).

---

## 📁 Project Structure

```
├── index.html                 # HTML entry point with SEO & Google Fonts
├── metadata.json              # Applet metadata
├── src/
│   ├── assets/images/         # Academy and concept book photographs
│   ├── components/            # Reusable React components
│   │   ├── Navbar.tsx         # Top Bar adhering to Top Bar Contract
│   │   ├── Hero.tsx           # Hero section with wave simulator
│   │   ├── WaveSimulator.tsx  # Interactive canvas wave visualizer
│   │   ├── About.tsx          # Academy intro & philosophy
│   │   ├── Programs.tsx       # 6 learning pathway cards
│   │   ├── Gallery.tsx        # Classroom photo gallery
│   │   ├── LightboxModal.tsx  # Accessible image preview modal
│   │   ├── ConceptBooks.tsx   # Jewel Sir's concept books
│   │   ├── NoticeBoard.tsx    # Filterable notice board
│   │   ├── NoticeModal.tsx    # Full notice reading modal
│   │   ├── Results.tsx        # Honest verified results section
│   │   ├── Contact.tsx        # Form with BD phone validation & WhatsApp
│   │   └── Footer.tsx         # Academy footer with quick links
│   ├── data/                  # Decoupled mock & static datasets
│   │   ├── notices.ts         # Structured notices
│   │   ├── programs.ts        # Course pathways
│   │   ├── books.ts           # Concept books
│   │   └── gallery.ts         # Classroom photos
│   ├── types/                 # Shared TypeScript interfaces
│   ├── App.tsx                # Root application component
│   ├── index.css              # Global styles, fonts & physics grids
│   └── main.tsx               # React mount entry
```

---

## 🛠️ Stage 2 Admin Panel & Supabase Architecture

The application now includes a **Admin Panel** accessible via the **"অ্যাডমিন"** button in the header and footer (or via `#admin` URL hash).

### Admin Panel Sections:
1. **Dashboard Overview**: KPI cards for total enrolled students, monthly fee collections, outstanding dues, and active batches, plus quick action triggers.
2. **Student Management**: Full student directory (Class 9 & 10) with search, class filtering, fee status badges, and an "Add Student" registration modal.
3. **Monthly Fee & Payment Management**: Monthly ledger, payment collection modal (Cash, bKash, Nagad), receipt preview modal, and SMS notification format.
4. **Notice Management**: Create, edit, publish/draft, and delete notices synced with the public notice board.
5. **Programs & Batches**: Active Class 9 & Class 10 batch schedules, time slots, instructor assignments, and capacity tracking.
6. **Results Management**: Exam scoring, chapter assessments, total vs. obtained marks, and grade calculations.
7. **Settings & Supabase Hub**: Academy profile editor and Supabase integration guidance.

### Supabase Connection Readiness:
- **Client Configuration**: `src/lib/supabase.ts` (supports `VITE_SUPABASE_URL` and `VITE_SUPABASE_ANON_KEY` gracefully).
- **Database Schema**: `src/db/supabase-schema.sql` ready to run in Supabase SQL Editor. Includes tables:
  - `students`
  - `fee_payments`
  - `notices`
  - `batches`
  - `exam_results`
  - Row Level Security (RLS) policies.

## v1.0.9 — Fix RLS errors across all admin tables

If creating or editing students, payments, notices, batches, or exam results shows a `new row violates row-level security policy` error:

1. In Supabase Dashboard, open **Authentication → Users** and copy the UUID for the administrator account used to sign in.
2. Open `src/db/fix-v1.0.9-all-tables-rls.sql`. Replace every occurrence of `67270b87-8169-4b2e-aa41-cc397ba2bdcd` with that exact UUID if it differs.
3. Open **SQL Editor → New query**, paste the entire migration, and click **Run**.
4. Sign out of the website admin panel, sign back in, then test adding a student and a batch.

This migration resets the existing RLS policies on the five app tables. Public visitors retain read-only access to published notices and active batches; student, payment, and exam-result data plus all write operations are restricted to the configured admin. It does not make private tables publicly writable.

**Important:** Do not skip the UUID check. If the configured UUID is not the account actually signing in, every admin write will still be denied. Do not run the old v1.0.8 batches-only migration after this all-tables migration.

Build status: not verified in this environment; run `npm install` and `npm run build` before deploying.


## v1.1.0 — Batch schedule correction

For an existing Supabase deployment, run `src/db/update-v1.1.0-batch-times.sql` in the Supabase SQL Editor to split multi-time batch rows into separate batch records. Existing students remain attached to the first time in each previously combined row. Review the schedule mapping before applying if your current timetable differs.


## v1.1.1 — Real academy imagery

Replaced generated classroom imagery and book visuals with the real academy photos and concept-book promotional images supplied by the user. The existing page structure and Supabase integration were retained. See `CHANGELOG-v1.1.1.md` for the image mapping note.


## v1.1.3 SMS gateway and optional contacts

Before deploying the frontend, run `src/db/v1.1.3-sms-contacts.sql` in the Supabase SQL Editor. Deploy the Edge Function in `supabase/functions/sms-gateway` and configure `SMSBD_API_KEY`, `PPA_ADMIN_USER_ID`, and `APP_ORIGIN` as Supabase Edge Function secrets. Never put the SMS API key in Vite environment variables or frontend code. Rotate any API key previously shared in chat. The SMS screen sends manual/bulk SMS and does not persist message history.

WhatsApp numbers are optional contact details only; the sms.bd API described by the user sends SMS, not WhatsApp messages.


## v1.1.4 — Monthly fee confirmation SMS
After a full monthly payment is saved, the admin can edit and send an SMS confirming the student name, billing month/year, and Photon Physics Academy. Select the guardian and/or student as recipients. Sending is manual and requires confirmation; no SMS history is stored. This uses the existing `sms-gateway` Supabase Edge Function and its configured secrets.


## v1.1.6 payment/SMS fixes
See `CHANGELOG-v1.1.6.md`. Deploy the frontend ZIP to Netlify and redeploy the `sms-gateway` Supabase Edge Function from `supabase/functions/sms-gateway/index.ts`.


## Version 1.2.1 — Book Store foundation

This release adds an integrated responsive Book Store section and a proposed Supabase store migration. See `CHANGELOG-v1.2.1.md` and `STORE-V1.2.1-IMPLEMENTATION-AND-DEPLOYMENT.md` for included work, migration review steps, and known limitations. The store migration is not automatically applied and must be reviewed in staging before production. The current release is not yet the full production store: store admin CRUD, payment verification/refunds, SMS queue/OTP, customer order history, invoices/reports, and end-to-end security/concurrency tests remain outstanding.


## Version 1.3.0 — Store Coupons & Advanced Management

- Added Store Admin coupon campaigns (percentage/fixed amount, minimum basket, max cap, schedule, total and per-customer usage limits).
- Added server-side coupon preview and order checkout validation/redemption, with order discount snapshots and coupon audit events.
- Added Store Management reports with order summaries and CSV export. CSV cells are escaped against spreadsheet formula injection.
- Before deploying this source, review and apply `src/db/migrations/store_coupons_advanced_v1.sql` to the matching Supabase project. The migration preserves academy batches and existing store admin membership.


## Version 1.3.1 — Guest checkout & Cash on Delivery-only

- Customers can place store orders without creating an account. Checkout collects name, phone, full address, area/thana, and district/city.
- Cash on Delivery is the only enabled method; bKash and Nagad are shown as disabled “Upcoming” options.
- Store Admin can see customer contact/address details and itemized order contents.
- Review and apply `src/db/migrations/store_guest_cod_v1_3_1.sql` before deploying the matching frontend. The migration preserves existing orders, books, batches, and admin membership; it does not deploy the website.
- See `CHANGELOG-v1.3.1.md` for details.


## Version 1.3.2 — Store UX & classroom slideshow

- Added a successful Add to Cart confirmation popup with Continue Shopping and View Cart actions.
- Customer-facing payment labels use the full term **Cash on Delivery**. bKash and Nagad remain visible but unavailable as Upcoming options.
- Added an animated homepage slideshow titled “ক্লাসরুম শিখন পরিবেশ · ময়মনসিংহ”, with automatic transitions, manual controls, dots, and pause/play.
- Added Settings → Homepage Slideshow so Store Admins can add/edit image URLs, titles and captions, reorder, publish/hide, and remove URL entries. Supabase stores only URLs and metadata, not image files.
- Review and apply `src/db/migrations/homepage_slideshow_urls_v1_3_2.sql` after the v1.3.1 guest-checkout migration. See `DEPLOY-V1.3.2.md`.
- The package does not apply database migrations or deploy Netlify automatically.


## Deployment on Netlify and Vercel

See [DEPLOY-NETLIFY-VERCEL.md](./DEPLOY-NETLIFY-VERCEL.md). Both providers use `npm run build` and publish the Vite `dist/` directory.

## Deployment quick settings

This project is a React + TypeScript + Vite single-page application. The repository root is the project root.

| Host | Build command | Output directory | Notes |
| --- | --- | --- | --- |
| Cloudflare Pages | `npm run build` | `dist` | See [Cloudflare Pages deployment guide](DEPLOY-CLOUDFLARE-PAGES.md). Set Node.js 22. |
| Netlify | `npm install --no-audit --no-fund && npm run build` | `dist` | Configured by `netlify.toml`; includes SPA fallback. |
| Vercel | `npm run build` | `dist` | Configured by `vercel.json`; includes SPA rewrite. |

Configure `VITE_SUPABASE_URL` and `VITE_SUPABASE_ANON_KEY` (or the publishable key) in the host's environment settings when needed. These are browser-visible client values; never add service-role keys or other secrets to `VITE_*` variables.

