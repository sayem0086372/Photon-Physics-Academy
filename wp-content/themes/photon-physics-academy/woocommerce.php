<?php if (!defined('ABSPATH')) { exit; } get_header(); ?>
<main class="ppa-container ppa-section ppa-store-page">
  <?php if (function_exists('is_shop') && (is_shop() || is_product_taxonomy())) : ?>
    <section class="ppa-bookstore-hero">
      <div class="ppa-bookstore-copy"><p class="ppa-eyebrow">PHOTON PHYSICS ACADEMY BOOKSTORE</p><h1 class="ppa-bangla">পদার্থবিজ্ঞান বুঝুন, বইয়ের সাথে এগিয়ে চলুন</h1><p class="ppa-bangla">কনসেপ্ট বুক, অনুশীলন ও প্রয়োজনীয় শিক্ষাসামগ্রী এক জায়গায়। পছন্দের বই বেছে নিয়ে অনলাইনে অর্ডার করুন।</p><a class="ppa-button" href="#ppa-book-catalogue">বইগুলো দেখুন ↓</a></div>
      <div class="ppa-floating-books" aria-label="একাডেমির বই"><img class="ppa-book-one" src="<?php echo esc_url(ppa_asset('book1')); ?>" alt="পদার্থবিজ্ঞানের বই"><img class="ppa-book-two" src="<?php echo esc_url(ppa_asset('book2')); ?>" alt="গতিবিদ্যার বই"></div>
    </section>
    <div id="ppa-book-catalogue" class="ppa-store-catalogue"><?php woocommerce_content(); ?></div>
  <?php else : ?>
    <?php woocommerce_content(); ?>
  <?php endif; ?>
</main>
<?php get_footer(); ?>