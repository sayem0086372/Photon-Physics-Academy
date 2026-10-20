# Photon Physics Academy v1.1.6

## Fixes
- Prevents rapid repeated taps on Confirm Payment from submitting multiple times; shows a saving state.
- Handles the existing one-record-per-student/month fee ledger by applying additional collections to the existing monthly record instead of blindly inserting a duplicate. Fully paid months are rejected with a clear message.
- Preserves the original receipt number when updating a monthly ledger record and generates much less collision-prone receipt numbers for new records.
- Keeps the in-memory payment list in sync when a monthly record is updated instead of showing duplicate rows.
- Improves sms.bd balance response normalization and displays more helpful gateway errors in Settings.
- SMS credentials remain server-side in Supabase Edge Function secrets; no SMS history is written by the app.

## Deployment note
Redeploy the web app and separately redeploy `supabase/functions/sms-gateway` to apply the SMS balance fix. Confirm Supabase secrets `SMSBD_API_KEY`, `PPA_ADMIN_USER_ID`, `SUPABASE_URL`, `SUPABASE_ANON_KEY`, and `APP_ORIGIN` are correctly set. This archive has not been confirmed against your live Supabase schema or live SMS provider.
