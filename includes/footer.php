</main>

<footer class="site-footer">
  <div class="container footer-grid">
    <div class="footer-col footer-col--brand">
      <a href="<?= asset('index.php') ?>" class="brand brand--footer">
        <?php if (setting('logo_path')): ?>
          <img src="<?= e(img(setting('logo_path'))) ?>" alt="<?= e(setting('company_name')) ?>" class="brand__logo">
        <?php else: ?>
          <span class="brand__mark">A</span>
          <span class="brand__name"><?= e(setting('company_name')) ?></span>
        <?php endif; ?>
      </a>
      <p class="footer-tagline"><?= e(setting('tagline')) ?></p>
      <div class="footer-socials">
        <?php
        $socials = db()->query('SELECT * FROM social_links WHERE is_active = 1 AND url != "" ORDER BY sort_order')->fetchAll();
        foreach ($socials as $s): ?>
          <a href="<?= e($s['url']) ?>" target="_blank" rel="noopener" aria-label="<?= e($s['platform']) ?>"><i class="<?= e($s['icon_class']) ?>"></i></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="footer-col">
      <h4>Explore</h4>
      <ul>
        <li><a href="<?= asset('about.php') ?>">About Us</a></li>
        <li><a href="<?= asset('services.php') ?>">Services</a></li>
        <li><a href="<?= asset('projects.php') ?>">Projects</a></li>
        <li><a href="<?= asset('gallery.php') ?>">Gallery</a></li>
        <li><a href="<?= asset('blog.php') ?>">Journal</a></li>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Services</h4>
      <ul>
        <?php
        $footerServices = db()->query('SELECT title, slug FROM services WHERE is_published = 1 ORDER BY sort_order LIMIT 5')->fetchAll();
        foreach ($footerServices as $fs): ?>
          <li><a href="<?= asset('service-detail.php?slug=' . urlencode($fs['slug'])) ?>"><?= e($fs['title']) ?></a></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="footer-col">
      <h4>Get in Touch</h4>
      <ul class="footer-contact">
        <li><i class="fa-solid fa-location-dot"></i><span><?= e(setting('address')) ?></span></li>
        <li><i class="fa-solid fa-phone"></i><a href="<?= tel_link() ?>"><?= e(setting('phone')) ?></a></li>
        <li><i class="fa-solid fa-envelope"></i><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></li>
        <li><i class="fa-solid fa-clock"></i><span><?= e(setting('business_hours')) ?></span></li>
      </ul>
      <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="btn btn--whatsapp btn--sm" onclick="trackEvent('whatsapp_click','footer')">
        <i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp
      </a>
    </div>
  </div>

  <div class="footer-bottom">
    <div class="container footer-bottom__inner">
      <p>&copy; <?= date('Y') ?> <?= e(setting('company_name')) ?>. All rights reserved.</p>
      <p class="footer-credit">Interior Design Studio — Pakistan</p>
    </div>
  </div>
</footer>

<a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="whatsapp-float" aria-label="Chat on WhatsApp" onclick="trackEvent('whatsapp_click','floating_button')">
  <i class="fa-brands fa-whatsapp"></i>
  <span class="whatsapp-float__pulse"></span>
</a>

<div class="mobile-sticky-cta">
  <a href="<?= tel_link() ?>" class="mobile-sticky-cta__btn" onclick="trackEvent('call_click','sticky_bar')"><i class="fa-solid fa-phone"></i> Call</a>
  <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="mobile-sticky-cta__btn mobile-sticky-cta__btn--whatsapp" onclick="trackEvent('whatsapp_click','sticky_bar')"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a>
  <a href="<?= asset('contact.php') ?>#consult" class="mobile-sticky-cta__btn mobile-sticky-cta__btn--primary">Get Quote</a>
</div>

<script>window.SITE_BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="<?= asset('assets/js/main.js') ?>?v=<?= filemtime(BASE_PATH . '/assets/js/main.js') ?>" defer></script>
</body>
</html>
