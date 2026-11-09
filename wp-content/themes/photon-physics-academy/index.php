<?php if (!defined('ABSPATH')) { exit; } get_header(); ?>
<main class="ppa-container ppa-section">
<?php if (have_posts()) : while (have_posts()) : the_post(); ?>
  <article <?php post_class('ppa-notice-card'); ?>>
    <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
    <p><?php echo esc_html(ppa_excerpt(get_the_ID(), 36)); ?></p>
    <a class="ppa-button" href="<?php the_permalink(); ?>">Read more</a>
  </article>
<?php endwhile; the_posts_pagination(); else : ?>
  <h1><?php esc_html_e('Nothing found', 'photon-physics-academy'); ?></h1>
<?php endif; ?>
</main>
<?php get_footer(); ?>
