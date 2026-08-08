<?php
require_once __DIR__ . '/includes/bootstrap.php';
log_event('page_view', 'gallery');

$items = db()->query('SELECT * FROM gallery ORDER BY sort_order DESC, created_at DESC')->fetchAll();
$categories = array_values(array_unique(array_filter(array_column($items, 'category'))));

$page = [
    'title' => 'Gallery — ' . setting('company_name'),
    'description' => 'Browse our interior design photo gallery — living rooms, kitchens, offices, restaurants and more.',
    'canonical' => BASE_URL . '/gallery.php',
    'schema' => [schema_breadcrumbs([
        ['name' => 'Home', 'url' => BASE_URL . '/index.php'],
        ['name' => 'Gallery', 'url' => BASE_URL . '/gallery.php'],
    ])],
];
$currentNav = 'gallery';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <div class="breadcrumb"><a href="<?= asset('index.php') ?>">Home</a> / <span>Gallery</span></div>
    <div class="eyebrow" style="color:#D2A24C">Visual Journal</div>
    <h1>A Closer Look at Our Work.</h1>
    <p>Details, textures and moments from projects across our portfolio.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <?php if ($categories): ?>
    <div class="filter-bar" data-filter-group="#galleryGrid .gallery-item">
      <button class="filter-btn is-active" data-filter="all">All</button>
      <?php foreach ($categories as $cat): ?>
        <button class="filter-btn" data-filter="<?= e($cat) ?>"><?= e($cat) ?></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="gallery-grid" id="galleryGrid">
      <?php foreach ($items as $item): ?>
        <div class="gallery-item reveal" data-category="<?= e($item['category']) ?>">
          <a href="<?= e(img($item['file_path'])) ?>" data-lightbox="<?= e(img($item['file_path'])) ?>">
            <img src="<?= e(img($item['file_path'])) ?>" alt="<?= e($item['title'] ?: $item['category']) ?>" loading="lazy">
            <div class="gallery-item__overlay"><?= e($item['caption'] ?: $item['title']) ?></div>
          </a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<div class="lightbox" id="lightbox">
  <button class="lightbox__close" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
  <button class="lightbox__nav lightbox__nav--prev" aria-label="Previous"><i class="fa-solid fa-chevron-left"></i></button>
  <img src="" alt="Gallery preview">
  <button class="lightbox__nav lightbox__nav--next" aria-label="Next"><i class="fa-solid fa-chevron-right"></i></button>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>
