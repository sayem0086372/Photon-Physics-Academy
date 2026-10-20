-- ============================================================================
-- Photon Physics Academy v1.1.7 — PAYMENT TRANSACTIONS / UNIQUE RECEIPTS
-- REVIEW-ONLY MIGRATION. DO NOT RUN until the live Supabase project and data
-- have been verified and this SQL has been explicitly approved.
--
-- Verified admin UID supplied by the connected Supabase investigation:
--   52b08ae8-28d6-4b1e-a205-9bc4c43cb7be
-- Verify it against the live RLS policies before running.
--
-- This migration keeps public.fee_payments as one monthly summary row and adds
-- public.payment_transactions as an immutable record of each collection.
-- Existing monthly receipt numbers are preserved. Existing paid monthly rows
-- are backfilled as one legacy transaction because older partial collections
-- cannot be reconstructed from the monthly summary alone.
-- ============================================================================

BEGIN;

-- 1. One row for each actual collection. Snapshots preserve history if a
-- student is later removed; student_id and fee_payment_id become NULL instead.
CREATE TABLE IF NOT EXISTS public.payment_transactions (
  id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  request_id UUID NOT NULL UNIQUE,
  fee_payment_id UUID REFERENCES public.fee_payments(id) ON DELETE SET NULL,
  student_id UUID REFERENCES public.students(id) ON DELETE SET NULL,
  student_name VARCHAR(150) NOT NULL,
  student_id_code VARCHAR(30) NOT NULL,
  class_level VARCHAR(20) NOT NULL,
  billing_month VARCHAR(20) NOT NULL,
  billing_year INTEGER NOT NULL,
  amount NUMERIC(10,2) NOT NULL CHECK (amount > 0),
  payable_amount NUMERIC(10,2) NOT NULL CHECK (payable_amount >= 0),
  monthly_paid_amount NUMERIC(10,2) NOT NULL CHECK (monthly_paid_amount >= amount),
  due_amount_after NUMERIC(10,2) NOT NULL CHECK (due_amount_after >= 0),
  payment_status VARCHAR(20) NOT NULL CHECK (payment_status IN ('Paid','Partial','Due')),
  payment_method VARCHAR(50) NOT NULL,
  payment_date DATE NOT NULL,
  receipt_number VARCHAR(50) NOT NULL UNIQUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_payment_transactions_student_month
  ON public.payment_transactions(student_id, billing_year, billing_month, created_at DESC);
CREATE INDEX IF NOT EXISTS idx_payment_transactions_created_at
  ON public.payment_transactions(created_at DESC);

ALTER TABLE public.payment_transactions ENABLE ROW LEVEL SECURITY;

DROP POLICY IF EXISTS "Academy admin reads payment transactions" ON public.payment_transactions;
CREATE POLICY "Academy admin reads payment transactions"
ON public.payment_transactions FOR SELECT TO authenticated
USING (auth.uid() = '52b08ae8-28d6-4b1e-a205-9bc4c43cb7be'::uuid);

REVOKE ALL ON TABLE public.payment_transactions FROM PUBLIC, anon, authenticated;
GRANT SELECT ON TABLE public.payment_transactions TO authenticated;

-- 2. Backfill each already-paid monthly summary as one legacy transaction.
-- This preserves its existing receipt number and totals; old partial-payment
-- events cannot be recovered if the old schema only kept cumulative totals.
INSERT INTO public.payment_transactions (
  request_id, fee_payment_id, student_id, student_name, student_id_code,
  class_level, billing_month, billing_year, amount, payable_amount,
  monthly_paid_amount, due_amount_after, payment_status, payment_method,
  payment_date, receipt_number, created_at
)
SELECT
  gen_random_uuid(), fp.id, s.id, s.full_name, s.student_id_code,
  s.class_level, fp.billing_month, fp.billing_year, fp.paid_amount,
  fp.payable_amount, fp.paid_amount, fp.due_amount, fp.payment_status,
  fp.payment_method, fp.payment_date, fp.receipt_number, fp.created_at
FROM public.fee_payments fp
JOIN public.students s ON s.id = fp.student_id
WHERE fp.paid_amount > 0
  AND NOT EXISTS (
    SELECT 1 FROM public.payment_transactions pt
    WHERE pt.fee_payment_id = fp.id
  );

-- 3. Atomic, admin-only payment function. A request UUID may be processed only
-- once; concurrent calls for the same student are serialized by row lock.
CREATE OR REPLACE FUNCTION public.record_student_fee_payment(
  p_student_id UUID,
  p_billing_month VARCHAR,
  p_billing_year INTEGER,
  p_payment_amount NUMERIC,
  p_payment_method VARCHAR,
  p_payment_date DATE,
  p_request_id UUID
)
RETURNS JSONB
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = ''
AS $$
DECLARE
  v_student public.students%ROWTYPE;
  v_fee public.fee_payments%ROWTYPE;
  v_existing public.payment_transactions%ROWTYPE;
  v_tx public.payment_transactions%ROWTYPE;
  v_receipt VARCHAR(50);
  v_payable NUMERIC(10,2);
  v_paid NUMERIC(10,2);
  v_due NUMERIC(10,2);
  v_status VARCHAR(20);
  v_fee_exists BOOLEAN := false;
BEGIN
  IF auth.uid() IS DISTINCT FROM '52b08ae8-28d6-4b1e-a205-9bc4c43cb7be'::uuid THEN
    RAISE EXCEPTION 'Not authorized to record fee payments' USING ERRCODE = '42501';
  END IF;

  IF p_request_id IS NULL OR p_student_id IS NULL OR p_payment_date IS NULL
     OR p_billing_month IS NULL OR btrim(p_billing_month) = '' THEN
    RAISE EXCEPTION 'Required payment fields are missing';
  END IF;
  IF p_billing_month NOT IN ('January','February','March','April','May','June',
                              'July','August','September','October','November','December') THEN
    RAISE EXCEPTION 'Invalid billing month';
  END IF;
  IF p_billing_year < 2000 OR p_billing_year > 2200 THEN
    RAISE EXCEPTION 'Invalid billing year';
  END IF;
  IF p_payment_amount IS NULL OR p_payment_amount <= 0
     OR p_payment_amount <> round(p_payment_amount, 2) THEN
    RAISE EXCEPTION 'Payment amount must be positive with at most two decimals';
  END IF;
  IF p_payment_method NOT IN ('Cash','bKash','Nagad') THEN
    RAISE EXCEPTION 'Unsupported payment method';
  END IF;

  -- Serialize duplicate retries before checking the unique request ID.
  PERFORM pg_advisory_xact_lock(
    hashtextextended('ppa-payment-request-' || p_request_id::text, 0)
  );

  SELECT * INTO v_existing
  FROM public.payment_transactions
  WHERE request_id = p_request_id;

  IF FOUND THEN
    IF v_existing.student_id IS DISTINCT FROM p_student_id
       OR v_existing.billing_month <> p_billing_month
       OR v_existing.billing_year <> p_billing_year
       OR v_existing.amount <> p_payment_amount
       OR v_existing.payment_method <> p_payment_method
       OR v_existing.payment_date <> p_payment_date THEN
      RAISE EXCEPTION 'Request ID was already used with different payment details';
    END IF;
    RETURN jsonb_build_object(
      'success', true, 'duplicate_request', true,
      'transaction_id', v_existing.id,
      'fee_payment_id', v_existing.fee_payment_id,
      'receipt_number', v_existing.receipt_number,
      'amount', v_existing.amount,
      'monthly_paid_amount', v_existing.monthly_paid_amount,
      'due_amount', v_existing.due_amount_after,
      'payment_status', v_existing.payment_status
    );
  END IF;

  -- This row lock serializes monthly fee changes for this student.
  SELECT * INTO v_student
  FROM public.students
  WHERE id = p_student_id
  FOR UPDATE;
  IF NOT FOUND THEN RAISE EXCEPTION 'Student not found'; END IF;

  SELECT * INTO v_fee
  FROM public.fee_payments
  WHERE student_id = p_student_id
    AND billing_month = p_billing_month
    AND billing_year = p_billing_year
  FOR UPDATE;
  v_fee_exists := FOUND;

  -- Generate a receipt for this collection, never from a row count.
  v_receipt := 'RCP-' || p_billing_year::text || '-' ||
    lpad((array_position(ARRAY['January','February','March','April','May','June',
      'July','August','September','October','November','December'], p_billing_month))::text, 2, '0') || '-' ||
    upper(replace(gen_random_uuid()::text, '-', ''));

  IF NOT v_fee_exists THEN
    v_payable := COALESCE(v_student.monthly_fee_bdt, 0);
    IF v_payable <= 0 THEN RAISE EXCEPTION 'Student monthly fee must be greater than zero'; END IF;
    INSERT INTO public.fee_payments (
      student_id, billing_month, billing_year, payable_amount, paid_amount,
      due_amount, payment_status, payment_method, receipt_number, payment_date
    ) VALUES (
      p_student_id, p_billing_month, p_billing_year, v_payable, 0,
      v_payable, 'Due', p_payment_method, v_receipt, p_payment_date
    ) RETURNING * INTO v_fee;
  ELSE
    v_payable := v_fee.payable_amount;
  END IF;

  IF v_fee.paid_amount >= v_payable OR v_fee.due_amount <= 0 THEN
    RAISE EXCEPTION 'This billing period is already fully paid';
  END IF;
  IF p_payment_amount > v_fee.due_amount THEN
    RAISE EXCEPTION 'Payment exceeds the remaining amount due';
  END IF;

  v_paid := v_fee.paid_amount + p_payment_amount;
  v_due := v_payable - v_paid;
  v_status := CASE WHEN v_due = 0 THEN 'Paid'
                   WHEN v_paid > 0 THEN 'Partial' ELSE 'Due' END;

  UPDATE public.fee_payments
  SET payable_amount = v_payable,
      paid_amount = v_paid,
      due_amount = v_due,
      payment_status = v_status,
      payment_method = p_payment_method,
      payment_date = p_payment_date
      -- Do not overwrite receipt_number on an existing monthly summary.
  WHERE id = v_fee.id;

  INSERT INTO public.payment_transactions (
    request_id, fee_payment_id, student_id, student_name, student_id_code,
    class_level, billing_month, billing_year, amount, payable_amount,
    monthly_paid_amount, due_amount_after, payment_status, payment_method,
    payment_date, receipt_number
  ) VALUES (
    p_request_id, v_fee.id, v_student.id, v_student.full_name,
    v_student.student_id_code, v_student.class_level,
    p_billing_month, p_billing_year, p_payment_amount, v_payable,
    v_paid, v_due, v_status, p_payment_method, p_payment_date, v_receipt
  ) RETURNING * INTO v_tx;

  RETURN jsonb_build_object(
    'success', true, 'duplicate_request', false,
    'transaction_id', v_tx.id,
    'fee_payment_id', v_fee.id,
    'receipt_number', v_tx.receipt_number,
    'amount', v_tx.amount,
    'monthly_paid_amount', v_tx.monthly_paid_amount,
    'due_amount', v_tx.due_amount_after,
    'payment_status', v_tx.payment_status
  );
END;
$$;

REVOKE ALL ON FUNCTION public.record_student_fee_payment(
  UUID, VARCHAR, INTEGER, NUMERIC, VARCHAR, DATE, UUID
) FROM PUBLIC, anon, authenticated;
GRANT EXECUTE ON FUNCTION public.record_student_fee_payment(
  UUID, VARCHAR, INTEGER, NUMERIC, VARCHAR, DATE, UUID
) TO authenticated;

-- Prevent authenticated clients from bypassing transaction history by writing
-- directly to the monthly summary. SELECT remains available under existing RLS.
REVOKE INSERT, UPDATE, DELETE ON TABLE public.fee_payments FROM PUBLIC, anon, authenticated;

COMMIT;

-- After manual approval/application, verify with read-only queries:
-- SELECT count(*) FROM public.students;
-- SELECT count(*) FROM public.fee_payments;
-- SELECT count(*) FROM public.payment_transactions;
-- SELECT student_id, billing_month, billing_year, count(*)
-- FROM public.fee_payments GROUP BY 1,2,3 HAVING count(*) > 1;
-- SELECT receipt_number, count(*) FROM public.payment_transactions
-- GROUP BY receipt_number HAVING count(*) > 1;
