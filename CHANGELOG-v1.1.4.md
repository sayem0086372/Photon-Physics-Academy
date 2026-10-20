# Photon Physics Academy v1.1.4

## Monthly fee payment confirmation SMS
- After a monthly fee is successfully recorded as fully paid, a **Send Message** popup opens.
- Provides an editable confirmation template containing the student's name, billing month/year, and Photon Physics Academy name.
- Lets the admin choose guardian and/or student phone numbers. Guardian is preselected when a guardian number exists.
- SMS is sent only after the admin presses **Send SMS** and confirms; it uses the existing Supabase `sms-gateway` Edge Function.
- No sent-message history is stored. Partial or due payments do not trigger this paid-in-full confirmation popup.

## Verification status
- Source ZIP integrity checked. A production build has not been confirmed in this environment.
