<?php
require_once __DIR__ . '/includes/bootstrap.php';
log_event('page_view', 'before_after');

$items = db()->query('SELECT * FROM before_after WHERE is_published = 1 ORDER BY sort_order')->fetchAll();

$page = [
    'title' => 'Before & After Transformations — ' . setting('company_name'),
    'description' => 'See real before-and-after interior design transformations by ' . setting('company_name') . '.',
    'canonical' => BASE_URL . '/before-after.php',
    'schema' => [schema_breadcrumbs([
        ['name' => 'Home', 'url' => BASE_URL . '/index.php'],
        ['name' => 'Before & After', 'url' => BASE_URL . '/before-after.php'],
    ])],
];
$currentNav = 'gallery';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <div class="breadcrumb"><a href="<?= asset('index.php') ?>">Home</a> / <span>Before &amp; After</span></div>
    <div class="eyebrow" style="color:#D2A24C">Real Transformations</div>
    <h1>See the Difference, Side by Side.</h1>
    <p>Drag the slider on each image to compare the space before and after our design and execution.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="ba-grid">
      <?php foreach ($items as $ba): ?>
        <div class="reveal">
          <div class="ba-item__label">
            <h3><?= e($ba['project_name']) ?></h3>
            <span><?= e($ba['description']) ?></span>
          </div>
          <div class="ba-slider">
            <img class="ba-slider__after" src="<?= e(img($ba['after_image'])) ?>" alt="After — <?= e($ba['project_name']) ?>">
            <img class="ba-slider__before" src="<?= e(img($ba['before_image'])) ?>" alt="Before — <?= e($ba['project_name']) ?>">
            <span class="ba-tag ba-tag--before">Before</span>
            <span class="ba-tag ba-tag--after">After</span>
            <div class="ba-handle"><span class="ba-handle__grip"><i class="fa-solid fa-left-right"></i></span></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="project-cta reveal mt-40">
      <h2>Ready for Your Own Transformation?</h2>
      <p>Book a free consultation and let's talk about what's possible for your space.</p>
      <div class="btn-row">
        <a href="<?= asset('contact.php') ?>#consult" class="btn btn--gradient">Book a Free Consultation</a>
        <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="btn btn--whatsapp" onclick="trackEvent('whatsapp_click','before_after_cta')"><i class="fa-brands fa-whatsapp"></i> WhatsApp Us</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
