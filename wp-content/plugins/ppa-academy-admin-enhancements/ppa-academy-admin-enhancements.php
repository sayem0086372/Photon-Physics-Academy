<?php
/**
 * Plugin Name: Photon Physics Academy Admin Enhancements
 * Description: Adds safe batch actions, a redesigned settings panel, and SMS/store usability improvements to the Academy Admin.
 * Version: 1.0.0
 * Author: OATHWILD (Sayeem)
 * Text Domain: photon-physics-academy
 */
if (!defined('ABSPATH')) { exit; }

/** Handle enhancement forms before the existing admin renderer exits. */
function ppa_v2_admin_enhancements_handle_post() {
    if (empty($_POST['ppa_v2_action'])) { return; }
    if (!is_user_logged_in() || !current_user_can('manage_options')) { wp_die('You are not allowed to do this.', 403); }
    check_admin_referer('ppa_v2_admin_enhancements', 'ppa_v2_nonce');

    $action = sanitize_key(wp_unslash($_POST['ppa_v2_action']));
    if ($action === 'save_profile') {
        update_option('ppa_academy_contact_phone', sanitize_text_field(wp_unslash($_POST['contact_phone'] ?? '')), false);
        update_option('ppa_academy_contact_email', sanitize_email(wp_unslash($_POST['contact_email'] ?? '')), false);
        update_option('ppa_academy_address', sanitize_textarea_field(wp_unslash($_POST['academy_address'] ?? '')), false);
        update_option('ppa_academy_session', sanitize_text_field(wp_unslash($_POST['academic_session'] ?? '')), false);
        ppa_v2_admin_enhancements_redirect('settings', 'profile_saved');
    }

    if (!in_array($action, array('update_batch', 'delete_batch'), true)) { return; }
    $batch_id = absint($_POST['batch_id'] ?? 0);
    $batch = get_post($batch_id);
    if (!$batch || $batch->post_type !== 'ppa_batch') { ppa_v2_admin_enhancements_redirect('programs', 'batch_missing'); }

    if ($action === 'delete_batch') {
        $snapshot = function_exists('ppa_ca_batch_snapshot') ? ppa_ca_batch_snapshot($batch) : array('values'=>array($batch->post_title));
        if (function_exists('ppa_db_delete_record')) { ppa_db_delete_record($batch_id); }
        wp_delete_post($batch_id, true);
        if (function_exists('ppa_ca_students_set_batch')) { ppa_ca_students_set_batch($snapshot['values'], ''); }
        ppa_v2_admin_enhancements_redirect('programs', 'batch_deleted');
    }

    $title = sanitize_text_field(wp_unslash($_POST['batch_name'] ?? ''));
    $class = sanitize_text_field(wp_unslash($_POST['batch_class'] ?? ''));
    if ($title === '' || !in_array($class, array('Class 8', 'Class 9', 'Class 10'), true)) {
        ppa_v2_admin_enhancements_redirect('programs', 'batch_invalid');
    }
    $snapshot = function_exists('ppa_ca_batch_snapshot') ? ppa_ca_batch_snapshot($batch) : array('values'=>array($batch->post_title));
    $capacity = absint($_POST['batch_capacity'] ?? 0);
    $days = sanitize_text_field(wp_unslash($_POST['batch_days'] ?? ''));
    $time = sanitize_text_field(wp_unslash($_POST['batch_time'] ?? ''));
    $instructor = sanitize_text_field(wp_unslash($_POST['batch_instructor'] ?? ''));
    $details = sanitize_textarea_field(wp_unslash($_POST['batch_details'] ?? ''));
    $updated = wp_update_post(array('ID'=>$batch_id, 'post_title'=>$title, 'post_content'=>$details), true);
    if (is_wp_error($updated)) { ppa_v2_admin_enhancements_redirect('programs', 'batch_error'); }
    update_post_meta($batch_id, '_ppa_class_level', $class);
    update_post_meta($batch_id, '_ppa_max_capacity', $capacity);
    update_post_meta($batch_id, '_ppa_schedule_days', $days);
    update_post_meta($batch_id, '_ppa_time_slot', $time);
    update_post_meta($batch_id, '_ppa_instructor', $instructor);
    if (function_exists('ppa_ca_propagate_batch_change')) { ppa_ca_propagate_batch_change($snapshot, $batch_id); }
    if (function_exists('ppa_db_sync_record')) {
        $sync = ppa_db_sync_record($batch_id);
        if (is_wp_error($sync)) {
            error_log('Photon Physics Academy batch update failed: ' . $sync->get_error_message());
            ppa_v2_admin_enhancements_redirect('programs', 'batch_database_error');
        }
    }
    ppa_v2_admin_enhancements_redirect('programs', 'batch_updated');
}
add_action('template_redirect', 'ppa_v2_admin_enhancements_handle_post', -2);

function ppa_v2_admin_enhancements_redirect($tab, $notice) {
    $url = add_query_arg(array('ppa_tab'=>$tab, 'ppa_notice'=>$notice), home_url('/academyadmin/'));
    wp_safe_redirect($url . '#ppa-ca-modal-close');
    exit;
}

/** Buffer only the custom Academy Admin response; no WordPress data is changed here. */
function ppa_v2_admin_enhancements_start_buffer() {
    if (is_admin()) { return; }
    ob_start('ppa_v2_admin_enhancements_render');
}
add_action('template_redirect', 'ppa_v2_admin_enhancements_start_buffer', -1);

function ppa_v2_settings_markup() {
    $phone = (string) get_option('ppa_academy_contact_phone', '');
    $email = (string) get_option('ppa_academy_contact_email', get_option('admin_email'));
    $address = (string) get_option('ppa_academy_address', '');
    $session = (string) get_option('ppa_academy_session', '');
    $plugin = get_file_data(WP_PLUGIN_DIR . '/ppa-academy-core/ppa-academy-core.php', array('Version'=>'Version'));
    ob_start(); ?>
    <div class="ppa-ca-section-head"><div><h2>Academy Settings</h2><p>Keep essential academy contact details and administration information in one place.</p></div><span class="ppa-ca-section-count">Academy profile</span></div>
    <div class="ppa-v2-settings-grid">
      <section class="ppa-ca-panel ppa-v2-settings-card"><div class="ppa-v2-settings-heading"><span class="ppa-v2-settings-icon">⌂</span><div><h3>Academy profile</h3><p>Contact details and current academic session for internal reference.</p></div></div>
        <form method="post" class="ppa-v2-profile-form">
          <?php wp_nonce_field('ppa_v2_admin_enhancements', 'ppa_v2_nonce'); ?>
          <input type="hidden" name="ppa_v2_action" value="save_profile">
          <div class="ppa-ca-form-grid">
            <label>Contact phone<input type="tel" name="contact_phone" inputmode="tel" maxlength="32" value="<?php echo esc_attr($phone); ?>" placeholder="Academy contact number"></label>
            <label>Contact email<input type="email" name="contact_email" maxlength="190" value="<?php echo esc_attr($email); ?>" placeholder="academy@example.com"></label>
            <label>Academic session<input type="text" name="academic_session" maxlength="80" value="<?php echo esc_attr($session); ?>" placeholder="e.g. 2026–2027"></label>
            <label class="is-wide">Academy address<textarea name="academy_address" rows="3" maxlength="500" placeholder="Enter the academy address"><?php echo esc_textarea($address); ?></textarea></label>
          </div>
          <p class="ppa-v2-settings-help">These profile fields are stored as WordPress options. They do not change public website content, payment settings, or SMS sender configuration.</p>
          <button class="ppa-ca-primary" type="submit">Save Academy Profile</button>
        </form>
      </section>
      <aside class="ppa-ca-panel ppa-v2-settings-card"><div class="ppa-v2-settings-heading"><span class="ppa-v2-settings-icon is-blue">✓</span><div><h3>Administration status</h3><p>Read-only checks for the installed Academy tools.</p></div></div>
        <div class="ppa-v2-settings-status"><span class="ppa-v2-status-dot"></span><div><strong>Custom admin panel</strong><small>Available at /academyadmin/</small></div><b>Active</b></div>
        <div class="ppa-v2-settings-status"><span class="ppa-v2-status-dot"></span><div><strong>Academy data storage</strong><small>WordPress database integration</small></div><b><?php echo function_exists('ppa_db_maybe_upgrade') ? 'Connected' : 'Unavailable'; ?></b></div>
        <div class="ppa-v2-settings-status"><span class="ppa-v2-status-dot"></span><div><strong>Store integration</strong><small>WooCommerce product and order tools</small></div><b><?php echo class_exists('WooCommerce') ? 'Active' : 'Not active'; ?></b></div>
        <div class="ppa-v2-settings-footer"><span>Core plugin version</span><strong><?php echo esc_html($plugin['Version'] ?? 'Unknown'); ?></strong></div>
      </aside>
    </div>
    <section class="ppa-ca-panel ppa-v2-settings-note"><strong>Data safety</strong><p>Academy records and financial history are kept in WordPress/MySQL. This settings page does not run imports, delete records, or make database schema changes. Back up the site before installing plugin updates.</p></section>
    <?php
    return ob_get_clean();
}

function ppa_v2_batch_controls($post) {
    if (!$post || $post->post_type !== 'ppa_batch') { return ''; }
    $id = (int) $post->ID;
    ob_start(); ?>
    <details class="ppa-v2-batch-edit"><summary>Edit batch details</summary>
      <form method="post" class="ppa-v2-batch-form">
        <?php wp_nonce_field('ppa_v2_admin_enhancements', 'ppa_v2_nonce'); ?>
        <input type="hidden" name="ppa_v2_action" value="update_batch"><input type="hidden" name="batch_id" value="<?php echo $id; ?>">
        <label>Batch name<input name="batch_name" required maxlength="180" value="<?php echo esc_attr($post->post_title); ?>"></label>
        <label>Class<select name="batch_class" required><?php foreach (array('Class 8','Class 9','Class 10') as $class) : ?><option value="<?php echo esc_attr($class); ?>" <?php selected(get_post_meta($id, '_ppa_class_level', true), $class); ?>><?php echo esc_html($class); ?></option><?php endforeach; ?></select></label>
        <label>Capacity<input type="number" min="0" step="1" name="batch_capacity" value="<?php echo esc_attr(get_post_meta($id, '_ppa_max_capacity', true)); ?>"></label>
        <label>Schedule days<input name="batch_days" maxlength="190" value="<?php echo esc_attr(get_post_meta($id, '_ppa_schedule_days', true)); ?>"></label>
        <label>Class time<input name="batch_time" maxlength="100" value="<?php echo esc_attr(get_post_meta($id, '_ppa_time_slot', true)); ?>"></label>
        <label>Instructor<input name="batch_instructor" maxlength="190" value="<?php echo esc_attr(get_post_meta($id, '_ppa_instructor', true)); ?>"></label>
        <label class="is-wide">Details<textarea name="batch_details" rows="3"><?php echo esc_textarea($post->post_content); ?></textarea></label>
        <button class="ppa-ca-primary" type="submit">Save batch changes</button>
      </form>
    </details>
    <form method="post" class="ppa-v2-batch-delete" onsubmit="return confirm('Delete this batch? Enrolled students will be left intact and unassigned from this batch.');">
      <?php wp_nonce_field('ppa_v2_admin_enhancements', 'ppa_v2_nonce'); ?>
      <input type="hidden" name="ppa_v2_action" value="delete_batch"><input type="hidden" name="batch_id" value="<?php echo $id; ?>">
      <button class="ppa-ca-delete" type="submit">Delete batch</button>
    </form>
    <?php
    return ob_get_clean();
}

function ppa_v2_admin_enhancements_render($html) {
    if (strpos($html, 'class="ppa-ca-body"') === false || strpos($html, 'class="ppa-ca-shell"') === false) { return $html; }

    // Replace only the Settings tab's rendered body; other sections remain untouched.
    $settings = ppa_v2_settings_markup();
    $pattern = '/(<main class="ppa-ca-content">.*?)(<div class="ppa-ca-section-head"><div><h2>Academy Settings<\/h2>.*?)(<\/main>)/s';
    $updated = preg_replace_callback($pattern, function($matches) use ($settings) {
        return $matches[1] . $settings . $matches[3];
    }, $html, 1);
    if (is_string($updated)) { $html = $updated; }

    // Add edit/delete controls to each existing batch card, preserving the card and enrollment display.
    $html = preg_replace_callback('/(<article class="ppa-ca-program-card">.*?)(<\/article>)/s', function($matches) {
        if (strpos($matches[1], 'data-ppa-batch-id=') !== false) { return $matches[0]; }
        if (!preg_match('/<article class="ppa-ca-program-card">.*?/', $matches[0])) { return $matches[0]; }
        // Batch ID is attached to the existing card by a safe, subsequent pass in the batch renderer filter below.
        return $matches[0];
    }, $html);

    // The existing card markup has no post ID, so add controls by matching rendered title plus metadata to the authorized batch list.
    if (function_exists('ppa_ca_records') && strpos($html, 'ppa-ca-program-grid') !== false) {
        $batches = ppa_ca_records('ppa_batch', 200);
        foreach ($batches as $batch) {
            $title = esc_html($batch->post_title);
            $needle = '<h3>' . $title . '</h3>';
            $position = strpos($html, $needle);
            if ($position === false) { continue; }
            $card_start = strrpos(substr($html, 0, $position), '<article class="ppa-ca-program-card">');
            $card_end_pos = strpos($html, '</article>', $position);
            if ($card_start === false || $card_end_pos === false) { continue; }
            $card_end = $card_end_pos + strlen('</article>');
            $card = substr($html, $card_start, $card_end - $card_start);
            if (strpos($card, 'ppa-v2-batch-edit') !== false) { continue; }
            $card = substr($card, 0, -strlen('</article>')) . ppa_v2_batch_controls($batch) . '</article>';
            $html = substr_replace($html, $card, $card_start, $card_end - $card_start);
        }
    }

    // Add useful message templates and a segment estimate without sending test messages or exposing recipient numbers.
    $marker = '<label class="ppa-ca-sms-field">Message<textarea name="sms_message"';
    if (strpos($html, $marker) !== false) {
        $templates = '<label class="ppa-ca-sms-field ppa-v2-template-label">Message template<select data-ppa-v2-template><option value="">Write a message</option><option value="সম্মানিত অভিভাবক, মাসিক বেতন পরিশোধের জন্য অনুরোধ করা হচ্ছে। ধন্যবাদ — Photon Physics Academy">Fee reminder</option><option value="প্রিয় শিক্ষার্থী, একাডেমির ক্লাসের সময়সূচি পরিবর্তন হয়েছে। বিস্তারিত জানতে একাডেমির সঙ্গে যোগাযোগ করুন। — Photon Physics Academy">Schedule update</option><option value="প্রিয় শিক্ষার্থী, পরীক্ষার সময়সূচি ও প্রস্তুতি সংক্রান্ত তথ্য জানতে একাডেমির নোটিশ দেখুন। — Photon Physics Academy">Exam notice</option><option value="প্রিয় শিক্ষার্থী, একাডেমি বন্ধের সময়সূচি সম্পর্কে হালনাগাদ তথ্য জানতে নোটিশ দেখুন। — Photon Physics Academy">Holiday notice</option></select></label>';
        $html = str_replace($marker, $templates . $marker, $html);
        $html = str_replace('<span data-ppa-sms-length>0</span>/1000 characters · Gateway charges may apply.', '<span data-ppa-sms-length>0</span>/1000 characters · <span data-ppa-v2-segments>0 SMS segments</span>. Unicode/Bangla messages can use more segments and cost more.', $html);
    }

    $extra = '<style>
    .ppa-v2-settings-grid{display:grid;grid-template-columns:minmax(0,1.25fr) minmax(280px,.75fr);gap:16px;align-items:start}.ppa-v2-settings-card{margin:0!important;padding:22px!important}.ppa-v2-settings-heading{display:flex;align-items:center;gap:12px;margin-bottom:18px}.ppa-v2-settings-heading h3{margin:0;color:#14243a;font-size:16px}.ppa-v2-settings-heading p{margin:4px 0 0;color:#718198;font-size:12px}.ppa-v2-settings-icon{display:grid;place-items:center;flex:0 0 40px;height:40px;border-radius:11px;background:#eff6ff;color:#1d4ed8;font-size:20px;font-weight:800}.ppa-v2-settings-icon.is-blue{background:#e8f1ff}.ppa-v2-profile-form .ppa-ca-form-grid{margin-bottom:14px}.ppa-v2-settings-help{color:#718198;font-size:11px;line-height:1.6}.ppa-v2-settings-status{display:grid;grid-template-columns:10px 1fr auto;align-items:center;gap:10px;padding:13px 0;border-top:1px solid #eef2f7}.ppa-v2-settings-status strong,.ppa-v2-settings-status small{display:block}.ppa-v2-settings-status strong{font-size:12px;color:#24364b}.ppa-v2-settings-status small,.ppa-v2-settings-footer{font-size:10px;color:#718198}.ppa-v2-settings-status b{font-size:10px;color:#047857}.ppa-v2-status-dot{width:8px;height:8px;border-radius:50%;background:#16a34a}.ppa-v2-settings-footer{display:flex;justify-content:space-between;padding-top:14px;border-top:1px solid #eef2f7}.ppa-v2-settings-footer strong{color:#33465d}.ppa-v2-settings-note{margin-top:16px!important;background:#f8fbff!important}.ppa-v2-settings-note p{margin:5px 0 0;color:#64748b;font-size:12px;line-height:1.7}.ppa-v2-batch-edit{width:100%;margin-top:14px;padding-top:12px;border-top:1px solid #eef2f7}.ppa-v2-batch-edit summary{color:#1d4ed8;font-size:11px;font-weight:750;cursor:pointer}.ppa-v2-batch-form{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;margin-top:12px}.ppa-v2-batch-form label{display:grid;gap:5px;color:#475569;font-size:10px;font-weight:700}.ppa-v2-batch-form .is-wide{grid-column:1/-1}.ppa-v2-batch-form input,.ppa-v2-batch-form select,.ppa-v2-batch-form textarea{box-sizing:border-box;width:100%;min-height:36px;padding:8px;border:1px solid #d7e0eb;border-radius:7px;background:#fff;font:inherit}.ppa-v2-batch-form .ppa-ca-primary{grid-column:1/-1;justify-self:start}.ppa-v2-batch-delete{margin-top:8px}.ppa-v2-template-label{max-width:100%}.ppa-v2-template-label select{width:100%;box-sizing:border-box;padding:10px;border:1px solid #d7e0eb;border-radius:8px;background:#fff}.ppa-v2-store-filters{display:flex;flex-wrap:wrap;gap:8px;align-items:center;margin:10px 0}.ppa-v2-store-filters input,.ppa-v2-store-filters select{min-height:36px;padding:7px 10px;border:1px solid #d7e0eb;border-radius:8px;background:#fff}.ppa-v2-low-stock{background:#fff7ed!important}.ppa-v2-low-stock td{color:#9a3412!important}.ppa-v2-low-stock td:nth-child(5){font-weight:800;color:#c2410c!important}.ppa-v2-sms-estimate{color:#475569;font-weight:700}
    @media(max-width:680px){.ppa-v2-settings-grid{grid-template-columns:1fr}.ppa-v2-settings-card{padding:16px!important}.ppa-v2-batch-form{grid-template-columns:1fr}.ppa-v2-batch-form .is-wide,.ppa-v2-batch-form .ppa-ca-primary{grid-column:auto}.ppa-v2-store-filters{align-items:stretch;flex-direction:column}.ppa-v2-store-filters input,.ppa-v2-store-filters select{width:100%;box-sizing:border-box}}
    </style><script>
    document.addEventListener("DOMContentLoaded",function(){
      var template=document.querySelector("[data-ppa-v2-template]"), message=document.querySelector("[name=\\\"sms_message\\\"]"), segments=document.querySelector("[data-ppa-v2-segments]");
      function updateSegments(){if(!message||!segments)return;var text=message.value||"",unicode=/[^\\x00-\\x7F]/.test(text),limit=unicode?70:160,concat=unicode?67:153,n=Array.from(text).length;segments.textContent=(n<=limit?1:Math.ceil(n/concat))+" SMS segment"+((n<=limit?1:Math.ceil(n/concat))===1?"":"s");}
      if(template&&message){template.addEventListener("change",function(){if(template.value){message.value=template.value;message.dispatchEvent(new Event("input",{bubbles:true}));}updateSegments();});message.addEventListener("input",updateSegments);updateSegments();}
      document.querySelectorAll(".ppa-ca-store-section").forEach(function(section){if(!/Orders & payment review/i.test(section.innerText||""))return;var table=section.querySelector(".ppa-ca-table");if(!table)return;var bar=document.createElement("div");bar.className="ppa-v2-store-filters";bar.innerHTML="<input type=search aria-label=Search placeholder=Search\ customer\,\ phone\ or\ order><select aria-label=Filter\ order\ status><option value=\"\">All statuses</option><option>pending</option><option>on-hold</option><option>processing</option><option>completed</option><option>cancelled</option><option>refunded</option><option>failed</option></select><select aria-label=Filter\ payment\ method><option value=\"\">All payment methods</option><option>cod</option><option>ppa_bkash</option><option>ppa_nagad</option></select>";section.insertBefore(bar,table.closest(".ppa-ca-panel")||table);var inputs=bar.querySelectorAll("input,select");function filter(){var q=inputs[0].value.toLowerCase(),status=inputs[1].value.toLowerCase(),method=inputs[2].value.toLowerCase();table.querySelectorAll("tbody tr").forEach(function(row){var text=row.innerText.toLowerCase();row.hidden=!!((q&&text.indexOf(q)<0)||(status&&text.indexOf(status)<0)||(method&&text.indexOf(method)<0));});}inputs.forEach(function(el){el.addEventListener("input",filter);el.addEventListener("change",filter);});});
      document.querySelectorAll(".ppa-ca-store-table table").forEach(function(table){table.querySelectorAll("tbody tr").forEach(function(row){var stock=row.cells[4];if(stock&&/^(0|1|2|3|4|5)$/.test((stock.innerText||"").trim()))row.classList.add("ppa-v2-low-stock");});});
    });
    </script>';
    if (strpos($html, '</body>') !== false) { $html = str_replace('</body>', $extra . '</body>', $html); }
    return $html;
}
