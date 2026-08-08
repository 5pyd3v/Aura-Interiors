<?php
require_once __DIR__ . '/includes/bootstrap.php';
log_event('page_view', 'contact');

$page = [
    'title' => 'Contact Us — ' . setting('company_name'),
    'description' => 'Get in touch with ' . setting('company_name') . ' for a free interior design consultation. Call, WhatsApp or request a quote online.',
    'canonical' => BASE_URL . '/contact.php',
    'schema' => [schema_local_business(), schema_breadcrumbs([
        ['name' => 'Home', 'url' => BASE_URL . '/index.php'],
        ['name' => 'Contact', 'url' => BASE_URL . '/contact.php'],
    ])],
];
$currentNav = 'contact';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <div class="breadcrumb"><a href="<?= asset('index.php') ?>">Home</a> / <span>Contact</span></div>
    <div class="eyebrow" style="color:#D2A24C">Get in Touch</div>
    <h1>Let's Talk About Your Space.</h1>
    <p>Call, WhatsApp, or send us your project details — our design team typically responds within one business day.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="contact-info-grid">
      <div class="contact-info-card reveal">
        <i class="fa-solid fa-phone"></i>
        <h3>Call Us</h3>
        <p><a href="<?= tel_link() ?>" onclick="trackEvent('call_click','contact_page')"><?= e(setting('phone')) ?></a></p>
      </div>
      <div class="contact-info-card reveal reveal-delay-1">
        <i class="fa-brands fa-whatsapp"></i>
        <h3>WhatsApp</h3>
        <p><a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" onclick="trackEvent('whatsapp_click','contact_page')">Chat with our team</a></p>
      </div>
      <div class="contact-info-card reveal reveal-delay-2">
        <i class="fa-solid fa-envelope"></i>
        <h3>Email</h3>
        <p><a href="mailto:<?= e(setting('email')) ?>"><?= e(setting('email')) ?></a></p>
      </div>
    </div>

    <div class="consult-wrap reveal" id="consult">
      <div class="consult-info">
        <div class="eyebrow" style="color:#D2A24C">Start Your Project</div>
        <h2>Let's Create Your Space.</h2>
        <p>Tell us a little about your project and our design team will reach out within one business day.</p>
        <ul class="consult-info__list">
          <li><i class="fa-solid fa-location-dot"></i> <?= e(setting('address')) ?></li>
          <li><i class="fa-solid fa-clock"></i> <?= e(setting('business_hours')) ?></li>
          <li><i class="fa-solid fa-shield-halved"></i> Your information is kept confidential</li>
        </ul>
        <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="btn btn--whatsapp" onclick="trackEvent('whatsapp_click','contact_consult')">
          <i class="fa-brands fa-whatsapp"></i> Prefer WhatsApp? Chat Now
        </a>
      </div>
      <div class="consult-form">
        <?php include __DIR__ . '/includes/consult-form.php'; ?>
      </div>
    </div>

    <?php if (setting('google_maps_url')): ?>
    <div class="map-embed reveal">
      <iframe src="<?= e(setting('google_maps_url')) ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Studio location"></iframe>
    </div>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
