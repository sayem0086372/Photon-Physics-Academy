-- Store wallet checkout v1.3.5: bKash/Nagad manual payment submission.
-- Apply after store_guest_cod_v1_3_1.sql. Does not auto-verify payments.
begin;
alter table public.store_orders add column if not exists sender_mobile text;
create or replace function public.place_guest_store_order_with_coupon(
  p_checkout_request_id uuid,
  p_customer_name text,
  p_phone text,
  p_delivery_address text,
  p_area text,
  p_city text,
  p_items jsonb,
  p_coupon_code text default null,
  p_payment_method text default 'cod',
  p_transaction_reference text default null,
  p_sender_mobile text default null
) returns jsonb
language plpgsql
security definer
set search_path = ''
as $function$
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
  v_customer_uses integer;
  v_phone_digits text := regexp_replace(coalesce(p_phone, ''), '[^0-9]', '', 'g');
  v_code text := nullif(upper(trim(coalesce(p_coupon_code,''))), '');
  v_method text := lower(trim(coalesce(p_payment_method,'cod')));
  v_trx text := nullif(trim(coalesce(p_transaction_reference,'')), '');
  v_sender text := nullif(trim(coalesce(p_sender_mobile,'')), '');
begin
  if p_checkout_request_id is null then raise exception 'Checkout request ID is required'; end if;
  if v_method not in ('cod','bkash','nagad') then raise exception 'Unsupported payment method'; end if;
  if v_method <> 'cod' and length(coalesce(v_trx,'')) not between 4 and 120 then raise exception 'Transaction ID is required for wallet payments'; end if;
  if v_method = 'cod' then v_trx := null; v_sender := null; end if;
  select * into v_existing from public.store_orders where checkout_request_id = p_checkout_request_id;
  if found then
    return jsonb_build_object('order_number',v_existing.order_number,'total_bdt',v_existing.total_bdt,'order_status',v_existing.order_status,'reused',true);
  end if;
  if length(trim(coalesce(p_customer_name,''))) not between 2 and 120 then raise exception 'Recipient name is required'; end if;
  if length(v_phone_digits) not between 8 and 15 then raise exception 'Valid phone number is required'; end if;
  if length(trim(coalesce(p_delivery_address,''))) not between 8 and 1000
    or length(trim(coalesce(p_area,''))) not between 2 and 120
    or length(trim(coalesce(p_city,''))) not between 2 and 120 then raise exception 'Complete delivery address, area/thana, and district/city are required'; end if;
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
    if v_user is not null then
      select count(*) into v_customer_uses from public.store_coupon_redemptions r where r.coupon_id=v_coupon.id and r.user_id=v_user;
    else
      select count(*) into v_customer_uses from public.store_coupon_redemptions r join public.store_orders o on o.id=r.order_id
        where r.coupon_id=v_coupon.id and regexp_replace(o.phone, '[^0-9]', '', 'g')=v_phone_digits;
    end if;
    if v_customer_uses>=v_coupon.per_user_limit then raise exception 'This coupon has reached its per-customer usage limit'; end if;
    if v_coupon.discount_type='percent' then v_discount:=round(v_subtotal*v_coupon.discount_value/100,2); else v_discount:=v_coupon.discount_value; end if;
    if v_coupon.max_discount_bdt is not null then v_discount:=least(v_discount,v_coupon.max_discount_bdt); end if;
    v_discount:=least(v_subtotal,greatest(0,v_discount));
  end if;

  insert into public.store_orders(checkout_request_id,user_id,customer_name,customer_email,phone,delivery_address,area,city,subtotal_bdt,delivery_fee_bdt,total_bdt,payment_method,payment_status,order_status,transaction_reference,sender_mobile,coupon_code,coupon_discount_bdt)
  values(p_checkout_request_id,v_user,trim(p_customer_name),null,trim(p_phone),trim(p_delivery_address),trim(p_area),trim(p_city),v_subtotal,v_delivery,v_subtotal+v_delivery-v_discount,v_method,'pending','placed',v_trx,v_sender,v_code,v_discount)
  returning * into v_order;
  for v_item in select value from jsonb_array_elements(p_items) loop
    v_book_id:=(v_item->>'book_id')::uuid; v_qty:=(v_item->>'quantity')::integer;
    select * into v_book from public.store_books where id=v_book_id;
    v_unit:=round(v_book.price_bdt*(1-v_book.discount_percent/100),2);
    insert into public.store_order_items(order_id,book_id,title_snapshot,unit_price_bdt,quantity,line_total_bdt) values(v_order.id,v_book.id,v_book.title,v_unit,v_qty,v_unit*v_qty);
    update public.store_books set stock_quantity=stock_quantity-v_qty,updated_at=now() where id=v_book.id and stock_quantity>=v_qty;
    if not found then raise exception 'Insufficient stock for %',v_book.title; end if;
    insert into public.store_inventory_movements(book_id,order_id,change_quantity,reason,actor_user_id,note) values(v_book.id,v_order.id,-v_qty,'order',v_user,'Stock reserved for guest checkout');
  end loop;
  if v_code is not null then
    insert into public.store_coupon_redemptions(coupon_id,order_id,user_id,code_snapshot,discount_bdt) values(v_coupon.id,v_order.id,v_user,v_coupon.code,v_discount);
  end if;
  insert into public.store_payment_events(order_id,event_type,method,transaction_reference,note,actor_user_id)
  values(v_order.id,'submitted',v_method,v_trx,case when v_method='cod' then 'Cash on Delivery selected; collect payment at delivery' else 'Customer submitted wallet transaction ID; manual verification required' end,v_user);
  insert into public.store_admin_audit(actor_user_id,action,entity_type,entity_id,details)
  values(v_user,'guest_store_order_placed','order',v_order.id,jsonb_build_object('order_number',v_order.order_number,'payment_method',v_method,'transaction_reference',v_trx,'sender_mobile',v_sender));
  return jsonb_build_object('order_number',v_order.order_number,'subtotal_bdt',v_order.subtotal_bdt,'coupon_code',v_order.coupon_code,'coupon_discount_bdt',v_order.coupon_discount_bdt,'delivery_fee_bdt',v_order.delivery_fee_bdt,'total_bdt',v_order.total_bdt,'payment_status',v_order.payment_status,'payment_method',v_order.payment_method,'order_status',v_order.order_status,'reused',false);
exception when unique_violation then
  select * into v_existing from public.store_orders where checkout_request_id=p_checkout_request_id;
  if found then return jsonb_build_object('order_number',v_existing.order_number,'total_bdt',v_existing.total_bdt,'order_status',v_existing.order_status,'reused',true); end if;
  raise;
end;
$function$;


revoke all on function public.place_guest_store_order_with_coupon(uuid,text,text,text,text,text,jsonb,text,text,text,text) from public;
grant execute on function public.place_guest_store_order_with_coupon(uuid,text,text,text,text,text,jsonb,text,text,text,text) to anon, authenticated;
commit;
