<?php
require_once __DIR__ . '/includes/bootstrap.php';
log_event('page_view', 'services');

$services = db()->query('SELECT * FROM services WHERE is_published = 1 ORDER BY sort_order')->fetchAll();

$page = [
    'title' => 'Interior Design Services in Pakistan — ' . setting('company_name'),
    'description' => 'Residential, commercial, restaurant, office and turnkey interior design services across Pakistan, delivered by ' . setting('company_name') . '.',
    'canonical' => BASE_URL . '/services.php',
    'schema' => [schema_breadcrumbs([
        ['name' => 'Home', 'url' => BASE_URL . '/index.php'],
        ['name' => 'Services', 'url' => BASE_URL . '/services.php'],
    ])],
];
$currentNav = 'services';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <div class="breadcrumb"><a href="<?= asset('index.php') ?>">Home</a> / <span>Services</span></div>
    <div class="eyebrow" style="color:#D2A24C">What We Do</div>
    <h1>Interior Design Services, End to End.</h1>
    <p>Whether it's a single room or a full commercial fit-out, our team handles design, procurement and execution under one roof.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="service-grid">
      <?php foreach ($services as $i => $s): ?>
        <div class="service-card reveal reveal-delay-<?= ($i % 3) + 1 ?>">
          <div class="service-card__media">
            <img src="<?= e(img($s['image_path'])) ?>" alt="<?= e($s['title']) ?>" loading="lazy">
            <div class="service-card__icon"><i class="<?= e($s['icon_class'] ?: 'fa-solid fa-house') ?>"></i></div>
          </div>
          <div class="service-card__body">
            <h3><?= e($s['title']) ?></h3>
            <p><?= e($s['short_description']) ?></p>
            <a href="<?= asset('service-detail.php?slug=' . urlencode($s['slug'])) ?>" class="service-card__link">Learn More <i class="fa-solid fa-arrow-right"></i></a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<section class="section section--tint">
  <div class="container">
    <div class="consult-wrap reveal">
      <div class="consult-info">
        <div class="eyebrow" style="color:#D2A24C">Not Sure Where to Start?</div>
        <h2>Let's Discuss Your Project.</h2>
        <p>Share a few details and our team will recommend the right service and next steps for your space.</p>
        <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="btn btn--whatsapp" onclick="trackEvent('whatsapp_click','services_consult')"><i class="fa-brands fa-whatsapp"></i> Chat on WhatsApp</a>
      </div>
      <div class="consult-form">
        <?php include __DIR__ . '/includes/consult-form.php'; ?>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
