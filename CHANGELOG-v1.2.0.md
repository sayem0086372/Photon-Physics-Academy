# Photon Physics Academy v1.2.0

## Added
- Added an SMS Balance card directly in SMS Management, with a Check/Refresh Balance button that uses the existing authenticated `sms-gateway` balance action.
- Added a link to the official SMS gateway portal: https://portal.sms.net.bd/login/

## Preserved
- Kept the existing Settings balance checker for compatibility.
- Kept the SMS send flow, student/batch filters, recipient selection, and no-SMS-history behavior unchanged.
- Kept receipt numbers out of the payment-confirmation SMS template; receipts remain available in website payment records.
- No database schema, records, RLS policies, Edge Function deployment, or secrets were changed by this release.

## Verification
- Source changes and ZIP structure checked.
- Production build should be run before deployment; no successful build is claimed unless verified separately.
