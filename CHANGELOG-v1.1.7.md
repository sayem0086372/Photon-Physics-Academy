# Photon Physics Academy — v1.1.7 (review build)

## Payment receipts and transaction history
- Each accepted collection is recorded as an individual `payment_transactions` row with its own server-generated receipt number.
- The existing `fee_payments` row remains the monthly summary; its existing receipt number is not overwritten.
- Payment recording now calls the `record_student_fee_payment` RPC for atomic monthly balance updates.
- A stable request UUID is retained across retries of the same form submission to prevent duplicate collections.
- Payments view displays individual transaction amounts and uses the latest transaction per student/month for monthly due/payable summaries.
- Added a review-only Supabase migration at `src/db/migrations/v1.1.7-payment-transactions.sql`.

## Important deployment order
1. Confirm the live website uses the Supabase project targeted by the migration.
2. Review and apply the migration manually in Supabase SQL Editor only after approval.
3. Deploy this frontend only after the migration succeeds.

This package has not been connected to the live site and its production build has not been verified in this environment.
