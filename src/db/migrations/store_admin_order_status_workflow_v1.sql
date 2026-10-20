-- Store Admin: guarded order-status progression
-- Applied to Supabase as store_admin_order_status_workflow_v1.
-- This function intentionally does not cancel/refund orders; those workflows require separate handling.
create or replace function public.set_store_order_status(p_order_id uuid, p_status text, p_note text default null)
returns void
language plpgsql
security definer
set search_path = ''
as $function$
declare
  v_order public.store_orders%rowtype;
  v_allowed boolean := false;
begin
  if (select auth.uid()) is null or not public.is_store_admin() then
    raise exception 'Admin access required';
  end if;
  if p_status is null or p_status not in ('confirmed','processing','shipped','delivered') then
    raise exception 'Unsupported order status';
  end if;

  select * into v_order from public.store_orders where id = p_order_id for update;
  if not found then raise exception 'Order not found'; end if;

  v_allowed := (v_order.order_status = 'placed' and p_status = 'confirmed')
    or (v_order.order_status = 'confirmed' and p_status = 'processing')
    or (v_order.order_status = 'processing' and p_status = 'shipped')
    or (v_order.order_status = 'shipped' and p_status = 'delivered');

  if not v_allowed then
    raise exception 'Invalid order status transition: % -> %', v_order.order_status, p_status;
  end if;

  if v_order.payment_method <> 'cod' and v_order.payment_status <> 'verified' and p_status in ('processing','shipped','delivered') then
    raise exception 'Wallet payment must be verified before processing or delivery';
  end if;

  update public.store_orders set order_status = p_status, updated_at = now() where id = p_order_id;
  insert into public.store_admin_audit(actor_user_id, action, entity_type, entity_id, details)
    values ((select auth.uid()), 'order_status_' || p_status, 'order', p_order_id,
      jsonb_build_object('previous_status', v_order.order_status, 'new_status', p_status, 'note', left(coalesce(p_note,''),1000)));
end;
$function$;

revoke all on function public.set_store_order_status(uuid,text,text) from public, anon;
grant execute on function public.set_store_order_status(uuid,text,text) to authenticated;
