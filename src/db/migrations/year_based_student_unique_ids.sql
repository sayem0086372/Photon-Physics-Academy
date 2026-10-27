-- Year-based student IDs: 260001, 260002, ... then 270001 in 2027.
-- Existing student IDs are preserved. Sequence counters are retained even when students are deleted.
CREATE TABLE IF NOT EXISTS public.student_id_sequences (
  admission_year SMALLINT PRIMARY KEY,
  last_number INTEGER NOT NULL DEFAULT 0 CHECK (last_number BETWEEN 0 AND 9999),
  updated_at TIMESTAMPTZ NOT NULL DEFAULT now()
);

ALTER TABLE public.student_id_sequences ENABLE ROW LEVEL SECURITY;

-- Seed counters from any existing six-digit YYNNNN IDs so those numbers are not reused.
INSERT INTO public.student_id_sequences (admission_year, last_number)
SELECT LEFT(student_id_code, 2)::SMALLINT, MAX(RIGHT(student_id_code, 4)::INTEGER)
FROM public.students
WHERE student_id_code ~ '^[0-9]{6}$'
GROUP BY LEFT(student_id_code, 2)
ON CONFLICT (admission_year) DO UPDATE
SET last_number = GREATEST(public.student_id_sequences.last_number, EXCLUDED.last_number),
    updated_at = now();

CREATE OR REPLACE FUNCTION public.assign_student_unique_id()
RETURNS TRIGGER
LANGUAGE plpgsql
SECURITY DEFINER
SET search_path = public
AS $$
DECLARE
  admission_year SMALLINT := (EXTRACT(YEAR FROM CURRENT_DATE)::INTEGER % 100)::SMALLINT;
  next_number INTEGER;
BEGIN
  INSERT INTO public.student_id_sequences (admission_year, last_number)
  VALUES (admission_year, 1)
  ON CONFLICT (admission_year) DO UPDATE
    SET last_number = public.student_id_sequences.last_number + 1,
        updated_at = now()
  RETURNING last_number INTO next_number;

  IF next_number > 9999 THEN
    RAISE EXCEPTION 'Student ID sequence exhausted for admission year %', admission_year;
  END IF;

  NEW.student_id_code := LPAD(admission_year::TEXT, 2, '0') || LPAD(next_number::TEXT, 4, '0');
  RETURN NEW;
END;
$$;

DROP TRIGGER IF EXISTS students_assign_unique_id ON public.students;
CREATE TRIGGER students_assign_unique_id
BEFORE INSERT ON public.students
FOR EACH ROW
EXECUTE FUNCTION public.assign_student_unique_id();

COMMENT ON TABLE public.student_id_sequences IS
  'Persistent per-admission-year counters for six-digit student IDs; keep rows when students are deleted.';
