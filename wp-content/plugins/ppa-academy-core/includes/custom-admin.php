<?php
/**
 * Standalone front-end admin application for Photon Physics Academy.
 * Data is stored in WordPress (CPTs/options); this file never loads wp-admin UI.
 */
if (!defined('ABSPATH')) { exit; }

function ppa_ca_register_types() {
    $types = array(
        'ppa_student' => array('Students','Student'),
        'ppa_batch' => array('Programs & Batches','Batch'),
        'ppa_fee_payment' => array('Payments & Fees','Fee Payment'),
        'ppa_exam_result' => array('Results & Exams','Exam Result'),
    );
    foreach ($types as $type => $labels) {
        register_post_type($type, array(
            'labels'=>array('name'=>__($labels[0],'photon-physics-academy'),'singular_name'=>__($labels[1],'photon-physics-academy')),
            'public'=>false,'show_ui'=>false,'show_in_rest'=>false,
            'supports'=>array('title','editor'),
        ));
    }
}
add_action('init','ppa_ca_register_types',30);

function ppa_ca_tabs() {
    return array(
        'overview'=>array('Dashboard Overview','▦'),
        'students'=>array('Student Management','♙'),
        'payments'=>array('Fees & Payments','৳'),
        'notices'=>array('Notice Management','▣'),
        'programs'=>array('Programs & Batches','▤'),
        'results'=>array('Results & Exams','✓'),
        'sms'=>array('SMS Management','✉'),
        'store'=>array('Store Management','▧'),
        'settings'=>array('Settings','⚙'),
    );
}
function ppa_ca_icon_svg($name) {
    $icons=array(
        'overview'=>'<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/>',
        'students'=>'<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="10" cy="7" r="4"/><path d="M20 8v6m3-3h-6"/>',
        'payments'=>'<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20M6 15h4"/>',
        'notices'=>'<path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M10 21h4"/>',
        'programs'=>'<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',
        'results'=>'<path d="M8 21h8m-4-4v4M7 4h10v4a5 5 0 0 1-10 0V4z"/><path d="M7 6H4v2a4 4 0 0 0 4 4m9-6h3v2a4 4 0 0 1-4 4"/>',
        'sms'=>'<path d="M21 11.5a8.5 8.5 0 0 1-8.5 8.5 9 9 0 0 1-4-.9L3 21l1.9-5.5a9 9 0 0 1-.9-4A8.5 8.5 0 0 1 12.5 3h.5a8.5 8.5 0 0 1 8 8v.5z"/>',
        'store'=>'<path d="M6 7V6a6 6 0 0 1 12 0v1"/><path d="M3 7h18l-1 14H4L3 7z"/><path d="M9 11v1m6-1v1"/>',
        'settings'=>'<circle cx="12" cy="12" r="3"/><path d="m19.4 15 .1.1 1.4 1.1-1.4 2.4-1.7-.6a8 8 0 0 1-1.5.9l-.3 1.8h-2.8l-.3-1.8a8 8 0 0 1-1.5-.9l-1.7.6-1.4-2.4 1.4-1.1a8 8 0 0 1 0-1.8l-1.4-1.1 1.4-2.4 1.7.6a8 8 0 0 1 1.5-.9l.3-1.8h2.8l.3 1.8a8 8 0 0 1 1.5.9l1.7-.6 1.4 2.4-1.4 1.1a8 8 0 0 1 0 1.7z"/>'
    );
    return '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.(isset($icons[$name])?$icons[$name]:'').'</svg>';
}
function ppa_ca_count($type) {
    $counts=wp_count_posts($type);
    return $counts ? (int)$counts->publish : 0;
}
function ppa_ca_redirect($tab,$notice='') {
    $url=add_query_arg(array('ppa_tab'=>$tab),home_url('/admin/'));
    if ($notice) $url=add_query_arg('ppa_notice',$notice,$url);
    wp_safe_redirect($url); exit;
}

/** sms.net.bd API helpers. Credentials remain server-side in wp-config.php. */
function ppa_ca_sms_api_key() {
    return defined('PPA_SMS_NET_BD_API_KEY') ? trim((string) PPA_SMS_NET_BD_API_KEY) : '';
}
function ppa_ca_sms_balance($force = false) {
    $key = ppa_ca_sms_api_key();
    if (!$key) return new WP_Error('ppa_sms_no_key', 'SMS API key is not configured in wp-config.php.');
    if (!$force) { $cached = get_transient('ppa_ca_sms_gateway_balance'); if (is_array($cached)) return $cached; }
    $response = wp_remote_get(add_query_arg('api_key', $key, 'https://api.sms.net.bd/user/balance/'), array('timeout'=>12,'redirection'=>2));
    if (is_wp_error($response)) return $response;
    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (!is_array($data) || !isset($data['error']) || (int)$data['error'] !== 0 || !isset($data['data']['balance'])) return new WP_Error('ppa_sms_balance_failed', is_array($data) ? sanitize_text_field($data['msg'] ?? 'Invalid gateway balance response.') : 'Invalid gateway response.');
    $result = array('balance'=>(string)$data['data']['balance'],'checked_at'=>time());
    set_transient('ppa_ca_sms_gateway_balance',$result,2*MINUTE_IN_SECONDS);
    return $result;
}
function ppa_ca_sms_send_one($phone, $message) {
    $key=ppa_ca_sms_api_key(); if(!$key) return new WP_Error('ppa_sms_no_key','SMS API key is not configured.');
    $phone=preg_replace('/\D+/','',(string)$phone);
    if(strlen($phone)===11 && substr($phone,0,2)==='01') $phone='88'.$phone;
    if(strlen($phone)!==13 || substr($phone,0,3)!=='880') return new WP_Error('ppa_sms_phone','Invalid Bangladesh mobile number.');
    $body=array('api_key'=>$key,'msg'=>$message,'to'=>$phone);
    $sender=get_option('ppa_ca_sms_sender_id',''); if($sender!=='') $body['sender_id']=sanitize_text_field($sender);
    $response=wp_remote_post('https://api.sms.net.bd/sendsms',array('timeout'=>15,'body'=>$body));
    if(is_wp_error($response)) return $response;
    $data=json_decode(wp_remote_retrieve_body($response),true);
    if(!is_array($data)||!isset($data['error'])||(int)$data['error']!==0) return new WP_Error('ppa_sms_send_failed',is_array($data)?sanitize_text_field($data['msg']??('Gateway error '.(int)($data['error']??-1))):'Invalid gateway response.');
    return true;
}

function ppa_ca_handle_post() {
    if (!isset($_POST['ppa_ca_action'])) return;
    $action=sanitize_key(wp_unslash($_POST['ppa_ca_action']));
    if ($action==='login') {
        if (!isset($_POST['ppa_ca_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ppa_ca_nonce'])),'ppa_ca_admin_action')) wp_die('Security check failed.');
        $creds=array(
            'user_login'=>sanitize_text_field(wp_unslash($_POST['log'] ?? '')),
            'user_password'=>wp_unslash($_POST['pwd'] ?? ''),
            'remember'=>!empty($_POST['rememberme']),
        );
        $user=wp_signon($creds,is_ssl());
        if (is_wp_error($user)) ppa_ca_redirect('overview','login_error');
        wp_safe_redirect(add_query_arg('ppa_tab','overview',home_url('/admin/'))); exit;
    }
    if (!is_user_logged_in() || !current_user_can('manage_options')) wp_die('You are not allowed to do this.');
    check_admin_referer('ppa_ca_admin_action','ppa_ca_nonce');
    if ($action==='logout') { wp_logout(); wp_safe_redirect(home_url('/admin/')); exit; }
    $tab=sanitize_key(wp_unslash($_POST['return_tab'] ?? 'overview'));
    if ($action==='add_notice') {
        $title=sanitize_text_field(wp_unslash($_POST['notice_title'] ?? ''));
        $content=sanitize_textarea_field(wp_unslash($_POST['notice_content'] ?? ''));
        $status=isset($_POST['notice_published']) ? 'publish' : 'draft';
        if (!$title || !$content) ppa_ca_redirect('notices','required');
        $notice_id=wp_insert_post(array('post_type'=>'ppa_notice','post_status'=>$status,'post_title'=>$title,'post_content'=>$content),true);
        if (is_wp_error($notice_id)) ppa_ca_redirect('notices','error');
        $category=sanitize_key(wp_unslash($_POST['notice_category'] ?? ''));
        if ($category) {
            $term=get_term_by('slug',$category,'ppa_notice_category');
            if ($term && !is_wp_error($term)) wp_set_object_terms($notice_id,(int)$term->term_id,'ppa_notice_category',false);
        }
        ppa_ca_redirect('notices','created');
    }
    if ($action==='toggle_notice') {
        $notice_id=absint($_POST['notice_id'] ?? 0);
        $notice=get_post($notice_id);
        if($notice && $notice->post_type==='ppa_notice') {
            wp_update_post(array('ID'=>$notice_id,'post_status'=>$notice->post_status==='publish'?'draft':'publish'));
        }
        ppa_ca_redirect('notices','updated');
    }
    if ($action==='delete_notice') {
        $notice_id=absint($_POST['notice_id'] ?? 0);
        $notice=get_post($notice_id);
        if ($notice && $notice->post_type==='ppa_notice') wp_delete_post($notice_id,true);
        ppa_ca_redirect('notices','deleted');
    }

    if ($action === 'send_academy_sms') {
        $message=sanitize_textarea_field(wp_unslash($_POST['sms_message']??''));
        $guardians=isset($_POST['sms_guardians']); $students_selected=isset($_POST['sms_students']);
        $class_filter=sanitize_text_field(wp_unslash($_POST['sms_class']??'')); $batch_filter=sanitize_text_field(wp_unslash($_POST['sms_batch']??''));
        if($message===''||(!$guardians&&!$students_selected)) ppa_ca_redirect('sms','sms_invalid');
        if((function_exists('mb_strlen')?mb_strlen($message,'UTF-8'):strlen($message))>1000) ppa_ca_redirect('sms','sms_too_long');
        $recipients=array();
        foreach(ppa_ca_records('ppa_student',2000) as $student) {
            if($class_filter!==''&&(string)get_post_meta($student->ID,'_ppa_class_level',true)!==$class_filter) continue;
            if($batch_filter!==''&&(string)get_post_meta($student->ID,'_ppa_batch_name',true)!==$batch_filter) continue;
            if($guardians){$phone=get_post_meta($student->ID,'_ppa_guardian_phone',true);if($phone)$recipients[$phone]=$phone;}
            if($students_selected){$phone=get_post_meta($student->ID,'_ppa_phone',true);if($phone)$recipients[$phone]=$phone;}
        }
        if(!$recipients) ppa_ca_redirect('sms','sms_no_recipients');
        if(count($recipients)>100) ppa_ca_redirect('sms','sms_too_many');
        $sent=0;$failed=0;$first_error='';
        foreach($recipients as $phone){$result=ppa_ca_sms_send_one($phone,$message);if(is_wp_error($result)){$failed++;if($first_error==='')$first_error=$result->get_error_message();}else{$sent++;}}
        set_transient('ppa_ca_sms_last_result_'.get_current_user_id(),array('sent'=>$sent,'failed'=>$failed),5*MINUTE_IN_SECONDS);
        if($first_error)set_transient('ppa_ca_sms_last_error_'.get_current_user_id(),$first_error,5*MINUTE_IN_SECONDS);
        delete_transient('ppa_ca_sms_gateway_balance'); ppa_ca_redirect('sms','sms_result');
    }

    if ($action==='save_settings') {
        update_option('ppa_ca_sms_gateway_url',esc_url_raw(wp_unslash($_POST['sms_gateway_url'] ?? '')));
        update_option('ppa_ca_sms_sender_id',sanitize_text_field(wp_unslash($_POST['sms_sender_id'] ?? '')));
        update_option('ppa_ca_slideshow_urls',sanitize_textarea_field(wp_unslash($_POST['slideshow_urls'] ?? '')));
        ppa_ca_redirect('settings','saved');
    }
    if ($action==='add_record') {
        $kind=sanitize_key(wp_unslash($_POST['record_type'] ?? ''));
        $allowed=array('student'=>'ppa_student','batch'=>'ppa_batch','payment'=>'ppa_fee_payment','result'=>'ppa_exam_result');
        if (!isset($allowed[$kind])) ppa_ca_redirect($tab,'invalid');
        $title=sanitize_text_field(wp_unslash($_POST['record_title'] ?? ''));
        $body=sanitize_textarea_field(wp_unslash($_POST['record_details'] ?? ''));
        if (!$title) ppa_ca_redirect($tab,'required');
        $id=wp_insert_post(array('post_type'=>$allowed[$kind],'post_status'=>'publish','post_title'=>$title,'post_content'=>$body),true);
        if (!is_wp_error($id)) {
            foreach (array('student_id','bangla_name','institution','guardian_name','phone','guardian_phone','whatsapp_phone','monthly_fee','batch_name','class_level','schedule_days','time_slot','instructor','billing_month','amount','payment_method','payment_date','exam_name','chapter','marks','total_marks','highest_marks','grade','exam_date','billing_year','payable_amount','paid_amount','due_amount','max_capacity') as $key) {
                if (isset($_POST[$key]) && !($kind==='student' && $key==='student_id')) update_post_meta($id,'_ppa_'.$key,sanitize_text_field(wp_unslash($_POST[$key])));
            }
            if($kind==='student') update_post_meta($id,'_ppa_student_id',ppa_ca_next_student_id(true));
            if($kind==='payment') {
                $paid_value=isset($_POST['paid_amount'])?(float)sanitize_text_field(wp_unslash($_POST['paid_amount'])):0;
                update_post_meta($id,'_ppa_amount',$paid_value);
                update_post_meta($id,'_ppa_receipt_number','PPA-'.wp_date('ym').'-'.str_pad((string)$id,6,'0',STR_PAD_LEFT));
            }
            ppa_ca_redirect($tab,'created');
        }
        ppa_ca_redirect($tab,'error');
    }
    if ($action==='update_record') {
        $id=absint($_POST['record_id'] ?? 0);
        $post=get_post($id);
        if(!$post || !in_array($post->post_type,array('ppa_student','ppa_batch'),true)) ppa_ca_redirect($tab,'invalid');
        $title=sanitize_text_field(wp_unslash($_POST['record_title'] ?? ''));
        $body=sanitize_textarea_field(wp_unslash($_POST['record_details'] ?? ''));
        if($title==='') ppa_ca_redirect($tab,'required');
        wp_update_post(array('ID'=>$id,'post_title'=>$title,'post_content'=>$body));
        $keys=$post->post_type==='ppa_student'?array('bangla_name','institution','guardian_name','phone','guardian_phone','whatsapp_phone','monthly_fee','batch_name','class_level'):array('class_level','max_capacity','schedule_days','time_slot','instructor');
        foreach($keys as $key) if(isset($_POST[$key])) update_post_meta($id,'_ppa_'.$key,sanitize_text_field(wp_unslash($_POST[$key])));
        ppa_ca_redirect($tab,'updated');
    }
    if ($action==='delete_record') {
        $id=absint($_POST['record_id'] ?? 0);
        $post=get_post($id);
        if ($post && current_user_can('manage_options') && in_array($post->post_type,array('ppa_student','ppa_batch','ppa_fee_payment','ppa_exam_result'),true)) wp_delete_post($id,true);
        ppa_ca_redirect($tab,'deleted');
    }
}
add_action('template_redirect','ppa_ca_handle_post',1);

function ppa_ca_login_screen() {
    $error=isset($_GET['ppa_notice']) && $_GET['ppa_notice']==='login_error';
    ob_start(); ?>
    <div class="ppa-ca-login"><form method="post" class="ppa-ca-login-card">
      <div class="ppa-ca-brand-mark"><img src="https://cdn.phototourl.com/member/2026-10-26-00b8c0d9-38d9-4d60-a377-dfd8928013de.png" alt="Photon Physics Academy"></div><div class="ppa-ca-kicker">PHOTON PHYSICS ACADEMY</div>
      <h1>Admin Login</h1><p>Sign in to manage your academy website.</p>
      <?php if($error): ?><div class="ppa-ca-alert is-error">Incorrect username or password. Please try again.</div><?php endif; ?>
      <?php wp_nonce_field('ppa_ca_admin_action','ppa_ca_nonce'); ?>
      <input type="hidden" name="ppa_ca_action" value="login">
      <label>Username or Email<input name="log" type="text" autocomplete="username" required></label>
      <label>Password<input name="pwd" type="password" autocomplete="current-password" required></label>
      <label class="ppa-ca-remember"><input type="checkbox" name="rememberme" value="1"> Remember me</label>
      <button class="ppa-ca-primary" type="submit">Sign in to Admin Panel</button>
      <a class="ppa-ca-back" href="<?php echo esc_url(home_url('/')); ?>">← Back to Website</a>
    </form></div><?php return ob_get_clean();
}
function ppa_ca_records($type,$limit=100) {
    $statuses = $type === 'ppa_notice' ? array('publish','draft') : array('publish');
    return get_posts(array('post_type'=>$type,'post_status'=>$statuses,'numberposts'=>$limit,'orderby'=>'date','order'=>'DESC'));
}
function ppa_ca_next_student_id($reserve=false) {
    $prefix=wp_date('y');
    $highest=(int)get_option('ppa_ca_student_sequence_'.$prefix,0);
    foreach(ppa_ca_records('ppa_student',2000) as $student) {
        $existing=(string)get_post_meta($student->ID,'_ppa_student_id',true);
        if(preg_match('/^'.preg_quote($prefix,'/').'([0-9]{3,4})$/',$existing,$match)) $highest=max($highest,(int)$match[1]);
    }
    $next=$highest+1;
    if($next>9999) return '';
    if($reserve) update_option('ppa_ca_student_sequence_'.$prefix,$next,false);
    return $prefix.sprintf('%04d',$next);
}
/**
 * One-time, idempotent conversion of legacy YYNNN student IDs to YYNNNN.
 * Existing payment and exam-result references are updated to keep records connected.
 */
function ppa_ca_migrate_student_ids_to_six_digits() {
    if(get_option('ppa_ca_student_ids_six_digit_migrated',false)) return;
    $mapping=array();
    $students=get_posts(array('post_type'=>'ppa_student','post_status'=>array('publish','draft','private'),'numberposts'=>-1,'orderby'=>'ID','order'=>'ASC'));
    foreach($students as $student) {
        $old=(string)get_post_meta($student->ID,'_ppa_student_id',true);
        if(preg_match('/^(\\d{2})(\\d{3})$/',$old,$match)) {
            $new=$match[1].sprintf('%04d',(int)$match[2]);
            if($new!==$old) {
                update_post_meta($student->ID,'_ppa_student_id',$new);
                $mapping[$old]=$new;
            }
        }
    }
    if($mapping) {
        foreach(array('ppa_fee_payment','ppa_exam_result') as $type) {
            $records=get_posts(array('post_type'=>$type,'post_status'=>array('publish','draft','private'),'numberposts'=>-1));
            foreach($records as $record) {
                $old_ref=(string)get_post_meta($record->ID,'_ppa_student_id',true);
                if(isset($mapping[$old_ref])) update_post_meta($record->ID,'_ppa_student_id',$mapping[$old_ref]);
            }
        }
    }
    $prefix=wp_date('y');
    $highest=0;
    foreach($students as $student) {
        $id=(string)get_post_meta($student->ID,'_ppa_student_id',true);
        if(preg_match('/^'.preg_quote($prefix,'/').'([0-9]{4})$/',$id,$match)) $highest=max($highest,(int)$match[1]);
    }
    if($highest>(int)get_option('ppa_ca_student_sequence_'.$prefix,0)) update_option('ppa_ca_student_sequence_'.$prefix,$highest,false);
    update_option('ppa_ca_student_ids_six_digit_migrated',1,false);
}
function ppa_ca_table($posts,$type,$tab) {
    if (!$posts) { echo '<div class="ppa-ca-empty">No records yet. Add your first record using the form below.</div>'; return; }
    $columns = array('ppa_student'=>array('Student ID'=>'student_id','Class'=>'class_level','Batch'=>'batch_name','Student Mobile'=>'phone','Guardian Mobile'=>'guardian_phone','WhatsApp'=>'whatsapp_phone','Institution'=>'institution','Monthly Fee'=>'monthly_fee'),'ppa_batch'=>array('Class Level'=>'class_level','Capacity'=>'max_capacity','Schedule Days'=>'schedule_days','Class Time'=>'time_slot','Instructor'=>'instructor'),'ppa_fee_payment'=>array('Receipt No.'=>'receipt_number','Student ID'=>'student_id','Billing Month'=>'billing_month','Year'=>'billing_year','Payable'=>'payable_amount','Paid'=>'paid_amount','Due'=>'due_amount','Method'=>'payment_method','Status'=>'payment_status','Payment Date'=>'payment_date'),'ppa_exam_result'=>array('Student ID'=>'student_id','Exam'=>'exam_name','Chapter'=>'chapter','Total'=>'total_marks','Obtained'=>'marks','Highest'=>'highest_marks','Grade'=>'grade','Exam Date'=>'exam_date'));
    $meta_columns = isset($columns[$type]) ? $columns[$type] : array();
    echo '<div class="ppa-ca-table-wrap"><table class="ppa-ca-table"><thead><tr><th>Name / Title</th>';
    foreach($meta_columns as $label=>$key) echo '<th>'.esc_html($label).'</th>';
    echo '<th>Details</th><th>Created</th><th>Action</th></tr></thead><tbody>';
    foreach($posts as $p) {
        echo '<tr><td><strong>'.esc_html($p->post_title).'</strong></td>';
        foreach($meta_columns as $label=>$key) {
            $value=$key==='record_details' ? wp_trim_words(wp_strip_all_tags($p->post_content),12) : get_post_meta($p->ID,'_ppa_'.$key,true);
            if($key==='monthly_fee' && $value!=='') $value='৳'.number_format_i18n((float)$value);
            if($key==='paid_amount' && $value==='') $value=get_post_meta($p->ID,'_ppa_amount',true);
            if($key==='receipt_number' && $value==='') $value='—';
            if($key==='payment_status') {
                $paid=(float)get_post_meta($p->ID,'_ppa_paid_amount',true);
                if($paid===0.0) $paid=(float)get_post_meta($p->ID,'_ppa_amount',true);
                $payable=(float)get_post_meta($p->ID,'_ppa_payable_amount',true);
                $due=(float)get_post_meta($p->ID,'_ppa_due_amount',true);
                $value=($due<=0 && $payable>0 && $paid>0)?'Paid':(($paid>0)?'Partial':'Due');
            }
            if(in_array($key,array('amount','payable_amount','paid_amount','due_amount'),true) && $value!=='') $value='৳'.number_format_i18n((float)$value);
            if($key==='payment_status') {
                $status_class=$value==='Paid'?'is-paid':($value==='Partial'?'is-partial':'is-due');
                echo '<td><span class="ppa-ca-payment-status '.esc_attr($status_class).'">'.esc_html($value).'</span></td>';
            } else {
                echo '<td>'.esc_html($value!=='' ? $value : '—').'</td>';
            }
        }
        echo '<td>'.esc_html(wp_trim_words(wp_strip_all_tags($p->post_content),16)).'</td><td>'.esc_html(get_the_date('d M Y',$p)).'</td><td>';
        if($type==='ppa_fee_payment') echo '<button type="button" class="ppa-ca-message-button" disabled title="SMS actions will be enabled after gateway integration">Send Message</button> ';
        echo '<form method="post" onsubmit="return confirm(\'Delete this record?\')" style="display:inline-block">';
        wp_nonce_field('ppa_ca_admin_action','ppa_ca_nonce');
        echo '<input type="hidden" name="ppa_ca_action" value="delete_record"><input type="hidden" name="record_id" value="'.(int)$p->ID.'"><input type="hidden" name="return_tab" value="'.esc_attr($tab).'"><button class="ppa-ca-delete" type="submit">Delete</button></form></td></tr>';
    }
    echo '</tbody></table></div>';
}
function ppa_ca_filter_records($posts,$query) {
    $query=strtolower(trim((string)$query));
    if ($query==='') return $posts;
    return array_values(array_filter($posts,function($post) use ($query) {
        $haystack=strtolower($post->post_title.' '.wp_strip_all_tags($post->post_content));
        foreach(array('student_id','guardian_name','guardian_phone','phone','whatsapp_phone','institution','bangla_name','billing_month','billing_year','amount','payable_amount','paid_amount','due_amount','exam_name','chapter','marks','total_marks','highest_marks','grade','class_level','batch_name') as $key) $haystack.=' '.strtolower((string)get_post_meta($post->ID,'_ppa_'.$key,true));
        return strpos($haystack,$query)!==false;
    }));
}
function ppa_ca_search_toolbar($placeholder) {
    $query=isset($_GET['ppa_search'])?sanitize_text_field(wp_unslash($_GET['ppa_search'])):'';
    echo '<form method="get" class="ppa-ca-search-toolbar"><input type="hidden" name="ppa_tab" value="'.esc_attr(isset($_GET['ppa_tab'])?sanitize_key(wp_unslash($_GET['ppa_tab'])):'overview').'"><label class="ppa-ca-search-box"><span>⌕</span><input type="search" name="ppa_search" value="'.esc_attr($query).'" placeholder="'.esc_attr($placeholder).'"></label><button class="ppa-ca-outline-link" type="submit">Search</button>';
    if($query!=='') echo '<a class="ppa-ca-clear-search" href="'.esc_url(add_query_arg('ppa_tab',sanitize_key(wp_unslash($_GET['ppa_tab']??'overview')),home_url('/admin/'))).'">Clear</a>';
    echo '</form>';
}
function ppa_ca_section_intro($title,$description,$count_label='') {
    echo '<div class="ppa-ca-section-head"><div><h2>'.esc_html($title).'</h2><p>'.esc_html($description).'</p></div>';
    if ($count_label!=='') echo '<span class="ppa-ca-section-count">'.esc_html($count_label).'</span>';
    echo '</div>';
}
function ppa_ca_metric_cards($items) {
    echo '<div class="ppa-ca-metric-grid">';
    foreach ($items as $item) echo '<article class="ppa-ca-metric"><span>'.esc_html($item[0]).'</span><strong>'.esc_html($item[1]).'</strong><small>'.esc_html($item[2]).'</small></article>';
    echo '</div>';
}
function ppa_ca_add_form($kind,$title,$fields,$tab) {
    $modal_id='ppa-ca-modal-'.$kind;
    echo '<div class="ppa-ca-modal-launch"><a href="#'.esc_attr($modal_id).'">＋ '.esc_html($title).'</a></div><div class="ppa-ca-modal" id="'.esc_attr($modal_id).'" role="dialog" aria-modal="true" aria-label="'.esc_attr($title).'"><a class="ppa-ca-modal-backdrop" href="#ppa-ca-modal-close" aria-label="Close dialog"></a><div class="ppa-ca-modal-card"><a class="ppa-ca-modal-close" href="#ppa-ca-modal-close" aria-label="Close dialog">×</a><form method="post" class="ppa-ca-form">';
    wp_nonce_field('ppa_ca_admin_action','ppa_ca_nonce');
    echo '<input type="hidden" name="ppa_ca_action" value="add_record"><input type="hidden" name="record_type" value="'.esc_attr($kind).'"><input type="hidden" name="return_tab" value="'.esc_attr($tab).'"><h3>'.esc_html($title).'</h3><div class="ppa-ca-form-grid">';
    foreach($fields as $field=>$label) {
        $wide=in_array($field,array('record_details'),true);
        echo '<label class="'.($wide?'is-wide':'').'">'.esc_html($label);
        if($field==='record_details') {
            echo '<textarea name="record_details" rows="3"></textarea>';
        } elseif($field==='student_id' && $kind==='student') {
            echo '<input name="student_id" type="text" value="'.esc_attr(ppa_ca_next_student_id()).'" readonly aria-readonly="true">';
        } elseif($field==='student_id') {
            $student_list=ppa_ca_records('ppa_student',500);
            $list_id='ppa-student-options-'.esc_attr($kind);
            echo '<input name="student_id" type="search" list="'.$list_id.'" placeholder="Search by student ID..." autocomplete="off"><datalist id="'.$list_id.'">';
            foreach($student_list as $student_option) {
                $code=get_post_meta($student_option->ID,'_ppa_student_id',true);
                if($code!=='') echo '<option value="'.esc_attr($code).'">'.esc_html($student_option->post_title).'</option>';
            }
            echo '</datalist>';
        } elseif($field==='class_level') {
            echo '<select name="class_level" required><option value="">Select class</option><option value="Class 8">Class 8</option><option value="Class 9">Class 9</option><option value="Class 10">Class 10</option></select>';
        } elseif($field==='batch_name') {
            echo '<select name="batch_name"><option value="">No batch assigned</option>';
            foreach(ppa_ca_records('ppa_batch',200) as $batch) echo '<option value="'.esc_attr($batch->post_title).'">'.esc_html($batch->post_title).'</option>';
            echo '</select>';
        } elseif($field==='billing_month') {
            echo '<select name="billing_month" required><option value="">Select month</option>';
            foreach(array('January','February','March','April','May','June','July','August','September','October','November','December') as $month) echo '<option value="'.esc_attr($month).'">'.esc_html($month).'</option>';
            echo '</select>';
        } elseif($field==='payment_method') {
            echo '<select name="payment_method" required><option value="Cash" selected>Cash</option><option value="bKash">bKash</option><option value="Nagad">Nagad</option></select>';
        } elseif($field==='grade') {
            echo '<select name="grade"><option value="">Select grade</option><option>A+</option><option>A</option><option>A-</option><option>B</option><option>C</option><option>F</option></select>';
        } elseif($field==='billing_year') {
            $current_year=(int)wp_date('Y'); echo '<select name="billing_year"><option value="">Select year</option>';
            for($year=$current_year;$year>=$current_year-3;$year--) echo '<option value="'.(int)$year.'" '.selected((string)$current_year,(string)$year,false).'>'.(int)$year.'</option>';
            echo '</select>';
        } elseif(in_array($field,array('monthly_fee','amount','marks','max_capacity','payable_amount','paid_amount','due_amount','total_marks','highest_marks'),true)) {
            echo '<input name="'.esc_attr($field).'" type="number" min="0" step="0.01" '.($field==='amount'?'required':'').' inputmode="decimal">';
        } elseif(in_array($field,array('payment_date','exam_date'),true)) {
            $default_date=$field==='payment_date'?wp_date('Y-m-d'):'';
            echo '<input name="'.esc_attr($field).'" type="date"'.($default_date!==''?' value="'.esc_attr($default_date).'"':'').'>';
        } elseif(in_array($field,array('phone','guardian_phone','whatsapp_phone'),true)) {
            echo '<input name="'.esc_attr($field).'" type="tel" inputmode="tel" placeholder="01XXXXXXXXX" '.($field==='guardian_phone'?'required':'').'>';
        } elseif($field==='record_title') {
            echo '<input name="record_title" type="text" required maxlength="180" placeholder="'.esc_attr($kind==='student'?'Student full name':($kind==='batch'?'Batch name':'Name / title')).'">';
        } else {
            echo '<input name="'.esc_attr($field).'" type="text" '.($field==='record_title'?'required':'').'>';
        }
        echo '</label>';
    }
    echo '</div><div class="ppa-ca-modal-actions"><a href="#ppa-ca-modal-close">Cancel</a><button class="ppa-ca-primary" type="submit">Save Record</button></div></form></div></div>';
}
function ppa_ca_edit_form($record_id,$tab) {
    $post=get_post(absint($record_id));
    if(!$post || !in_array($post->post_type,array('ppa_student','ppa_batch'),true)) return;
    $kind=$post->post_type==='ppa_student'?'student':'batch';
    echo '<div class="ppa-ca-modal" id="ppa-ca-edit-record" role="dialog" aria-modal="true" aria-label="Edit record"><a class="ppa-ca-modal-backdrop" href="#ppa-ca-modal-close" aria-label="Close dialog"></a><div class="ppa-ca-modal-card"><a class="ppa-ca-modal-close" href="#ppa-ca-modal-close" aria-label="Close dialog">×</a><form method="post" class="ppa-ca-form">';
    wp_nonce_field('ppa_ca_admin_action','ppa_ca_nonce');
    echo '<input type="hidden" name="ppa_ca_action" value="update_record"><input type="hidden" name="record_id" value="'.(int)$post->ID.'"><input type="hidden" name="return_tab" value="'.esc_attr($tab).'"><h3>Edit '.($kind==='student'?'Student':'Batch').'</h3><div class="ppa-ca-form-grid"><label>Record Name<input name="record_title" type="text" required value="'.esc_attr($post->post_title).'"></label>';
    if($kind==='student') echo '<label>Student ID<input type="text" value="'.esc_attr(get_post_meta($post->ID,'_ppa_student_id',true)).'" readonly></label>';
    echo '<label>Class Level<select name="class_level"><option value="">Select class</option>';
    foreach(array('Class 8','Class 9','Class 10') as $class) echo '<option value="'.esc_attr($class).'" '.selected(get_post_meta($post->ID,'_ppa_class_level',true),$class,false).'>'.esc_html($class).'</option>';
    echo '</select></label>';
    if($kind==='student') {
        echo '<label>Batch<select name="batch_name"><option value="">No batch assigned</option>';
        foreach(ppa_ca_records('ppa_batch',200) as $batch) echo '<option value="'.esc_attr($batch->post_title).'" '.selected(get_post_meta($post->ID,'_ppa_batch_name',true),$batch->post_title,false).'>'.esc_html($batch->post_title).'</option>';
        echo '</select></label>';
        foreach(array('bangla_name'=>'Student Name (Bangla)','institution'=>'Institution','phone'=>'Student Mobile','guardian_name'=>'Guardian Name','guardian_phone'=>'Guardian Mobile','whatsapp_phone'=>'WhatsApp Number','monthly_fee'=>'Monthly Fee (৳)') as $key=>$label) {
            $type=in_array($key,array('phone','guardian_phone','whatsapp_phone'),true)?'tel':($key==='monthly_fee'?'number':'text');
            echo '<label>'.esc_html($label).'<input name="'.esc_attr($key).'" type="'.esc_attr($type).'" value="'.esc_attr(get_post_meta($post->ID,'_ppa_'.$key,true)).'"></label>';
        }
    } else {
        echo '<label>Maximum Capacity<input name="max_capacity" type="number" min="0" value="'.esc_attr(get_post_meta($post->ID,'_ppa_max_capacity',true)).'"></label>';
        foreach(array('schedule_days'=>'Schedule Days','time_slot'=>'Class Time','instructor'=>'Instructor') as $key=>$label) echo '<label>'.esc_html($label).'<input name="'.esc_attr($key).'" type="text" value="'.esc_attr(get_post_meta($post->ID,'_ppa_'.$key,true)).'"></label>';
    }
    echo '<label class="is-wide">Details<textarea name="record_details" rows="4">'.esc_textarea($post->post_content).'</textarea></label></div><div class="ppa-ca-modal-actions"><a href="#ppa-ca-modal-close">Cancel</a><button class="ppa-ca-primary" type="submit">Save Changes</button></div></form></div></div>';
}
function ppa_ca_styles() { ?>
<style>
html{margin-top:0!important}html body.ppa-ca-body{margin:0!important;padding:0!important;background:#f8fafc!important;color:#1e293b}body.ppa-ca-body #wpadminbar{display:none!important}body.ppa-ca-body .ppa-ca-app{margin:0!important;padding:0!important}
.ppa-ca-shell{min-height:100vh;display:flex;font-family:Arial,'Hind Siliguri',sans-serif;color:#1e293b;background:#f8fafc}.ppa-ca-sidebar{width:256px;flex:0 0 256px;height:100vh;position:sticky;top:0;background:#0f172a;color:#cbd5e1;display:flex;flex-direction:column;border-right:1px solid #1e293b}.ppa-ca-brand{height:64px;padding:0 16px;border-bottom:1px solid #1e293b;display:flex;align-items:center;gap:12px}.ppa-ca-brand-mark{width:36px;height:36px;border-radius:9px;background:#fff;color:#1d4ed8;font-weight:900;display:grid;place-items:center;overflow:hidden}.ppa-ca-brand-mark img{width:100%;height:100%;object-fit:contain}.ppa-ca-brand strong{display:block;color:white;font-size:14px;letter-spacing:.5px}.ppa-ca-brand small{font-size:11px;color:#94a3b8}.ppa-ca-nav{padding:12px;overflow:auto;flex:1}.ppa-ca-nav a{display:flex;gap:12px;align-items:center;padding:11px 12px;margin:3px 0;border-radius:8px;color:#cbd5e1;text-decoration:none;font-size:13px;font-weight:600}.ppa-ca-nav a:hover{background:#1e293b;color:white}.ppa-ca-nav a.is-active{background:#2563eb;color:white}.ppa-ca-nav i{font-style:normal;width:17px;text-align:center}.ppa-ca-side-bottom{padding:12px;border-top:1px solid #1e293b}.ppa-ca-side-bottom a{display:block;text-align:center;padding:10px;background:#1e293b;border-radius:8px;color:#e2e8f0;text-decoration:none;font-size:12px}
.ppa-ca-main{min-width:0;flex:1;display:flex;flex-direction:column}.ppa-ca-header{height:64px;position:sticky;top:0;z-index:5;background:#fff;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;padding:0 24px}.ppa-ca-header h1{font-size:18px;margin:0;color:#0f172a}.ppa-ca-user{display:flex;align-items:center;gap:10px;font-size:12px}.ppa-ca-avatar{width:32px;height:32px;border-radius:8px;background:#dbeafe;color:#1d4ed8;display:grid;place-items:center;font-weight:bold}.ppa-ca-logout{border:1px solid #e2e8f0;border-radius:8px;padding:8px 12px;background:white;color:#334155;font-weight:600;cursor:pointer}.ppa-ca-content{width:100%;max-width:1280px;margin:0 auto;padding:28px}.ppa-ca-welcome{margin:0 0 22px}.ppa-ca-welcome p{color:#64748b;margin:6px 0}.ppa-ca-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin:20px 0 28px}.ppa-ca-card,.ppa-ca-panel{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;box-shadow:0 4px 16px rgba(16,43,78,.035)}.ppa-ca-card span{font-size:12px;color:#64748b;font-weight:600}.ppa-ca-card strong{display:block;font-size:30px;margin-top:8px;color:#102b4e}.ppa-ca-panel{margin:18px 0}.ppa-ca-panel h2{font-size:17px;margin:0 0 15px}.ppa-ca-table-wrap{overflow:auto}.ppa-ca-table{width:100%;border-collapse:collapse;font-size:13px}.ppa-ca-table th,.ppa-ca-table td{text-align:left;padding:12px;border-bottom:1px solid #e2e8f0;vertical-align:top}.ppa-ca-table th{color:#64748b;font-size:11px;text-transform:uppercase;letter-spacing:.04em}.ppa-ca-empty{padding:22px;border:1px dashed #cbd5e1;border-radius:10px;color:#64748b;font-size:13px}.ppa-ca-form{background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;margin:20px 0}.ppa-ca-form h3{margin:0 0 16px;font-size:16px}.ppa-ca-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px;margin-bottom:16px}.ppa-ca-form label,.ppa-ca-login-card label{display:flex;flex-direction:column;gap:6px;font-size:12px;font-weight:700;color:#475569}.ppa-ca-form label.is-wide{grid-column:1/-1}.ppa-ca-form input,.ppa-ca-form textarea,.ppa-ca-login-card input:not([type=checkbox]){width:100%;border:1px solid #cbd5e1;border-radius:8px;padding:10px;font:inherit;background:#fff;color:#0f172a}.ppa-ca-primary{background:#2563eb;color:white;border:0;border-radius:8px;padding:11px 16px;font-size:13px;font-weight:700;cursor:pointer}.ppa-ca-delete{border:0;background:#fff1f2;color:#be123c;border-radius:6px;padding:7px 10px;cursor:pointer}.ppa-ca-shortcuts{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px}.ppa-ca-shortcuts a{border:1px solid #e2e8f0;border-radius:10px;padding:16px;text-decoration:none;color:#0f172a;font-weight:700;background:white}.ppa-ca-shortcuts a span{display:block;color:#64748b;font-weight:400;font-size:12px;margin-top:6px}.ppa-ca-alert{padding:12px 14px;border-radius:9px;margin-bottom:16px;font-size:13px;background:#eff6ff;color:#1d4ed8}.ppa-ca-alert.is-error{background:#fff1f2;color:#be123c}.ppa-ca-login{min-height:100vh;display:grid;place-items:center;padding:20px;background:#f8fafc;font-family:Arial,sans-serif}.ppa-ca-login-card{width:100%;max-width:410px;background:white;border:1px solid #e2e8f0;border-radius:18px;padding:30px;box-shadow:0 15px 45px rgba(15,23,42,.08)}.ppa-ca-login-card .ppa-ca-brand-mark{margin-bottom:15px}.ppa-ca-kicker{font-size:10px;letter-spacing:1.5px;color:#64748b;font-weight:bold}.ppa-ca-login-card h1{font-size:25px;margin:8px 0}.ppa-ca-login-card p{font-size:13px;color:#64748b;margin:0 0 22px}.ppa-ca-login-card label{margin:14px 0}.ppa-ca-login-card .ppa-ca-remember{display:flex;flex-direction:row;align-items:center;font-weight:400}.ppa-ca-login-card .ppa-ca-remember input{width:auto}.ppa-ca-login-card .ppa-ca-primary{width:100%;margin-top:6px}.ppa-ca-back{display:block;text-align:center;margin-top:18px;color:#64748b;text-decoration:none;font-size:12px}.ppa-ca-mobile-toggle{display:none}
@media(max-width:900px){.ppa-ca-sidebar{width:220px;flex-basis:220px}.ppa-ca-content{padding:20px}.ppa-ca-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.ppa-ca-shortcuts{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:680px){.ppa-ca-shell{display:block}.ppa-ca-sidebar{width:100%;height:auto;position:relative}.ppa-ca-nav{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));padding:8px}.ppa-ca-nav a{font-size:11px;padding:9px}.ppa-ca-side-bottom{display:none}.ppa-ca-header{height:58px;padding:0 14px}.ppa-ca-header h1{font-size:15px}.ppa-ca-user .ppa-ca-user-label{display:none}.ppa-ca-content{padding:16px}.ppa-ca-grid{gap:10px}.ppa-ca-card{padding:14px}.ppa-ca-card strong{font-size:24px}.ppa-ca-form-grid{grid-template-columns:1fr}.ppa-ca-form label.is-wide{grid-column:auto}.ppa-ca-shortcuts{grid-template-columns:1fr}}
</style>
<?php }

/**
 * UI refinement layer. Kept separate from the data model so the interface
 * can be reviewed before the WordPress data schema is finalized.
 */
function ppa_ca_enhanced_styles() { ?>
<style>
.ppa-ca-shell{font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",Arial,sans-serif;background:#f1f5f9}
.ppa-ca-sidebar{box-shadow:5px 0 24px rgba(15,23,42,.08);z-index:30}
.ppa-ca-brand{height:72px;padding:0 18px}
.ppa-ca-brand-mark{width:40px;height:40px;background:#fff;border-radius:10px;padding:2px}
.ppa-ca-brand-mark img{mix-blend-mode:multiply}
.ppa-ca-brand strong{font-size:14px;letter-spacing:.8px}
.ppa-ca-nav{padding:18px 12px}
.ppa-ca-nav a{min-height:42px;margin:4px 0;padding:11px 13px;border:1px solid transparent;transition:background .18s,border-color .18s,transform .18s}
.ppa-ca-nav a:hover{transform:translateX(2px);background:#1e293b}
.ppa-ca-nav a.is-active{background:linear-gradient(110deg,#2563eb,#1d4ed8);box-shadow:0 5px 14px rgba(37,99,235,.25);border-color:rgba(147,197,253,.2)}
.ppa-ca-nav a i{font-size:16px;opacity:.95}
.ppa-ca-side-bottom{padding:14px}
.ppa-ca-side-bottom a{padding:11px;border:1px solid #334155;transition:.18s}
.ppa-ca-side-bottom a:hover{background:#334155}
.ppa-ca-header{height:72px;padding:0 30px;box-shadow:0 2px 8px rgba(15,23,42,.025)}
.ppa-ca-header h1{font-size:18px;font-weight:750;letter-spacing:-.02em}
.ppa-ca-user{gap:12px}
.ppa-ca-avatar{width:36px;height:36px;border-radius:10px;font-size:12px}
.ppa-ca-content{max-width:1360px;padding:30px 32px 48px}
.ppa-ca-welcome{padding:2px 0 3px}
.ppa-ca-welcome h2{font-size:23px;font-weight:800;letter-spacing:-.035em;color:#0f172a}
.ppa-ca-welcome p{font-size:13px}
.ppa-ca-grid{gap:18px;margin:22px 0 28px}
.ppa-ca-card,.ppa-ca-panel,.ppa-ca-form{border-radius:14px;border-color:#e2e8f0;box-shadow:0 2px 8px rgba(15,23,42,.025);transition:box-shadow .18s,border-color .18s}
.ppa-ca-card{padding:21px 22px;position:relative;overflow:hidden}
.ppa-ca-card:after{content:"";position:absolute;right:-24px;top:-28px;width:90px;height:90px;border-radius:50%;background:#eff6ff;opacity:.75}
.ppa-ca-card span{position:relative;z-index:1;font-size:12px;letter-spacing:.01em}
.ppa-ca-card strong{position:relative;z-index:1;font-size:32px;letter-spacing:-.04em;color:#0f2b50}
.ppa-ca-panel{padding:22px;margin:20px 0}
.ppa-ca-panel h2{font-size:15px;font-weight:750;letter-spacing:-.01em}
.ppa-ca-table{font-size:12px}
.ppa-ca-table th{background:#f8fafc;color:#64748b;font-size:10px;letter-spacing:.07em;padding:13px 14px}
.ppa-ca-table td{padding:14px;color:#475569}
.ppa-ca-table tbody tr:hover{background:#f8fbff}
.ppa-ca-table td strong{color:#0f172a;font-weight:700}
.ppa-ca-empty{background:#f8fafc;text-align:center;padding:30px}
.ppa-ca-form{padding:23px}
.ppa-ca-form h3{font-size:16px;font-weight:750}
.ppa-ca-form label{font-size:11px;letter-spacing:.01em;gap:7px}
.ppa-ca-form input,.ppa-ca-form textarea,.ppa-ca-form select{box-sizing:border-box;min-height:40px;border-color:#cbd5e1;border-radius:9px;padding:10px 12px;font-size:13px;transition:border-color .15s,box-shadow .15s}
.ppa-ca-form input:focus,.ppa-ca-form textarea:focus,.ppa-ca-form select:focus,.ppa-ca-login-card input:focus{outline:0;border-color:#3b82f6;box-shadow:0 0 0 3px rgba(59,130,246,.12)}
.ppa-ca-primary{border-radius:9px;padding:11px 17px;box-shadow:0 3px 8px rgba(37,99,235,.15);transition:background .15s,transform .15s,box-shadow .15s}
.ppa-ca-primary:hover{background:#1d4ed8;transform:translateY(-1px);box-shadow:0 5px 12px rgba(37,99,235,.2)}
.ppa-ca-shortcuts{gap:13px}
.ppa-ca-shortcuts a{border-radius:12px;padding:17px;transition:.18s}
.ppa-ca-shortcuts a:hover{border-color:#93c5fd;background:#f8fbff;transform:translateY(-2px);box-shadow:0 7px 18px rgba(15,23,42,.05)}
.ppa-ca-alert{border:1px solid #bfdbfe}
.ppa-ca-login{background:radial-gradient(ellipse at top,#eff6ff 0%,#f8fafc 56%)}
.ppa-ca-login-card{border-radius:20px;padding:34px;box-shadow:0 22px 65px rgba(15,23,42,.10)}
.ppa-ca-login-card .ppa-ca-brand-mark{width:52px;height:52px;padding:3px;border:1px solid #dbeafe}
.ppa-ca-login-card .ppa-ca-brand-mark img{width:100%;height:100%;object-fit:contain}
.ppa-ca-login-card h1{font-size:27px;letter-spacing:-.04em}
.ppa-ca-login-card input:not([type=checkbox]){box-sizing:border-box;min-height:44px;border-radius:9px}
.ppa-ca-login-card .ppa-ca-primary{min-height:44px}
.ppa-ca-mobile-toggle{display:none}
@media(max-width:900px){
 .ppa-ca-sidebar{width:236px;flex-basis:236px}
 .ppa-ca-content{padding:24px}
 .ppa-ca-header{padding:0 22px}
}
@media(max-width:680px){
 .ppa-ca-shell{display:flex;min-height:100vh;position:relative}
 .ppa-ca-sidebar{position:fixed;left:0;top:0;bottom:0;height:100dvh;width:min(290px,84vw);flex-basis:auto;transform:translateX(-105%);transition:transform .22s ease;box-shadow:12px 0 40px rgba(15,23,42,.2)}
 .ppa-ca-shell.is-nav-open .ppa-ca-sidebar{transform:translateX(0)}
 .ppa-ca-shell.is-nav-open:after{content:"";position:fixed;inset:0;background:rgba(15,23,42,.48);z-index:25}
 .ppa-ca-nav{display:block;padding:14px 12px;overflow:auto}
 .ppa-ca-nav a{font-size:13px;padding:12px 13px}
 .ppa-ca-side-bottom{display:block}
 .ppa-ca-main{width:100%;min-height:100vh}
 .ppa-ca-header{height:62px;padding:0 14px;gap:10px;z-index:20}
 .ppa-ca-header h1{font-size:15px;flex:1}
 .ppa-ca-mobile-toggle{display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;border:1px solid #e2e8f0;border-radius:9px;background:#fff;color:#334155;font-size:19px;cursor:pointer}
 .ppa-ca-user{gap:7px}
 .ppa-ca-avatar{width:32px;height:32px}
 .ppa-ca-logout{padding:7px 9px;font-size:11px}
 .ppa-ca-content{padding:18px 14px 32px}
 .ppa-ca-welcome h2{font-size:20px}
 .ppa-ca-grid{gap:10px;margin:16px 0 20px}
 .ppa-ca-card{padding:15px}
 .ppa-ca-card strong{font-size:25px}
 .ppa-ca-panel{padding:16px;margin:14px 0}
 .ppa-ca-form{padding:16px;margin:14px 0}
 .ppa-ca-form-grid{grid-template-columns:1fr;gap:12px}
 .ppa-ca-form label.is-wide{grid-column:auto}
 .ppa-ca-shortcuts{grid-template-columns:1fr}
 .ppa-ca-table{min-width:640px}
 .ppa-ca-login-card{padding:25px}
}
@media(prefers-reduced-motion:reduce){.ppa-ca-sidebar,.ppa-ca-nav a,.ppa-ca-shortcuts a,.ppa-ca-primary{transition:none}}
</style>
<?php }
function ppa_ca_section_styles() { ?><style>
.ppa-ca-notice-row form{margin-left:auto;flex:0 0 auto}.ppa-ca-notice-row time{font-size:10px;color:#64748b;white-space:nowrap}.ppa-ca-check{display:flex!important;flex-direction:row!important;align-items:center!important;gap:9px!important}.ppa-ca-check input{width:auto!important;min-height:0!important}.ppa-ca-form select{box-sizing:border-box;min-height:40px;border:1px solid #cbd5e1;border-radius:9px;padding:10px 12px;background:#fff;font-size:13px}.ppa-ca-notice-copy{min-width:0;flex:1}.ppa-ca-notice-copy strong,.ppa-ca-notice-copy span{display:block}.ppa-ca-notice-copy span{font-size:11px;color:#64748b;margin-top:4px}.ppa-ca-section-head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin:2px 0 20px}
.ppa-ca-section-head h2{font-size:21px;font-weight:800;letter-spacing:-.035em;color:#0f172a;margin:0}
.ppa-ca-section-head p{font-size:12px;color:#64748b;margin:5px 0 0}
.ppa-ca-section-count,.ppa-ca-soft-badge{font-size:11px;font-weight:700;color:#1d4ed8;background:#eff6ff;border:1px solid #dbeafe;border-radius:999px;padding:7px 11px;white-space:nowrap}
.ppa-ca-panel-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;margin-bottom:15px}
.ppa-ca-panel-head h2{margin:0 0 4px}
.ppa-ca-panel-head p{font-size:11px;color:#64748b;margin:0}
.ppa-ca-notice-list{display:grid;gap:0}
.ppa-ca-notice-row{display:flex;align-items:center;gap:13px;padding:14px 4px;border-top:1px solid #f1f5f9}
.ppa-ca-notice-icon,.ppa-ca-store-icon,.ppa-ca-info-icon{width:38px;height:38px;flex:0 0 38px;border-radius:11px;background:#eff6ff;color:#2563eb;display:grid;place-items:center;font-size:17px;font-weight:800}
.ppa-ca-notice-copy{min-width:0;flex:1;display:grid;gap:4px}
.ppa-ca-notice-copy strong{font-size:12px;color:#0f172a}
.ppa-ca-notice-copy span{font-size:11px;color:#64748b;overflow:hidden;text-overflow:ellipsis}
.ppa-ca-notice-row time{font-size:10px;color:#94a3b8;white-space:nowrap}
.ppa-ca-info-panel{display:flex;align-items:flex-start;gap:13px;padding:18px 20px;border:1px solid #dbeafe;background:#f8fbff;border-radius:13px;margin:18px 0}
.ppa-ca-info-icon{background:#dbeafe;color:#1d4ed8;font-size:15px}
.ppa-ca-info-panel strong{font-size:12px;color:#1e3a8a}
.ppa-ca-info-panel p{font-size:11px;line-height:1.7;color:#475569;margin:5px 0 0}
.ppa-ca-store-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.ppa-ca-store-card{display:flex;align-items:flex-start;gap:14px;padding:19px;background:white;border:1px solid #e2e8f0;border-radius:13px}
.ppa-ca-store-icon{width:42px;height:42px;flex-basis:42px;font-size:19px}
.ppa-ca-store-card h3{font-size:13px;color:#0f172a;font-weight:750;margin:1px 0 5px}
.ppa-ca-store-card p{font-size:11px;line-height:1.6;color:#64748b;margin:0 0 10px}
.ppa-ca-coming-tag{font-size:10px;color:#64748b;background:#f1f5f9;border-radius:5px;padding:4px 7px;display:inline-block}
.ppa-ca-sms-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px}
.ppa-ca-gateway-status{display:flex;align-items:flex-start;gap:10px;padding:15px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;margin:16px 0}
.ppa-ca-status-dot{width:9px;height:9px;border-radius:50%;background:#f59e0b;margin-top:4px;box-shadow:0 0 0 4px #fef3c7}
.ppa-ca-gateway-status strong,.ppa-ca-gateway-status small{display:block}
.ppa-ca-gateway-status strong{font-size:12px;color:#334155}
.ppa-ca-gateway-status small{font-size:10px;color:#64748b;margin-top:4px;line-height:1.5}
.ppa-ca-field-label{display:block;font-size:11px;font-weight:700;color:#475569;margin:12px 0}
.ppa-ca-field-label select,.ppa-ca-field-label textarea{display:block;width:100%;box-sizing:border-box;margin-top:6px;padding:10px;border:1px solid #cbd5e1;border-radius:9px;font:inherit;background:#f8fafc}
.ppa-ca-primary:disabled{opacity:.5;cursor:not-allowed;transform:none}
.ppa-ca-outline-link{font-size:11px;font-weight:700;text-decoration:none;color:#1d4ed8;border:1px solid #bfdbfe;border-radius:8px;padding:9px 12px;white-space:nowrap}
@media(max-width:680px){.ppa-ca-section-head{align-items:flex-start;flex-direction:column}.ppa-ca-section-head h2{font-size:19px}.ppa-ca-store-grid,.ppa-ca-sms-grid{grid-template-columns:1fr}.ppa-ca-notice-row{align-items:flex-start}.ppa-ca-notice-row time{padding-top:3px}.ppa-ca-info-panel{padding:15px}}
/* Fine details for content cards and compact responsive layouts. */
.ppa-ca-stat-top span{display:block;font-size:12px;font-weight:500;color:#64748b}
.ppa-ca-card strong{display:block;margin-top:0;font-size:26px;line-height:1.25;font-weight:700;color:#0f172a}
.ppa-ca-recent-panel{padding:20px}
.ppa-ca-recent-panel .ppa-ca-panel-head{margin-bottom:12px}
.ppa-ca-notice-row{padding:15px 3px}
.ppa-ca-notice-icon{width:38px;height:38px;flex:0 0 38px;border-radius:10px;background:#eff6ff;color:#2563eb;display:grid;place-items:center;font-size:15px}
.ppa-ca-notice-copy strong{font-size:13px}
.ppa-ca-notice-copy span{font-size:11px;line-height:1.5}
.ppa-ca-notice-row time{font-size:10px;color:#94a3b8}
.ppa-ca-store-card{padding:20px}
.ppa-ca-store-card h3{font-size:13px;font-weight:750}
.ppa-ca-store-card p{font-size:11px;line-height:1.65}
.ppa-ca-gateway-status{border-radius:11px}
.ppa-ca-field-label{font-size:12px;font-weight:650}
@media(max-width:680px){.ppa-ca-card strong{font-size:24px}.ppa-ca-recent-panel{padding:16px}.ppa-ca-notice-row{gap:9px}.ppa-ca-notice-row time{white-space:normal}}
/* Section-level parity: toolbar, filters, cards, tables, empty states and modal surfaces. */
.ppa-ca-section-head{margin:0 0 20px;align-items:center}
.ppa-ca-section-head h2{font-size:21px;font-weight:800;letter-spacing:-.035em}
.ppa-ca-section-head p{font-size:12px;line-height:1.6}
.ppa-ca-section-count,.ppa-ca-soft-badge{font-size:11px;padding:6px 10px;border-radius:999px}
.ppa-ca-panel-head{align-items:center;margin-bottom:15px}
.ppa-ca-panel-head h2{font-size:15px;font-weight:700}
.ppa-ca-panel-head p{font-size:11px;color:#64748b;line-height:1.5}
.ppa-ca-form{margin:20px 0;background:#fff;border:1px solid #e2e8f0;border-radius:12px;padding:22px}
.ppa-ca-form h3{font-size:18px;font-weight:750;color:#0f172a;margin:0 0 17px}
.ppa-ca-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}
.ppa-ca-form-grid label{display:flex;flex-direction:column;gap:6px;font-size:12px;font-weight:650;color:#334155}
.ppa-ca-form-grid label.is-wide{grid-column:1/-1}
.ppa-ca-form input,.ppa-ca-form select,.ppa-ca-form textarea{width:100%;min-height:42px;padding:10px 12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:9px;color:#0f172a;font:inherit;font-size:13px}
.ppa-ca-form textarea{min-height:110px;line-height:1.6}
.ppa-ca-form input:focus,.ppa-ca-form select:focus,.ppa-ca-form textarea:focus{outline:0;border-color:#3b82f6;background:#fff;box-shadow:0 0 0 3px rgba(59,130,246,.12)}
.ppa-ca-form .ppa-ca-primary{margin-top:17px}
.ppa-ca-primary{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:40px;padding:10px 16px;background:#2563eb;color:#fff;border:0;border-radius:9px;font-size:12px;font-weight:700;text-decoration:none;cursor:pointer}
.ppa-ca-primary:hover{background:#1d4ed8}
.ppa-ca-table-wrap{overflow-x:auto;border:1px solid #f1f5f9;border-radius:9px}
.ppa-ca-table{width:100%;border-collapse:collapse;font-size:12px}
.ppa-ca-table th{padding:13px 14px;background:#f8fafc;color:#64748b;font-size:10px;font-weight:700;letter-spacing:.04em;text-align:left;text-transform:uppercase;white-space:nowrap}
.ppa-ca-table td{padding:13px 14px;border-top:1px solid #f1f5f9;color:#475569;vertical-align:middle}
.ppa-ca-table tbody tr:hover{background:#f8fbff}
.ppa-ca-table td strong{color:#0f172a;font-weight:700}
.ppa-ca-empty{padding:34px 18px;border:1px dashed #e2e8f0;border-radius:10px;background:#f8fafc;color:#64748b;font-size:12px;line-height:1.8;text-align:center}
.ppa-ca-shortcuts{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}
.ppa-ca-shortcuts a{display:flex;align-items:center;justify-content:center;min-height:54px;padding:12px;border:1px solid #e2e8f0;border-radius:9px;background:#f8fafc;color:#334155;font-size:12px;font-weight:650;text-align:center;text-decoration:none}
.ppa-ca-shortcuts a:hover{border-color:#bfdbfe;background:#eff6ff;color:#1d4ed8}
.ppa-ca-stat-top{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:10px}
.ppa-ca-stat-top i{display:grid;place-items:center;width:32px;height:32px;border-radius:9px;background:#eff6ff;color:#2563eb;font-size:16px;font-style:normal}
.ppa-ca-overview-columns{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:20px}
.ppa-ca-recent-panel{min-width:0}
.ppa-ca-view-all{font-size:11px;font-weight:700;color:#1d4ed8;text-decoration:none;white-space:nowrap}
.ppa-ca-recent-list{display:grid}
.ppa-ca-recent-row{display:flex;align-items:center;gap:11px;padding:12px 2px;border-top:1px solid #f1f5f9}
.ppa-ca-recent-icon{display:grid;place-items:center;width:34px;height:34px;flex:0 0 34px;border-radius:9px;background:#eff6ff;color:#2563eb;font-size:15px}
.ppa-ca-recent-row div{display:grid;gap:3px;min-width:0;flex:1}
.ppa-ca-recent-row strong{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#0f172a;font-size:12px}
.ppa-ca-recent-row small,.ppa-ca-recent-row time{font-size:10px;color:#94a3b8}
.ppa-ca-recent-row time{white-space:nowrap}
.ppa-ca-notice-row{gap:12px}
.ppa-ca-notice-row form{margin-left:auto}
.ppa-ca-delete{padding:7px 10px;border:1px solid #fecdd3;border-radius:7px;background:#fff1f2;color:#be123c;font-size:11px;font-weight:650;cursor:pointer}
.ppa-ca-store-card{min-height:132px;border-radius:12px;box-shadow:none}
.ppa-ca-coming-tag{font-size:10px;border-radius:6px;padding:5px 8px}
.ppa-ca-sms-grid{gap:18px}
.ppa-ca-info-panel{border-radius:12px}
@media(max-width:900px){.ppa-ca-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.ppa-ca-shortcuts{grid-template-columns:repeat(2,minmax(0,1fr))}}
@media(max-width:680px){.ppa-ca-form{padding:16px}.ppa-ca-form-grid{grid-template-columns:1fr;gap:12px}.ppa-ca-form-grid label.is-wide{grid-column:auto}.ppa-ca-overview-columns{grid-template-columns:1fr}.ppa-ca-shortcuts{grid-template-columns:repeat(2,minmax(0,1fr))}.ppa-ca-section-head{align-items:flex-start;flex-direction:column}.ppa-ca-section-head h2{font-size:19px}.ppa-ca-notice-row{align-items:flex-start}.ppa-ca-notice-row time{padding-top:3px}.ppa-ca-store-grid,.ppa-ca-sms-grid{grid-template-columns:1fr}}
/* Main-branch visual parity pass: keep the custom WordPress panel aligned with the React admin shell. */
.ppa-ca-shell{font-family:'Plus Jakarta Sans',Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f8fafc;color:#1e293b}
.ppa-ca-sidebar{width:256px;flex:0 0 256px;height:100vh;background:#0f172a;box-shadow:none;border-right:1px solid #1e293b}
.ppa-ca-brand{height:64px;min-height:64px;padding:0 16px;gap:12px;border-bottom-color:#1e293b}
.ppa-ca-brand-mark{width:36px;height:36px;flex:0 0 36px;padding:3px;border-radius:9px}
.ppa-ca-brand strong{font-size:14px;letter-spacing:-.02em}
.ppa-ca-brand small{font-size:11px}
.ppa-ca-nav{padding:12px;gap:0}
.ppa-ca-nav a{min-height:40px;margin:4px 0;padding:10px 12px;gap:12px;border:0;border-radius:8px;font-size:13px;font-weight:600;transition:background-color .16s,color .16s}
.ppa-ca-nav a:hover{transform:none;background:#1e293b;color:#fff}
.ppa-ca-nav a.is-active{background:#2563eb;color:#fff;box-shadow:none;border:0}
.ppa-ca-nav a i{width:16px;flex:0 0 16px;font-size:15px}
.ppa-ca-side-bottom{padding:12px;border-top:1px solid #1e293b}
.ppa-ca-side-bottom a{padding:10px;border:0;border-radius:8px;background:#1e293b;font-size:12px}
.ppa-ca-header{height:64px;min-height:64px;padding:0 24px;box-shadow:none;border-bottom:1px solid #e2e8f0}
.ppa-ca-header h1{font-size:18px;font-weight:700;letter-spacing:0;color:#0f172a}
.ppa-ca-user{gap:12px}
.ppa-ca-avatar{width:32px;height:32px;border-radius:8px;font-size:12px}
.ppa-ca-logout,.ppa-ca-public-site{display:inline-flex;align-items:center;justify-content:center;gap:7px;min-height:34px;padding:7px 11px;border:1px solid #e2e8f0;border-radius:8px;background:#fff;color:#334155;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap;transition:background .15s,color .15s,border-color .15s}
.ppa-ca-logout:hover,.ppa-ca-public-site:hover{background:#f0f9ff;color:#1d4ed8;border-color:#bfdbfe}
.ppa-ca-content{max-width:1360px;padding:28px 28px 48px}
.ppa-ca-welcome{margin-bottom:18px}
.ppa-ca-welcome h2{font-size:23px;font-weight:800;letter-spacing:-.035em}
.ppa-ca-welcome p{font-size:13px;color:#64748b}
.ppa-ca-grid{grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin:20px 0 28px}
.ppa-ca-card{min-height:112px;padding:18px 20px;border-radius:12px;box-shadow:none;border-color:#e2e8f0}
.ppa-ca-card:after{display:none}
.ppa-ca-card span{font-size:12px;font-weight:500;color:#64748b;letter-spacing:0}
.ppa-ca-card strong{font-size:26px;font-weight:700;letter-spacing:0;color:#0f172a}
.ppa-ca-panel,.ppa-ca-form{border-radius:12px;box-shadow:none;border-color:#e2e8f0}
.ppa-ca-panel{padding:20px;margin:18px 0}
.ppa-ca-panel h2{font-size:15px;font-weight:700}
.ppa-ca-table{font-size:12px}
.ppa-ca-table th{padding:12px 14px;background:#f8fafc;font-size:10px}
.ppa-ca-table td{padding:13px 14px}
.ppa-ca-form{padding:20px}
.ppa-ca-form input,.ppa-ca-form textarea,.ppa-ca-form select{font-family:inherit;font-size:13px}
.ppa-ca-primary{min-height:38px}
.ppa-ca-mobile-toggle{display:none}
@media(max-width:1023px){.ppa-ca-sidebar{width:256px;flex-basis:256px}.ppa-ca-content{padding:24px}}
@media(max-width:680px){
 .ppa-ca-shell{display:flex;min-height:100vh;position:relative}
 .ppa-ca-sidebar{position:fixed;left:0;top:0;bottom:0;height:100dvh;width:min(288px,84vw);flex:0 0 min(288px,84vw);transform:translateX(-105%);transition:transform .22s ease;z-index:50;box-shadow:12px 0 40px rgba(15,23,42,.2)}
 .ppa-ca-shell.is-nav-open .ppa-ca-sidebar{transform:translateX(0)}
 .ppa-ca-shell.is-nav-open:after{content:"";position:fixed;inset:0;background:rgba(15,23,42,.52);z-index:40}
 .ppa-ca-nav{display:block;padding:14px 12px;overflow-y:auto}
 .ppa-ca-nav a{font-size:13px;padding:11px 12px}
 .ppa-ca-side-bottom{display:block}
 .ppa-ca-main{width:100%;min-height:100vh}
 .ppa-ca-header{height:62px;min-height:62px;padding:0 14px;gap:9px;z-index:35}
 .ppa-ca-header h1{font-size:15px;flex:1}
 .ppa-ca-mobile-toggle{display:inline-flex;align-items:center;justify-content:center;flex:0 0 36px;width:36px;height:36px;border:1px solid #e2e8f0;border-radius:9px;background:#fff;color:#334155;font-size:19px}
 .ppa-ca-user{gap:7px}
 .ppa-ca-user .ppa-ca-user-label{display:none}
 .ppa-ca-avatar{width:32px;height:32px}
 .ppa-ca-logout{min-height:34px;padding:7px 9px;font-size:11px}
 .ppa-ca-public-site{display:none}
 .ppa-ca-content{padding:18px 14px 32px}
 .ppa-ca-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin:16px 0 20px}
 .ppa-ca-card{min-height:96px;padding:14px}
 .ppa-ca-card strong{font-size:24px}
 .ppa-ca-panel,.ppa-ca-form{padding:16px;margin:14px 0}
 .ppa-ca-form-grid{grid-template-columns:1fr;gap:12px}
 .ppa-ca-form label.is-wide{grid-column:auto}
 .ppa-ca-shortcuts{grid-template-columns:1fr}
 .ppa-ca-table{min-width:640px}
}
@media(max-width:360px){.ppa-ca-header{padding:0 10px}.ppa-ca-header h1{font-size:14px}.ppa-ca-user{gap:5px}.ppa-ca-logout{padding:6px 7px}.ppa-ca-grid{gap:8px}.ppa-ca-card{padding:12px}}
@media(prefers-reduced-motion:reduce){.ppa-ca-sidebar,.ppa-ca-nav a,.ppa-ca-primary{transition:none!important}}
</style><?php }
function ppa_ca_admin_script() { ?>
<script>
document.addEventListener('DOMContentLoaded',function(){
 const shell=document.querySelector('.ppa-ca-shell');
 const toggle=document.querySelector('.ppa-ca-mobile-toggle');
 if(toggle&&shell){toggle.addEventListener('click',function(){shell.classList.toggle('is-nav-open');toggle.setAttribute('aria-expanded',shell.classList.contains('is-nav-open')?'true':'false');});}
 document.querySelectorAll('.ppa-ca-nav a').forEach(function(link){link.addEventListener('click',function(){if(shell)shell.classList.remove('is-nav-open');});});
 document.addEventListener('keydown',function(e){if(e.key==='Escape'&&shell)shell.classList.remove('is-nav-open');});
 if(shell){shell.addEventListener('click',function(e){if(e.target===shell&&shell.classList.contains('is-nav-open'))shell.classList.remove('is-nav-open');});}
});
</script>
<?php }

function ppa_ca_visual_parity_styles() { ?><style>
.ppa-ca-modal-launch{display:flex;justify-content:flex-end;margin:16px 0}.ppa-ca-modal-launch>a{display:inline-flex;align-items:center;justify-content:center;min-height:38px;padding:10px 15px;border-radius:9px;background:#2563eb;color:#fff;text-decoration:none;font-size:12px;font-weight:750;box-shadow:0 3px 8px rgba(37,99,235,.15)}.ppa-ca-modal-launch>a:hover{background:#1d4ed8}.ppa-ca-modal{display:none;position:fixed;inset:0;z-index:100;align-items:center;justify-content:center;padding:16px;background:rgba(15,23,42,.58);backdrop-filter:blur(3px)}.ppa-ca-modal:target{display:flex}.ppa-ca-modal-backdrop{position:absolute;inset:0}.ppa-ca-modal-card{position:relative;z-index:1;width:100%;max-width:520px;max-height:90vh;overflow-y:auto}.ppa-ca-modal-card .ppa-ca-form{margin:0;padding:24px;box-shadow:0 24px 70px rgba(15,23,42,.22)}.ppa-ca-modal-card .ppa-ca-form h3{padding-right:32px}.ppa-ca-modal-close{position:absolute;z-index:2;right:30px;top:29px;display:grid;place-items:center;width:30px;height:30px;border-radius:8px;background:#f1f5f9;color:#64748b;text-decoration:none;font-size:22px;line-height:1}.ppa-ca-modal-close:hover{background:#e2e8f0;color:#0f172a}.ppa-ca-modal-actions{display:flex;align-items:center;justify-content:flex-end;gap:10px;margin-top:17px;padding-top:14px;border-top:1px solid #f1f5f9}.ppa-ca-modal-actions>a{padding:10px 14px;border-radius:8px;background:#f1f5f9;color:#475569;text-decoration:none;font-size:12px;font-weight:650}.ppa-ca-modal-actions .ppa-ca-primary{margin:0}
@media(max-width:560px){.ppa-ca-modal{padding:10px}.ppa-ca-modal-card .ppa-ca-form{padding:19px}.ppa-ca-modal-close{right:24px;top:24px}}
.ppa-ca-payment-status{display:inline-block;padding:5px 8px;border-radius:6px;font-size:10px;font-weight:750;white-space:nowrap}.ppa-ca-payment-status.is-paid{background:#ecfdf5;color:#047857}.ppa-ca-payment-status.is-partial{background:#fffbeb;color:#b45309}.ppa-ca-payment-status.is-due{background:#fff7ed;color:#c2410c}.ppa-ca-message-button{padding:6px 8px;border:1px solid #dbeafe;border-radius:7px;background:#eff6ff;color:#1d4ed8;font-size:10px;font-weight:700;opacity:.55;cursor:not-allowed}
.ppa-ca-program-enrollment{margin-top:16px;padding-top:13px;border-top:1px solid #f1f5f9}.ppa-ca-program-enrollment>div:first-child{display:flex;align-items:center;justify-content:space-between;gap:8px}.ppa-ca-program-enrollment strong{color:#334155;font-size:11px}.ppa-ca-program-enrollment>div:first-child span{color:#94a3b8;font-size:10px}.ppa-ca-program-progress{height:7px;margin:9px 0 6px;border-radius:99px;background:#f1f5f9;overflow:hidden}.ppa-ca-program-progress span{display:block;height:100%;border-radius:99px;background:linear-gradient(90deg,#2563eb,#38bdf8)}.ppa-ca-program-enrollment small{color:#059669;font-size:10px;font-weight:700}.ppa-ca-program-enrollment small.is-full{color:#be123c}
.ppa-ca-notice-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}.ppa-ca-notice-card{display:flex;flex-direction:column;min-width:0;padding:18px;border:1px solid #e2e8f0;border-radius:12px;background:#fff}.ppa-ca-notice-card-top{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:13px}.ppa-ca-notice-category{padding:5px 8px;border:1px solid #dbeafe;border-radius:6px;background:#eff6ff;color:#1d4ed8;font-size:10px;font-weight:750}.ppa-ca-notice-card time{color:#94a3b8;font-size:10px}.ppa-ca-notice-card h3{margin:0 0 7px;color:#0f172a;font-size:14px;font-weight:800;line-height:1.5}.ppa-ca-notice-card>p{flex:1;margin:0 0 16px;color:#64748b;font-size:11px;line-height:1.7}.ppa-ca-notice-card-actions{display:flex;align-items:center;gap:8px;padding-top:12px;border-top:1px solid #f1f5f9}.ppa-ca-notice-card-actions form{margin:0}.ppa-ca-notice-status{margin-right:auto;padding:5px 8px;border-radius:6px;font-size:10px;font-weight:750}.ppa-ca-notice-status.is-published{background:#ecfdf5;color:#047857}.ppa-ca-notice-status.is-draft{background:#f1f5f9;color:#64748b}.ppa-ca-notice-toggle{padding:7px 10px;border:1px solid #bfdbfe;border-radius:7px;background:#eff6ff;color:#1d4ed8;font-size:10px;font-weight:750;cursor:pointer}.ppa-ca-notice-toggle:hover{background:#dbeafe}
@media(max-width:680px){.ppa-ca-notice-grid{grid-template-columns:1fr}}
.ppa-ca-store-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin:0 0 20px}.ppa-ca-store-heading h2{margin:4px 0 5px;color:#0f172a;font-size:24px;font-weight:850;letter-spacing:-.04em}.ppa-ca-store-heading p{margin:0;color:#64748b;font-size:12px;line-height:1.6}.ppa-ca-store-kicker{font-size:10px!important;font-weight:800!important;letter-spacing:.16em;color:#1d4ed8!important}.ppa-ca-store-refresh{display:inline-flex;align-items:center;gap:7px;padding:9px 13px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;color:#64748b;font-size:12px;font-weight:700}.ppa-ca-store-metrics{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:12px;margin-bottom:20px}.ppa-ca-store-metrics article,.ppa-ca-report-grid article{padding:15px 16px;border:1px solid #e2e8f0;border-radius:12px;background:#fff}.ppa-ca-store-metrics article>div{display:flex;align-items:center;justify-content:space-between;gap:8px}.ppa-ca-store-metrics span,.ppa-ca-report-grid span{color:#64748b;font-size:11px;font-weight:650}.ppa-ca-store-metrics i{display:grid;place-items:center;width:29px;height:29px;border-radius:9px;background:#eff6ff;color:#2563eb;font-size:14px;font-style:normal}.ppa-ca-store-metrics strong,.ppa-ca-report-grid strong{display:block;margin-top:9px;color:#0f172a;font-size:24px;font-weight:850}.ppa-ca-store-tabs{display:flex;flex-wrap:wrap;gap:8px;padding:0 0 15px;margin:0 0 18px;border-bottom:1px solid #e2e8f0}.ppa-ca-store-tabs a{display:inline-flex;align-items:center;padding:10px 13px;border:1px solid #e2e8f0;border-radius:10px;background:#fff;color:#64748b;text-decoration:none;font-size:12px;font-weight:700;white-space:nowrap}.ppa-ca-store-tabs a.is-active{border-color:#0f172a;background:#0f172a;color:#fff}.ppa-ca-store-section{display:grid;gap:14px}.ppa-ca-store-toolbar{display:flex;align-items:center;justify-content:space-between;gap:14px}.ppa-ca-store-toolbar h3,.ppa-ca-store-settings h3{margin:0;color:#0f172a;font-size:14px;font-weight:800}.ppa-ca-store-toolbar p,.ppa-ca-store-settings p{margin:5px 0 0;color:#64748b;font-size:11px;line-height:1.6}.ppa-ca-store-search{display:flex;align-items:center;gap:9px;flex:1;max-width:420px;padding:0 12px;border:1px solid #cbd5e1;border-radius:10px;background:#fff;color:#94a3b8}.ppa-ca-store-search input{width:100%;min-width:0;padding:10px 0;border:0;outline:0;background:transparent;font-size:12px}.ppa-ca-store-toolbar select{min-width:190px;padding:10px 12px;border:1px solid #cbd5e1;border-radius:9px;background:#fff;color:#64748b;font-size:12px}.ppa-ca-store-table{padding:0;overflow:hidden}.ppa-ca-store-table .ppa-ca-table-wrap{border:0}.ppa-ca-store-table .ppa-ca-empty{margin:14px}.ppa-ca-dark-button{background:#0f172a!important}.ppa-ca-report-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px}.ppa-ca-store-help{margin:0;color:#475569;font-size:12px;line-height:1.7}.ppa-ca-store-settings{display:grid;gap:18px;max-width:820px;margin:0}.ppa-ca-store-form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:15px}.ppa-ca-store-form-grid label{display:grid;gap:7px;color:#334155;font-size:11px;font-weight:700}.ppa-ca-store-form-grid label.is-wide{grid-column:1/-1}.ppa-ca-store-form-grid input,.ppa-ca-store-form-grid textarea{box-sizing:border-box;width:100%;min-height:40px;padding:10px 12px;border:1px solid #e2e8f0;border-radius:9px;background:#f8fafc;color:#64748b;font:inherit;font-size:12px}.ppa-ca-store-form-grid textarea{min-height:84px}.ppa-ca-store-form-grid input:disabled,.ppa-ca-store-form-grid textarea:disabled{opacity:1;color:#94a3b8;background:#f8fafc}.ppa-ca-store-settings .ppa-ca-primary:disabled,.ppa-ca-store-toolbar .ppa-ca-primary:disabled{opacity:.55;cursor:not-allowed;transform:none;box-shadow:none}
@media(max-width:1100px){.ppa-ca-store-metrics{grid-template-columns:repeat(3,minmax(0,1fr))}}
@media(max-width:680px){.ppa-ca-store-heading{align-items:flex-start;flex-direction:column}.ppa-ca-store-heading h2{font-size:21px}.ppa-ca-store-metrics{grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}.ppa-ca-store-metrics article{padding:13px}.ppa-ca-store-metrics strong{font-size:21px}.ppa-ca-store-tabs{flex-wrap:nowrap;overflow-x:auto}.ppa-ca-store-toolbar{align-items:stretch;flex-direction:column}.ppa-ca-store-search{max-width:none;min-height:42px}.ppa-ca-store-toolbar select{width:100%}.ppa-ca-report-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.ppa-ca-store-form-grid{grid-template-columns:1fr}.ppa-ca-store-form-grid label.is-wide{grid-column:auto}}
.ppa-ca-nav a i{display:inline-flex;align-items:center;justify-content:center}.ppa-ca-nav a i svg{display:block;width:16px;height:16px;flex:0 0 16px}
.ppa-ca-balance-card{display:flex;align-items:center;gap:13px;padding:17px;border-radius:12px;background:#f0fdf4;border:1px solid #dcfce7;margin:15px 0}.ppa-ca-balance-icon{width:42px;height:42px;display:grid;place-items:center;border-radius:12px;background:#dcfce7;color:#15803d;font-size:19px;font-weight:900}.ppa-ca-balance-card small,.ppa-ca-balance-card em{display:block;color:#64748b;font-size:10px;font-style:normal}.ppa-ca-balance-card strong{display:block;margin:3px 0;color:#14532d;font-size:18px}.ppa-ca-recipient-options{display:grid;gap:9px;padding:13px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px}.ppa-ca-recipient-options small{color:#94a3b8;font-weight:400}.ppa-ca-recipient-count{display:flex;align-items:center;gap:10px;padding:12px;border-radius:10px;background:#eff6ff;color:#1e40af}.ppa-ca-recipient-count strong{font-size:20px}.ppa-ca-recipient-count span{font-size:10px;color:#64748b}.ppa-ca-message-footer{display:flex;justify-content:space-between;align-items:center;gap:12px;margin-top:14px}.ppa-ca-message-footer>span{font-size:10px;color:#94a3b8}.ppa-ca-store-tabs{display:flex;gap:5px;overflow-x:auto;padding:5px;margin:0 0 18px;border-bottom:1px solid #e2e8f0}.ppa-ca-store-tabs a{white-space:nowrap;padding:10px 13px;border-radius:9px 9px 0 0;color:#64748b;text-decoration:none;font-size:12px;font-weight:700;border-bottom:2px solid transparent}.ppa-ca-store-tabs a:hover{background:#f8fafc;color:#1e40af}.ppa-ca-store-tabs a.is-active{color:#2563eb;border-bottom-color:#2563eb;background:#eff6ff}.ppa-ca-store-workspace{margin-top:18px}.ppa-ca-store-empty{display:grid;justify-items:center;text-align:center;padding:36px 20px;border:1px dashed #cbd5e1;border-radius:12px;background:#f8fafc}.ppa-ca-store-empty>span{width:48px;height:48px;display:grid;place-items:center;border-radius:14px;background:#eff6ff;color:#2563eb;font-size:22px}.ppa-ca-store-empty strong{margin-top:12px;color:#0f172a;font-size:14px}.ppa-ca-store-empty p{max-width:430px;margin:5px 0 0;color:#64748b;font-size:12px;line-height:1.6}
@media(max-width:680px){.ppa-ca-message-footer{align-items:flex-start;flex-direction:column}.ppa-ca-store-tabs a{padding:9px 10px;font-size:11px}.ppa-ca-recipient-count{align-items:flex-start}}

.ppa-ca-search-toolbar{display:flex;align-items:center;gap:9px;padding:14px 18px;border-bottom:1px solid #eef2f7;background:#fff}.ppa-ca-search-box{display:flex;align-items:center;gap:9px;flex:1;min-width:0;border:1px solid #cbd5e1;border-radius:9px;padding:0 12px;background:#fff;color:#94a3b8}.ppa-ca-search-box span{font-size:18px}.ppa-ca-search-box input{width:100%;min-width:0;border:0!important;outline:0!important;box-shadow:none!important;padding:10px 0!important;font-size:12px!important}.ppa-ca-clear-search{font-size:11px;color:#64748b;text-decoration:none;white-space:nowrap}

/* Main-branch section layout parity */
.ppa-ca-metric-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin:0 0 20px}
.ppa-ca-metric{background:#fff;border:1px solid #e2e8f0;border-radius:13px;padding:17px 18px;box-shadow:0 2px 8px rgba(15,23,42,.025)}
.ppa-ca-metric span,.ppa-ca-metric small{display:block;color:#64748b;font-size:11px;font-weight:650}.ppa-ca-metric strong{display:block;margin:8px 0 3px;font-size:25px;line-height:1.2;font-weight:850;letter-spacing:-.035em;color:#0f172a}.ppa-ca-metric small{font-weight:400}
.ppa-ca-data-panel{padding:0;overflow:hidden}.ppa-ca-data-panel>.ppa-ca-panel-head{padding:20px 22px 16px;border-bottom:1px solid #eef2f7}.ppa-ca-data-panel .ppa-ca-table-wrap{padding:0 8px 8px}
.ppa-ca-program-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin:0 0 20px}.ppa-ca-program-card{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:20px;box-shadow:0 2px 8px rgba(15,23,42,.025)}.ppa-ca-program-top{display:flex;align-items:center;justify-content:space-between}.ppa-ca-program-icon{width:42px;height:42px;border-radius:12px;background:#eff6ff;color:#2563eb;display:grid;place-items:center;font-size:19px}.ppa-ca-program-card h3{font-size:17px;font-weight:800;color:#0f172a;margin:15px 0}.ppa-ca-program-meta{display:grid;gap:9px;color:#64748b;font-size:12px}.ppa-ca-program-foot{border-top:1px solid #f1f5f9;margin-top:17px;padding-top:13px;color:#64748b;font-size:11px}.ppa-ca-program-foot span{display:block;margin-top:5px;color:#334155;font-size:12px}
.ppa-ca-settings-grid{display:grid;grid-template-columns:1.2fr .8fr;gap:18px;align-items:start}.ppa-ca-settings-form{margin:0;padding:0;border:0;box-shadow:none}.ppa-ca-settings-preview{margin:0}.ppa-ca-settings-note{padding:15px;border-radius:10px;background:#f8fafc;border:1px solid #e2e8f0;color:#64748b;font-size:12px;line-height:1.7}
.ppa-ca-store-banner{display:flex;align-items:center;justify-content:space-between;gap:20px;padding:25px 28px;margin:0 0 20px;border-radius:16px;background:linear-gradient(115deg,#102b50,#1d4ed8);color:white;overflow:hidden}.ppa-ca-store-banner span{font-size:10px;letter-spacing:1.8px;font-weight:800;color:#bfdbfe}.ppa-ca-store-banner h3{font-size:21px;font-weight:850;margin:7px 0}.ppa-ca-store-banner p{font-size:12px;color:#dbeafe;margin:0}.ppa-ca-store-banner-mark{width:66px;height:66px;border:1px solid rgba(255,255,255,.3);border-radius:18px;display:grid;place-items:center;font-size:19px;font-weight:900;color:#fff;background:rgba(255,255,255,.1)}.ppa-ca-store-card{position:relative;min-height:150px;padding:21px 48px 20px 20px}.ppa-ca-store-card h3{font-size:15px;font-weight:800;color:#0f172a}.ppa-ca-store-card p{font-size:12px;line-height:1.6;color:#64748b}.ppa-ca-store-arrow{position:absolute;right:18px;top:20px;color:#94a3b8;font-size:17px}
@media(max-width:900px){.ppa-ca-settings-grid{grid-template-columns:1fr}}
@media(max-width:680px){.ppa-ca-metric-grid{grid-template-columns:1fr;gap:10px}.ppa-ca-metric{padding:14px 16px}.ppa-ca-program-grid{grid-template-columns:1fr}.ppa-ca-store-banner{padding:20px}.ppa-ca-store-banner h3{font-size:18px}.ppa-ca-store-banner-mark{width:50px;height:50px}.ppa-ca-data-panel>.ppa-ca-panel-head{padding:16px}}
</style><?php }

function ppa_ca_store_preview($store_view) {
    if (function_exists('ppa_store_render_admin')) { ppa_store_render_admin($store_view); return; }
    echo '<div class="ppa-ca-info-panel"><strong>Store integration is loading.</strong></div>';
}

function ppa_ca_polished_ui_styles() { ?>
<style>
/* Subtle admin UI refinement: keep the existing dashboard structure and controls. */
body.ppa-ca-body{font-family:Inter,'Plus Jakarta Sans','Hind Siliguri',Arial,sans-serif!important;-webkit-font-smoothing:antialiased}
.ppa-ca-shell{background:#f4f7fb}
.ppa-ca-sidebar{background:linear-gradient(180deg,#10233d 0%,#0b1a2e 100%);border-right-color:#1b3554}
.ppa-ca-brand{height:72px;border-bottom-color:rgba(255,255,255,.09)}
.ppa-ca-brand-mark{border-radius:11px;box-shadow:0 4px 12px rgba(0,0,0,.12)}
.ppa-ca-nav{padding:16px 12px}
.ppa-ca-nav a{min-height:42px;border-radius:9px;font-size:13px;transition:background .18s ease,color .18s ease,transform .18s ease}
.ppa-ca-nav a:hover{transform:translateX(2px);background:rgba(255,255,255,.08)}
.ppa-ca-nav a.is-active{background:linear-gradient(110deg,#2563eb,#1d4ed8);box-shadow:0 5px 14px rgba(37,99,235,.22)}
.ppa-ca-side-bottom{border-top-color:rgba(255,255,255,.1)}
.ppa-ca-header{height:72px;padding:0 30px;border-bottom-color:#e7edf4;box-shadow:0 3px 12px rgba(15,35,60,.025)}
.ppa-ca-header h1{font-size:19px;letter-spacing:-.025em}
.ppa-ca-user{gap:12px;font-size:13px}
.ppa-ca-avatar{border-radius:10px}
.ppa-ca-content{padding:30px}
.ppa-ca-welcome h2{letter-spacing:-.035em;color:#14243a}
.ppa-ca-welcome p{color:#66778d}
.ppa-ca-card,.ppa-ca-panel,.ppa-ca-metric,.ppa-ca-program-card,.ppa-ca-store-card,.ppa-ca-info-panel{border-color:#e3eaf2!important;border-radius:14px!important;box-shadow:0 4px 16px rgba(20,45,75,.035)!important}
.ppa-ca-card,.ppa-ca-metric,.ppa-ca-program-card,.ppa-ca-store-card{transition:transform .18s ease,box-shadow .18s ease,border-color .18s ease}
.ppa-ca-card:hover,.ppa-ca-metric:hover,.ppa-ca-program-card:hover,.ppa-ca-store-card:hover{transform:translateY(-2px);border-color:#d1deec!important;box-shadow:0 10px 24px rgba(20,45,75,.07)!important}
.ppa-ca-panel{padding:22px}
.ppa-ca-panel h2,.ppa-ca-panel-head h2{letter-spacing:-.02em}
.ppa-ca-table th{background:#f8fafc;color:#64748b;font-size:11px;letter-spacing:.055em}
.ppa-ca-table td{color:#33465d}
.ppa-ca-table tbody tr:hover{background:#f8fbff}
.ppa-ca-primary,.ppa-ca-logout,.ppa-ca-public-site,.ppa-ca-view-all,.ppa-ca-shortcuts a{border-radius:9px;transition:background .18s ease,box-shadow .18s ease,transform .18s ease}
.ppa-ca-primary{box-shadow:0 4px 12px rgba(37,99,235,.14)}
.ppa-ca-primary:hover{transform:translateY(-1px);box-shadow:0 7px 16px rgba(37,99,235,.2)}
.ppa-ca-content input,.ppa-ca-content select,.ppa-ca-content textarea,.ppa-ca-modal input,.ppa-ca-modal select,.ppa-ca-modal textarea{border-radius:8px}
.ppa-ca-content input:focus,.ppa-ca-content select:focus,.ppa-ca-content textarea:focus,.ppa-ca-modal input:focus,.ppa-ca-modal select:focus,.ppa-ca-modal textarea:focus{outline:0;border-color:#8bb2ed;box-shadow:0 0 0 3px rgba(37,99,235,.1)}
.ppa-ca-store-banner{border-radius:16px;box-shadow:0 10px 25px rgba(16,43,78,.12)}
@media(max-width:680px){.ppa-ca-header{height:64px;padding:0 16px}.ppa-ca-content{padding:18px}.ppa-ca-panel{padding:17px}}
</style>
<?php }

function ppa_ca_render_page() {
    status_header(200); nocache_headers();
    $user=wp_get_current_user();
    $tab=isset($_GET['ppa_tab'])?sanitize_key(wp_unslash($_GET['ppa_tab'])):'overview';
    $tabs=ppa_ca_tabs(); if(!isset($tabs[$tab])) $tab='overview';
    echo '<!doctype html><html '.get_language_attributes().'><head><meta charset="'.esc_attr(get_bloginfo('charset')).'"><meta name="viewport" content="width=device-width, initial-scale=1">';
    wp_head();
    echo '</head><body class="ppa-ca-body"><div class="ppa-ca-app">';
    ppa_ca_styles();
    ppa_ca_enhanced_styles();
    ppa_ca_section_styles();
    ppa_ca_visual_parity_styles();
    ppa_ca_polished_ui_styles();
    if(!is_user_logged_in() || !current_user_can('manage_options')) {
        echo ppa_ca_login_screen();
    } else {
        ppa_ca_migrate_student_ids_to_six_digits();
        echo '<div class="ppa-ca-shell"><aside class="ppa-ca-sidebar"><div class="ppa-ca-brand"><div class="ppa-ca-brand-mark"><img src="https://cdn.phototourl.com/member/2026-10-26-00b8c0d9-38d9-4d60-a377-dfd8928013de.png" alt="Photon Physics Academy"></div><div><strong>PHOTON</strong><small>Admin Panel</small></div></div><nav class="ppa-ca-nav">';
        foreach($tabs as $key=>$item) echo '<a class="'.($tab===$key?'is-active':'').'" href="'.esc_url(add_query_arg('ppa_tab',$key,home_url('/admin/'))).'"><i>'.ppa_ca_icon_svg($key).'</i><span>'.esc_html($item[0]).'</span></a>';
        echo '</nav><div class="ppa-ca-side-bottom"><a href="'.esc_url(home_url('/')).'">← Back to Website</a></div></aside>';
        echo '<div class="ppa-ca-main"><header class="ppa-ca-header"><button type="button" class="ppa-ca-mobile-toggle" aria-label="Open admin navigation" aria-expanded="false">☰</button><h1>'.esc_html($tabs[$tab][0]).'</h1><div class="ppa-ca-user"><div class="ppa-ca-avatar">'.esc_html('JS').'</div><span class="ppa-ca-user-label">'.esc_html('Jewel Sir').' · Administrator</span><form method="post">';
        wp_nonce_field('ppa_ca_admin_action','ppa_ca_nonce');
        echo '<input type="hidden" name="ppa_ca_action" value="logout"><button class="ppa-ca-logout" type="submit"><span aria-hidden="true">↪</span> <span>Log out</span></button></form><a class="ppa-ca-public-site" href="'.esc_url(home_url('/')).'" target="_blank" rel="noopener"><span aria-hidden="true">↗</span><span>Public Site</span></a></div></header><main class="ppa-ca-content">';
        if(isset($_GET['ppa_notice']) && $_GET['ppa_notice']!=='login_error') echo '<div class="ppa-ca-alert">'.esc_html(sanitize_text_field(wp_unslash($_GET['ppa_notice']))==='saved'?'Settings saved.':'Action completed.').'</div>';
        if($tab==='overview') {
            $student_total=ppa_ca_count('ppa_student');
            $active_batch_total=ppa_ca_count('ppa_batch');
            $dashboard_payments=ppa_ca_records('ppa_fee_payment',500);
            $collected_total=0;
            $pending_total=0;
            $has_due_data=false;
            foreach($dashboard_payments as $dashboard_payment) {
                $paid_value=get_post_meta($dashboard_payment->ID,'_ppa_paid_amount',true);
                if($paid_value==='') $paid_value=get_post_meta($dashboard_payment->ID,'_ppa_amount',true);
                $collected_total+=(float)$paid_value;
                $due_value=get_post_meta($dashboard_payment->ID,'_ppa_due_amount',true);
                if($due_value!=='') { $has_due_data=true; $pending_total+=(float)$due_value; }
            }
            echo '<div class="ppa-ca-welcome"><h2>Welcome back, '.esc_html($user->display_name).'!</h2><p>Here is what is happening at Photon Physics Academy.</p></div>';
            echo '<div class="ppa-ca-grid">';
            $dashboard_cards=array(
                array('Total Students',number_format_i18n($student_total),'♙','blue'),
                array('Collected','৳'.number_format_i18n($collected_total),'৳','emerald'),
                array('Pending Due',$has_due_data?'৳'.number_format_i18n($pending_total):'—','!','amber'),
                array('Active Batches',number_format_i18n($active_batch_total),'▤','sky')
            );
            foreach($dashboard_cards as $card) {
                echo '<article class="ppa-ca-card"><div class="ppa-ca-stat-top"><span>'.esc_html($card[0]).'</span><i class="tone-'.esc_attr($card[3]).'">'.esc_html($card[2]).'</i></div><strong>'.esc_html($card[1]).'</strong></article>';
            }
            echo '</div><section class="ppa-ca-panel ppa-ca-quick-panel"><h2>Quick Actions</h2><div class="ppa-ca-shortcuts">';
            foreach(array('students'=>'＋ Add Student','payments'=>'▤ Record Payment','notices'=>'♧ Publish Notice','results'=>'★ Add Exam Marks') as $key=>$label) {
                echo '<a href="'.esc_url(add_query_arg('ppa_tab',$key,home_url('/admin/'))).'">'.esc_html($label).'</a>';
            }
            echo '</div></section><div class="ppa-ca-overview-columns">';
            $recent_students=ppa_ca_records('ppa_student',4);
            echo '<section class="ppa-ca-panel ppa-ca-recent-panel"><div class="ppa-ca-panel-head"><div><h2>Recent Students</h2><p>Recently enrolled students</p></div><a class="ppa-ca-view-all" href="'.esc_url(add_query_arg('ppa_tab','students',home_url('/admin/'))).'">View all ↗</a></div>';
            if($recent_students) {
                echo '<div class="ppa-ca-table-wrap"><table class="ppa-ca-table"><thead><tr><th>ID</th><th>Name</th><th>Class</th><th>Fee</th></tr></thead><tbody>';
                foreach($recent_students as $student) {
                    $student_id=get_post_meta($student->ID,'_ppa_student_id',true);
                    $class_level=get_post_meta($student->ID,'_ppa_class_level',true);
                    $monthly_fee=(float)get_post_meta($student->ID,'_ppa_monthly_fee',true);
                    echo '<tr><td>'.esc_html($student_id?:'—').'</td><td><strong>'.esc_html($student->post_title).'</strong></td><td>'.esc_html($class_level?:'—').'</td><td>'.($monthly_fee>0?'৳'.esc_html(number_format_i18n($monthly_fee)):'—').'</td></tr>';
                }
                echo '</tbody></table></div>';
            } else {
                echo '<div class="ppa-ca-empty"><strong>No students yet</strong><br>Newly enrolled students will appear here.</div>';
            }
            echo '</section>';
            $recent_payments=array_slice($dashboard_payments,0,4);
            echo '<section class="ppa-ca-panel ppa-ca-recent-panel"><div class="ppa-ca-panel-head"><div><h2>Recent Payments</h2><p>Latest fee transactions</p></div><a class="ppa-ca-view-all" href="'.esc_url(add_query_arg('ppa_tab','payments',home_url('/admin/'))).'">View all ↗</a></div>';
            if($recent_payments) {
                echo '<div class="ppa-ca-table-wrap"><table class="ppa-ca-table"><thead><tr><th>Receipt</th><th>Student</th><th>Method</th><th class="is-right">Paid</th></tr></thead><tbody>';
                foreach($recent_payments as $payment) {
                    $receipt=get_post_meta($payment->ID,'_ppa_receipt_number',true);
                    $paid_value=get_post_meta($payment->ID,'_ppa_paid_amount',true);
                    if($paid_value==='') $paid_value=get_post_meta($payment->ID,'_ppa_amount',true);
                    $method=get_post_meta($payment->ID,'_ppa_payment_method',true);
                    $amount=(float)$paid_value;
                    echo '<tr><td>'.esc_html($receipt?:'—').'</td><td><strong>'.esc_html($payment->post_title).'</strong></td><td><span class="ppa-ca-method">'.esc_html($method?:'—').'</span></td><td class="is-right">'.($amount>0?'৳'.esc_html(number_format_i18n($amount)):'—').'</td></tr>';
                }
                echo '</tbody></table></div>';
            } else {
                echo '<div class="ppa-ca-empty"><strong>No payments recorded</strong><br>Recorded fee payments will appear here.</div>';
            }
            echo '</section></div>';
        } elseif($tab==='students') {
            $student_posts=ppa_ca_records('ppa_student');
            $student_query=isset($_GET['ppa_search'])?sanitize_text_field(wp_unslash($_GET['ppa_search'])):'';
            $student_posts=ppa_ca_filter_records($student_posts,$student_query);
            ppa_ca_section_intro('Student Management','View, search and maintain academy student records.',number_format_i18n(count($student_posts)).' students');
            ppa_ca_metric_cards(array(array('Total Students',(string)count($student_posts),'All registered students'),array('Active Classes','8 · 9 · 10','Academy class levels'),array('Student ID Format','YYNNNN','Six-digit student codes')));
            echo '<section class="ppa-ca-panel ppa-ca-data-panel"><div class="ppa-ca-panel-head"><div><h2>All Students</h2><p>Student profiles, guardian contacts and monthly fee information.</p></div><span class="ppa-ca-soft-badge">Student directory</span></div>';
            ppa_ca_search_toolbar('Search by student name, ID or phone number...');
            ppa_ca_add_form('student','Add New Student',array('record_title'=>'Student Name','student_id'=>'Student ID (6 digits, YYNNNN)','class_level'=>'Class (8 / 9 / 10)','batch_name'=>'Batch (Optional)','bangla_name'=>'Student Name (Bangla, Optional)','institution'=>'Institution (Optional)','phone'=>'Student Mobile (Optional)','guardian_name'=>'Guardian Name (Optional)','guardian_phone'=>'Guardian Mobile','whatsapp_phone'=>'WhatsApp Number (Optional)','monthly_fee'=>'Monthly Fee (৳)','record_details'=>'Additional Details'),$tab);
            ppa_ca_table($student_posts,'ppa_student',$tab); echo '</section>';
        } elseif($tab==='programs') {
            $batch_posts=ppa_ca_records('ppa_batch');
            ppa_ca_section_intro('Programs & Batches','Organize class groups, schedules and teaching information.',number_format_i18n(count($batch_posts)).' batches');
            ppa_ca_add_form('batch','Create New Batch',array('record_title'=>'Batch Name','class_level'=>'Class Level','max_capacity'=>'Maximum Capacity','schedule_days'=>'Schedule Days','time_slot'=>'Class Time','instructor'=>'Instructor','record_details'=>'Batch details / class schedule'),$tab);
            echo '<div class="ppa-ca-program-grid">';
            if($batch_posts){
                $all_students=ppa_ca_records('ppa_student',1000);
                foreach($batch_posts as $batch){
                    $class=get_post_meta($batch->ID,'_ppa_class_level',true);
                    $days=get_post_meta($batch->ID,'_ppa_schedule_days',true);
                    $time=get_post_meta($batch->ID,'_ppa_time_slot',true);
                    $teacher=get_post_meta($batch->ID,'_ppa_instructor',true);
                    $capacity=(int)get_post_meta($batch->ID,'_ppa_max_capacity',true);
                    $enrolled=0;
                    foreach($all_students as $enrolled_student) if(get_post_meta($enrolled_student->ID,'_ppa_batch_name',true)===$batch->post_title) $enrolled++;
                    $percent=$capacity>0?min(100,(int)round(($enrolled/$capacity)*100)):0;
                    $is_full=$capacity>0 && $enrolled >= $capacity;
                    echo '<article class="ppa-ca-program-card"><div class="ppa-ca-program-top"><span class="ppa-ca-program-icon">▤</span><span class="ppa-ca-soft-badge">'.esc_html($class?:'Academy Batch').'</span></div><h3>'.esc_html($batch->post_title).'</h3><div class="ppa-ca-program-meta"><span>◷ '.esc_html($days?:'Schedule not set').'</span><span>◴ '.esc_html($time?:'Time not set').'</span><span>♙ '.esc_html($teacher?:'Instructor not set').'</span></div><div class="ppa-ca-program-enrollment"><div><strong>'.number_format_i18n($enrolled).' enrolled</strong><span>'.($capacity>0?number_format_i18n($capacity).' seats':'Capacity not set').'</span></div><div class="ppa-ca-program-progress"><span style="width:'.(int)$percent.'%"></span></div><small class="'.($is_full?'is-full':'').'">'.($is_full?'Full':($capacity>0?number_format_i18n(max(0,$capacity-$enrolled)).' seats available':'Enrollment tracking')) .'</small></div><div class="ppa-ca-program-foot">Batch details <span>'.esc_html(wp_trim_words(wp_strip_all_tags($batch->post_content),12)).'</span></div></article>';
                }
            } else echo '<div class="ppa-ca-empty">No batches have been created yet.</div>';
            echo '</div>';
            echo '<section class="ppa-ca-panel ppa-ca-info-panel"><div class="ppa-ca-info-icon">▤</div><div><strong>Course pages</strong><p>Course page editing will be brought into this custom panel. The public course content and existing pages will be preserved during the database integration stage.</p></div></section>';
        } elseif($tab==='payments') {
            $payment_posts=ppa_ca_records('ppa_fee_payment');
            $payment_query=isset($_GET['ppa_search'])?sanitize_text_field(wp_unslash($_GET['ppa_search'])):'';
            $payment_posts=ppa_ca_filter_records($payment_posts,$payment_query);
            ppa_ca_section_intro('Fees & Payments','Track monthly collections and record student fee payments.',number_format_i18n(count($payment_posts)).' records');
            $total_payable=0; $total_collected=0; $total_due=0; $has_payable_data=false; $has_due_data=false;
            foreach($payment_posts as $payment_post) {
                $payable_value=get_post_meta($payment_post->ID,'_ppa_payable_amount',true);
                $paid_value=get_post_meta($payment_post->ID,'_ppa_paid_amount',true);
                if($paid_value==='') $paid_value=get_post_meta($payment_post->ID,'_ppa_amount',true);
                $due_value=get_post_meta($payment_post->ID,'_ppa_due_amount',true);
                if($payable_value!=='') { $has_payable_data=true; $total_payable+=(float)$payable_value; }
                $total_collected+=(float)$paid_value;
                if($due_value!=='') { $has_due_data=true; $total_due+=(float)$due_value; }
                elseif($payable_value!=='') { $has_due_data=true; $total_due+=max(0,(float)$payable_value-(float)$paid_value); }
            }
            ppa_ca_metric_cards(array(array('Total Payable',$has_payable_data?'৳'.number_format_i18n($total_payable):'—','From saved monthly fee entries'),array('Total Collected','৳'.number_format_i18n($total_collected),'From recorded payments'),array('Total Due',$has_due_data?'৳'.number_format_i18n($total_due):'—','Outstanding amount')));
            echo '<section class="ppa-ca-panel ppa-ca-data-panel"><div class="ppa-ca-panel-head"><div><h2>Payment Records</h2><p>Review recent payments and their billing details.</p></div><span class="ppa-ca-soft-badge">Fee ledger</span></div>';
            ppa_ca_search_toolbar('Search student, ID, billing month or amount...');
            ppa_ca_add_form('payment','Record a Fee Payment',array('record_title'=>'Student Name / ID','student_id'=>'Find Student by ID','billing_month'=>'Billing Month','billing_year'=>'Billing Year','payment_method'=>'Payment Method','payable_amount'=>'Payable Amount (৳)','paid_amount'=>'Paid Amount (৳)','due_amount'=>'Due Amount (৳)','payment_date'=>'Payment Date','record_details'=>'Payment note'),$tab);
            ppa_ca_table($payment_posts,'ppa_fee_payment',$tab); echo '</section>';
        } elseif($tab==='notices') {
            $notice_posts=ppa_ca_records('ppa_notice',50);
            $notice_terms=get_terms(array('taxonomy'=>'ppa_notice_category','hide_empty'=>false));
            if (is_wp_error($notice_terms)) $notice_terms=array();
            echo '<div class="ppa-ca-section-head"><div><h2>Notice Management</h2><p>Create, publish and manage announcements shown on the academy website.</p></div><span class="ppa-ca-section-count">'.number_format_i18n(ppa_ca_count('ppa_notice')).' published</span></div>';
            echo '<section class="ppa-ca-panel ppa-ca-notice-panel"><div class="ppa-ca-panel-head"><div><h2>All Notices</h2><p>Drafts and published notices are managed here.</p></div><span class="ppa-ca-soft-badge">Website content</span></div>';
            if($notice_posts){
                echo '<div class="ppa-ca-notice-grid">';
                foreach($notice_posts as $notice){
                    $status=get_post_status($notice);
                    $notice_terms_for_card=get_the_terms($notice->ID,'ppa_notice_category');
                    $category_label=(!is_wp_error($notice_terms_for_card)&&$notice_terms_for_card)?$notice_terms_for_card[0]->name:'General';
                    echo '<article class="ppa-ca-notice-card"><div class="ppa-ca-notice-card-top"><span class="ppa-ca-notice-category">'.esc_html($category_label).'</span><time>'.esc_html(get_the_date('d M Y',$notice)).'</time></div><h3>'.esc_html($notice->post_title).'</h3><p>'.esc_html(wp_trim_words(wp_strip_all_tags($notice->post_content),26)).'</p><div class="ppa-ca-notice-card-actions"><span class="ppa-ca-notice-status '.($status==='publish'?'is-published':'is-draft').'">'.esc_html($status==='publish'?'Published':'Draft').'</span><form method="post">';
                    wp_nonce_field('ppa_ca_admin_action','ppa_ca_nonce');
                    echo '<input type="hidden" name="ppa_ca_action" value="toggle_notice"><input type="hidden" name="notice_id" value="'.(int)$notice->ID.'"><button class="ppa-ca-notice-toggle" type="submit">'.esc_html($status==='publish'?'Unpublish':'Publish').'</button></form><form method="post" onsubmit="return confirm(\'Delete this notice?\')">';
                    wp_nonce_field('ppa_ca_admin_action','ppa_ca_nonce');
                    echo '<input type="hidden" name="ppa_ca_action" value="delete_notice"><input type="hidden" name="notice_id" value="'.(int)$notice->ID.'"><button class="ppa-ca-delete" type="submit">Delete</button></form></div></article>';
                }
            }
            echo '</div>';
            } else {
                echo '<div class="ppa-ca-empty">No notices have been published yet.</div>';
            }
            echo '</section>';
        } elseif ($tab === 'results') {
            $result_posts = ppa_ca_records('ppa_exam_result', 100);
            ppa_ca_section_intro('Results & Exams','Record and review student examination results.',number_format_i18n(count($result_posts)).' records');
            ppa_ca_add_form('result','Add Exam Result',array('record_title'=>'Student Name / ID','student_id'=>'Student ID','exam_name'=>'Exam Name','chapter'=>'Chapter / Subject','marks'=>'Obtained Marks','total_marks'=>'Total Marks','highest_marks'=>'Highest Marks','grade'=>'Grade','exam_date'=>'Exam Date','record_details'=>'Result notes'),$tab);
            ppa_ca_table($result_posts,'ppa_exam_result',$tab);
        } elseif ($tab === 'sms') {
            $balance = ppa_ca_sms_balance();
            echo '<div class="ppa-ca-section-head"><div><h2>SMS Management</h2><p>Check gateway balance and send academy updates to selected contacts.</p></div></div>';
            if (is_wp_error($balance)) {
                echo '<section class="ppa-ca-panel"><p>'.esc_html($balance->get_error_message()).'</p><p>Configure the API key in wp-config.php to enable gateway access.</p></section>';
            } else {
                echo '<section class="ppa-ca-panel"><h2>SMS Gateway Balance</h2><p class="ppa-ca-metric-value">'.esc_html($balance['balance']).'</p><small>Balance lookup only — no SMS has been sent.</small></section>';
            }
            echo '<section class="ppa-ca-panel"><p>Gateway portal: <a href="https://portal.sms.net.bd/login/" target="_blank" rel="noopener">sms.net.bd login</a></p></section>';
        } elseif ($tab === 'settings') {
            echo '<div class="ppa-ca-section-head"><div><h2>Academy Settings</h2><p>Manage homepage slideshow images and SMS sender details.</p></div></div>';
            echo '<section class="ppa-ca-panel"><form method="post">';
            wp_nonce_field('ppa_ca_admin_action','ppa_ca_nonce');
            echo '<input type="hidden" name="ppa_ca_action" value="save_settings"><input type="hidden" name="return_tab" value="settings">';
            echo '<label>SMS Sender ID<input name="sms_sender_id" value="'.esc_attr(get_option('ppa_ca_sms_sender_id','')).'"></label>';
            echo '<label>Homepage slideshow image URLs<textarea name="slideshow_urls" rows="5">'.esc_textarea(get_option('ppa_ca_slideshow_urls','')).'</textarea></label>';
            echo '<button class="ppa-ca-primary" type="submit">Save Settings</button></form></section>';
        } elseif ($tab === 'store') {
            $store_view = isset($_GET['ppa_store_view']) ? sanitize_key(wp_unslash($_GET['ppa_store_view'])) : 'books';
            ppa_store_render_admin($store_view);
        }
        echo '</main></div></div>';
    }
    wp_footer();
    echo '</div></body></html>';
}
