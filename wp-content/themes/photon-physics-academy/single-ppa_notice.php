<?php if (!defined('ABSPATH')) { exit; } get_header(); ?>
<main class="ppa-container ppa-section">
<?php while (have_posts()) : the_post(); ?>
  <article <?php post_class('ppa-notice-card'); ?>>
    <p class="ppa-eyebrow">NOTICE BOARD — নোটিশ বোর্ড</p>
    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('d F Y')); ?></time>
    <h1 class="ppa-bangla"><?php the_title(); ?></h1>
    <div class="entry-content ppa-bangla"><?php the_content(); ?></div>
    <p><a class="ppa-button" href="<?php echo esc_url(home_url('/#notices')); ?>">← নোটিশ বোর্ডে ফিরে যান</a></p>
  </article>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
