<?php
/**
 * Single course detail page.
 * @package Photon_Physics_Academy
 */
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main id="primary" class="ppa-course-page">
  <section class="ppa-course-hero">
    <div class="ppa-container">
      <a class="ppa-back-link" href="<?php echo esc_url(home_url('/#programs')); ?>">← সকল প্রোগ্রাম</a>
      <div class="ppa-course-hero-grid">
        <div class="ppa-course-intro">
          <p class="ppa-eyebrow">PHOTON PHYSICS ACADEMY · LEARNING PROGRAM</p>
          <h1 class="ppa-bangla"><?php the_title(); ?></h1>
          <?php $subtitle = get_post_meta(get_the_ID(), '_ppa_course_subtitle', true); if ($subtitle) : ?>
            <p class="ppa-course-subtitle ppa-bangla"><?php echo esc_html($subtitle); ?></p>
          <?php endif; ?>
          <?php $audience = get_post_meta(get_the_ID(), '_ppa_course_audience', true); if ($audience) : ?>
            <div class="ppa-course-audience"><span>উপযুক্ত শিক্ষার্থী</span><strong class="ppa-bangla"><?php echo esc_html($audience); ?></strong></div>
          <?php endif; ?>
          <div class="ppa-course-actions">
            <a class="ppa-button" href="https://wa.me/8801312364499?text=<?php echo rawurlencode('আমি ' . get_the_title() . ' প্রোগ্রাম সম্পর্কে বিস্তারিত জানতে চাই।'); ?>" target="_blank" rel="noopener noreferrer">ভর্তি ও ব্যাচ সম্পর্কে জানুন ↗</a>
            <a class="ppa-button ppa-button-outline" href="tel:+8801312364499">কল করুন</a>
          </div>
        </div>
        <?php $image = get_post_meta(get_the_ID(), '_ppa_course_image', true); if (!$image && has_post_thumbnail()) { $image = get_the_post_thumbnail_url(get_the_ID(), 'large'); } ?>
        <?php if ($image) : ?><figure class="ppa-course-image"><img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" fetchpriority="high"></figure><?php endif; ?>
      </div>
    </div>
  </section>
  <section class="ppa-section">
    <div class="ppa-container ppa-course-content-grid">
      <article class="ppa-course-content">
        <p class="ppa-eyebrow">PROGRAM OVERVIEW — প্রোগ্রামের বিস্তারিত</p>
        <div class="entry-content ppa-bangla"><?php while (have_posts()) : the_post(); the_content(); endwhile; ?></div>
      </article>
      <aside class="ppa-course-sidebar">
        <h2 class="ppa-bangla">এই প্রোগ্রামের বৈশিষ্ট্য</h2>
        <?php $features = get_post_meta(get_the_ID(), '_ppa_course_features', true); if (is_array($features) && $features) : ?>
          <ul><?php foreach ($features as $feature) : ?><li class="ppa-bangla"><?php echo esc_html($feature); ?></li><?php endforeach; ?></ul>
        <?php else : ?><p class="ppa-bangla">প্রোগ্রামের বিস্তারিত জানতে সরাসরি যোগাযোগ করুন।</p><?php endif; ?>
        <div class="ppa-sidebar-contact"><span>ADMISSIONS & INFORMATION</span><strong>+880 1312-364499</strong><a href="https://wa.me/8801312364499" target="_blank" rel="noopener noreferrer">WhatsApp-এ কথা বলুন ↗</a></div>
      </aside>
    </div>
  </section>
  <section class="ppa-course-cta"><div class="ppa-container"><div><p class="ppa-eyebrow">READY TO GET STARTED?</p><h2 class="ppa-bangla">আপনার শেখার যাত্রা শুরু হোক আজই</h2><p class="ppa-bangla">বর্তমান ব্যাচ, রুটিন ও ভর্তি সংক্রান্ত তথ্য জানতে আমাদের সঙ্গে যোগাযোগ করুন।</p></div><a class="ppa-button ppa-button-light" href="https://wa.me/8801312364499" target="_blank" rel="noopener noreferrer">যোগাযোগ করুন ↗</a></div></section>
</main>
<?php get_footer(); ?>
