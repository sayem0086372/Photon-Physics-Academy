-- Photon Physics Academy v1.1.3: optional contact numbers for SMS/WhatsApp
-- Run in Supabase SQL Editor before deploying the new frontend.
BEGIN;
ALTER TABLE public.students ALTER COLUMN student_phone DROP NOT NULL;
ALTER TABLE public.students ALTER COLUMN guardian_phone DROP NOT NULL;
ALTER TABLE public.students ADD COLUMN IF NOT EXISTS whatsapp_phone VARCHAR(20);
COMMIT;
