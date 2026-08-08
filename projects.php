<?php
require_once __DIR__ . '/includes/bootstrap.php';
log_event('page_view', 'projects');

$categories = ['Residential','Commercial','Office','Restaurant','Bedroom','Kitchen','Living Room','Luxury Interiors'];
$activeCategory = $_GET['category'] ?? 'all';

$perPage = 12;
$currentPage = max(1, (int) ($_GET['p'] ?? 1));

if ($activeCategory !== 'all' && in_array($activeCategory, $categories, true)) {
    $countStmt = db()->prepare('SELECT COUNT(*) FROM projects WHERE is_published = 1 AND category = ?');
    $countStmt->execute([$activeCategory]);
} else {
    $countStmt = db()->query('SELECT COUNT(*) FROM projects WHERE is_published = 1');
}
$total = (int) $countStmt->fetchColumn();
[$offset, $totalPages, $currentPage] = paginate($total, $perPage, $currentPage);

if ($activeCategory !== 'all' && in_array($activeCategory, $categories, true)) {
    $stmt = db()->prepare('SELECT * FROM projects WHERE is_published = 1 AND category = ? ORDER BY sort_order LIMIT ' . $perPage . ' OFFSET ' . $offset);
    $stmt->execute([$activeCategory]);
} else {
    $stmt = db()->query('SELECT * FROM projects WHERE is_published = 1 ORDER BY sort_order LIMIT ' . $perPage . ' OFFSET ' . $offset);
}
$projects = $stmt->fetchAll();

$page = [
    'title' => 'Our Projects — Interior Design Portfolio | ' . setting('company_name'),
    'description' => 'Browse residential, commercial, office and restaurant interior design projects completed by ' . setting('company_name') . ' across Pakistan.',
    'canonical' => BASE_URL . '/projects.php',
    'schema' => [schema_breadcrumbs([
        ['name' => 'Home', 'url' => BASE_URL . '/index.php'],
        ['name' => 'Projects', 'url' => BASE_URL . '/projects.php'],
    ])],
];
$currentNav = 'projects';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <div class="breadcrumb"><a href="<?= asset('index.php') ?>">Home</a> / <span>Projects</span></div>
    <div class="eyebrow" style="color:#D2A24C">Our Portfolio</div>
    <h1>Spaces We've Designed Across Pakistan.</h1>
    <p>Explore our residential, commercial and hospitality projects — filter by category to find inspiration for your own space.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="filter-bar">
      <a href="<?= asset('projects.php') ?>" class="filter-btn <?= $activeCategory === 'all' ? 'is-active' : '' ?>">All</a>
      <?php foreach ($categories as $cat): ?>
        <a href="<?= asset('projects.php?category=' . urlencode($cat)) ?>" class="filter-btn <?= $activeCategory === $cat ? 'is-active' : '' ?>"><?= e($cat) ?></a>
      <?php endforeach; ?>
    </div>

    <?php if ($projects): ?>
    <div class="project-grid">
      <?php foreach ($projects as $i => $p): ?>
        <a href="<?= asset('project-detail.php?slug=' . urlencode($p['slug'])) ?>" class="project-card reveal reveal-delay-<?= ($i % 3) + 1 ?>" style="aspect-ratio:4/3">
          <div class="project-card__img"><img src="<?= e(img($p['cover_image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy"></div>
          <span class="project-card__tag"><?= e($p['category']) ?></span>
          <span class="project-card__arrow"><i class="fa-solid fa-arrow-up-right"></i></span>
          <div class="project-card__body">
            <h3><?= e($p['title']) ?></h3>
            <span><i class="fa-solid fa-location-dot"></i> <?= e($p['location']) ?> &middot; <?= e((string) $p['completion_year']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php for ($pg = 1; $pg <= $totalPages; $pg++): ?>
        <a href="<?= asset('projects.php?category=' . urlencode($activeCategory) . '&p=' . $pg) ?>" class="<?= $pg === $currentPage ? 'is-active' : '' ?>"><?= $pg ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <p class="text-center" style="padding:60px 0">No projects found in this category yet. Check back soon.</p>
    <?php endif; ?>
  </div>
</section>

<section class="section section--tint">
  <div class="container">
    <div class="project-cta reveal">
      <h2>Like What You See?</h2>
      <p>Let's talk about how we can bring the same quality of design and execution to your project.</p>
      <div class="btn-row">
        <a href="<?= asset('contact.php') ?>#consult" class="btn btn--gradient">Start Your Project</a>
        <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="btn btn--whatsapp" onclick="trackEvent('whatsapp_click','projects_cta')"><i class="fa-brands fa-whatsapp"></i> WhatsApp Us</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
