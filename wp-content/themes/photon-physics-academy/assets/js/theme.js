(function () {
  'use strict';
  document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.ppa-menu-toggle');
    var menu = document.getElementById('ppa-mobile-nav');
    if (toggle && menu) {
      toggle.addEventListener('click', function () {
        var expanded = toggle.getAttribute('aria-expanded') === 'true';
        toggle.setAttribute('aria-expanded', String(!expanded));
        menu.classList.toggle('is-open', !expanded);
      });
      menu.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
          menu.classList.remove('is-open');
          toggle.setAttribute('aria-expanded', 'false');
        });
      });
    }

    var noticeButtons = document.querySelectorAll('[data-notice-filter]');
    var noticeCards = document.querySelectorAll('[data-notice-card]');
    var emptyNotice = document.querySelector('.ppa-notice-empty');
    noticeButtons.forEach(function (button) {
      button.addEventListener('click', function () {
        var category = button.getAttribute('data-notice-filter');
        noticeButtons.forEach(function (item) { item.classList.toggle('is-active', item === button); });
        var visible = 0;
        noticeCards.forEach(function (card) {
          var show = category === 'all' || card.getAttribute('data-category') === category;
          card.hidden = !show;
          if (show) visible++;
        });
        if (emptyNotice) emptyNotice.hidden = visible > 0;
      });
    });

    var noticeModal = document.getElementById('ppa-notice-modal');
    if (noticeModal) {
      document.querySelectorAll('[data-notice-open]').forEach(function (button) {
        button.addEventListener('click', function () {
          document.getElementById('ppa-notice-modal-title').textContent = button.getAttribute('data-title') || '';
          document.getElementById('ppa-notice-modal-date').textContent = button.getAttribute('data-date') || '';
          document.getElementById('ppa-notice-modal-content').textContent = button.getAttribute('data-content') || '';
          noticeModal.showModal();
        });
      });
      noticeModal.querySelector('.ppa-modal-close').addEventListener('click', function () { noticeModal.close(); });
      noticeModal.addEventListener('click', function (event) { if (event.target === noticeModal) noticeModal.close(); });
    }

    var galleryModal = document.getElementById('ppa-gallery-modal');
    if (galleryModal) {
      var galleryImages = Array.prototype.slice.call(document.querySelectorAll('[data-gallery-open] img'));
      var currentImage = 0;
      var largeImage = document.getElementById('ppa-gallery-large');
      var caption = document.getElementById('ppa-gallery-caption');
      function showGalleryImage(index) {
        currentImage = (index + galleryImages.length) % galleryImages.length;
        largeImage.src = galleryImages[currentImage].src;
        largeImage.alt = galleryImages[currentImage].alt;
        caption.textContent = galleryImages[currentImage].alt;
      }
      document.querySelectorAll('[data-gallery-open]').forEach(function (button) {
        button.addEventListener('click', function () { showGalleryImage(Number(button.getAttribute('data-gallery-open'))); galleryModal.showModal(); });
      });
      galleryModal.querySelector('.ppa-modal-close').addEventListener('click', function () { galleryModal.close(); });
      galleryModal.querySelector('.ppa-lightbox-prev').addEventListener('click', function () { showGalleryImage(currentImage - 1); });
      galleryModal.querySelector('.ppa-lightbox-next').addEventListener('click', function () { showGalleryImage(currentImage + 1); });
      galleryModal.addEventListener('click', function (event) { if (event.target === galleryModal) galleryModal.close(); });
    }

    var counters = document.querySelectorAll('[data-count]');
    if ('IntersectionObserver' in window && counters.length) {
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var el = entry.target, target = Number(el.getAttribute('data-count')), suffix = el.getAttribute('data-suffix') || '', start = 0, duration = 1300, began = null;
          function tick(timestamp) {
            if (!began) began = timestamp;
            var progress = Math.min((timestamp - began) / duration, 1);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = Math.round(start + (target - start) * eased).toLocaleString('en-US') + suffix;
            if (progress < 1) window.requestAnimationFrame(tick);
          }
          window.requestAnimationFrame(tick);
          observer.unobserve(el);
        });
      }, { threshold: 0.4 });
      counters.forEach(function (counter) { observer.observe(counter); });
    }
  });
})();