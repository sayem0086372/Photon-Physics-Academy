-- Photon Physics Academy v1.3.2: URL-only homepage slideshow configuration.
-- Stores URL and presentation metadata only. No image bytes are uploaded or stored in Supabase.
-- Apply after the store core/security/coupon migrations and after store_guest_cod_v1_3_1.sql.

begin;

create table if not exists public.homepage_slideshow_images (
  id uuid primary key default gen_random_uuid(),
  image_url text not null check (
    length(image_url) <= 2048 and image_url ~* '^https?://[^[:space:]]+$'
  ),
  title text check (title is null or length(title) <= 160),
  caption text check (caption is null or length(caption) <= 500),
  display_order integer not null default 0 check (display_order >= 0),
  is_active boolean not null default true,
  created_by uuid references auth.users(id) on delete set null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);

create index if not exists homepage_slideshow_active_order_idx
  on public.homepage_slideshow_images (is_active, display_order, created_at);

alter table public.homepage_slideshow_images enable row level security;

drop policy if exists "Public can read active homepage slideshow URLs" on public.homepage_slideshow_images;
drop policy if exists "Store admins can read all homepage slideshow URLs" on public.homepage_slideshow_images;
drop policy if exists "Store admins can insert homepage slideshow URLs" on public.homepage_slideshow_images;
drop policy if exists "Store admins can update homepage slideshow URLs" on public.homepage_slideshow_images;
drop policy if exists "Store admins can delete homepage slideshow URLs" on public.homepage_slideshow_images;

create policy "Public can read active homepage slideshow URLs"
  on public.homepage_slideshow_images
  for select to anon, authenticated
  using (is_active = true);

create policy "Store admins can read all homepage slideshow URLs"
  on public.homepage_slideshow_images
  for select to authenticated
  using ((select public.is_store_admin()));

create policy "Store admins can insert homepage slideshow URLs"
  on public.homepage_slideshow_images
  for insert to authenticated
  with check ((select public.is_store_admin()) and created_by = (select auth.uid()));

create policy "Store admins can update homepage slideshow URLs"
  on public.homepage_slideshow_images
  for update to authenticated
  using ((select public.is_store_admin()))
  with check ((select public.is_store_admin()));

create policy "Store admins can delete homepage slideshow URLs"
  on public.homepage_slideshow_images
  for delete to authenticated
  using ((select public.is_store_admin()));

grant select on public.homepage_slideshow_images to anon, authenticated;
grant insert, update, delete on public.homepage_slideshow_images to authenticated;

commit;
