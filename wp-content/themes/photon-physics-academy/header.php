<?php if (!defined('ABSPATH')) { exit; } ?><!doctype html>
<html <?php language_attributes(); ?>>
<head><meta charset="<?php bloginfo('charset'); ?>"><meta name="viewport" content="width=device-width, initial-scale=1"><?php wp_head(); ?></head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="ppa-header">
  <div class="ppa-container ppa-header-inner">
    <a class="ppa-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('Photon Physics Academy home', 'photon-physics-academy'); ?>">
      <?php $ppa_logo_id = (int) get_theme_mod('custom_logo'); if ($ppa_logo_id) { echo wp_get_attachment_image($ppa_logo_id, 'full', false, array('class' => 'ppa-logo-img', 'alt' => 'Photon Physics Academy')); } else { ?>
        <img class="ppa-logo-img" src="https://cdn.phototourl.com/member/2026-10-26-00b8c0d9-38d9-4d60-a377-dfd8928013de.png" alt="Photon Physics Academy">
      <?php } ?>
      <span><span class="ppa-brand-main">PHOTON <span>PHYSICS</span></span><br><span class="ppa-brand-sub">ACADEMY</span><br><span class="ppa-brand-bn">পদার্থবিজ্ঞান একাডেমি · ময়মনসিংহ</span></span>
    </a>
    <nav class="ppa-nav" aria-label="<?php esc_attr_e('Primary navigation', 'photon-physics-academy'); ?>">
      <?php if (has_nav_menu('primary')) { wp_nav_menu(array('theme_location'=>'primary','container'=>false,'items_wrap'=>'%3$s','fallback_cb'=>false)); } else { ppa_fallback_menu(); } ?>
    </nav>
    <button class="ppa-menu-toggle" type="button" aria-expanded="false" aria-controls="ppa-mobile-nav">☰ <span class="screen-reader-text"><?php esc_html_e('Open menu', 'photon-physics-academy'); ?></span></button>
  </div>
  <nav id="ppa-mobile-nav" class="ppa-mobile-nav ppa-container" aria-label="<?php esc_attr_e('Mobile navigation', 'photon-physics-academy'); ?>">
    <?php if (has_nav_menu('primary')) { wp_nav_menu(array('theme_location'=>'primary','container'=>false,'items_wrap'=>'%3$s','fallback_cb'=>false)); } else { ppa_fallback_menu(); } ?>
  </nav>
</header>
