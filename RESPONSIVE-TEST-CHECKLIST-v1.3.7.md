# v1.3.7 Responsive QA checklist

## Viewports to test
- 320 × 568 (very narrow phone)
- 360 × 800 (compact Android)
- 375 × 812 and 390 × 844 (common phones)
- 430 × 932 (large phone)
- 768 × 1024 (tablet portrait)
- 1024 × 768 (tablet / small laptop)
- 1280 × 800 and 1440 × 900 (desktop)
- 1920 × 1080 (large desktop)

## Pages and flows
- Homepage: navbar, hero, programs, gallery, notices, results, contact, footer.
- Mobile nav: open/close, every section link, call and admission actions.
- Store: catalog grid, search/category filters, product detail, cart, checkout, order confirmation.
- Checkout: long Bangla/English text, form fields, coupon row, COD, bKash/Nagad instructions and transaction ID fields.
- Admin: login, sidebar/navigation, dashboard cards, students/results/payment tables, store orders and products.
- Payment verification: SMS confirmation dialog, long customer names/numbers, long message, send and skip actions.
- Keyboard navigation, visible focus, reduced motion, landscape orientation, and browser zoom.

## Pass criteria
- No page-level horizontal scrolling at any target width (intentional table scrollers are allowed).
- No clipped labels, overlapping controls, hidden action buttons, or inaccessible dialog buttons.
- Forms remain usable with the on-screen keyboard open.
- Tables can be scrolled horizontally within their own panels.
- All key buttons have comfortable touch targets and clear focus states.
- Checkout and SMS flows still work after responsive styling.

## Current status
The release adds global CSS safeguards and was reviewed at source level. A production build and actual browser/device screenshots were not available because dependency installation timed out. Complete this checklist on a staging deployment before describing the site as fully verified across all displays.
