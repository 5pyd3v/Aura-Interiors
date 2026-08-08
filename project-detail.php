<?php
require_once __DIR__ . '/includes/bootstrap.php';

$slug = $_GET['slug'] ?? '';
$stmt = db()->prepare('SELECT * FROM projects WHERE slug = ? AND is_published = 1');
$stmt->execute([$slug]);
$project = $stmt->fetch();

if (!$project) {
    http_response_code(404);
    $page = ['title' => 'Project Not Found — ' . setting('company_name')];
    $currentNav = 'projects';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section text-center"><div class="container"><h1>Project Not Found</h1><p>This project may have been moved or unpublished.</p><a href="' . asset('projects.php') . '" class="btn btn--gradient mt-40">Back to Projects</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

db()->prepare('UPDATE projects SET views = views + 1 WHERE id = ?')->execute([$project['id']]);
log_event('project_view', $project['slug']);

$imgStmt = db()->prepare('SELECT * FROM project_images WHERE project_id = ? ORDER BY sort_order');
$imgStmt->execute([$project['id']]);
$galleryImages = $imgStmt->fetchAll();

$relStmt = db()->prepare('SELECT * FROM projects WHERE is_published = 1 AND category = ? AND id != ? ORDER BY sort_order LIMIT 3');
$relStmt->execute([$project['category'], $project['id']]);
$related = $relStmt->fetchAll();

$page = [
    'title' => $project['title'] . ' — ' . $project['category'] . ' Interior Design | ' . setting('company_name'),
    'description' => $project['short_description'],
    'canonical' => BASE_URL . '/project-detail.php?slug=' . urlencode($project['slug']),
    'image' => img($project['cover_image']),
    'type' => 'article',
    'schema' => [schema_breadcrumbs([
        ['name' => 'Home', 'url' => BASE_URL . '/index.php'],
        ['name' => 'Projects', 'url' => BASE_URL . '/projects.php'],
        ['name' => $project['title'], 'url' => BASE_URL . '/project-detail.php?slug=' . urlencode($project['slug'])],
    ])],
];
$currentNav = 'projects';
require __DIR__ . '/includes/header.php';
?>

<section class="project-hero">
  <img src="<?= e(img($project['cover_image'])) ?>" alt="<?= e($project['title']) ?>">
  <div class="project-hero__content">
    <div class="container">
      <div class="breadcrumb"><a href="<?= asset('index.php') ?>">Home</a> / <a href="<?= asset('projects.php') ?>">Projects</a> / <span><?= e($project['title']) ?></span></div>
      <div class="eyebrow"><?= e($project['category']) ?></div>
      <h1><?= e($project['title']) ?></h1>
    </div>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="project-meta">
      <div class="project-meta__item"><span>Location</span><b><?= e($project['location'] ?: '—') ?></b></div>
      <div class="project-meta__item"><span>Property Type</span><b><?= e($project['property_type'] ?: '—') ?></b></div>
      <div class="project-meta__item"><span>Area</span><b><?= $project['area_sqft'] ? number_format((int) $project['area_sqft']) . ' sq. ft' : '—' ?></b></div>
      <div class="project-meta__item"><span>Completion Year</span><b><?= e((string) $project['completion_year']) ?></b></div>
      <div class="project-meta__item"><span>Category</span><b><?= e($project['category']) ?></b></div>
    </div>

    <div class="project-body reveal">
      <p><?= nl2br(e($project['description'])) ?></p>
    </div>

    <?php if ($galleryImages): ?>
    <div class="project-gallery reveal">
      <?php foreach ($galleryImages as $i => $gi): ?>
        <a href="<?= e(img($gi['image_path'])) ?>" data-lightbox="<?= e(img($gi['image_path'])) ?>" class="<?= $i === 0 ? 'span-2' : '' ?>">
          <img src="<?= e(img($gi['image_path'])) ?>" alt="<?= e($gi['caption'] ?: $project['title']) ?>" loading="lazy">
        </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($project['video_url']): ?>
    <div class="reveal mt-40" style="border-radius:var(--radius-lg);overflow:hidden;aspect-ratio:16/9">
      <iframe src="<?= e($project['video_url']) ?>" style="width:100%;height:100%;border:0" allowfullscreen title="Project video"></iframe>
    </div>
    <?php endif; ?>

    <div class="project-cta reveal">
      <h2>Have a Similar Project in Mind?</h2>
      <p>Let's talk about how we can design and deliver something just as considered for your space.</p>
      <div class="btn-row">
        <a href="<?= asset('contact.php') ?>#consult" class="btn btn--gradient">Start Your Project</a>
        <a href="<?= e(whatsapp_link('Assalamualaikum, I saw your project \'' . $project['title'] . '\' and would like to discuss something similar.')) ?>" target="_blank" rel="noopener" class="btn btn--whatsapp" onclick="trackEvent('whatsapp_click','project_detail')"><i class="fa-brands fa-whatsapp"></i> WhatsApp Us</a>
      </div>
    </div>
  </div>
</section>

<?php if ($related): ?>
<section class="section section--tint">
  <div class="container">
    <div class="section-head reveal">
      <div class="eyebrow">More Like This</div>
      <h2>Related Projects</h2>
    </div>
    <div class="project-grid">
      <?php foreach ($related as $i => $p): ?>
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

<div class="lightbox" id="lightbox">
  <button class="lightbox__close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
  <button class="lightbox__nav lightbox__nav--prev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
  <img src="" alt="Project image preview">
  <button class="lightbox__nav lightbox__nav--next" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
