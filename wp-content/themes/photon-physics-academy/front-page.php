<?php
/**
 * Custom homepage matching the original Photon Physics Academy layout.
 * @package Photon_Physics_Academy
 */
if (!defined('ABSPATH')) { exit; }
get_header();
?>
<main id="primary">
  <section class="ppa-store-banner" aria-labelledby="ppa-store-title"><div class="ppa-container"><div class="ppa-store-box">
    <div><div class="ppa-eyebrow" style="margin:0">PHOTON PHYSICS ACADEMY</div><h2 id="ppa-store-title" class="ppa-store-title">Find Your Next Book</h2><p style="margin:0;color:#64748b">Explore concept books and other learning resources, and order online.</p></div>
    <a class="ppa-store-button" href="<?php echo esc_url(home_url('/shop/')); ?>">Browse &amp; Buy Books ↗</a>
  </div></div></section>

  <section id="home" class="ppa-hero"><div class="ppa-container">
    <div class="ppa-kicker"><strong class="ppa-bangla">১০, জিলা স্কুল রোড, ময়মনসিংহ</strong><span>·</span><span class="ppa-bangla">কনসেপ্টভিত্তিক পদার্থবিজ্ঞান একাডেমি</span><span>·</span><span class="ppa-bangla">নবম ও দশম শ্রেণি অ্যাকাডেমিক কেয়ার</span></div>
    <div class="ppa-hero-grid">
      <div>
        <h1>PHOTON <span>PHYSICS</span> ACADEMY</h1>
        <h2>Feel the Power of <span style="color:#1d4ed8">Physics...</span></h2>
        <h2 class="ppa-bangla" style="font-size:clamp(23px,2.7vw,34px);color:#334155">পদার্থবিজ্ঞানের ধারণা হোক সহজ ও আনন্দময়</h2>
        <p class="ppa-bangla">Physics learning in Mymensingh with a focus on concepts, practice and confidence. মুখস্থের পরিবর্তে প্রতিটি সূত্রের গভীর বাস্তবতা, যৌক্তিক বিশ্লেষণ ও নিয়মিত অনুশীলনের মাধ্যমে পদার্থবিজ্ঞানে নিখুঁত পারদর্শিতা অর্জন।</p>
        <div class="ppa-actions"><a class="ppa-button" href="#programs">Explore Programs →</a><a class="ppa-button ppa-button-outline" href="#contact">যোগাযোগ করুন</a><a class="ppa-button ppa-button-light" href="#contact">ভর্তি অনুসন্ধান</a></div>
        <div class="ppa-phone-row"><strong class="ppa-bangla">সরাসরি কথা বলুন:</strong><a href="tel:+8801312364499">+880 1312-364499</a><a href="tel:01722003995">01722-003995</a></div>
      </div>
      <figure class="ppa-hero-photo"><img src="<?php echo esc_url(ppa_asset('hero')); ?>" alt="Photon Physics Academy classroom learning in Mymensingh" fetchpriority="high"><figcaption class="ppa-bangla">ক্লাসরুম শিখন পরিবেশ · ময়মনসিংহ</figcaption></figure>
    </div>
  </div></section>

  <?php
  $notice_query = new WP_Query(array('post_type'=>'ppa_notice','post_status'=>'publish','posts_per_page'=>12,'orderby'=>'date','order'=>'DESC'));
  $notice_terms = get_terms(array('taxonomy'=>'ppa_notice_category','hide_empty'=>true));
  ?>
  <section id="notices" class="ppa-section ppa-gray"><div class="ppa-container">
    <p class="ppa-eyebrow">NOTICE BOARD — নোটিশ বোর্ড</p><h2 class="ppa-bangla">একাডেমিক নোটিশ ও নির্দেশনা</h2><p class="ppa-lead ppa-bangla">ভর্তি, পরীক্ষা ও ক্লাসরুটিনের তথ্য ও বিজ্ঞপ্তি।</p>
    <div class="ppa-notice-filters" role="group" aria-label="নোটিশ বিভাগ">
      <button type="button" class="is-active" data-notice-filter="all">সব নোটিশ</button>
      <?php if (!is_wp_error($notice_terms) && $notice_terms) : foreach ($notice_terms as $term) : ?>
        <button type="button" data-notice-filter="<?php echo esc_attr($term->slug); ?>"><?php echo esc_html($term->name); ?></button>
      <?php endforeach; endif; ?>
    </div>
    <?php if ($notice_query->have_posts()) : ?><div class="ppa-cards ppa-notice-grid">
      <?php while ($notice_query->have_posts()) : $notice_query->the_post(); $notice_cats=get_the_terms(get_the_ID(),'ppa_notice_category'); $notice_slug=(!is_wp_error($notice_cats)&&$notice_cats)?$notice_cats[0]->slug:'uncategorized'; ?>
        <article class="ppa-notice-card" data-notice-card data-category="<?php echo esc_attr($notice_slug); ?>">
          <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('d F Y')); ?></time>
          <h3 class="ppa-bangla"><?php the_title(); ?></h3>
          <p class="ppa-bangla"><?php echo esc_html(ppa_excerpt(get_the_ID(), 26)); ?></p>
          <button class="ppa-button ppa-notice-open" type="button" data-notice-open data-title="<?php echo esc_attr(get_the_title()); ?>" data-date="<?php echo esc_attr(get_the_date('d F Y')); ?>" data-content="<?php echo esc_attr(wp_strip_all_tags(apply_filters('the_content',get_the_content()))); ?>">বিস্তারিত দেখুন →</button>
        </article>
      <?php endwhile; ?>
    </div><p class="ppa-notice-empty ppa-bangla" hidden>এই বিভাগে কোনো নোটিশ পাওয়া যায়নি।</p>
    <?php else : ?><div class="ppa-notice-card"><p class="ppa-bangla" style="margin:0">বর্তমানে কোনো নোটিশ প্রকাশিত হয়নি। নতুন নোটিশ প্রকাশিত হলে এখানে দেখা যাবে।</p></div><?php endif; ?>
  </div></section>
  <dialog class="ppa-modal" id="ppa-notice-modal" aria-labelledby="ppa-notice-modal-title"><div class="ppa-modal-inner"><button class="ppa-modal-close" type="button" aria-label="বন্ধ করুন">×</button><p class="ppa-eyebrow">ACADEMY NOTICE</p><h2 id="ppa-notice-modal-title" class="ppa-bangla"></h2><time id="ppa-notice-modal-date"></time><div id="ppa-notice-modal-content" class="ppa-bangla"></div></div></dialog>
  <?php wp_reset_postdata(); ?>

  <section class="ppa-stats"><div class="ppa-container ppa-stat-grid">
    <div class="ppa-stat"><strong data-count="23" data-suffix=" Years">0</strong><b class="ppa-bangla">শিক্ষার ক্ষেত্রে আমাদের ২৩ বছরের ঐতিহ্য</b></div>
    <div class="ppa-stat"><strong data-count="11000" data-suffix="+">0</strong><b class="ppa-bangla">শিক্ষার্থী আমাদের কাছ থেকে পড়াশোনা করেছে</b></div>
  </div></section>

  <section id="about" class="ppa-section"><div class="ppa-container">
    <p class="ppa-eyebrow">ABOUT PHOTON — আমাদের সম্পর্কে</p><h2 class="ppa-bangla">পদার্থবিজ্ঞানের আনন্দময় ও স্বচ্ছ ধারণার অভিযাত্রা</h2>
    <p class="ppa-lead ppa-bangla">Photon Physics Academy focuses on making physics understandable, enjoyable, and concept-based for students in Mymensingh. আমরা বিশ্বাস করি, পদার্থবিজ্ঞান কোনো ভীতি বা মুখস্থের বিষয় নয়—এটি যুক্তি, পর্যবেক্ষণ ও চিন্তাশীলতার বিজ্ঞান।</p>
    <div class="ppa-about-grid"><img class="ppa-about-photo" src="<?php echo esc_url(ppa_asset('teacher')); ?>" alt="Physics conceptual teaching at Photon Physics Academy" loading="lazy">
      <div class="ppa-feature-grid">
        <article class="ppa-feature"><h3 class="ppa-bangla">কনসেপ্ট ও থিওরির স্পষ্টতা</h3><p class="ppa-bangla">মুখস্থ নির্ভরতার বিপরীতে প্রতিটি সূত্রের উৎপত্তি, তাৎপর্য ও ব্যবহারিক প্রয়োগের পুঙ্খানুপুঙ্খ ব্যাখ্যা।</p></article>
        <article class="ppa-feature"><h3 class="ppa-bangla">গাণিতিক সমস্যা সমাধান</h3><p class="ppa-bangla">সৃজনশীল প্রশ্ন (CQ) এবং বহুনির্বাচনী (MCQ)-এর জন্য পর্যায়ক্রমিক সমাধান পদ্ধতি ও ক্যালকুলেশন দক্ষতা।</p></article>
        <article class="ppa-feature"><h3 class="ppa-bangla">ব্যক্তিগত যত্ন ও পরামর্শ</h3><p class="ppa-bangla">শিক্ষার্থীদের ব্যক্তিগত জিজ্ঞাসা নিরসন ও নিয়মিত অগ্রগতি পর্যবেক্ষণের মাধ্যমে আত্মবিশ্বাস তৈরি।</p></article>
        <article class="ppa-feature"><h3 class="ppa-bangla">পরিমিত ও নিবেদিত পরিবেশ</h3><p class="ppa-bangla">একটি সুশৃঙ্খল ও আন্তরিক ক্লাসরুম যেখানে প্রশ্ন করতে শিক্ষার্থীরা কখনোই দ্বিধাবোধ করে না।</p></article>
      </div>
    </div>
  </div></section>

  <section id="programs" class="ppa-section ppa-programs-section">
    <div class="ppa-container">
      <div class="ppa-section-heading-row">
        <div>
          <p class="ppa-eyebrow">LEARNING PATHWAYS — শিক্ষা কার্যক্রম</p>
          <h2 class="ppa-bangla">শ্রেণিভিত্তিক অ্যাকাডেমিক কার্যক্রম</h2>
          <p class="ppa-lead ppa-bangla">প্রতিটি শ্রেণির জন্য পরিকল্পিত পাঠদান, কনসেপ্টের স্পষ্টতা এবং ধারাবাহিক অনুশীলন।</p>
        </div>
      </div>
      <?php $course_query = new WP_Query(array('post_type'=>'ppa_course','post_status'=>'publish','posts_per_page'=>3,'orderby'=>'menu_order title','order'=>'ASC')); ?>
      <?php if ($course_query->have_posts()) : ?>
        <div class="ppa-program-grid ppa-course-reference-grid">
          <?php while ($course_query->have_posts()) : $course_query->the_post();
            $course_subtitle = get_post_meta(get_the_ID(), '_ppa_course_subtitle', true);
            $course_audience = get_post_meta(get_the_ID(), '_ppa_course_audience', true);
            $course_features = get_post_meta(get_the_ID(), '_ppa_course_features', true);
            $course_image = get_post_meta(get_the_ID(), '_ppa_course_image', true);
            $course_slug = get_post_field('post_name', get_the_ID());
            $course_badge = array('class-eight-science'=>'Class Eight Science','class-nine-physics'=>'Class Nine Physics','class-ten-physics'=>'Class Ten Physics');
          ?>
            <article class="ppa-program-card ppa-course-reference-card">
              <?php if ($course_image) : ?>
                <div class="ppa-course-reference-image"><img src="<?php echo esc_url($course_image); ?>" alt="<?php echo esc_attr(get_the_title()); ?>" loading="lazy"></div>
              <?php elseif (has_post_thumbnail()) : ?>
                <div class="ppa-course-reference-image"><?php the_post_thumbnail('large', array('loading'=>'lazy')); ?></div>
              <?php endif; ?>
              <div class="ppa-course-reference-body">
                <div class="ppa-course-reference-badge"><?php echo esc_html(isset($course_badge[$course_slug]) ? $course_badge[$course_slug] : get_the_title()); ?></div>
                <h3 class="ppa-bangla"><?php the_title(); ?></h3>
                <?php if ($course_subtitle) : ?><p class="ppa-course-reference-subtitle ppa-bangla"><?php echo esc_html($course_subtitle); ?></p><?php endif; ?>
                <?php if ($course_audience) : ?>
                  <div class="ppa-course-reference-audience"><span class="ppa-bangla">উপযুক্ত শিক্ষার্থী:</span> <strong class="ppa-bangla"><?php echo esc_html($course_audience); ?></strong></div>
                <?php endif; ?>
                <?php if (is_array($course_features) && $course_features) : ?>
                  <ul class="ppa-course-reference-features">
                    <?php foreach (array_slice($course_features,0,4) as $feature) : ?><li class="ppa-bangla"><?php echo esc_html($feature); ?></li><?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              </div>
              <div class="ppa-course-reference-footer">
                <p class="ppa-bangla"><span aria-hidden="true">◉</span> প্রস্তাবিত লার্নিং পাথওয়ে · বিস্তারিত জানতে যোগাযোগ করুন</p>
                <a class="ppa-course-reference-button ppa-bangla" href="https://wa.me/8801312364499" target="_blank" rel="noopener noreferrer"><span aria-hidden="true">◯</span> ব্যাচ ও সময়সূচি জানতে যোগাযোগ</a>
              </div>
            </article>
          <?php endwhile; ?>
        </div>
      <?php else : ?>
        <div class="ppa-notice-card"><p class="ppa-bangla">প্রোগ্রামের তথ্য শিগগিরই প্রকাশ করা হবে।</p></div>
      <?php endif; wp_reset_postdata(); ?>
    </div>
  </section>



  <section id="gallery" class="ppa-section"><div class="ppa-container">
    <p class="ppa-eyebrow">CLASSROOM GALLERY — ক্লাসরুম</p><h2 class="ppa-bangla">শেখার পরিবেশ ও একাডেমির মুহূর্ত</h2><p class="ppa-lead ppa-bangla">আমাদের শ্রেণিকক্ষের পাঠদান ও শিক্ষামূলক কার্যক্রমের কিছু মুহূর্ত।</p>
    <div class="ppa-gallery-grid">
      <?php $gallery_items=array(array('gallery1','Students discussing physics in class'),array('gallery2','Students reading concept books'),array('hero','Physics classroom'),array('teacher','Physics teacher mentoring students')); foreach($gallery_items as $i=>$item): ?>
        <button class="ppa-gallery-item" type="button" data-gallery-open="<?php echo esc_attr($i); ?>" aria-label="ছবি বড় করে দেখুন"><img src="<?php echo esc_url(ppa_asset($item[0])); ?>" alt="<?php echo esc_attr($item[1]); ?>" loading="lazy"><span aria-hidden="true">⤢</span></button>
      <?php endforeach; ?>
    </div>
    <dialog class="ppa-modal ppa-lightbox" id="ppa-gallery-modal" aria-label="ক্লাসরুম গ্যালারি"><div class="ppa-lightbox-inner"><button class="ppa-modal-close" type="button" aria-label="বন্ধ করুন">×</button><button type="button" class="ppa-lightbox-prev" aria-label="আগের ছবি">‹</button><img id="ppa-gallery-large" src="" alt=""><button type="button" class="ppa-lightbox-next" aria-label="পরের ছবি">›</button><p id="ppa-gallery-caption"></p></div></dialog>
  </div></section>

  <section id="contact" class="ppa-section ppa-gray"><div class="ppa-container">
    <p class="ppa-eyebrow">START A CONVERSATION — যোগাযোগ</p><h2 class="ppa-bangla">ভর্তি সংক্রান্ত তথ্যের জন্য যোগাযোগ করুন</h2><p class="ppa-lead ppa-bangla">কোর্সের সময়সূচি, আসন সংখ্যা বা কনসেপ্ট বুক সম্পর্কে বিস্তারিত জানতে সরাসরি আমাদের ফোন নম্বরে যোগাযোগ করুন অথবা WhatsApp-এ বার্তা পাঠান।</p>
    <div class="ppa-contact-grid">
      <div class="ppa-contact-card"><h3 class="ppa-bangla">সরাসরি হেল্পলাইন</h3><p><a href="tel:+8801312364499">+880 1312-364499</a></p><p><a href="tel:01722003995">01722-003995</a></p><hr><h3 class="ppa-bangla">একাডেমির অফিশিয়াল ঠিকানা</h3><p class="ppa-bangla">১০, জিলা স্কুল রোড (জিলা স্কুলের বিপরীত গলি), ময়মনসিংহ</p><p class="ppa-bangla">যোগাযোগের সময়: সকাল ৯:০০ টা – রাত ৯:০০ টা (প্রতিদিন)</p></div>
      <div class="ppa-contact-card ppa-inquiry-card"><h3 class="ppa-bangla">ভর্তি ও কোর্স সংক্রান্ত অনুসন্ধান</h3>
        <?php if(isset($_GET['ppa_inquiry']) && $_GET['ppa_inquiry']==='sent'): ?><p class="ppa-form-success ppa-bangla" role="status">আপনার বার্তা সফলভাবে পাঠানো হয়েছে। একাডেমি থেকে যোগাযোগ করা হবে।</p><?php elseif(isset($_GET['ppa_inquiry']) && $_GET['ppa_inquiry']==='error'): ?><p class="ppa-form-error ppa-bangla" role="alert">বার্তা পাঠানো যায়নি। অনুগ্রহ করে আবার চেষ্টা করুন অথবা ফোন করুন।</p><?php endif; ?>
        <form class="ppa-inquiry-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
          <input type="hidden" name="action" value="ppa_submit_inquiry"><?php wp_nonce_field('ppa_submit_inquiry','ppa_inquiry_nonce'); ?>
          <p class="ppa-honeypot" aria-hidden="true"><label>Leave this empty<input type="text" name="website" tabindex="-1" autocomplete="off"></label></p>
          <label class="ppa-bangla">আপনার নাম<input required name="inquiry_name" maxlength="100" autocomplete="name"></label>
          <label class="ppa-bangla">মোবাইল নম্বর<input required name="inquiry_phone" maxlength="24" inputmode="tel" autocomplete="tel"></label>
          <label class="ppa-bangla">কোন কোর্সে আগ্রহী?
            <select name="inquiry_course"><option value="">নির্বাচন করুন</option><option>অষ্টম শ্রেণি বিজ্ঞান</option><option>নবম শ্রেণি পদার্থবিজ্ঞান</option><option>দশম শ্রেণি পদার্থবিজ্ঞান</option><option>অন্যান্য</option></select>
          </label>
          <label class="ppa-bangla">আপনার বার্তা<textarea name="inquiry_message" rows="3" maxlength="1200"></textarea></label>
          <button class="ppa-button" type="submit">অনুসন্ধান পাঠান</button>
        </form>
        <p style="margin-top:18px">অথবা <a href="https://wa.me/8801312364499" target="_blank" rel="noopener noreferrer">WhatsApp-এ যোগাযোগ করুন ↗</a></p>
      </div>
    </div>
  </div></section>
</main>
<?php get_footer(); ?>
