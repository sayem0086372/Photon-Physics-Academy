# v1.1.7 payment receipts — review and deployment guide

## What changes
- `fee_payments` remains the monthly summary table.
- `payment_transactions` stores each individual collection, amount, receipt, payment date/method, and the monthly balance after that collection.
- The database RPC `record_student_fee_payment` performs the monthly update and transaction insert atomically.
- Each new collection receives a server-generated unique receipt. Existing monthly summary receipt numbers are not overwritten.
- A stable request UUID makes retrying the same request idempotent.

## Do not apply until the live project is confirmed
The investigation previously reported zero students and zero fee-payment rows in the inspected Supabase project. Confirm that the project is the one used by the live Netlify website and compare expected record counts before applying any migration. The website has no connected GitHub repository, so this ZIP is a standalone source package; it is not automatically deployed.

## Safe order
1. In Supabase Dashboard, verify the project reference and admin UID against the live policies. The SQL draft currently uses admin UID `52b08ae8-28d6-4b1e-a205-9bc4c43cb7be` as reported by the connected investigation.
2. Run read-only preflight checks first: row counts, current fee-payment constraints, and current admin RLS policy UID. Do not paste private student data into a report.
3. Review `src/db/migrations/v1.1.7-payment-transactions.sql`. It is a proposal only and has not been executed.
4. Back up/export the database before applying any approved migration.
5. Apply the SQL only after explicit approval and confirmation that the frontend source matches the target project.
6. Deploy this ZIP only after the migration succeeds.
7. Test one student in a controlled test period: partial payment, another partial payment, exact final payment, overpayment rejection, retry with same request UUID, and receipt uniqueness. Verify the monthly summary and transaction rows after each step.

## Legacy records
For any existing monthly row with `paid_amount > 0`, the migration creates one legacy transaction using that row's existing receipt and cumulative amount. It cannot reconstruct separate historic partial collections if the previous database stored only a monthly total. It does not update existing student IDs or monthly receipt numbers.

## Rollback caution
Do not drop `payment_transactions` after real payments have been recorded; doing so destroys individual receipt history. A rollback should disable the frontend RPC usage and restore a reviewed backup/forward-fix plan rather than deleting the transaction ledger.

## Verification status
This package has not been built or tested against a live Supabase instance in this environment. SQL has not been executed.
