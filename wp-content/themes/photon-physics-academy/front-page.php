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
    <a class="ppa-store-button" href="<?php echo esc_url(home_url('/store/')); ?>">Browse &amp; Buy Books ↗</a>
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
  $notice_query = new WP_Query(array('post_type'=>'ppa_notice','post_status'=>'publish','posts_per_page'=>6,'orderby'=>'date','order'=>'DESC'));
  if ($notice_query->have_posts()) : ?>
  <section id="notices" class="ppa-section ppa-gray"><div class="ppa-container">
    <p class="ppa-eyebrow">NOTICE BOARD — নোটিশ বোর্ড</p><h2 class="ppa-bangla">একাডেমিক নোটিশ ও নির্দেশনা</h2><p class="ppa-lead ppa-bangla">ভর্তি, পরীক্ষা ও ক্লাসরুটিনের তথ্য ও বিজ্ঞপ্তি।</p>
    <div class="ppa-cards">
      <?php while ($notice_query->have_posts()) : $notice_query->the_post(); ?>
        <article class="ppa-notice-card"><time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('d F Y')); ?></time><h3 class="ppa-bangla"><?php the_title(); ?></h3><p class="ppa-bangla"><?php echo esc_html(ppa_excerpt(get_the_ID(), 26)); ?></p><a class="ppa-button" style="margin-top:16px" href="<?php the_permalink(); ?>">বিস্তারিত দেখুন →</a></article>
      <?php endwhile; ?>
    </div>
  </div></section>
  <?php endif; wp_reset_postdata(); ?>

  <section class="ppa-stats"><div class="ppa-container ppa-stat-grid">
    <div class="ppa-stat"><strong>23 Years</strong><b class="ppa-bangla">শিক্ষার ক্ষেত্রে আমাদের ২৩ বছরের ঐতিহ্য</b></div>
    <div class="ppa-stat"><strong>11,000+</strong><b class="ppa-bangla">শিক্ষার্থী আমাদের কাছ থেকে পড়াশোনা করেছে</b></div>
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

  <section id="programs" class="ppa-section ppa-gray"><div class="ppa-container">
    <p class="ppa-eyebrow">LEARNING PATHWAYS — শিক্ষা কার্যক্রম</p><h2 class="ppa-bangla">শ্রেণিভিত্তিক অ্যাকাডেমিক কার্যক্রম</h2><p class="ppa-lead ppa-bangla">নবম ও দশম শ্রেণির শিক্ষার্থীদের জন্য পদার্থবিজ্ঞানের সমন্বিত ও নিবিড় পাঠদান রূপরেখা।</p>
    <div class="ppa-cards">
      <article class="ppa-card"><img src="https://cdn.phototourl.com/member/2026-10-24-d41bef46-5d75-4bd3-b5d7-8dc5cc22ea39.png" alt="Class eight learning programme" loading="lazy"><div class="ppa-card-body"><h3 class="ppa-bangla">অষ্টম শ্রেণি</h3><p class="ppa-bangla">পদার্থবিজ্ঞানের ভিত্তি ও প্রাথমিক ধারণা গড়ে তোলার পাঠদান।</p></div></article>
      <article class="ppa-card"><img src="https://cdn.phototourl.com/member/2026-10-24-b370d7b1-7e0f-4454-9623-fbca99e4b95a.png" alt="Class nine physics programme" loading="lazy"><div class="ppa-card-body"><h3 class="ppa-bangla">নবম শ্রেণি</h3><p class="ppa-bangla">কনসেপ্ট, থিওরি, গাণিতিক সমস্যা ও নিয়মিত অনুশীলন।</p></div></article>
      <article class="ppa-card"><img src="https://cdn.phototourl.com/member/2026-10-24-eab7643b-7bec-4dd6-b5aa-7a8cfd4bc8ac.png" alt="Class ten physics programme" loading="lazy"><div class="ppa-card-body"><h3 class="ppa-bangla">দশম শ্রেণি</h3><p class="ppa-bangla">এসএসসি প্রস্তুতি, অধ্যায়ভিত্তিক মূল্যায়ন ও প্রশ্ন সমাধান।</p></div></article>
    </div>
    <p class="ppa-bangla" style="margin-top:20px;color:#64748b">বর্তমান সক্রিয় ব্যাচ ও রুটিন নিশ্চিতকরণের জন্য সরাসরি একাডেমি অফিসে যোগাযোগ করুন।</p>
  </div></section>

  <section id="gallery" class="ppa-section"><div class="ppa-container">
    <p class="ppa-eyebrow">CLASSROOM GALLERY — ক্লাসরুম</p><h2 class="ppa-bangla">শেখার পরিবেশ ও একাডেমির মুহূর্ত</h2><p class="ppa-lead ppa-bangla">আমাদের শ্রেণিকক্ষের পাঠদান ও শিক্ষামূলক কার্যক্রমের কিছু মুহূর্ত।</p>
    <div class="ppa-gallery-grid">
      <img src="<?php echo esc_url(ppa_asset('gallery1')); ?>" alt="Students discussing physics in class" loading="lazy">
      <img src="<?php echo esc_url(ppa_asset('gallery2')); ?>" alt="Students reading concept books" loading="lazy">
      <img src="<?php echo esc_url(ppa_asset('hero')); ?>" alt="Physics classroom" loading="lazy">
      <img src="<?php echo esc_url(ppa_asset('teacher')); ?>" alt="Physics teacher mentoring students" loading="lazy">
    </div>
  </div></section>

  <section id="contact" class="ppa-section ppa-gray"><div class="ppa-container">
    <p class="ppa-eyebrow">START A CONVERSATION — যোগাযোগ</p><h2 class="ppa-bangla">ভর্তি সংক্রান্ত তথ্যের জন্য যোগাযোগ করুন</h2><p class="ppa-lead ppa-bangla">কোর্সের সময়সূচি, আসন সংখ্যা বা কনসেপ্ট বুক সম্পর্কে বিস্তারিত জানতে সরাসরি আমাদের ফোন নম্বরে যোগাযোগ করুন অথবা WhatsApp-এ বার্তা পাঠান।</p>
    <div class="ppa-contact-grid">
      <div class="ppa-contact-card"><h3 class="ppa-bangla">সরাসরি হেল্পলাইন</h3><p><a href="tel:+8801312364499">+880 1312-364499</a></p><p><a href="tel:01722003995">01722-003995</a></p><hr><h3 class="ppa-bangla">একাডেমির অফিশিয়াল ঠিকানা</h3><p class="ppa-bangla">১০, জিলা স্কুল রোড (জিলা স্কুলের বিপরীত গলি), ময়মনসিংহ</p><p class="ppa-bangla">যোগাযোগের সময়: সকাল ৯:০০ টা – রাত ৯:০০ টা (প্রতিদিন)</p></div>
      <div class="ppa-contact-card"><h3>WhatsApp Us</h3><p>Have a question about admissions, class schedules, or concept books? Chat with Photon Physics Academy directly on WhatsApp.</p><a class="ppa-button" href="https://wa.me/8801312364499" target="_blank" rel="noopener noreferrer">WhatsApp-এ যোগাযোগ করুন ↗</a></div>
    </div>
  </div></section>
</main>
<?php get_footer(); ?>
