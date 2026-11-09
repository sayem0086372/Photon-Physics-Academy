<?php
/**
 * Plugin Name: Photon Physics Academy Core
 * Description: Persistent academy content types and core site features for Photon Physics Academy.
 * Version: 1.1.0
 * Author: OATHWILD (Sayeem)
 * Text Domain: photon-physics-academy
 */
if (!defined('ABSPATH')) { exit; }

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
        'public' => true, 'show_in_rest' => true, 'has_archive' => false,
        'menu_icon' => 'dashicons-welcome-learn-more',
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail', 'page-attributes'),
        'rewrite' => array('slug' => 'program'),
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
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'ppa_core_activate');
function ppa_core_deactivate() { flush_rewrite_rules(); }
register_deactivation_hook(__FILE__, 'ppa_core_deactivate');
