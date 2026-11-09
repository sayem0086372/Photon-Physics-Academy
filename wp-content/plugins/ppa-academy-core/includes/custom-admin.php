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
function ppa_ca_count($type) {
    $counts=wp_count_posts($type);
    return $counts ? (int)$counts->publish : 0;
}
function ppa_ca_redirect($tab,$notice='') {
    $url=add_query_arg(array('ppa_tab'=>$tab),home_url('/admin/'));
    if ($notice) $url=add_query_arg('ppa_notice',$notice,$url);
    wp_safe_redirect($url); exit;
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
    if ($action==='save_settings') {
        update_option('ppa_ca_sms_gateway_url',esc_url_raw(wp_unslash($_POST['sms_gateway_url'] ?? '')));
        update_option('ppa_ca_sms_api_key',sanitize_text_field(wp_unslash($_POST['sms_api_key'] ?? '')));
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
            foreach (array('student_id','phone','guardian_phone','monthly_fee','batch_name','billing_month','amount','exam_name','marks') as $key) {
                if (isset($_POST[$key])) update_post_meta($id,'_ppa_'.$key,sanitize_text_field(wp_unslash($_POST[$key])));
            }
            ppa_ca_redirect($tab,'created');
        }
        ppa_ca_redirect($tab,'error');
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
      <div class="ppa-ca-brand-mark">P</div><div class="ppa-ca-kicker">PHOTON PHYSICS ACADEMY</div>
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
    return get_posts(array('post_type'=>$type,'post_status'=>'publish','numberposts'=>$limit,'orderby'=>'date','order'=>'DESC'));
}
function ppa_ca_table($posts,$type,$tab) {
    if (!$posts) { echo '<div class="ppa-ca-empty">No records yet. Add your first record using the form below.</div>'; return; }
    echo '<div class="ppa-ca-table-wrap"><table class="ppa-ca-table"><thead><tr><th>Name / Title</th><th>Details</th><th>Created</th><th></th></tr></thead><tbody>';
    foreach($posts as $p) {
        echo '<tr><td><strong>'.esc_html($p->post_title).'</strong></td><td>'.esc_html(wp_trim_words(wp_strip_all_tags($p->post_content),18)).'</td><td>'.esc_html(get_the_date('d M Y',$p)).'</td><td><form method="post" onsubmit="return confirm(\'Delete this record?\')">';
        wp_nonce_field('ppa_ca_admin_action','ppa_ca_nonce');
        echo '<input type="hidden" name="ppa_ca_action" value="delete_record"><input type="hidden" name="record_id" value="'.(int)$p->ID.'"><input type="hidden" name="return_tab" value="'.esc_attr($tab).'"><button class="ppa-ca-delete" type="submit">Delete</button></form></td></tr>';
    }
    echo '</tbody></table></div>';
}
function ppa_ca_add_form($kind,$title,$fields,$tab) {
    echo '<form method="post" class="ppa-ca-form">';
    wp_nonce_field('ppa_ca_admin_action','ppa_ca_nonce');
    echo '<input type="hidden" name="ppa_ca_action" value="add_record"><input type="hidden" name="record_type" value="'.esc_attr($kind).'"><input type="hidden" name="return_tab" value="'.esc_attr($tab).'"><h3>'.esc_html($title).'</h3><div class="ppa-ca-form-grid">';
    foreach($fields as $field=>$label) {
        $wide=in_array($field,array('record_details'),true);
        echo '<label class="'.($wide?'is-wide':'').'">'.esc_html($label);
        if ($field==='record_details') echo '<textarea name="record_details" rows="3"></textarea>';
        else echo '<input name="'.esc_attr($field).'" type="text" '.($field==='record_title'?'required':'').'>';
        echo '</label>';
    }
    echo '</div><button class="ppa-ca-primary" type="submit">Save Record</button></form>';
}
function ppa_ca_styles() { ?>
<style>
body.ppa-ca-body{background:#f8fafc!important;color:#1e293b}body.ppa-ca-body #wpadminbar{display:none!important}body.ppa-ca-body{margin-top:0!important}
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
.ppa-ca-section-head{display:flex;align-items:center;justify-content:space-between;gap:16px;margin:2px 0 20px}
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
</style><?php }
function ppa_ca_admin_script() { ?>
<script>
document.addEventListener('DOMContentLoaded',function(){
 const shell=document.querySelector('.ppa-ca-shell');
 const toggle=document.querySelector('.ppa-ca-mobile-toggle');
 if(toggle&&shell){toggle.addEventListener('click',function(){shell.classList.toggle('is-nav-open');toggle.setAttribute('aria-expanded',shell.classList.contains('is-nav-open')?'true':'false');});}
 document.querySelectorAll('.ppa-ca-nav a').forEach(function(link){link.addEventListener('click',function(){if(shell)shell.classList.remove('is-nav-open');});});
 document.addEventListener('keydown',function(e){if(e.key==='Escape'&&shell)shell.classList.remove('is-nav-open');});
});
</script>
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
    if(!is_user_logged_in() || !current_user_can('manage_options')) {
        echo ppa_ca_login_screen();
    } else {
        echo '<div class="ppa-ca-shell"><aside class="ppa-ca-sidebar"><div class="ppa-ca-brand"><div class="ppa-ca-brand-mark"><img src="https://cdn.phototourl.com/member/2026-10-26-00b8c0d9-38d9-4d60-a377-dfd8928013de.png" alt="Photon Physics Academy"></div><div><strong>PHOTON</strong><small>Admin Panel</small></div></div><nav class="ppa-ca-nav">';
        foreach($tabs as $key=>$item) echo '<a class="'.($tab===$key?'is-active':'').'" href="'.esc_url(add_query_arg('ppa_tab',$key,home_url('/admin/'))).'"><i>'.esc_html($item[1]).'</i><span>'.esc_html($item[0]).'</span></a>';
        echo '</nav><div class="ppa-ca-side-bottom"><a href="'.esc_url(home_url('/')).'">← Back to Website</a></div></aside>';
        echo '<div class="ppa-ca-main"><header class="ppa-ca-header"><button type="button" class="ppa-ca-mobile-toggle" aria-label="Open admin navigation" aria-expanded="false">☰</button><h1>'.esc_html($tabs[$tab][0]).'</h1><div class="ppa-ca-user"><div class="ppa-ca-avatar">'.esc_html('JS').'</div><span class="ppa-ca-user-label">'.esc_html('Jewel Sir').' · Administrator</span><form method="post">';
        wp_nonce_field('ppa_ca_admin_action','ppa_ca_nonce');
        echo '<input type="hidden" name="ppa_ca_action" value="logout"><button class="ppa-ca-logout" type="submit">Log out</button></form></div></header><main class="ppa-ca-content">';
        if(isset($_GET['ppa_notice']) && $_GET['ppa_notice']!=='login_error') echo '<div class="ppa-ca-alert">'.esc_html(sanitize_text_field(wp_unslash($_GET['ppa_notice']))==='saved'?'Settings saved.':'Action completed.').'</div>';
        if($tab==='overview') {
            echo '<div class="ppa-ca-welcome"><h2>Welcome back, '.esc_html($user->display_name).'!</h2><p>Here is what is happening at Photon Physics Academy.</p></div><div class="ppa-ca-grid">';
            $stats=array('Students'=>ppa_ca_count('ppa_student'),'Programs & Batches'=>ppa_ca_count('ppa_batch'),'Published Notices'=>ppa_ca_count('ppa_notice'),'Fee Payments'=>ppa_ca_count('ppa_fee_payment'));
            foreach($stats as $label=>$number) echo '<div class="ppa-ca-card"><span>'.esc_html($label).'</span><strong>'.number_format_i18n($number).'</strong></div>';
            echo '</div><section class="ppa-ca-panel"><h2>Quick Management</h2><div class="ppa-ca-shortcuts">';
            foreach(array('students'=>'Manage Students','programs'=>'Programs & Batches','payments'=>'Record Fee Payment','notices'=>'Publish Notices','results'=>'Exam Results','store'=>'Store Orders') as $key=>$label) echo '<a href="'.esc_url(add_query_arg('ppa_tab',$key,home_url('/admin/'))).'">'.esc_html($label).'<span>Open management section →</span></a>';
            echo '</div></section>';
        } elseif($tab==='students') {
            echo '<section class="ppa-ca-panel"><h2>Students Management</h2>'; ppa_ca_table(ppa_ca_records('ppa_student'),'ppa_student',$tab); echo '</section>';
            ppa_ca_add_form('student','Add Student',array('record_title'=>'Student Name','student_id'=>'Student ID (YYNNN)','phone'=>'Student Mobile','guardian_phone'=>'Guardian Mobile','monthly_fee'=>'Monthly Fee (৳)','batch_name'=>'Batch','record_details'=>'Additional Details'),$tab);
        } elseif($tab==='programs') {
            echo '<section class="ppa-ca-panel"><h2>Programs & Batches</h2>'; ppa_ca_table(ppa_ca_records('ppa_batch'),'ppa_batch',$tab); echo '</section>';
            ppa_ca_add_form('batch','Add Batch',array('record_title'=>'Batch Name','record_details'=>'Batch details / class schedule'),$tab);
            echo '<section class="ppa-ca-panel"><h2>Course Pages</h2><p>Manage public course descriptions in the WordPress content manager.</p><a class="ppa-ca-primary" href="'.esc_url(admin_url('edit.php?post_type=ppa_course')).'">Open Course Manager</a></section>';
        } elseif($tab==='payments') {
            echo '<section class="ppa-ca-panel"><h2>Payments & Fees</h2>'; ppa_ca_table(ppa_ca_records('ppa_fee_payment'),'ppa_fee_payment',$tab); echo '</section>';
            ppa_ca_add_form('payment','Record Fee Payment',array('record_title'=>'Student Name / ID','billing_month'=>'Billing Month','amount'=>'Amount (৳)','record_details'=>'Payment note'),$tab);
        } elseif($tab==='notices') {
            echo '<div class="ppa-ca-section-head"><div><h2>Notice Management</h2><p>Manage admission, exam and class schedule announcements displayed on the public website.</p></div><span class="ppa-ca-section-count">'.number_format_i18n(ppa_ca_count('ppa_notice')).' published</span></div>';
            echo '<section class="ppa-ca-panel"><div class="ppa-ca-panel-head"><div><h2>Published Notices</h2><p>Review the latest announcements and their publication dates.</p></div><span class="ppa-ca-soft-badge">Website content</span></div>';
            $notice_posts=ppa_ca_records('ppa_notice',50);
            if($notice_posts){ echo '<div class="ppa-ca-notice-list">'; foreach($notice_posts as $notice){ echo '<article class="ppa-ca-notice-row"><div class="ppa-ca-notice-icon">▣</div><div class="ppa-ca-notice-copy"><strong>'.esc_html($notice->post_title).'</strong><span>'.esc_html(wp_trim_words(wp_strip_all_tags($notice->post_content),22)).'</span></div><time>'.esc_html(get_the_date('d M Y',$notice)).'</time></article>'; } echo '</div>'; }
            else { echo '<div class="ppa-ca-empty"><strong>No published notices yet</strong><br>Published academy announcements will appear here.</div>'; }
            echo '</section><section class="ppa-ca-panel ppa-ca-info-panel"><div class="ppa-ca-info-icon">✦</div><div><strong>Notice workspace</strong><p>The custom notice editor and publish controls will be connected to the WordPress content model in the database integration stage. This panel will remain inside the academy Admin Panel.</p></div></section>';
        } elseif($tab==='results') {
            echo '<section class="ppa-ca-panel"><h2>Results & Exams</h2>'; ppa_ca_table(ppa_ca_records('ppa_exam_result'),'ppa_exam_result',$tab); echo '</section>';
            ppa_ca_add_form('result','Add Exam Result',array('record_title'=>'Student Name / ID','exam_name'=>'Exam Name','marks'=>'Marks / Grade','record_details'=>'Result details'),$tab);
        } elseif($tab==='sms') {
            echo '<div class="ppa-ca-section-head"><div><h2>SMS Management</h2><p>Send academy updates to students and guardians through the SMS gateway.</p></div><a class="ppa-ca-outline-link" href="https://portal.sms.net.bd/login/" target="_blank" rel="noopener">Gateway Login ↗</a></div>';
            echo '<div class="ppa-ca-sms-grid"><section class="ppa-ca-panel"><div class="ppa-ca-panel-head"><div><h2>Gateway Connection</h2><p>SMS provider configuration and account status.</p></div><span class="ppa-ca-soft-badge">sms.net.bd</span></div><div class="ppa-ca-gateway-status"><span class="ppa-ca-status-dot"></span><div><strong>Connection setup pending</strong><small>Secure API connection will be configured during database and integration setup.</small></div></div><a class="ppa-ca-primary" href="https://portal.sms.net.bd/login/" target="_blank" rel="noopener">Open SMS Gateway</a></section>';
            echo '<section class="ppa-ca-panel"><div class="ppa-ca-panel-head"><div><h2>Message Centre</h2><p>Prepare announcements for selected student groups.</p></div></div><label class="ppa-ca-field-label">Recipient group<select disabled><option>All active students and guardians</option></select></label><label class="ppa-ca-field-label">Message<textarea rows="4" disabled placeholder="Write your academy announcement…"></textarea></label><button class="ppa-ca-primary" type="button" disabled>Send SMS · Integration pending</button></section></div><section class="ppa-ca-info-panel"><div class="ppa-ca-info-icon">✓</div><div><strong>Privacy rule</strong><p>Sent SMS history will not be stored. Guardian numbers will be the default recipient; student mobile numbers remain optional. WhatsApp contacts will not be treated as SMS recipients.</p></div></section>';
        } elseif($tab==='settings') {
            echo '<form method="post" class="ppa-ca-form">'; wp_nonce_field('ppa_ca_admin_action','ppa_ca_nonce');
            echo '<input type="hidden" name="ppa_ca_action" value="save_settings"><input type="hidden" name="return_tab" value="settings"><h3>SMS Gateway & Homepage Slideshow</h3><div class="ppa-ca-form-grid">';
            foreach(array('sms_gateway_url'=>'SMS Gateway URL','sms_api_key'=>'SMS API Key','sms_sender_id'=>'Sender ID','slideshow_urls'=>'Slideshow Image URLs (one per line)') as $key=>$label) {
                $value=get_option('ppa_ca_'.$key,'');
                echo '<label class="'.($key==='slideshow_urls'?'is-wide':'').'">'.esc_html($label);
                if($key==='slideshow_urls') echo '<textarea rows="5" name="slideshow_urls">'.esc_textarea($value).'</textarea>';
                else echo '<input type="'.($key==='sms_api_key'?'password':'text').'" name="'.esc_attr($key).'" value="'.esc_attr($value).'">';
                echo '</label>';
            }
            echo '</div><button class="ppa-ca-primary" type="submit">Save Settings</button></form>';
        } elseif($tab==='store') {
            echo '<div class="ppa-ca-section-head"><div><h2>Store Management</h2><p>Manage books, customer orders, promotions and delivery operations.</p></div><span class="ppa-ca-soft-badge">Online bookstore</span></div>';
            echo '<div class="ppa-ca-store-grid">';
            $storeCards=array(
              array('▣','Books & Inventory','Manage book details, cover images, pricing, discounts and stock.','books'),
              array('↗','Customer Orders','Review new orders, payment verification and fulfilment status.','orders'),
              array('％','Coupons & Offers','Create discount codes, usage limits and promotional campaigns.','coupons'),
              array('⌁','Delivery Settings','Configure delivery charges, free delivery threshold and instructions.','delivery'),
              array('▥','Sales Analytics','Review order volume, revenue and top-selling books.','analytics')
            );
            foreach($storeCards as $card) echo '<article class="ppa-ca-store-card"><div class="ppa-ca-store-icon">'.esc_html($card[0]).'</div><div><h3>'.esc_html($card[1]).'</h3><p>'.esc_html($card[2]).'</p><span class="ppa-ca-coming-tag">Workspace UI · Database connection next</span></div></article>';
            echo '</div><section class="ppa-ca-panel ppa-ca-info-panel"><div class="ppa-ca-info-icon">i</div><div><strong>Store data protection</strong><p>Book catalogue, customer orders, payment references, coupon records and delivery settings will be preserved. Store management will be implemented in this custom panel without sending administrators to the WordPress Dashboard.</p></div></section>';
        }
        echo '</main></div></div>';
    }
    echo '</div>';
    ppa_ca_admin_script();
    wp_footer();
    echo '</body></html>';
}
function ppa_ca_register_route() {
    add_rewrite_rule('^admin/?$','index.php?ppa_custom_admin=1','top');
    add_rewrite_tag('%ppa_custom_admin%','1');
}
add_action('init','ppa_ca_register_route',99);
add_action('init',function(){ if(get_option('ppa_ca_rewrite_version')!=='1') { flush_rewrite_rules(false); update_option('ppa_ca_rewrite_version','1'); } },100);
add_filter('query_vars',function($vars){$vars[]='ppa_custom_admin';return $vars;});
add_action('template_redirect',function(){if(get_query_var('ppa_custom_admin')) { ppa_ca_render_page(); exit; }},20);
add_filter('body_class',function($classes){if(get_query_var('ppa_custom_admin')) $classes[]='ppa-ca-body';return $classes;});
add_filter('login_url',function($url,$redirect,$force_reauth){return home_url('/admin/');},10,3);
