<?php
/**
 * WooCommerce integration for the Photon Physics Academy front-end admin.
 */
if (!defined('ABSPATH')) { exit; }

function ppa_store_ready() { return class_exists('WooCommerce') && function_exists('wc_get_products'); }
function ppa_store_money($value) { return function_exists('wc_price') ? wc_price((float)$value) : '৳'.number_format_i18n((float)$value,2); }

/** Render externally hosted product images entered in the academy admin. */
function ppa_store_external_product_image($html, $product, $size, $attr, $placeholder) {
    if (!$product instanceof WC_Product) { return $html; }
    $url = get_post_meta($product->get_id(), '_ppa_external_image_url', true);
    if (!$url || !wp_http_validate_url($url)) { return $html; }
    $alt = $product->get_name();
    $classes = 'wp-post-image';
    if (is_array($attr) && !empty($attr['class'])) { $classes .= ' ' . sanitize_html_class($attr['class']); }
    return '<img src="' . esc_url($url) . '" alt="' . esc_attr($alt) . '" class="' . esc_attr($classes) . '" loading="lazy" decoding="async">';
}
add_filter('woocommerce_product_get_image', 'ppa_store_external_product_image', 10, 5);
function ppa_store_redirect($view='books',$notice='saved') {
    $url=add_query_arg(array('ppa_tab'=>'store','ppa_store_view'=>$view,'ppa_store_notice'=>$notice),home_url('/admin/'));
    wp_safe_redirect($url); exit;
}
function ppa_store_handle_admin_post() {
    if (empty($_POST['ppa_store_action'])) return;
    if (!is_user_logged_in() || !current_user_can('manage_options')) wp_die('Not allowed.',403);
    check_admin_referer('ppa_store_admin','ppa_store_nonce');
    if (!ppa_store_ready()) ppa_store_redirect('books','woocommerce_missing');
    $action=sanitize_key(wp_unslash($_POST['ppa_store_action']));
    $view=sanitize_key(wp_unslash($_POST['store_view'] ?? 'books'));

    if ($action==='save_settings') {
        update_option('ppa_store_delivery_fee',max(0,(float)($_POST['delivery_fee'] ?? 0)));
        update_option('ppa_store_free_delivery',max(0,(float)($_POST['free_delivery'] ?? 0)));
        update_option('ppa_store_receiver',sanitize_text_field(wp_unslash($_POST['receiver'] ?? '01312364499')));
        update_option('ppa_store_bkash_instructions',sanitize_textarea_field(wp_unslash($_POST['bkash_instructions'] ?? '')));
        update_option('ppa_store_nagad_instructions',sanitize_textarea_field(wp_unslash($_POST['nagad_instructions'] ?? '')));
        update_option('ppa_store_cod_enabled',isset($_POST['cod_enabled'])?'yes':'no');
        $cod_settings=get_option('woocommerce_cod_settings',array()); if(!is_array($cod_settings)) $cod_settings=array(); $cod_settings['enabled']=isset($_POST['cod_enabled'])?'yes':'no'; update_option('woocommerce_cod_settings',$cod_settings);
        update_option('ppa_store_bkash_enabled',isset($_POST['bkash_enabled'])?'yes':'no');
        update_option('ppa_store_nagad_enabled',isset($_POST['nagad_enabled'])?'yes':'no');
        ppa_store_redirect('delivery','settings_saved');
    }

    if ($action==='save_product') {
        $id=absint($_POST['product_id'] ?? 0);
        $product=$id ? wc_get_product($id) : new WC_Product_Simple();
        if (!$product) ppa_store_redirect('books','product_missing');
        $name=sanitize_text_field(wp_unslash($_POST['product_name'] ?? ''));
        if ($name==='') ppa_store_redirect('books','product_required');
        $product->set_name($name);
        update_post_meta($id ?: 0, '_ppa_book_author', sanitize_text_field(wp_unslash($_POST['book_author'] ?? '')));
        $product->set_status(isset($_POST['published'])?'publish':'draft');
        $product->set_catalog_visibility(isset($_POST['visible'])?'visible':'hidden');
        $product->set_regular_price(wc_format_decimal(wp_unslash($_POST['regular_price'] ?? '0')));
        $sale=wc_format_decimal(wp_unslash($_POST['sale_price'] ?? ''));
        $product->set_sale_price($sale);
        $product->set_description(wp_kses_post(wp_unslash($_POST['description'] ?? '')));
        $product->set_short_description(wp_kses_post(wp_unslash($_POST['short_description'] ?? '')));
        $product->set_sku(sanitize_text_field(wp_unslash($_POST['sku'] ?? '')));
        $product->set_manage_stock(true);
        $product->set_stock_quantity(max(0,absint($_POST['stock'] ?? 0)));
        $product->set_stock_status($product->get_stock_quantity()>0?'instock':'outofstock');
        $product->set_category_ids(array_values(array_filter(array_map('absint',(array)($_POST['categories'] ?? array())))));
        $saved=$product->save();
        if (!$id) $id=$saved;
        update_post_meta($id, '_ppa_book_author', sanitize_text_field(wp_unslash($_POST['book_author'] ?? '')));
        $image_url=esc_url_raw(wp_unslash($_POST['image_url'] ?? ''));
        if ($image_url && wp_http_validate_url($image_url)) {
            update_post_meta($id,'_ppa_external_image_url',$image_url);
            $attachment_id=attachment_url_to_postid($image_url);
            if ($attachment_id) set_post_thumbnail($id,$attachment_id);
        } else {
            delete_post_meta($id,'_ppa_external_image_url');
        }
        ppa_store_redirect('books','product_saved');
    }

    if ($action==='delete_product') {
        $id=absint($_POST['product_id'] ?? 0);
        $product=wc_get_product($id);
        if ($product) $product->delete(true);
        ppa_store_redirect('books','product_deleted');
    }

    if ($action==='update_order') {
        $order=wc_get_order(absint($_POST['order_id'] ?? 0));
        if (!$order) ppa_store_redirect('orders','order_missing');
        $status=sanitize_key(wp_unslash($_POST['order_status'] ?? ''));
        $allowed=array('pending','on-hold','processing','completed','cancelled','refunded','failed');
        if (in_array($status,$allowed,true)) $order->update_status($status,'Updated from Photon Physics Academy admin.');
        if (isset($_POST['payment_received'])) {
            $order->update_meta_data('_ppa_payment_verified','yes');
            $order->add_order_note('Manual payment marked received by academy administrator.');
            if ($order->has_status(array('pending','on-hold'))) $order->payment_complete();
        }
        if (isset($_POST['payment_not_received'])) {
            $order->update_meta_data('_ppa_payment_verified','no');
            $order->add_order_note('Payment not verified by academy administrator.');
        }
        if (isset($_POST['cod_collected']) && $order->get_payment_method()==='cod') {
            $order->update_meta_data('_ppa_cod_collected','yes');
            $order->add_order_note('Cash on delivery collected and confirmed by academy administrator.');
            if (!$order->is_paid()) $order->payment_complete();
        }
        $order->save();
        ppa_store_redirect('orders','order_updated');
    }

    if ($action==='create_coupon') {
        $code=wc_format_coupon_code(sanitize_text_field(wp_unslash($_POST['coupon_code'] ?? '')));
        $amount=wc_format_decimal(wp_unslash($_POST['coupon_amount'] ?? '0'));
        if (!$code || wc_get_coupon_id_by_code($code)) ppa_store_redirect('coupons','coupon_invalid');
        $coupon=new WC_Coupon();
        $coupon->set_code($code);
        $coupon->set_discount_type(in_array($_POST['discount_type'] ?? '',array('percent','fixed_cart'),true)?sanitize_key($_POST['discount_type']):'percent');
        $coupon->set_amount($amount);
        $coupon->set_usage_limit(absint($_POST['usage_limit'] ?? 0) ?: '');
        $coupon->set_description(sanitize_text_field(wp_unslash($_POST['coupon_description'] ?? '')));
        $coupon->set_individual_use(isset($_POST['individual_use']));
        $coupon->save();
        ppa_store_redirect('coupons','coupon_created');
    }
    if ($action==='delete_coupon') {
        $coupon= new WC_Coupon(absint($_POST['coupon_id'] ?? 0));
        if ($coupon->get_id()) $coupon->delete(true);
        ppa_store_redirect('coupons','coupon_deleted');
    }
    if ($action==='export_orders') {
        $orders=wc_get_orders(array('limit'=>-1,'orderby'=>'date','order'=>'DESC'));
        nocache_headers();
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=ppa-store-orders-'.gmdate('Y-m-d').'.csv');
        $out=fopen('php://output','w');
        fputcsv($out,array('Order','Date','Customer','Phone','Email','Payment Method','Payment Status','Order Status','Subtotal','Shipping','Total','Address'));
        foreach($orders as $o) fputcsv($out,array($o->get_order_number(),$o->get_date_created()?$o->get_date_created()->date('Y-m-d H:i:s'):'',$o->get_formatted_billing_full_name(),$o->get_billing_phone(),$o->get_billing_email(),$o->get_payment_method_title(),$o->is_paid()?'Paid':'Unpaid',wc_get_order_status_name($o->get_status()),$o->get_subtotal(),$o->get_shipping_total(),$o->get_total(),$o->get_formatted_shipping_address()));
        fclose($out); exit;
    }
    ppa_store_redirect($view,'unknown_action');
}
add_action('template_redirect','ppa_store_handle_admin_post',0);

function ppa_store_register_settings() {
    if (!ppa_store_ready()) return;
    add_filter('woocommerce_enable_guest_checkout','__return_true');
    add_filter('woocommerce_checkout_registration_required','__return_false');
    add_filter('woocommerce_checkout_fields',function($fields) {
        $fields['billing']['billing_phone']['required']=true;
        $fields['billing']['billing_first_name']['label']='Customer name';
        return $fields;
    });
    add_action('woocommerce_checkout_create_order',function($order,$data) {
        $order->update_meta_data('_ppa_order_origin','Photon Physics Academy Bookstore');
    },10,2);
}
add_action('woocommerce_init','ppa_store_register_settings');

add_filter('woocommerce_available_payment_gateways',function($gateways) {
    if (isset($gateways['cod']) && get_option('ppa_store_cod_enabled','yes')!=='yes') unset($gateways['cod']);
    return $gateways;
});
add_action('woocommerce_cart_calculate_fees',function($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;
    if (!$cart || $cart->is_empty()) return;
    $fee=(float)get_option('ppa_store_delivery_fee',0);
    $threshold=(float)get_option('ppa_store_free_delivery',0);
    if ($threshold>0 && (float)$cart->get_subtotal()>=$threshold) return;
    if ($fee>0) $cart->add_fee(__('Delivery charge','photon-physics-academy'),$fee,false);
});
add_action('woocommerce_checkout_create_order',function($order,$data) {
    if (!empty($_POST['ppa_wallet_transaction_id'])) {
        $order->update_meta_data('_ppa_wallet_transaction_id',sanitize_text_field(wp_unslash($_POST['ppa_wallet_transaction_id'])));
        $order->update_meta_data('_ppa_payment_verified','no');
    }
},20,2);


function ppa_store_register_gateways($gateways) {
    if (!class_exists('WC_Payment_Gateway')) return $gateways;
    if (!class_exists('PPA_Manual_Wallet_Gateway')) {
        class PPA_Manual_Wallet_Gateway extends WC_Payment_Gateway {
            public function __construct($method='bkash') {
                $this->id='ppa_'.$method;
                $this->method_title=ucfirst($method).' (Manual)';
                $this->method_description='Manual mobile wallet payment with transaction ID verification by the academy.';
                $this->has_fields=true;
                $this->title=ucfirst($method);
                $this->description='';
                $this->init_form_fields();
                $this->init_settings();
                $this->title=$this->get_option('title',ucfirst($method));
                $this->enabled=get_option('ppa_store_'.$method.'_enabled','yes')==='yes'?'yes':'no';
                $this->instructions=get_option('ppa_store_'.$method.'_instructions',get_option('ppa_store_'.$method.'_instructions',''));
                $this->receiver=get_option('ppa_store_receiver','01312364499');
                add_action('woocommerce_update_options_payment_gateways_'.$this->id,array($this,'process_admin_options'));
            }
            public function init_form_fields() {
                $this->form_fields=array('enabled'=>array('title'=>'Enable','type'=>'checkbox','default'=>'yes'),'title'=>array('title'=>'Title','type'=>'text','default'=>$this->method_title));
            }
            public function payment_fields() {
                $number=esc_html($this->receiver);
                $method=strtoupper(str_replace('ppa_','',$this->id));
                $code=$method==='BKASH'?'*247#':'*167#';
                echo '<div class="ppa-wallet-instructions"><p>'.wp_kses_post(wpautop($this->instructions ?: 'Send the order total to the number below, then enter your transaction ID.')).'</p><p><strong>Receiver:</strong> '.$number.'</p><p><strong>USSD:</strong> '.esc_html($code).'</p><p><label for="ppa_wallet_transaction_id"><strong>Transaction ID *</strong><input id="ppa_wallet_transaction_id" name="ppa_wallet_transaction_id" type="text" required autocomplete="off"></label></p></div>';
            }
            public function validate_fields() {
                if (empty($_POST['ppa_wallet_transaction_id'])) { wc_add_notice('Please enter your mobile wallet transaction ID.','error'); return false; }
                return true;
            }
            public function process_payment($order_id) {
                $order=wc_get_order($order_id);
                $tx=sanitize_text_field(wp_unslash($_POST['ppa_wallet_transaction_id'] ?? ''));
                $order->update_meta_data('_ppa_wallet_transaction_id',$tx);
                $order->update_meta_data('_ppa_payment_verified','no');
                $order->add_order_note('Customer submitted mobile wallet transaction ID. Awaiting manual verification.');
                $order->update_status('on-hold','Awaiting manual mobile wallet payment verification.');
                wc_reduce_stock_levels($order_id);
                WC()->cart->empty_cart();
                return array('result'=>'success','redirect'=>$this->get_return_url($order));
            }
        }
    }
    if (get_option('ppa_store_bkash_enabled','yes')==='yes') $gateways[] = new class('bkash') extends PPA_Manual_Wallet_Gateway {};
    if (get_option('ppa_store_nagad_enabled','yes')==='yes') $gateways[] = new class('nagad') extends PPA_Manual_Wallet_Gateway {};
    return $gateways;
}
add_filter('woocommerce_payment_gateways','ppa_store_register_gateways');

function ppa_store_admin_form($action,$view) {
    echo '<form method="post" class="ppa-ca-form ppa-store-admin-form">';
    wp_nonce_field('ppa_store_admin','ppa_store_nonce');
    echo '<input type="hidden" name="ppa_store_action" value="'.esc_attr($action).'"><input type="hidden" name="store_view" value="'.esc_attr($view).'">';
}
function ppa_store_render_admin($view) {
    if (!ppa_store_ready()) { echo '<div class="ppa-ca-info-panel"><strong>WooCommerce is not active.</strong><p>Activate WooCommerce to connect products, orders, coupons and checkout.</p></div>'; return; }
    $view=in_array($view,array('books','orders','coupons','analytics','delivery'),true)?$view:'books';
    $products=wc_get_products(array('limit'=>-1,'status'=>array('publish','draft','private'),'orderby'=>'date','order'=>'DESC'));
    $orders=wc_get_orders(array('limit'=>100,'orderby'=>'date','order'=>'DESC'));
    $coupons=wc_get_coupons(array('limit'=>100,'orderby'=>'date','order'=>'DESC'));
    $low_stock=0; foreach($products as $p) if($p->managing_stock() && (int)$p->get_stock_quantity()<=5) $low_stock++;
    $pending=0; $gross=0; $delivered=0; foreach($orders as $o) { if(!$o->is_paid()) $pending++; $gross+=(float)$o->get_total(); if($o->has_status('completed')) $delivered++; }
    echo '<div class="ppa-ca-store-heading"><div><p class="ppa-ca-store-kicker">COMMERCE OPERATIONS</p><h2>Store Management</h2><p>Live WooCommerce catalogue, orders, coupons and checkout settings.</p></div><a class="ppa-ca-outline-link" href="'.esc_url(get_permalink(wc_get_page_id('shop'))).'" target="_blank" rel="noopener">View Store ↗</a></div>';
    echo '<div class="ppa-ca-store-metrics">';
    foreach(array(array('Catalog books',count($products),'▣'),array('Low-stock alerts',$low_stock,'▤'),array('Pending payments / COD',$pending,'৳'),array('Active coupons',count($coupons),'％'),array('Orders loaded',count($orders),'▥')) as $m) echo '<article><div><span>'.esc_html($m[0]).'</span><i>'.esc_html($m[2]).'</i></div><strong>'.esc_html(number_format_i18n($m[1])).'</strong></article>';
    echo '</div><nav class="ppa-ca-store-tabs" aria-label="Store management sections">';
    $views=array('books'=>'Books & Inventory','orders'=>'Orders & Payments','coupons'=>'Coupons & Promotions','analytics'=>'Reports & Export','delivery'=>'Delivery Settings');
    foreach($views as $key=>$label) echo '<a class="'.($view===$key?'is-active':'').'" href="'.esc_url(add_query_arg(array('ppa_tab'=>'store','ppa_store_view'=>$key),home_url('/admin/'))).'">'.esc_html($label).'</a>';
    echo '</nav>';

    if($view==='books') {
        $edit_id=absint($_GET['edit_product'] ?? 0); $editing=$edit_id?wc_get_product($edit_id):false;
        echo '<section class="ppa-ca-store-section"><div class="ppa-ca-store-toolbar"><form method="get" class="ppa-ca-store-search"><input type="hidden" name="ppa_tab" value="store"><input type="hidden" name="ppa_store_view" value="books"><input type="search" name="store_search" placeholder="Search title, author or SKU" value="'.esc_attr(sanitize_text_field(wp_unslash($_GET['store_search'] ?? ''))).'"><button class="ppa-ca-outline-link">Search</button></form><a class="ppa-ca-primary" href="'.esc_url(add_query_arg(array('ppa_tab'=>'store','ppa_store_view'=>'books','edit_product'=>0),home_url('/admin/'))).'">＋ Add new book</a></div>';
        echo '<div class="ppa-ca-panel ppa-ca-store-table"><div class="ppa-ca-table-wrap"><table class="ppa-ca-table"><thead><tr><th>Book</th><th>Author</th><th>SKU</th><th>Price</th><th>Stock</th><th>Visibility</th><th>Actions</th></tr></thead><tbody>';
        $search=strtolower(sanitize_text_field(wp_unslash($_GET['store_search'] ?? '')));
        $shown=0;
        foreach($products as $p) {
            if($search && strpos(strtolower($p->get_name().' '.$p->get_sku()),$search)===false) continue;
            $shown++; echo '<tr><td><strong>'.esc_html($p->get_name()).'</strong></td><td>'.esc_html(get_post_meta($p->get_id(),'_ppa_book_author',true)?:'—').'</td><td>'.esc_html($p->get_sku()?:'—').'</td><td>'.wp_kses_post($p->get_price_html()).'</td><td>'.esc_html($p->managing_stock()?(string)$p->get_stock_quantity():'Not managed').'</td><td>'.esc_html($p->get_status()==='publish'?'Published':'Draft').'</td><td><a href="'.esc_url(add_query_arg(array('ppa_tab'=>'store','ppa_store_view'=>'books','edit_product'=>$p->get_id()),home_url('/admin/'))).'">Edit</a> ';
            ppa_store_admin_form('delete_product','books'); echo '<input type="hidden" name="product_id" value="'.$p->get_id().'"><button class="ppa-ca-delete" onclick="return confirm(\'Delete this product?\')">Delete</button></form></td></tr>';
        }
        if(!$shown) echo '<tr><td colspan="7"><div class="ppa-ca-empty">No matching products found.</div></td></tr>';
        echo '</tbody></table></div></div>';
        $cats=get_terms(array('taxonomy'=>'product_cat','hide_empty'=>false));
        echo '<div class="ppa-ca-panel" style="padding:22px;margin-top:18px"><h3>'.($editing?'Edit Book':'Add a Book').'</h3>';
        ppa_store_admin_form('save_product','books');
        echo '<input type="hidden" name="product_id" value="'.($editing?$editing->get_id():0).'"><div class="ppa-ca-form-grid"><label>Book title<input required name="product_name" value="'.esc_attr($editing?$editing->get_name():'').'"></label><label>Author<input name="book_author" value="'.esc_attr($editing?get_post_meta($editing->get_id(),'_ppa_book_author',true):'').'"></label><label>SKU<input name="sku" value="'.esc_attr($editing?$editing->get_sku():'').'"></label><label>Regular price (৳)<input required type="number" min="0" step="0.01" name="regular_price" value="'.esc_attr($editing?$editing->get_regular_price():'').'"></label><label>Sale price (৳)<input type="number" min="0" step="0.01" name="sale_price" value="'.esc_attr($editing?$editing->get_sale_price():'').'"></label><label>Stock quantity<input type="number" min="0" name="stock" value="'.esc_attr($editing?$editing->get_stock_quantity():0).'"></label><label>Product image URL<input type="url" name="image_url" placeholder="https://..." value="'.esc_attr($editing?(get_post_meta($editing->get_id(),'_ppa_external_image_url',true)?:wp_get_attachment_url($editing->get_image_id())):'').'"></label><label class="is-wide">Short description<textarea name="short_description" rows="2">'.esc_textarea($editing?$editing->get_short_description():'').'</textarea></label><label class="is-wide">Full description<textarea name="description" rows="4">'.esc_textarea($editing?$editing->get_description():'').'</textarea></label><label>Categories<select name="categories[]" multiple>';
        foreach($cats as $cat) echo '<option value="'.$cat->term_id.'" '.selected($editing?has_term($cat->term_id,'product_cat',$editing->get_id()):false,true,false).'>'.esc_html($cat->name).'</option>';
        echo '</select></label><label class="ppa-store-check"><input type="checkbox" name="published" '.checked($editing?$editing->get_status()==='publish':true,true,false).'> Published</label><label class="ppa-store-check"><input type="checkbox" name="visible" '.checked($editing?$editing->get_catalog_visibility()==='visible':true,true,false).'> Visible in catalogue</label></div><button class="ppa-ca-primary" type="submit">Save Book</button></form></div></section>';
    } elseif($view==='orders') {
        echo '<section class="ppa-ca-store-section"><div class="ppa-ca-store-toolbar"><div><h3>Orders & payment review</h3><p>Review guest orders, COD collection and bKash/Nagad verification. Wallet payments stay on hold until confirmed.</p></div></div><div class="ppa-ca-panel"><div class="ppa-ca-table-wrap"><table class="ppa-ca-table"><thead><tr><th>Order</th><th>Customer</th><th>Items</th><th>Total</th><th>Payment</th><th>Status / action</th></tr></thead><tbody>';
        foreach($orders as $o) {
            echo '<tr><td><strong>#'.esc_html($o->get_order_number()).'</strong><br><small>'.esc_html($o->get_date_created()?$o->get_date_created()->date('d M Y'):'').'</small></td><td>'.esc_html($o->get_formatted_billing_full_name()).'<br>'.esc_html($o->get_billing_phone()).'<br><small>'.esc_html($o->get_billing_email()).'</small></td><td>'.esc_html(wp_trim_words(implode(', ',array_map(function($it){return $it->get_name().' × '.$it->get_quantity();},$o->get_items())),12)).'</td><td>'.wp_kses_post($o->get_formatted_order_total()).'</td><td>'.esc_html($o->get_payment_method_title()).'<br>'.esc_html($o->is_paid()?'Paid':'Awaiting').'<br><small>TX: '.esc_html($o->get_meta('_ppa_wallet_transaction_id')?:'—').'</small></td><td>';
            ppa_store_admin_form('update_order','orders'); echo '<input type="hidden" name="order_id" value="'.$o->get_id().'"><select name="order_status">';
            foreach(array('pending','on-hold','processing','completed','cancelled','refunded','failed') as $s) echo '<option value="'.$s.'" '.selected($o->get_status(),$s,false).'>'.esc_html(wc_get_order_status_name($s)).'</option>';
            echo '</select><button class="ppa-ca-outline-link" type="submit">Update</button>';
            if(!$o->is_paid() && strpos($o->get_payment_method(),'ppa_')===0) echo '<button class="ppa-ca-primary" name="payment_received" value="1">Payment received</button>';
            if(!$o->is_paid() && $o->get_payment_method()==='cod') echo '<button class="ppa-ca-primary" name="cod_collected" value="1">COD collected</button>';
            echo '</form></td></tr>';
        }
        echo '</tbody></table></div></div></section>';
    } elseif($view==='coupons') {
        echo '<section class="ppa-ca-store-section"><div class="ppa-ca-panel" style="padding:22px"><h3>Create coupon</h3>';
        ppa_store_admin_form('create_coupon','coupons');
        echo '<div class="ppa-ca-form-grid"><label>Coupon code<input required name="coupon_code" placeholder="e.g. PHYSICS10"></label><label>Discount type<select name="discount_type"><option value="percent">Percentage</option><option value="fixed_cart">Fixed cart amount</option></select></label><label>Discount amount<input required type="number" min="0.01" step="0.01" name="coupon_amount"></label><label>Usage limit<input type="number" min="0" name="usage_limit" placeholder="Unlimited"></label><label class="is-wide">Description<input name="coupon_description"></label><label class="ppa-store-check"><input type="checkbox" name="individual_use"> Cannot be combined with other coupons</label></div><button class="ppa-ca-primary">Create coupon</button></form></div><div class="ppa-ca-panel" style="padding:18px;margin-top:16px"><h3>Existing coupons</h3><div class="ppa-ca-table-wrap"><table class="ppa-ca-table"><thead><tr><th>Code</th><th>Discount</th><th>Used</th><th>Action</th></tr></thead><tbody>';
        foreach($coupons as $c) { echo '<tr><td>'.esc_html($c->get_code()).'</td><td>'.esc_html($c->get_amount()).($c->get_discount_type()==='percent'?'%':' ৳').'</td><td>'.esc_html($c->get_usage_count()).'</td><td>'; ppa_store_admin_form('delete_coupon','coupons'); echo '<input type="hidden" name="coupon_id" value="'.$c->get_id().'"><button class="ppa-ca-delete" onclick="return confirm(\'Delete coupon?\')">Delete</button></form></td></tr>'; }
        echo '</tbody></table></div></div></section>';
    } elseif($view==='analytics') {
        echo '<section class="ppa-ca-store-section"><div class="ppa-ca-store-toolbar"><div><h3>Sales reports</h3><p>Totals below are calculated from the latest 100 orders loaded.</p></div>';
        ppa_store_admin_form('export_orders','analytics'); echo '<button class="ppa-ca-primary">↓ Export all orders CSV</button></form></div><div class="ppa-ca-report-grid"><article><span>Orders loaded</span><strong>'.count($orders).'</strong></article><article><span>Delivered orders</span><strong>'.$delivered.'</strong></article><article><span>Active / unpaid orders</span><strong>'.$pending.'</strong></article><article><span>Gross order value</span><strong>'.wp_kses_post(wc_price($gross)).'</strong></article></div></section>';
    } else {
        ppa_store_admin_form('save_settings','delivery');
        echo '<section class="ppa-ca-store-section"><div class="ppa-ca-panel" style="padding:22px"><h3>Delivery & payment settings</h3><p>These options control checkout availability and manual mobile-wallet guidance.</p><div class="ppa-ca-form-grid"><label>Default delivery fee (৳)<input type="number" min="0" step="1" name="delivery_fee" value="'.esc_attr(get_option('ppa_store_delivery_fee',0)).'"></label><label>Free delivery threshold (৳)<input type="number" min="0" step="1" name="free_delivery" value="'.esc_attr(get_option('ppa_store_free_delivery',0)).'"></label><label>Wallet receiver number<input name="receiver" value="'.esc_attr(get_option('ppa_store_receiver','01312364499')).'"></label><label class="ppa-store-check"><input type="checkbox" name="cod_enabled" '.checked(get_option('ppa_store_cod_enabled','yes'),'yes',false).'> Enable Cash on Delivery</label><label class="ppa-store-check"><input type="checkbox" name="bkash_enabled" '.checked(get_option('ppa_store_bkash_enabled','yes'),'yes',false).'> Enable bKash</label><label class="ppa-store-check"><input type="checkbox" name="nagad_enabled" '.checked(get_option('ppa_store_nagad_enabled','yes'),'yes',false).'> Enable Nagad</label><label class="is-wide">bKash instructions<textarea rows="3" name="bkash_instructions">'.esc_textarea(get_option('ppa_store_bkash_instructions','Send money to the receiver number and enter the transaction ID below.')).'</textarea></label><label class="is-wide">Nagad instructions<textarea rows="3" name="nagad_instructions">'.esc_textarea(get_option('ppa_store_nagad_instructions','Send money to the receiver number and enter the transaction ID below.')).'</textarea></label></div><button class="ppa-ca-primary">Save store settings</button></div></section></form>';
    }
    echo '<section class="ppa-ca-info-panel"><div class="ppa-ca-info-icon">i</div><div><strong>Connected to WooCommerce</strong><p>Products, stock, orders and coupons are read from WooCommerce. Updates made here are saved to the same WooCommerce records used by the public shop.</p></div></section>';
}
add_action('init',function(){ if(ppa_store_ready()) { update_option('woocommerce_enable_guest_checkout','yes'); update_option('woocommerce_enable_signup_and_login_from_checkout','no'); } },120);
