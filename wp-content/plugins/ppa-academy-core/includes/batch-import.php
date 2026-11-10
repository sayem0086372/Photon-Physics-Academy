<?php
/**
 * One-time, repeatable importer for the 13 academy batches from the legacy Supabase source.
 * Writes through WordPress MySQL ($wpdb) and creates/links legacy CPT mirrors so Academy Admin can read them.
 */
if (!defined('ABSPATH')) { exit; }

function ppa_batch_import_source_rows() {
    return array(
        array('class'=>'Class 10','name'=>'ছেলেদের ব্যাচ','days'=>'শনি · সোম · বুধ','time'=>'২:৩০ PM'),
        array('class'=>'Class 10','name'=>'ছেলেদের ব্যাচ','days'=>'শনি · সোম · বুধ','time'=>'৯:০০ AM'),
        array('class'=>'Class 10','name'=>'মেয়েদের ব্যাচ','days'=>'শনি · সোম · বুধ','time'=>'৮:০০ AM'),
        array('class'=>'Class 10','name'=>'মেয়েদের ব্যাচ','days'=>'রবি · মঙ্গল · বৃহস্পতি','time'=>'৫:৩০ PM'),
        array('class'=>'Class 8','name'=>'বিজ্ঞান ব্যাচ','days'=>'রবি · মঙ্গল · বৃহস্পতি','time'=>'৬:৩০ PM'),
        array('class'=>'Class 9','name'=>'ছেলেদের ব্যাচ','days'=>'শনি · সোম · বুধ','time'=>'১১:০০ AM'),
        array('class'=>'Class 9','name'=>'ছেলেদের ব্যাচ','days'=>'শনি · সোম · বুধ','time'=>'৩:৩০ PM'),
        array('class'=>'Class 9','name'=>'ছেলেদের ব্যাচ','days'=>'রবি · মঙ্গল · বৃহস্পতি','time'=>'৮:০০ AM'),
        array('class'=>'Class 9','name'=>'মেয়েদের ব্যাচ','days'=>'শনি · সোম · বুধ','time'=>'৪:৩০ PM'),
        array('class'=>'Class 9','name'=>'মেয়েদের ব্যাচ','days'=>'শনি · সোম · বুধ','time'=>'৫:৩০ PM'),
        array('class'=>'Class 9','name'=>'মেয়েদের ব্যাচ','days'=>'রবি · মঙ্গল · বৃহস্পতি','time'=>'৯:০০ AM'),
        array('class'=>'Class 9','name'=>'মেয়েদের ব্যাচ','days'=>'শনি · সোম · বুধ','time'=>'১২:৩০ PM'),
        array('class'=>'Class 9','name'=>'মেয়েদের ব্যাচ','days'=>'শনি · সোম · বুধ','time'=>'১০:০০ AM'),
    );
}

function ppa_batch_import_fingerprint($row) {
    $parts = array($row['class'], $row['name'], $row['days'], $row['time'], 'Rafiqul Islam (Jewel Sir)');
    return hash('sha256', implode('|', array_map('trim', $parts)));
}

function ppa_batch_import_find_existing_post($fingerprint, $row) {
    $posts = get_posts(array(
        'post_type'=>'ppa_batch',
        'post_status'=>array('publish','draft','private'),
        'numberposts'=>-1,
        'meta_key'=>'_ppa_import_fingerprint',
        'meta_value'=>$fingerprint,
    ));
    if ($posts) { return $posts[0]; }

    // Adopt an exact legacy batch if one already exists, rather than creating a duplicate.
    $all = get_posts(array('post_type'=>'ppa_batch','post_status'=>array('publish','draft','private'),'numberposts'=>-1));
    foreach ($all as $post) {
        if ((string)get_post_meta($post->ID, '_ppa_class_level', true) === $row['class']
            && (string)get_post_meta($post->ID, '_ppa_schedule_days', true) === $row['days']
            && (string)get_post_meta($post->ID, '_ppa_time_slot', true) === $row['time']
            && (string)$post->post_title === $row['name']) {
            update_post_meta($post->ID, '_ppa_import_fingerprint', $fingerprint);
            return $post;
        }
    }
    return null;
}

function ppa_batch_import_run() {
    global $wpdb;
    if (!function_exists('ppa_db_sync_record') || !function_exists('ppa_db_install_schema')) {
        return new WP_Error('ppa_batch_import_db_unavailable', 'Academy database integration is not loaded.');
    }

    ppa_db_install_schema();
    $table = $wpdb->prefix . 'ppa_batches';
    $summary = array('total'=>0, 'created'=>0, 'linked'=>0, 'already'=>0, 'failed'=>array());

    foreach (ppa_batch_import_source_rows() as $index => $row) {
        $summary['total']++;
        $fingerprint = ppa_batch_import_fingerprint($row);
        $post = ppa_batch_import_find_existing_post($fingerprint, $row);
        $created = false;

        if (!$post) {
            $post_id = wp_insert_post(array(
                'post_type'=>'ppa_batch',
                'post_status'=>'publish',
                'post_title'=>$row['name'],
                'post_content'=>'Imported from the academy batch source.',
            ), true);
            if (is_wp_error($post_id)) {
                $summary['failed'][] = 'Batch '.($index+1).': '.$post_id->get_error_message();
                continue;
            }
            $post = get_post($post_id);
            $created = true;
        }

        update_post_meta($post->ID, '_ppa_class_level', $row['class']);
        update_post_meta($post->ID, '_ppa_max_capacity', 35);
        update_post_meta($post->ID, '_ppa_schedule_days', $row['days']);
        update_post_meta($post->ID, '_ppa_time_slot', $row['time']);
        update_post_meta($post->ID, '_ppa_instructor', 'Rafiqul Islam (Jewel Sir)');
        update_post_meta($post->ID, '_ppa_import_fingerprint', $fingerprint);
        update_post_meta($post->ID, '_ppa_source_enrolled_students', 0);
        update_post_meta($post->ID, '_ppa_source_status', 'Active');

        // Reuse an orphaned matching MySQL row if a previous partial import left one behind.
        $orphan_id = (int)$wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$table} WHERE legacy_post_id IS NULL AND batch_name=%s AND class_level=%s AND schedule_days=%s AND time_slot=%s AND instructor=%s LIMIT 1",
            $row['name'], $row['class'], $row['days'], $row['time'], 'Rafiqul Islam (Jewel Sir)'
        ));
        if ($orphan_id) {
            $wpdb->update($table, array('legacy_post_id'=>(int)$post->ID), array('id'=>$orphan_id), array('%d'), array('%d'));
        }

        $synced = ppa_db_sync_record($post->ID);
        if (is_wp_error($synced)) {
            $summary['failed'][] = 'Batch '.($index+1).' (post #'.$post->ID.'): '.$synced->get_error_message();
            continue;
        }

        if ($created) { $summary['created']++; }
        elseif ($orphan_id) { $summary['linked']++; }
        else { $summary['already']++; }
    }

    update_option('ppa_supabase_batches_import_summary', $summary, false);
    if (!$summary['failed']) {
        update_option('ppa_supabase_batches_import_v1_complete', current_time('mysql'), false);
    }
    return $summary;
}

function ppa_batch_import_handle_request() {
    if (!isset($_POST['ppa_ca_action']) || sanitize_key(wp_unslash($_POST['ppa_ca_action'])) !== 'import_source_batches') { return; }
    if (!is_user_logged_in() || !current_user_can('manage_options')) { wp_die('You are not allowed to do this.'); }
    check_admin_referer('ppa_ca_admin_action', 'ppa_ca_nonce');
    $result = ppa_batch_import_run();
    $notice = is_wp_error($result) ? 'batch_import_error' : (empty($result['failed']) ? 'batch_import_done' : 'batch_import_partial');
    $url = add_query_arg(array('ppa_tab'=>'programs','ppa_notice'=>$notice), home_url('/academyadmin/'));
    wp_safe_redirect($url . '#ppa-ca-modal-close');
    exit;
}
add_action('template_redirect', 'ppa_batch_import_handle_request', 0);

function ppa_ca_render_batch_import_panel() {
    if (!current_user_can('manage_options')) { return; }
    $summary = get_option('ppa_supabase_batches_import_summary', array());
    echo '<section class="ppa-ca-panel" style="margin:18px 0;padding:20px;border:1px solid #dce5ef;border-radius:14px;background:#fff">';
    echo '<div class="ppa-ca-panel-head"><div><h2>Restore the 13 Existing Batches</h2><p>Safely import the original academy batch schedule into MySQL and make it visible here.</p></div><span class="ppa-ca-soft-badge">13 source batches</span></div>';
    if (!empty($summary) && is_array($summary)) {
        echo '<p><strong>Last run:</strong> '.(int)($summary['created']??0).' created · '.(int)($summary['linked']??0).' linked · '.(int)($summary['already']??0).' already present · '.count($summary['failed']??array()).' errors.</p>';
    }
    echo '<p style="color:#64748b;font-size:13px;line-height:1.65">This action is repeat-safe: it matches each batch by its class, name, days and time, so running it again will not intentionally create duplicates. It does not alter student, fee, payment or result records.</p>';
    echo '<form method="post" onsubmit="return confirm(\'Import or verify all 13 academy batches in MySQL now?\')">';
    wp_nonce_field('ppa_ca_admin_action','ppa_ca_nonce');
    echo '<input type="hidden" name="ppa_ca_action" value="import_source_batches"><button type="submit" class="ppa-ca-primary-button" style="border:0;cursor:pointer">Import / Verify 13 Batches</button></form>';
    if (!empty($summary['failed'])) {
        echo '<ul style="color:#b42318">';
        foreach ($summary['failed'] as $error) { echo '<li>'.esc_html($error).'</li>'; }
        echo '</ul>';
    }
    echo '</section>';
}
