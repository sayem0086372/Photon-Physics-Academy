<?php
/**
 * Plugin Name: Photon Physics Academy Core
 * Description: Persistent academy content types and core site features for Photon Physics Academy.
 * Version: 1.0.0
 * Author: OATHWILD (Sayeem)
 * Text Domain: photon-physics-academy
 */
if (!defined('ABSPATH')) { exit; }

/**
 * Register academy notices independently of the active theme so published
 * notices remain available if the visual theme is changed.
 */
function ppa_core_register_notice_type() {
    register_post_type('ppa_notice', array(
        'labels' => array(
            'name' => __('Academy Notices', 'photon-physics-academy'),
            'singular_name' => __('Academy Notice', 'photon-physics-academy'),
            'add_new_item' => __('Add New Notice', 'photon-physics-academy'),
            'edit_item' => __('Edit Notice', 'photon-physics-academy'),
            'all_items' => __('All Academy Notices', 'photon-physics-academy'),
        ),
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => false,
        'menu_icon' => 'dashicons-megaphone',
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
        'rewrite' => array('slug' => 'academy-notice'),
    ));
}
add_action('init', 'ppa_core_register_notice_type');
