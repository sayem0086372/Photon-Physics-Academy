-- Photon Physics Academy v1.2.1 online store — REVIEW PROPOSAL ONLY.
-- DO NOT RUN on production until the existing schema, admin identity, grants,
-- storage policies, and deployment plan have been reviewed and approved.
-- This migration adds new store tables only; it does not modify/delete academy data.

begin;
create extension if not exists pgcrypto;

create table if not exists public.store_admins (
  user_id uuid primary key references auth.users(id) on delete restrict,
  created_at timestamptz not null default now(),
  created_by uuid references auth.users(id) on delete set null
);
-- Intentionally no client write policy on store_admins. Populate only after
-- independently verifying the administrator in Supabase Auth dashboard.
alter table public.store_admins enable row level security;
revoke all on public.store_admins from public, anon, authenticated;

create or replace function public.is_store_admin()
returns boolean language sql stable security definer
set search_path = public, auth
as $$ select exists (select 1 from public.store_admins a where a.user_id = auth.uid()) $$;
revoke all on function public.is_store_admin() from public;
grant execute on function public.is_store_admin() to anon, authenticated;

create table if not exists public.store_books (
  id uuid primary key default gen_random_uuid(),
  title text not null check (length(trim(title)) between 1 and 240),
  author text,
  description text,
  category text,
  cover_image_url text,
  price_bdt numeric(12,2) not null check (price_bdt >= 0),
  discount_percent numeric(5,2) not null default 0 check (discount_percent between 0 and 100),
  stock_quantity integer not null default 0 check (stock_quantity >= 0),
  low_stock_threshold integer not null default 5 check (low_stock_threshold >= 0),
  is_active boolean not null default false,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create index if not exists store_books_active_title_idx on public.store_books (is_active, title);
create index if not exists store_books_category_idx on public.store_books (category) where is_active;

create table if not exists public.store_delivery_settings (
  id smallint primary key default 1 check (id = 1),
  default_delivery_fee_bdt numeric(12,2) not null default 0 check (default_delivery_fee_bdt >= 0),
  free_delivery_threshold_bdt numeric(12,2) check (free_delivery_threshold_bdt is null or free_delivery_threshold_bdt >= 0),
  bkash_instructions text,
  nagad_instructions text,
  updated_at timestamptz not null default now()
);
insert into public.store_delivery_settings (id) values (1) on conflict (id) do nothing;

create table if not exists public.store_addresses (
  id uuid primary key default gen_random_uuid(),
  user_id uuid not null references auth.users(id) on delete restrict,
  recipient_name text not null,
  phone text not null,
  address_line text not null,
  city text not null,
  is_default boolean not null default false,
  created_at timestamptz not null default now()
);
create index if not exists store_addresses_user_idx on public.store_addresses(user_id, created_at desc);

create table if not exists public.store_orders (
  id uuid primary key default gen_random_uuid(),
  order_number text not null unique default ('PPA-' || upper(substr(replace(gen_random_uuid()::text, '-', ''), 1, 12))),
  user_id uuid not null references auth.users(id) on delete restrict,
  customer_name text not null,
  customer_email text,
  phone text not null,
  delivery_address text not null,
  city text not null,
  subtotal_bdt numeric(12,2) not null check (subtotal_bdt >= 0),
  delivery_fee_bdt numeric(12,2) not null check (delivery_fee_bdt >= 0),
  total_bdt numeric(12,2) not null check (total_bdt = subtotal_bdt + delivery_fee_bdt),
  payment_method text not null check (payment_method in ('cod','bkash','nagad')),
  payment_status text not null default 'pending' check (payment_status in ('pending','verified','rejected','refunded')),
  order_status text not null default 'placed' check (order_status in ('placed','confirmed','processing','shipped','delivered','cancelled','refunded')),
  transaction_reference text,
  payment_verified_by uuid references auth.users(id) on delete set null,
  payment_verified_at timestamptz,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now()
);
create index if not exists store_orders_user_created_idx on public.store_orders(user_id, created_at desc);
create index if not exists store_orders_status_created_idx on public.store_orders(order_status, created_at desc);
create index if not exists store_orders_payment_status_idx on public.store_orders(payment_status, created_at desc);

create table if not exists public.store_order_items (
  id uuid primary key default gen_random_uuid(),
  order_id uuid not null references public.store_orders(id) on delete restrict,
  book_id uuid references public.store_books(id) on delete set null,
  title_snapshot text not null,
  unit_price_bdt numeric(12,2) not null check (unit_price_bdt >= 0),
  quantity integer not null check (quantity between 1 and 100),
  line_total_bdt numeric(12,2) not null check (line_total_bdt = unit_price_bdt * quantity),
  created_at timestamptz not null default now()
);
create index if not exists store_order_items_order_idx on public.store_order_items(order_id);

create table if not exists public.store_inventory_movements (
  id uuid primary key default gen_random_uuid(),
  book_id uuid references public.store_books(id) on delete set null,
  order_id uuid references public.store_orders(id) on delete set null,
  change_quantity integer not null check (change_quantity <> 0),
  reason text not null check (reason in ('order','restock','correction','cancellation_return')),
  actor_user_id uuid references auth.users(id) on delete set null,
  note text,
  created_at timestamptz not null default now()
);
create index if not exists store_inventory_movements_book_idx on public.store_inventory_movements(book_id, created_at desc);

create table if not exists public.store_payment_events (
  id uuid primary key default gen_random_uuid(),
  order_id uuid not null references public.store_orders(id) on delete restrict,
  event_type text not null check (event_type in ('submitted','verified','rejected','refunded')),
  method text not null check (method in ('cod','bkash','nagad')),
  transaction_reference text,
  note text,
  actor_user_id uuid references auth.users(id) on delete set null,
  created_at timestamptz not null default now()
);
create index if not exists store_payment_events_order_idx on public.store_payment_events(order_id, created_at desc);

create table if not exists public.store_admin_audit (
  id uuid primary key default gen_random_uuid(),
  actor_user_id uuid references auth.users(id) on delete set null,
  action text not null,
  entity_type text not null,
  entity_id uuid,
  details jsonb not null default '{}'::jsonb,
  created_at timestamptz not null default now()
);
create index if not exists store_admin_audit_created_idx on public.store_admin_audit(created_at desc);

create table if not exists public.store_sms_logs (
  id uuid primary key default gen_random_uuid(),
  order_id uuid references public.store_orders(id) on delete set null,
  phone text not null,
  message_type text not null,
  attempt_number integer not null default 1 check (attempt_number > 0),
  status text not null default 'queued' check (status in ('queued','sent','failed')),
  provider_message_id text,
  error_summary text,
  idempotency_key text not null unique,
  created_at timestamptz not null default now(),
  sent_at timestamptz
);
create index if not exists store_sms_logs_status_created_idx on public.store_sms_logs(status, created_at);

alter table public.store_books enable row level security;
alter table public.store_delivery_settings enable row level security;
alter table public.store_addresses enable row level security;
alter table public.store_orders enable row level security;
alter table public.store_order_items enable row level security;
alter table public.store_inventory_movements enable row level security;
alter table public.store_payment_events enable row level security;
alter table public.store_admin_audit enable row level security;
alter table public.store_sms_logs enable row level security;

-- Public catalog read only. No public book edits.
create policy store_books_public_active_read on public.store_books for select to anon, authenticated using (is_active = true or public.is_store_admin());
create policy store_books_admin_write on public.store_books for all to authenticated using (public.is_store_admin()) with check (public.is_store_admin());
create policy store_delivery_public_read on public.store_delivery_settings for select to anon, authenticated using (true);
create policy store_delivery_admin_write on public.store_delivery_settings for all to authenticated using (public.is_store_admin()) with check (public.is_store_admin());
create policy store_addresses_owner_read on public.store_addresses for select to authenticated using (user_id = auth.uid() or public.is_store_admin());
create policy store_addresses_owner_insert on public.store_addresses for insert to authenticated with check (user_id = auth.uid());
create policy store_addresses_owner_update on public.store_addresses for update to authenticated using (user_id = auth.uid() or public.is_store_admin()) with check (user_id = auth.uid() or public.is_store_admin());
create policy store_orders_owner_read on public.store_orders for select to authenticated using (user_id = auth.uid() or public.is_store_admin());
create policy store_orders_admin_update on public.store_orders for update to authenticated using (public.is_store_admin()) with check (public.is_store_admin());
create policy store_items_owner_read on public.store_order_items for select to authenticated using (exists (select 1 from public.store_orders o where o.id = order_id and (o.user_id = auth.uid() or public.is_store_admin())));
create policy store_inventory_admin_read on public.store_inventory_movements for select to authenticated using (public.is_store_admin());
create policy store_payment_owner_read on public.store_payment_events for select to authenticated using (exists (select 1 from public.store_orders o where o.id = order_id and (o.user_id = auth.uid() or public.is_store_admin())));
create policy store_payment_admin_insert on public.store_payment_events for insert to authenticated with check (public.is_store_admin());
create policy store_payment_admin_read on public.store_payment_events for select to authenticated using (public.is_store_admin());
create policy store_audit_admin_read on public.store_admin_audit for select to authenticated using (public.is_store_admin());
create policy store_sms_admin_read on public.store_sms_logs for select to authenticated using (public.is_store_admin());

-- The only customer-facing order creation path. Prices/discounts/stock/delivery fee
-- are calculated server-side; row locks and transaction rollback prevent overselling.
create or replace function public.place_store_order(
  p_customer_name text,
  p_phone text,
  p_delivery_address text,
  p_city text,
  p_payment_method text,
  p_transaction_reference text,
  p_items jsonb
) returns jsonb
language plpgsql security definer
set search_path = public, auth
as $$
declare
  v_user uuid := auth.uid();
  v_order public.store_orders%rowtype;
  v_item jsonb;
  v_book public.store_books%rowtype;
  v_qty integer;
  v_subtotal numeric(12,2) := 0;
  v_line numeric(12,2);
  v_delivery numeric(12,2) := 0;
  v_free_threshold numeric(12,2);
  v_count integer;
  v_order_id uuid;
begin
  if v_user is null then raise exception 'Sign in before checkout'; end if;
  if length(trim(coalesce(p_customer_name,''))) < 2 then raise exception 'Recipient name is required'; end if;
  if length(regexp_replace(coalesce(p_phone,''), '[^0-9]', '', 'g')) < 8 then raise exception 'Valid phone number is required'; end if;
  if length(trim(coalesce(p_delivery_address,''))) < 8 or length(trim(coalesce(p_city,''))) < 2 then raise exception 'Complete delivery address is required'; end if;
  if p_payment_method not in ('cod','bkash','nagad') then raise exception 'Unsupported payment method'; end if;
  if p_payment_method <> 'cod' and length(trim(coalesce(p_transaction_reference,''))) < 4 then raise exception 'Payment reference is required but does not confirm payment'; end if;
  if jsonb_typeof(p_items) <> 'array' or jsonb_array_length(p_items) < 1 or jsonb_array_length(p_items) > 30 then raise exception 'Cart must contain 1 to 30 items'; end if;
  select count(*) into v_count from (select (x->>'book_id') as id from jsonb_array_elements(p_items) x group by 1) d;
  if v_count <> jsonb_array_length(p_items) then raise exception 'Duplicate book entries are not allowed'; end if;
  -- Lock requested book rows in deterministic order before checking stock.
  perform b.id from public.store_books b join (select (x->>'book_id')::uuid id from jsonb_array_elements(p_items) x) req on req.id = b.id order by b.id for update of b;
  select default_delivery_fee_bdt, free_delivery_threshold_bdt into v_delivery, v_free_threshold from public.store_delivery_settings where id = 1;
  if v_delivery is null then v_delivery := 0; end if;
  for v_item in select value from jsonb_array_elements(p_items) loop
    if coalesce(v_item->>'book_id','') !~* '^[0-9a-f-]{36}$' then raise exception 'Invalid book id'; end if;
    if coalesce(v_item->>'quantity','') !~ '^[0-9]+$' then raise exception 'Invalid quantity'; end if;
    v_qty := (v_item->>'quantity')::integer;
    if v_qty < 1 or v_qty > 100 then raise exception 'Quantity must be between 1 and 100'; end if;
    select * into v_book from public.store_books where id = (v_item->>'book_id')::uuid and is_active = true for update;
    if not found then raise exception 'A selected book is unavailable'; end if;
    if v_book.stock_quantity < v_qty then raise exception 'Insufficient stock for %', v_book.title; end if;
    v_line := round(v_book.price_bdt * (1 - v_book.discount_percent / 100), 2) * v_qty;
    v_subtotal := v_subtotal + v_line;
  end loop;
  if v_free_threshold is not null and v_subtotal >= v_free_threshold then v_delivery := 0; end if;
  insert into public.store_orders(user_id, customer_name, customer_email, phone, delivery_address, city, subtotal_bdt, delivery_fee_bdt, total_bdt, payment_method, payment_status, order_status, transaction_reference)
  values (v_user, trim(p_customer_name), (select email from auth.users where id = v_user), trim(p_phone), trim(p_delivery_address), trim(p_city), v_subtotal, v_delivery, v_subtotal + v_delivery, p_payment_method, 'pending', 'placed', nullif(trim(p_transaction_reference),'')) returning * into v_order;
  for v_item in select value from jsonb_array_elements(p_items) loop
    v_qty := (v_item->>'quantity')::integer;
    select * into v_book from public.store_books where id = (v_item->>'book_id')::uuid for update;
    v_line := round(v_book.price_bdt * (1 - v_book.discount_percent / 100), 2);
    insert into public.store_order_items(order_id, book_id, title_snapshot, unit_price_bdt, quantity, line_total_bdt)
    values (v_order.id, v_book.id, v_book.title, v_line, v_qty, v_line * v_qty);
    update public.store_books set stock_quantity = stock_quantity - v_qty, updated_at = now() where id = v_book.id;
    insert into public.store_inventory_movements(book_id, order_id, change_quantity, reason, actor_user_id, note)
    values (v_book.id, v_order.id, -v_qty, 'order', v_user, 'Stock reserved at checkout');
  end loop;
  insert into public.store_payment_events(order_id, event_type, method, transaction_reference, note, actor_user_id)
  values (v_order.id, 'submitted', p_payment_method, nullif(trim(p_transaction_reference),''), 'Payment not verified at order creation', v_user);
  return jsonb_build_object('order_id', v_order.id, 'order_number', v_order.order_number, 'total_bdt', v_order.total_bdt, 'payment_status', v_order.payment_status, 'order_status', v_order.order_status);
end;
$$;
revoke all on function public.place_store_order(text,text,text,text,text,text,jsonb) from public;
grant execute on function public.place_store_order(text,text,text,text,text,text,jsonb) to authenticated;

grant select on public.store_books to anon, authenticated;
grant select on public.store_delivery_settings to anon, authenticated;
grant select on public.store_addresses, public.store_orders, public.store_order_items, public.store_payment_events to authenticated;
grant select on public.store_inventory_movements, public.store_admin_audit, public.store_sms_logs to authenticated;

-- No client insert/update/delete grants for order internals, stock movements, audit,
-- payment events or SMS logs. Administrative payment/order transitions should be
-- implemented in a separately reviewed RPC that writes audit/payment events.
revoke insert, update, delete on public.store_orders from anon, authenticated;
revoke insert, update, delete on public.store_order_items from anon, authenticated;
revoke insert, update, delete on public.store_inventory_movements from anon, authenticated;
revoke insert, update, delete on public.store_admin_audit from anon, authenticated;
revoke insert, update, delete on public.store_sms_logs from anon, authenticated;
revoke insert, update, delete on public.store_payment_events from anon, authenticated;

commit;
