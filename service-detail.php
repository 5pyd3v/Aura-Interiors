<?php
require_once __DIR__ . '/includes/bootstrap.php';

$slug = $_GET['slug'] ?? '';
$stmt = db()->prepare('SELECT * FROM services WHERE slug = ? AND is_published = 1');
$stmt->execute([$slug]);
$service = $stmt->fetch();

if (!$service) {
    http_response_code(404);
    $page = ['title' => 'Service Not Found — ' . setting('company_name')];
    $currentNav = 'services';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section text-center"><div class="container"><h1>Service Not Found</h1><p>The service you are looking for is no longer available.</p><a href="' . asset('services.php') . '" class="btn btn--gradient mt-40">Back to Services</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$otherServices = db()->prepare('SELECT * FROM services WHERE is_published = 1 AND id != ? ORDER BY sort_order LIMIT 3');
$otherServices->execute([$service['id']]);
$otherServices = $otherServices->fetchAll();

$relatedProjects = db()->prepare('SELECT * FROM projects WHERE is_published = 1 AND category = ? ORDER BY sort_order LIMIT 3');
$relatedProjects->execute([explode(' ', $service['title'])[0]]);
$relatedProjects = $relatedProjects->fetchAll();

$page = [
    'title' => $service['title'] . ' in Pakistan — ' . setting('company_name'),
    'description' => $service['short_description'],
    'canonical' => BASE_URL . '/service-detail.php?slug=' . urlencode($service['slug']),
    'image' => img($service['image_path']),
    'schema' => [schema_service($service), schema_breadcrumbs([
        ['name' => 'Home', 'url' => BASE_URL . '/index.php'],
        ['name' => 'Services', 'url' => BASE_URL . '/services.php'],
        ['name' => $service['title'], 'url' => BASE_URL . '/service-detail.php?slug=' . urlencode($service['slug'])],
    ])],
];
$currentNav = 'services';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <div class="breadcrumb"><a href="<?= asset('index.php') ?>">Home</a> / <a href="<?= asset('services.php') ?>">Services</a> / <span><?= e($service['title']) ?></span></div>
    <div class="eyebrow" style="color:#D2A24C"><i class="<?= e($service['icon_class']) ?>"></i> Service</div>
    <h1><?= e($service['title']) ?></h1>
    <p><?= e($service['short_description']) ?></p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="about-story">
      <div class="about-story__media reveal">
        <img src="<?= e(img($service['image_path'])) ?>" alt="<?= e($service['title']) ?>">
      </div>
      <div class="reveal reveal-delay-1">
        <div class="eyebrow">Overview</div>
        <h2>How We Approach This Service</h2>
        <div style="margin-top:18px;font-size:1.05rem;color:var(--ink-soft)"><?= $service['content'] ? nl2br(e($service['content'])) : '' ?></div>
        <div class="btn-row mt-40">
          <a href="<?= asset('contact.php') ?>#consult" class="btn btn--gradient">Request a Quote</a>
          <a href="<?= e(whatsapp_link('Assalamualaikum, I am interested in your ' . $service['title'] . ' service. I would like to discuss my project.')) ?>" target="_blank" rel="noopener" class="btn btn--whatsapp" onclick="trackEvent('whatsapp_click','service_detail')"><i class="fa-brands fa-whatsapp"></i> WhatsApp Us</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ($relatedProjects): ?>
<section class="section section--tint">
  <div class="container">
    <div class="section-head reveal">
      <div class="eyebrow">Related Work</div>
      <h2>Projects in This Category</h2>
    </div>
    <div class="project-grid">
      <?php foreach ($relatedProjects as $i => $p): ?>
        <a href="<?= asset('project-detail.php?slug=' . urlencode($p['slug'])) ?>" class="project-card reveal reveal-delay-<?= $i + 1 ?>" style="aspect-ratio:4/3">
          <div class="project-card__img"><img src="<?= e(img($p['cover_image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy"></div>
          <span class="project-card__tag"><?= e($p['category']) ?></span>
          <div class="project-card__body"><h3><?= e($p['title']) ?></h3><span><i class="fa-solid fa-location-dot"></i> <?= e($p['location']) ?></span></div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div class="eyebrow">Explore More</div>
      <h2>Other Services</h2>
    </div>
    <div class="service-grid">
      <?php foreach ($otherServices as $i => $s): ?>
        <div class="service-card reveal reveal-delay-<?= $i + 1 ?>">
          <div class="service-card__media">
            <img src="<?= e(img($s['image_path'])) ?>" alt="<?= e($s['title']) ?>" loading="lazy">
          </div>
          <div class="service-card__icon"><i class="<?= e($s['icon_class']) ?>"></i></div>
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

<?php require __DIR__ . '/includes/footer.php'; ?>
