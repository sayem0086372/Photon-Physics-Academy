# Photon Physics Academy v1.1.3

## SMS gateway and contact fields
- Added an SMS Management page for manual and bulk SMS through the sms.bd API.
- Added guardian and student recipient selection, class/batch filters, duplicate-number de-duplication, and confirmation before sending. Guardian numbers are selected by default.
- Added Settings with a gateway balance check. API credentials must be configured as Supabase Edge Function secrets.
- No sent-message history is stored by this app. Gateway provider records may still exist on sms.bd.
- Student mobile and guardian phone are optional. Added optional WhatsApp contact number. WhatsApp is stored as a contact field only; sms.bd API sends SMS, not WhatsApp.
- Added SQL migration `src/db/v1.1.3-sms-contacts.sql` for existing databases.

## Deployment requirements
1. Run `src/db/v1.1.3-sms-contacts.sql` in Supabase SQL Editor.
2. Deploy `supabase/functions/sms-gateway` with the Supabase CLI.
3. Set `SMSBD_API_KEY`, `PPA_ADMIN_USER_ID`, and `APP_ORIGIN` as Supabase Function secrets. Rotate any API key previously shared in chat before configuring the new secret.
4. Verify the function using the Settings balance check and then send a test SMS to an authorized test number.

## Verification status
ZIP integrity can be checked, but a production build was not confirmed because dependency installation timed out in this environment.
