<?php
/**
 * Plugin Name: Photon Physics Academy Core
 * Description: Persistent academy content types and core site features for Photon Physics Academy.
 * Version: 2.1.1
 * Author: OATHWILD (Sayeem)
 * Text Domain: photon-physics-academy
 */
if (!defined('ABSPATH')) { exit; }


// Notice categories mirror the original site's interactive notice filters.
function ppa_core_register_notice_taxonomy() {
    register_taxonomy('ppa_notice_category', array('ppa_notice'), array(
        'labels' => array('name'=>__('Notice Categories','photon-physics-academy'),'singular_name'=>__('Notice Category','photon-physics-academy')),
        'public'=>false,'show_ui'=>true,'show_admin_column'=>true,'show_in_rest'=>true,'hierarchical'=>true,
    ));
}
add_action('init','ppa_core_register_notice_taxonomy',11);
function ppa_core_seed_notice_categories() {
    $categories = array('ভর্তি বিজ্ঞপ্তি'=>'admission','পরীক্ষার সময়সূচি'=>'exam-schedule','ক্লাসের সময়সূচি'=>'class-schedule','জরুরি ঘোষণা'=>'urgent','ফলাফল প্রকাশ'=>'results');
    foreach ($categories as $name=>$slug) {
        if (!term_exists($slug,'ppa_notice_category')) { wp_insert_term($name,'ppa_notice_category',array('slug'=>$slug)); }
    }
}
add_action('init','ppa_core_seed_notice_categories',20);

// Store admission/contact enquiries in WordPress for the academy team.
function ppa_core_register_inquiry_type() {
    register_post_type('ppa_inquiry', array(
        'labels'=>array('name'=>__('Admission Inquiries','photon-physics-academy'),'singular_name'=>__('Admission Inquiry','photon-physics-academy'),'all_items'=>__('All Inquiries','photon-physics-academy')),
        'public'=>false,'show_ui'=>true,'show_in_menu'=>true,'show_in_rest'=>false,'supports'=>array('title','editor'),
        'menu_icon'=>'dashicons-email-alt',
    ));
}
add_action('init','ppa_core_register_inquiry_type',12);

function ppa_core_handle_public_inquiry() {
    $redirect = wp_get_referer() ? wp_get_referer() : home_url('/');
    if (!isset($_POST['ppa_inquiry_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ppa_inquiry_nonce'])),'ppa_submit_inquiry')) {
        wp_safe_redirect(add_query_arg('ppa_inquiry','error',$redirect).'#contact'); exit;
    }
    if (!empty($_POST['website'])) { wp_safe_redirect(add_query_arg('ppa_inquiry','sent',$redirect).'#contact'); exit; }
    $name = isset($_POST['inquiry_name']) ? sanitize_text_field(wp_unslash($_POST['inquiry_name'])) : '';
    $phone = isset($_POST['inquiry_phone']) ? sanitize_text_field(wp_unslash($_POST['inquiry_phone'])) : '';
    $course = isset($_POST['inquiry_course']) ? sanitize_text_field(wp_unslash($_POST['inquiry_course'])) : '';
    $message = isset($_POST['inquiry_message']) ? sanitize_textarea_field(wp_unslash($_POST['inquiry_message'])) : '';
    if (!$name || !$phone || strlen($phone) > 24) { wp_safe_redirect(add_query_arg('ppa_inquiry','error',$redirect).'#contact'); exit; }
    $post_id = wp_insert_post(array(
        'post_type'=>'ppa_inquiry','post_status'=>'private',
        'post_title'=>sprintf('%s — %s', $name, $phone),
        'post_content'=>"Phone: {$phone}\nCourse: {$course}\n\n{$message}",
    ), true);
    if (is_wp_error($post_id)) { wp_safe_redirect(add_query_arg('ppa_inquiry','error',$redirect).'#contact'); exit; }
    $admin_email = get_option('admin_email');
    wp_mail($admin_email, 'New Photon Physics Academy inquiry', "Name: {$name}\nPhone: {$phone}\nCourse: {$course}\nMessage: {$message}");
    wp_safe_redirect(add_query_arg('ppa_inquiry','sent',$redirect).'#contact'); exit;
}
add_action('admin_post_nopriv_ppa_submit_inquiry','ppa_core_handle_public_inquiry');
add_action('admin_post_ppa_submit_inquiry','ppa_core_handle_public_inquiry');

/** Branded admin landing page with live WordPress content counts and shortcuts. */
function ppa_core_admin_menu() {
    add_menu_page(
        __('Photon Physics Academy','photon-physics-academy'),
        __('Photon Academy','photon-physics-academy'),
        'manage_options',
        'ppa-academy-admin',
        'ppa_core_render_admin_dashboard',
        'dashicons-welcome-learn-more',
        3
    );
}
add_action('admin_menu','ppa_core_admin_menu');

function ppa_core_render_admin_dashboard() {
    if (!current_user_can('manage_options')) { return; }
    $count = function($type) {
        $counts = wp_count_posts($type);
        return $counts && isset($counts->publish) ? (int)$counts->publish : 0;
    };
    $inquiry_counts = wp_count_posts('ppa_inquiry');
    $inquiries = $inquiry_counts && isset($inquiry_counts->private) ? (int)$inquiry_counts->private : 0;
    $notices = $count('ppa_notice');
    $courses = $count('ppa_course');
    $products = function_exists('wc_get_products') ? (int)wp_count_posts('product')->publish : 0;
    $links = array(
        array(__('Admission Inquiries','photon-physics-academy'), admin_url('edit.php?post_type=ppa_inquiry'), __('Review messages submitted from the public homepage.','photon-physics-academy')),
        array(__('Notices','photon-physics-academy'), admin_url('edit.php?post_type=ppa_notice'), __('Publish and organize academy notices.','photon-physics-academy')),
        array(__('Courses & Programs','photon-physics-academy'), admin_url('edit.php?post_type=ppa_course'), __('Edit homepage course cards and program information.','photon-physics-academy')),
        array(__('Store Products','photon-physics-academy'), admin_url('edit.php?post_type=product'), __('Manage books and product inventory with WooCommerce.','photon-physics-academy')),
        array(__('Store Orders','photon-physics-academy'), admin_url('admin.php?page=wc-orders'), __('Review and process customer orders.','photon-physics-academy')),
    );
    echo '<div class="wrap ppa-admin-wrap"><h1>Photon Physics Academy</h1><p class="ppa-admin-subtitle">Academy management overview</p>';
    echo '<div class="ppa-admin-stats">';
    foreach (array(array(__('Published Notices','photon-physics-academy'),$notices),array(__('Courses','photon-physics-academy'),$courses),array(__('New Inquiries','photon-physics-academy'),$inquiries),array(__('Store Products','photon-physics-academy'),$products)) as $stat) {
        echo '<div class="ppa-admin-stat"><span>'.esc_html($stat[0]).'</span><strong>'.esc_html(number_format_i18n($stat[1])).'</strong></div>';
    }
    echo '</div><h2>'.esc_html__('Quick Management','photon-physics-academy').'</h2><div class="ppa-admin-links">';
    foreach ($links as $link) {
        echo '<a class="ppa-admin-link" href="'.esc_url($link[1]).'"><strong>'.esc_html($link[0]).'</strong><span>'.esc_html($link[2]).'</span></a>';
    }
    echo '</div><div class="notice notice-info inline"><p><strong>'.esc_html__('Version 2 restoration in progress:','photon-physics-academy').'</strong> '.esc_html__('Student records, batches, fees, results, SMS gateway and the full legacy custom admin workflows are being migrated in staged releases. Do not use this dashboard as a replacement for those workflows until their migration is complete.','photon-physics-academy').'</p></div></div>';
}
function ppa_core_admin_dashboard_styles() {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    if (!$screen || $screen->id !== 'toplevel_page_ppa-academy-admin') { return; }
    echo '<style>
    .ppa-admin-wrap{max-width:1180px}.ppa-admin-subtitle{color:#64748b;font-size:14px;margin:6px 0 24px}
    .ppa-admin-stats{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:16px;margin:20px 0 30px}
    .ppa-admin-stat{background:#fff;border:1px solid #dce4ec;border-radius:12px;padding:20px;box-shadow:0 4px 16px #102b4e0a}
    .ppa-admin-stat span{display:block;color:#64748b;font-size:12px;font-weight:600}.ppa-admin-stat strong{display:block;color:#102b4e;font-size:30px;margin-top:8px}
    .ppa-admin-links{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;margin:16px 0 28px}
    .ppa-admin-link{display:block;text-decoration:none;background:#fff;border:1px solid #dce4ec;border-radius:12px;padding:18px;color:#14243a;transition:.2s}
    .ppa-admin-link:hover{border-color:#2474bd;box-shadow:0 8px 22px #102b4e12;transform:translateY(-2px)}
    .ppa-admin-link strong{display:block;font-size:14px;margin-bottom:7px}.ppa-admin-link span{display:block;color:#64748b;font-size:12px;line-height:1.6}
    @media(max-width:900px){.ppa-admin-stats{grid-template-columns:repeat(2,minmax(0,1fr))}.ppa-admin-links{grid-template-columns:repeat(2,minmax(0,1fr))}}
    @media(max-width:520px){.ppa-admin-stats,.ppa-admin-links{grid-template-columns:1fr}}
    </style>';
}
add_action('admin_head','ppa_core_admin_dashboard_styles');



/** Register academy notices and structured course pages independently of the active theme. */
function ppa_core_register_content_types() {
    register_post_type('ppa_notice', array(
        'labels' => array(
            'name' => __('Academy Notices', 'photon-physics-academy'),
            'singular_name' => __('Academy Notice', 'photon-physics-academy'),
            'add_new_item' => __('Add New Notice', 'photon-physics-academy'),
            'edit_item' => __('Edit Notice', 'photon-physics-academy'),
            'all_items' => __('All Academy Notices', 'photon-physics-academy'),
        ),
        'public' => true, 'show_in_rest' => true, 'has_archive' => false,
        'menu_icon' => 'dashicons-megaphone',
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
        'rewrite' => array('slug' => 'academy-notice'),
    ));

    register_post_type('ppa_course', array(
        'labels' => array(
            'name' => __('Courses & Programs', 'photon-physics-academy'),
            'singular_name' => __('Course', 'photon-physics-academy'),
            'add_new' => __('Add Course', 'photon-physics-academy'),
            'add_new_item' => __('Add New Course', 'photon-physics-academy'),
            'edit_item' => __('Edit Course', 'photon-physics-academy'),
            'all_items' => __('All Courses', 'photon-physics-academy'),
            'view_item' => __('View Course Details', 'photon-physics-academy'),
        ),
        'public' => false, 'publicly_queryable' => false, 'show_ui' => true, 'show_in_rest' => true, 'has_archive' => false, 'rewrite' => false,
        'menu_icon' => 'dashicons-welcome-learn-more',
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'),
    ));
}
add_action('init', 'ppa_core_register_content_types');

/** Seed the three approved learning pathways once; admins can edit them from Courses & Programs. */
function ppa_core_seed_default_courses() {
    $courses = array(
        array(
            'slug' => 'class-eight-science',
            'title' => 'অষ্টম শ্রেণি বিজ্ঞান',
            'excerpt' => 'বিজ্ঞানের মৌলিক ধারণা ও আনন্দময় শেখার সূচনা।',
            'audience' => 'অষ্টম শ্রেণির শিক্ষার্থী (বিজ্ঞান বিষয়)',
            'subtitle' => 'বিজ্ঞানের মৌলিক ধারণা ও আনন্দময় শেখার সূচনা',
            'features' => array('পাঠ্যবইয়ের অধ্যায়ভিত্তিক সহজ ও ধারাবাহিক পাঠদান','বিজ্ঞানের মৌলিক ধারণা ও দৈনন্দিন জীবনের উদাহরণ','নিয়মিত অনুশীলন, প্রশ্ন সমাধান ও মূল্যায়ন','নবম শ্রেণির বিজ্ঞান শিক্ষার জন্য মজবুত ভিত্তি তৈরি'),
            'image' => 'https://cdn.phototourl.com/member/2026-10-24-d41bef46-5d75-4bd3-b5d7-8dc5cc22ea39.png',
            'content' => '<p>অষ্টম শ্রেণির শিক্ষার্থীদের জন্য বিজ্ঞানের মৌলিক ধারণা পরিষ্কার করা এবং পরবর্তী শ্রেণির বিজ্ঞান শিক্ষার জন্য মজবুত ভিত্তি তৈরি করাই এই প্রোগ্রামের লক্ষ্য।</p><h2>এই প্রোগ্রামে যা থাকছে</h2><ul><li>পাঠ্যবইয়ের অধ্যায়ভিত্তিক সহজ ও ধারাবাহিক পাঠদান</li><li>বিজ্ঞানের মৌলিক ধারণা ও দৈনন্দিন জীবনের উদাহরণ</li><li>নিয়মিত অনুশীলন, প্রশ্ন সমাধান ও মূল্যায়ন</li><li>নবম শ্রেণির বিজ্ঞান শিক্ষার জন্য মজবুত ভিত্তি তৈরি</li></ul><h2>ক্লাস ও ভর্তি সংক্রান্ত তথ্য</h2><p>বর্তমান ব্যাচ, ক্লাসের সময়সূচি, আসন এবং ভর্তি সংক্রান্ত হালনাগাদ তথ্য জানতে একাডেমির সঙ্গে সরাসরি যোগাযোগ করুন।</p>'
        ),
        array(
            'slug' => 'class-nine-physics',
            'title' => 'নবম শ্রেণি পদার্থবিজ্ঞান',
            'excerpt' => 'পদার্থবিজ্ঞানের বুনিয়াদি সূচনা ও কনসেপ্ট গঠন।',
            'audience' => 'নবম শ্রেণির শিক্ষার্থী (বাংলা ও ইংরেজি ভার্সন)',
            'subtitle' => 'পদার্থবিজ্ঞানের বুনিয়াদি সূচনা ও কনসেপ্ট গঠন',
            'features' => array('মৌলিক ধারণা ও ভৌত রাশির নিখুঁত বোঝাপড়া','অধ্যায়ভিত্তিক গণিত ও গাণিতিক উদাহরণ বিশ্লেষণ','কনসেপ্ট বুকের সহায়তায় সূত্রাবলির প্রমাণ ও প্রয়োগ','প্রতিটি টপিকের বিস্তারিত সমস্যা সমাধান ও মূল্যায়ন'),
            'image' => 'https://cdn.phototourl.com/member/2026-10-24-b370d7b1-7e0f-4454-9623-fbca99e4b95a.png',
            'content' => '<p>নবম শ্রেণিতে পদার্থবিজ্ঞানের ভিত্তি মজবুত করতে প্রতিটি অধ্যায়ের ধারণা, সূত্রের অর্থ এবং গাণিতিক প্রয়োগ ধাপে ধাপে শেখানো হয়।</p><h2>এই প্রোগ্রামে যা থাকছে</h2><ul><li>মৌলিক ধারণা ও ভৌত রাশির নিখুঁত বোঝাপড়া</li><li>অধ্যায়ভিত্তিক গণিত ও গাণিতিক উদাহরণ বিশ্লেষণ</li><li>কনসেপ্ট বুকের সহায়তায় সূত্রাবলির প্রমাণ ও প্রয়োগ</li><li>প্রতিটি টপিকের বিস্তারিত সমস্যা সমাধান ও মূল্যায়ন</li></ul><h2>কারা ভর্তি হতে পারবে?</h2><p>বাংলা ও ইংরেজি ভার্সনের নবম শ্রেণির শিক্ষার্থীরা এই প্রোগ্রামের উপযোগী।</p><h2>ক্লাস ও ভর্তি সংক্রান্ত তথ্য</h2><p>বর্তমান ব্যাচ, ক্লাসের সময়সূচি, আসন এবং ভর্তি সংক্রান্ত হালনাগাদ তথ্য জানতে একাডেমির সঙ্গে সরাসরি যোগাযোগ করুন।</p>'
        ),
        array(
            'slug' => 'class-ten-physics',
            'title' => 'দশম শ্রেণি পদার্থবিজ্ঞান',
            'excerpt' => 'বোর্ড সিলেবাস সমাপ্তি এবং গভীর কনসেপ্ট বিশ্লেষণ।',
            'audience' => 'দশম শ্রেণির শিক্ষার্থী ও আগামী এসএসসি পরীক্ষার্থী',
            'subtitle' => 'বোর্ড সিলেবাস সমাপ্তি এবং গভীর কনসেপ্ট বিশ্লেষণ',
            'features' => array('সম্পূর্ণ পাঠ্যবইয়ের ধারাবাহিক ও বিস্তৃত পাঠদান','গতিবিদ্যা, বল ও কাজের সৃজনশীল প্রশ্ন (CQ) দক্ষতা','বহুনির্বাচনী (MCQ) নিখুঁত ও দ্রুত সমাধান কৌশল','অধ্যায়ভিত্তিক নিবিড় প্র্যাকটিস ও স্পেশাল কেয়ার সেশন'),
            'image' => 'https://cdn.phototourl.com/member/2026-10-24-eab7643b-7bec-4dd6-b5aa-7a8cfd4bc8ac.png',
            'content' => '<p>দশম শ্রেণির শিক্ষার্থী ও এসএসসি পরীক্ষার্থীদের জন্য পাঠ্যবইয়ের পূর্ণাঙ্গ প্রস্তুতি, ধারণার গভীরতা এবং প্রশ্ন সমাধানের দক্ষতা তৈরিতে এই প্রোগ্রাম সাজানো হয়েছে।</p><h2>এই প্রোগ্রামে যা থাকছে</h2><ul><li>সম্পূর্ণ পাঠ্যবইয়ের ধারাবাহিক ও বিস্তৃত পাঠদান</li><li>গতিবিদ্যা, বল ও কাজের সৃজনশীল প্রশ্ন (CQ) দক্ষতা</li><li>বহুনির্বাচনী (MCQ) নিখুঁত ও দ্রুত সমাধান কৌশল</li><li>অধ্যায়ভিত্তিক নিবিড় প্র্যাকটিস ও স্পেশাল কেয়ার সেশন</li></ul><h2>কারা ভর্তি হতে পারবে?</h2><p>দশম শ্রেণির শিক্ষার্থী এবং আগামী এসএসসি পরীক্ষার্থীরা এই প্রোগ্রামের উপযোগী।</p><h2>ক্লাস ও ভর্তি সংক্রান্ত তথ্য</h2><p>বর্তমান ব্যাচ, ক্লাসের সময়সূচি, আসন এবং ভর্তি সংক্রান্ত হালনাগাদ তথ্য জানতে একাডেমির সঙ্গে সরাসরি যোগাযোগ করুন।</p>'
        ),
    );
    foreach ($courses as $course) {
        if (get_page_by_path($course['slug'], OBJECT, 'ppa_course')) { continue; }
        $post_id = wp_insert_post(array(
            'post_type' => 'ppa_course', 'post_status' => 'publish',
            'post_name' => $course['slug'], 'post_title' => $course['title'],
            'post_excerpt' => $course['excerpt'], 'post_content' => $course['content'],
            'menu_order' => count(get_posts(array('post_type'=>'ppa_course','post_status'=>'any','numberposts'=>-1))),
        ));
        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, '_ppa_course_audience', $course['audience']);
            update_post_meta($post_id, '_ppa_course_subtitle', $course['subtitle']);
            update_post_meta($post_id, '_ppa_course_features', $course['features']);
            update_post_meta($post_id, '_ppa_course_image', $course['image']);
        }
    }
}
add_action('init', 'ppa_core_seed_default_courses', 30);

/** Refresh rewrite rules once after a plugin upgrade adds the course permalink type. */
function ppa_core_upgrade_rewrites() {
    if (get_option('ppa_core_version') !== '1.2.0') {
        flush_rewrite_rules(false);
        update_option('ppa_core_version', '1.2.0');
    }
}
add_action('init', 'ppa_core_upgrade_rewrites', 99);

/** Editable course information shown on both cards and detail pages. */
function ppa_core_course_meta_box() {
    add_meta_box('ppa_course_details', __('Course Information', 'photon-physics-academy'), 'ppa_core_course_meta_box_html', 'ppa_course', 'normal', 'high');
}
add_action('add_meta_boxes', 'ppa_core_course_meta_box');
function ppa_core_course_meta_box_html($post) {
    wp_nonce_field('ppa_course_meta_save', 'ppa_course_meta_nonce');
    $fields = array(
        '_ppa_course_subtitle' => __('Short subtitle', 'photon-physics-academy'),
        '_ppa_course_audience' => __('Target students', 'photon-physics-academy'),
        '_ppa_course_image' => __('Course image URL', 'photon-physics-academy'),
    );
    foreach ($fields as $key => $label) {
        printf('<p><label for="%1$s"><strong>%2$s</strong></label><br><input class="widefat" type="text" id="%1$s" name="%1$s" value="%3$s"></p>', esc_attr($key), esc_html($label), esc_attr(get_post_meta($post->ID, $key, true)));
    }
    $features = get_post_meta($post->ID, '_ppa_course_features', true);
    if (!is_array($features)) { $features = array(); }
    echo '<p><strong>' . esc_html__('Course highlights (one item per line)', 'photon-physics-academy') . '</strong><br><textarea class="widefat" rows="5" name="_ppa_course_features">' . esc_textarea(implode("\n", $features)) . '</textarea></p>';
}
function ppa_core_save_course_meta($post_id) {
    if (!isset($_POST['ppa_course_meta_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['ppa_course_meta_nonce'])), 'ppa_course_meta_save')) { return; }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) { return; }
    if (!current_user_can('edit_post', $post_id)) { return; }
    foreach (array('_ppa_course_subtitle','_ppa_course_audience','_ppa_course_image') as $key) {
        if (isset($_POST[$key])) { update_post_meta($post_id, $key, sanitize_text_field(wp_unslash($_POST[$key]))); }
    }
    if (isset($_POST['_ppa_course_features'])) {
        $lines = preg_split('/\r\n|\r|\n/', sanitize_textarea_field(wp_unslash($_POST['_ppa_course_features'])));
        update_post_meta($post_id, '_ppa_course_features', array_values(array_filter(array_map('trim', $lines))));
    }
}
add_action('save_post_ppa_course', 'ppa_core_save_course_meta');

/** Keep permalinks ready when the plugin is first activated. */
function ppa_core_activate() {
    ppa_core_register_content_types();
    if (function_exists('ppa_ca_register_types')) { ppa_ca_register_types(); }
    if (function_exists('ppa_db_install_schema')) { ppa_db_install_schema(); }
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'ppa_core_activate');
function ppa_core_deactivate() { flush_rewrite_rules(); }
register_deactivation_hook(__FILE__, 'ppa_core_deactivate');

// Load dedicated Academy MySQL storage before the custom admin application.
require_once __DIR__ . '/includes/database.php';
add_action('init', function () {
    if (is_user_logged_in() && current_user_can('manage_options')) { ppa_db_maybe_upgrade(); }
}, 120);

// Load the standalone, front-end academy administration application.
require_once __DIR__ . '/includes/store-integration.php';
require_once __DIR__ . '/includes/custom-admin.php';


/** Ensure WooCommerce has a published Shop page at /shop/ and uses it as its catalogue page. */
function ppa_core_ensure_woocommerce_shop_page() {
    if (!function_exists('wc_get_page_id') || !post_type_exists('product')) { return; }

    $shop_id = (int) wc_get_page_id('shop');
    $shop_page = $shop_id ? get_post($shop_id) : null;
    if (!$shop_page || 'publish' !== $shop_page->post_status || 'shop' !== $shop_page->post_name) {
        $shop_page = get_page_by_path('shop', OBJECT, 'page');
        if (!$shop_page) {
            $shop_id = wp_insert_post(array(
                'post_type' => 'page',
                'post_status' => 'publish',
                'post_title' => __('Shop', 'photon-physics-academy'),
                'post_name' => 'shop',
                'post_content' => '',
            ), true);
            if (is_wp_error($shop_id)) { return; }
        } else {
            $shop_id = (int) $shop_page->ID;
        }
        update_option('woocommerce_shop_page_id', (int) $shop_id);
        flush_rewrite_rules(false);
    }
}
add_action('init', 'ppa_core_ensure_woocommerce_shop_page', 110);
