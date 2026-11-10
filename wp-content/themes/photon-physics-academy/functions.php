<?php
/**
 * Photon Physics Academy theme functions.
 *
 * @package Photon_Physics_Academy
 */
if (!defined('ABSPATH')) { exit; }

define('PPA_THEME_VERSION', '2.0.0');

function ppa_theme_setup() {
    load_theme_textdomain('photon-physics-academy', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array('height' => 120, 'width' => 420, 'flex-height' => true, 'flex-width' => true));
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
    add_theme_support('woocommerce');
    register_nav_menus(array('primary' => __('Primary Navigation', 'photon-physics-academy'), 'footer' => __('Footer Navigation', 'photon-physics-academy')));
}
add_action('after_setup_theme', 'ppa_theme_setup');

function ppa_enqueue_assets() {
    wp_enqueue_style('ppa-google-fonts', 'https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&display=swap', array(), null);
    wp_enqueue_style('ppa-theme', get_stylesheet_uri(), array('ppa-google-fonts'), file_exists(get_stylesheet_directory() . '/style.css') ? filemtime(get_stylesheet_directory() . '/style.css') : PPA_THEME_VERSION);
    wp_enqueue_script('ppa-theme', get_template_directory_uri() . '/assets/js/theme.js', array(), file_exists(get_template_directory() . '/assets/js/theme.js') ? filemtime(get_template_directory() . '/assets/js/theme.js') : PPA_THEME_VERSION, true);
}
add_action('wp_enqueue_scripts', 'ppa_enqueue_assets');

function ppa_asset($filename) {
    $allowed = array(
        'hero' => 'hero_physics_classroom_1792301360099.jpg',
        'teacher' => 'teacher_mentorship_physics_1792301361137.jpg',
        'gallery1' => 'classroom_students_discussion_1792301360431.jpg',
        'gallery2' => 'students_reading_concept_book_1792301361773.jpg',
        'book1' => 'book_bol_authentic_cover_1792302614714.jpg',
        'book2' => 'book_gati_authentic_cover_1792302615144.jpg',
        'book3' => 'book_quantities_measurement_cover_1792302614659.jpg',
    );
    if (!isset($allowed[$filename])) { return ''; }
    return 'https://raw.githubusercontent.com/oathwi1d/Photon-Physics-Academy/wordpress-migration/src/assets/images/' . rawurlencode($allowed[$filename]);
}

function ppa_fallback_menu() {
    $links = array(
        'Home' => home_url('/'),
        'About' => home_url('/#about'),
        'Programs' => home_url('/#programs'),
        'Classroom' => home_url('/#gallery'),
        'Book Store' => home_url('/shop/'),
        'Notices' => home_url('/#notices'),
        'Results' => home_url('/#results'),
        'Resources' => home_url('/#resources'),
        'Contact' => home_url('/#contact'),
    );
    echo '<ul class="ppa-nav-list">';
    foreach ($links as $label => $url) {
        printf('<li><a href="%s">%s</a></li>', esc_url($url), esc_html($label));
    }
    echo '</ul>';
}

function ppa_excerpt($post_id = null, $length = 24) {
    $post = get_post($post_id);
    if (!$post) { return ''; }
    $text = has_excerpt($post) ? get_the_excerpt($post) : wp_strip_all_tags($post->post_content);
    return wp_trim_words($text, $length, '…');
}


/**
 * Use the academy logo as the browser tab/site icon.
 */
function ppa_academy_site_icon() {
    $icon = 'https://cdn.phototourl.com/member/2026-10-26-00b8c0d9-38d9-4d60-a377-dfd8928013de.png';
    printf(
        '<link rel="icon" href="%1$s" type="image/png"><link rel="apple-touch-icon" href="%1$s">',
        esc_url($icon)
    );
}
add_action('wp_head', 'ppa_academy_site_icon', 1);
remove_action('wp_head', 'wp_site_icon', 99);

/**
 * Move WordPress's untouched default "Hello world!" sample post to Trash once.
 * Other posts and pages are left unchanged.
 */
function ppa_trash_default_hello_world_post() {
    if (get_option('ppa_hello_world_cleanup_v1')) {
        return;
    }
    $sample = get_page_by_path('hello-world', OBJECT, 'post');
    if ($sample && $sample->post_title === 'Hello world!' && $sample->post_status !== 'trash') {
        wp_trash_post($sample->ID);
    }
    update_option('ppa_hello_world_cleanup_v1', 1, false);
}
add_action('admin_init', 'ppa_trash_default_hello_world_post');
