# Photon Physics Academy v1.3.7 — All-display responsiveness

## Responsive improvements
- Added a global responsive safety layer to reduce accidental horizontal page overflow on phones and narrow viewports.
- Added safe wrapping for long URLs, order identifiers, customer-provided text, and code blocks.
- Improved media sizing, form-control width handling, touch interaction, and horizontal table scrolling.
- Set mobile form controls to a 16px font size to reduce unwanted auto-zoom in iOS Safari.
- Made dialogs respect the visible mobile viewport height and scroll internally when content is taller than the screen.
- Added reduced-motion support and adjusted decorative background patterns on very narrow displays.
- Confirmed the document includes the responsive viewport meta tag.

## Compatibility and preservation
- No Supabase schema or data migrations are included.
- No Private Coaching Batch data is changed by this frontend package.
- Existing store payment, SMS confirmation, and checkout logic are carried forward from v1.3.6.

## Validation status
- Source-level responsiveness safeguards added.
- ZIP integrity should be checked after packaging.
- Production build and real-browser/device viewport testing could not be completed in this environment because `npm install` timed out. Do not treat this release as fully device-certified until the included test checklist has been completed.
