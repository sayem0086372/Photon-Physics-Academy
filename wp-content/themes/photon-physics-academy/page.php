<?php if (!defined('ABSPATH')) { exit; } get_header(); ?>
<main class="ppa-container ppa-section">
<?php while (have_posts()) : the_post(); ?>
  <article <?php post_class(); ?>>
    <header><p class="ppa-eyebrow">PHOTON PHYSICS ACADEMY</p><h1><?php the_title(); ?></h1></header>
    <div class="entry-content"><?php the_content(); ?></div>
  </article>
<?php endwhile; ?>
</main>
<?php get_footer(); ?>
