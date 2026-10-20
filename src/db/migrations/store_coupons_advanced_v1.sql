-- Photon Physics Academy Store v1.3.0: coupon and advanced management support.
-- Additive migration. Preserves academy tables, batches, Store Admin membership, and existing orders.
-- Apply only after reviewing and deploying the matching v1.3.0 application source.

begin;

create table if not exists public.store_coupons (
  id uuid primary key default gen_random_uuid(),
  code text not null,
  description text,
  discount_type text not null check (discount_type in ('percent','fixed')),
  discount_value numeric(12,2) not null check (discount_value > 0),
  max_discount_bdt numeric(12,2) check (max_discount_bdt is null or max_discount_bdt >= 0),
  min_subtotal_bdt numeric(12,2) not null default 0 check (min_subtotal_bdt >= 0),
  usage_limit integer check (usage_limit is null or usage_limit >= 1),
  per_user_limit integer not null default 1 check (per_user_limit >= 1),
  starts_at timestamptz,
  ends_at timestamptz,
  is_active boolean not null default true,
  created_by uuid references auth.users(id) on delete set null,
  created_at timestamptz not null default now(),
  updated_at timestamptz not null default now(),
  constraint store_coupons_code_format check (code = upper(trim(code)) and code ~ '^[A-Z0-9_-]{3,40}$'),
  constraint store_coupons_date_range check (starts_at is null or ends_at is null or ends_at > starts_at),
  constraint store_coupons_percent_range check (discount_type <> 'percent' or discount_value <= 100)
);
create unique index if not exists store_coupons_code_unique_idx on public.store_coupons (upper(code));
create index if not exists store_coupons_active_dates_idx on public.store_coupons (is_active, starts_at, ends_at);

alter table public.store_orders add column if not exists coupon_code text;
alter table public.store_orders add column if not exists coupon_discount_bdt numeric(12,2) not null default 0;
alter table public.store_orders drop constraint if exists store_orders_check;
alter table public.store_orders add constraint store_orders_total_after_coupon_check
  check (total_bdt = subtotal_bdt + delivery_fee_bdt - coupon_discount_bdt);
alter table public.store_orders add constraint store_orders_coupon_discount_nonnegative_check
  check (coupon_discount_bdt >= 0 and coupon_discount_bdt <= subtotal_bdt);

create table if not exists public.store_coupon_redemptions (
  id uuid primary key default gen_random_uuid(),
  coupon_id uuid not null references public.store_coupons(id) on delete restrict,
  order_id uuid not null unique references public.store_orders(id) on delete restrict,
  user_id uuid not null references auth.users(id) on delete restrict,
  code_snapshot text not null,
  discount_bdt numeric(12,2) not null check (discount_bdt >= 0),
  created_at timestamptz not null default now()
);
create index if not exists store_coupon_redemptions_coupon_user_idx on public.store_coupon_redemptions(coupon_id, user_id, created_at desc);
create index if not exists store_coupon_redemptions_created_idx on public.store_coupon_redemptions(created_at desc);

alter table public.store_coupons enable row level security;
alter table public.store_coupon_redemptions enable row level security;
revoke all on public.store_coupons from public, anon, authenticated;
revoke all on public.store_coupon_redemptions from public, anon, authenticated;
grant select, insert, update, delete on public.store_coupons to authenticated;
grant select on public.store_coupon_redemptions to authenticated;

drop policy if exists store_coupons_admin_manage on public.store_coupons;
create policy store_coupons_admin_manage on public.store_coupons
  for all to authenticated using ((select public.is_store_admin())) with check ((select public.is_store_admin()));
drop policy if exists store_coupon_redemptions_admin_read on public.store_coupon_redemptions;
create policy store_coupon_redemptions_admin_read on public.store_coupon_redemptions
  for select to authenticated using ((select public.is_store_admin()));


create or replace function public.audit_store_coupon_change()
returns trigger
language plpgsql
security definer
set search_path = ''
as $$
declare
  v_id uuid;
  v_code text;
  v_action text;
begin
  if tg_op = 'DELETE' then v_id := old.id; v_code := old.code; v_action := 'coupon_deleted';
  elsif tg_op = 'INSERT' then v_id := new.id; v_code := new.code; v_action := 'coupon_created';
  else v_id := new.id; v_code := new.code; v_action := 'coupon_updated'; end if;
  insert into public.store_admin_audit(actor_user_id, action, entity_type, entity_id, details)
  values ((select auth.uid()), v_action, 'coupon', v_id, jsonb_build_object('code', v_code, 'operation', tg_op));
  if tg_op = 'DELETE' then return old; else return new; end if;
end;
$$;
revoke all on function public.audit_store_coupon_change() from public, anon, authenticated;
drop trigger if exists store_coupons_audit_trigger on public.store_coupons;
create trigger store_coupons_audit_trigger after insert or update or delete on public.store_coupons
for each row execute function public.audit_store_coupon_change();

create or replace function public.preview_store_coupon(p_code text, p_subtotal_bdt numeric)
returns jsonb
language plpgsql
security definer
set search_path = ''
as $$
declare
  v_user uuid := (select auth.uid());
  v_coupon public.store_coupons%rowtype;
  v_uses integer;
  v_user_uses integer;
  v_discount numeric(12,2);
  v_subtotal numeric(12,2) := round(coalesce(p_subtotal_bdt, 0), 2);
begin
  if v_user is null then raise exception 'Sign in to apply a coupon'; end if;
  if v_subtotal <= 0 then raise exception 'Add books to your cart first'; end if;
  if length(trim(coalesce(p_code,''))) not between 3 and 40 then raise exception 'Enter a valid coupon code'; end if;
  select * into v_coupon from public.store_coupons c where c.code = upper(trim(p_code)) and c.is_active = true;
  if not found then raise exception 'Coupon code is invalid or inactive'; end if;
  if v_coupon.starts_at is not null and now() < v_coupon.starts_at then raise exception 'This coupon is not active yet'; end if;
  if v_coupon.ends_at is not null and now() >= v_coupon.ends_at then raise exception 'This coupon has expired'; end if;
  if v_subtotal < v_coupon.min_subtotal_bdt then raise exception 'Cart subtotal does not meet the minimum for this coupon'; end if;
  select count(*) into v_uses from public.store_coupon_redemptions r where r.coupon_id = v_coupon.id;
  if v_coupon.usage_limit is not null and v_uses >= v_coupon.usage_limit then raise exception 'This coupon has reached its usage limit'; end if;
  select count(*) into v_user_uses from public.store_coupon_redemptions r where r.coupon_id = v_coupon.id and r.user_id = v_user;
  if v_user_uses >= v_coupon.per_user_limit then raise exception 'You have already used this coupon the maximum number of times'; end if;
  if v_coupon.discount_type = 'percent' then v_discount := round(v_subtotal * v_coupon.discount_value / 100, 2);
  else v_discount := v_coupon.discount_value; end if;
  if v_coupon.max_discount_bdt is not null then v_discount := least(v_discount, v_coupon.max_discount_bdt); end if;
  v_discount := least(v_subtotal, greatest(0, v_discount));
  return jsonb_build_object('valid', true, 'code', v_coupon.code, 'discount_bdt', v_discount, 'subtotal_bdt', v_subtotal, 'total_after_discount_bdt', greatest(0, v_subtotal - v_discount));
end;
$$;
revoke all on function public.preview_store_coupon(text,numeric) from public, anon;
grant execute on function public.preview_store_coupon(text,numeric) to authenticated;

create or replace function public.place_store_order_with_coupon(
  p_checkout_request_id uuid,
  p_customer_name text,
  p_phone text,
  p_delivery_address text,
  p_city text,
  p_payment_method text,
  p_transaction_reference text,
  p_items jsonb,
  p_coupon_code text default null
) returns jsonb
language plpgsql
security definer
set search_path = ''
as $$
declare
  v_user uuid := (select auth.uid());
  v_existing public.store_orders%rowtype;
  v_order public.store_orders%rowtype;
  v_item jsonb;
  v_book public.store_books%rowtype;
  v_coupon public.store_coupons%rowtype;
  v_qty integer;
  v_book_id uuid;
  v_subtotal numeric(12,2) := 0;
  v_unit numeric(12,2);
  v_delivery numeric(12,2) := 0;
  v_free_threshold numeric(12,2);
  v_discount numeric(12,2) := 0;
  v_count integer;
  v_user_uses integer;
  v_code text := nullif(upper(trim(coalesce(p_coupon_code,''))), '');
begin
  if v_user is null then raise exception 'Sign in before checkout'; end if;
  if p_checkout_request_id is null then raise exception 'Checkout request ID is required'; end if;
  select * into v_existing from public.store_orders where user_id = v_user and checkout_request_id = p_checkout_request_id;
  if found then
    return jsonb_build_object('order_id',v_existing.id,'order_number',v_existing.order_number,'subtotal_bdt',v_existing.subtotal_bdt,'coupon_code',v_existing.coupon_code,'coupon_discount_bdt',v_existing.coupon_discount_bdt,'delivery_fee_bdt',v_existing.delivery_fee_bdt,'total_bdt',v_existing.total_bdt,'payment_status',v_existing.payment_status,'order_status',v_existing.order_status,'reused',true);
  end if;
  if length(trim(coalesce(p_customer_name,''))) not between 2 and 120 then raise exception 'Recipient name is required'; end if;
  if length(regexp_replace(coalesce(p_phone,''), '[^0-9]', '', 'g')) not between 8 and 15 then raise exception 'Valid phone number is required'; end if;
  if length(trim(coalesce(p_delivery_address,''))) not between 8 and 1000 or length(trim(coalesce(p_city,''))) not between 2 and 120 then raise exception 'Complete delivery address is required'; end if;
  if p_payment_method is null or p_payment_method not in ('cod','bkash','nagad') then raise exception 'Unsupported payment method'; end if;
  if p_payment_method <> 'cod' and length(trim(coalesce(p_transaction_reference,''))) not between 4 and 120 then raise exception 'Payment reference is required; it does not confirm payment'; end if;
  if jsonb_typeof(p_items) is distinct from 'array' or jsonb_array_length(p_items) not between 1 and 30 then raise exception 'Cart must contain 1 to 30 items'; end if;
  for v_item in select value from jsonb_array_elements(p_items) loop
    if coalesce(v_item->>'book_id','') !~* '^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$' then raise exception 'Invalid book id'; end if;
    if coalesce(v_item->>'quantity','') !~ '^[0-9]{1,3}$' then raise exception 'Invalid quantity'; end if;
    v_qty := (v_item->>'quantity')::integer;
    if v_qty < 1 or v_qty > 100 then raise exception 'Quantity must be between 1 and 100'; end if;
  end loop;
  select count(*) into v_count from (select x->>'book_id' as id from jsonb_array_elements(p_items) x group by 1) q;
  if v_count <> jsonb_array_length(p_items) then raise exception 'Duplicate book entries are not allowed'; end if;
  perform b.id from public.store_books b join (select (x->>'book_id')::uuid id from jsonb_array_elements(p_items) x) req on req.id=b.id order by b.id for update of b;
  select default_delivery_fee_bdt, free_delivery_threshold_bdt into v_delivery, v_free_threshold from public.store_delivery_settings where id=1;
  if not found then raise exception 'Delivery settings are not configured'; end if;
  for v_item in select value from jsonb_array_elements(p_items) loop
    v_book_id := (v_item->>'book_id')::uuid; v_qty := (v_item->>'quantity')::integer;
    select * into v_book from public.store_books where id=v_book_id and is_active=true;
    if not found then raise exception 'A selected book is unavailable'; end if;
    if v_book.stock_quantity < v_qty then raise exception 'Insufficient stock for %',v_book.title; end if;
    v_unit := round(v_book.price_bdt * (1-v_book.discount_percent/100),2);
    v_subtotal := v_subtotal + v_unit*v_qty;
  end loop;
  if v_free_threshold is not null and v_subtotal >= v_free_threshold then v_delivery := 0; end if;

  if v_code is not null then
    select * into v_coupon from public.store_coupons c where c.code=v_code and c.is_active=true for update;
    if not found then raise exception 'Coupon code is invalid or inactive'; end if;
    if v_coupon.starts_at is not null and now()<v_coupon.starts_at then raise exception 'This coupon is not active yet'; end if;
    if v_coupon.ends_at is not null and now()>=v_coupon.ends_at then raise exception 'This coupon has expired'; end if;
    if v_subtotal < v_coupon.min_subtotal_bdt then raise exception 'Cart subtotal does not meet the minimum for this coupon'; end if;
    select count(*) into v_count from public.store_coupon_redemptions r where r.coupon_id=v_coupon.id;
    if v_coupon.usage_limit is not null and v_count>=v_coupon.usage_limit then raise exception 'This coupon has reached its usage limit'; end if;
    select count(*) into v_user_uses from public.store_coupon_redemptions r where r.coupon_id=v_coupon.id and r.user_id=v_user;
    if v_user_uses>=v_coupon.per_user_limit then raise exception 'You have already used this coupon the maximum number of times'; end if;
    if v_coupon.discount_type='percent' then v_discount:=round(v_subtotal*v_coupon.discount_value/100,2); else v_discount:=v_coupon.discount_value; end if;
    if v_coupon.max_discount_bdt is not null then v_discount:=least(v_discount,v_coupon.max_discount_bdt); end if;
    v_discount:=least(v_subtotal,greatest(0,v_discount));
  end if;

  insert into public.store_orders(checkout_request_id,user_id,customer_name,customer_email,phone,delivery_address,city,subtotal_bdt,delivery_fee_bdt,total_bdt,payment_method,payment_status,order_status,transaction_reference,coupon_code,coupon_discount_bdt)
  values(p_checkout_request_id,v_user,trim(p_customer_name),(select u.email from auth.users u where u.id=v_user),trim(p_phone),trim(p_delivery_address),trim(p_city),v_subtotal,v_delivery,v_subtotal+v_delivery-v_discount,p_payment_method,'pending','placed',nullif(trim(p_transaction_reference),''),v_code,v_discount)
  returning * into v_order;
  for v_item in select value from jsonb_array_elements(p_items) loop
    v_book_id:=(v_item->>'book_id')::uuid; v_qty:=(v_item->>'quantity')::integer;
    select * into v_book from public.store_books where id=v_book_id;
    v_unit:=round(v_book.price_bdt*(1-v_book.discount_percent/100),2);
    insert into public.store_order_items(order_id,book_id,title_snapshot,unit_price_bdt,quantity,line_total_bdt) values(v_order.id,v_book.id,v_book.title,v_unit,v_qty,v_unit*v_qty);
    update public.store_books set stock_quantity=stock_quantity-v_qty,updated_at=now() where id=v_book.id and stock_quantity>=v_qty;
    if not found then raise exception 'Insufficient stock for %',v_book.title; end if;
    insert into public.store_inventory_movements(book_id,order_id,change_quantity,reason,actor_user_id,note) values(v_book.id,v_order.id,-v_qty,'order',v_user,'Stock reserved at checkout');
  end loop;
  if v_code is not null then
    insert into public.store_coupon_redemptions(coupon_id,order_id,user_id,code_snapshot,discount_bdt) values(v_coupon.id,v_order.id,v_user,v_coupon.code,v_discount);
  end if;
  insert into public.store_payment_events(order_id,event_type,method,transaction_reference,note,actor_user_id)
  values(v_order.id,'submitted',p_payment_method,nullif(trim(p_transaction_reference),''),'Payment pending manual verification; reference alone is not proof of payment',v_user);
  return jsonb_build_object('order_id',v_order.id,'order_number',v_order.order_number,'subtotal_bdt',v_order.subtotal_bdt,'coupon_code',v_order.coupon_code,'coupon_discount_bdt',v_order.coupon_discount_bdt,'delivery_fee_bdt',v_order.delivery_fee_bdt,'total_bdt',v_order.total_bdt,'payment_status',v_order.payment_status,'order_status',v_order.order_status,'reused',false);
exception when unique_violation then
  select * into v_existing from public.store_orders where user_id=(select auth.uid()) and checkout_request_id=p_checkout_request_id;
  if found then return jsonb_build_object('order_id',v_existing.id,'order_number',v_existing.order_number,'subtotal_bdt',v_existing.subtotal_bdt,'coupon_code',v_existing.coupon_code,'coupon_discount_bdt',v_existing.coupon_discount_bdt,'delivery_fee_bdt',v_existing.delivery_fee_bdt,'total_bdt',v_existing.total_bdt,'payment_status',v_existing.payment_status,'order_status',v_existing.order_status,'reused',true); end if;
  raise;
end;
$$;
revoke all on function public.place_store_order_with_coupon(uuid,text,text,text,text,text,text,jsonb,text) from public, anon;
grant execute on function public.place_store_order_with_coupon(uuid,text,text,text,text,text,text,jsonb,text) to authenticated;

commit;
