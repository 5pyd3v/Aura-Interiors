<?php
require_once __DIR__ . '/includes/bootstrap.php';
log_event('page_view', 'blog');

$categories = db()->query('SELECT * FROM blog_categories ORDER BY name')->fetchAll();
$activeCat = $_GET['category'] ?? '';
$perPage = 9;
$currentPage = max(1, (int) ($_GET['p'] ?? 1));

$where = 'bp.is_published = 1';
$params = [];
if ($activeCat !== '') {
    $where .= ' AND bc.slug = ?';
    $params[] = $activeCat;
}

$countStmt = db()->prepare("SELECT COUNT(*) FROM blog_posts bp LEFT JOIN blog_categories bc ON bc.id = bp.category_id WHERE $where");
$countStmt->execute($params);
$total = (int) $countStmt->fetchColumn();
[$offset, $totalPages, $currentPage] = paginate($total, $perPage, $currentPage);

$stmt = db()->prepare("SELECT bp.*, bc.name AS category_name, bc.slug AS category_slug FROM blog_posts bp LEFT JOIN blog_categories bc ON bc.id = bp.category_id WHERE $where ORDER BY bp.published_at DESC LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$posts = $stmt->fetchAll();

$page = [
    'title' => 'Design Journal — Interior Design Insights | ' . setting('company_name'),
    'description' => 'Interior design tips, cost guides and trends for Pakistani homes and offices, from ' . setting('company_name') . '.',
    'canonical' => BASE_URL . '/blog.php',
    'schema' => [schema_breadcrumbs([
        ['name' => 'Home', 'url' => BASE_URL . '/index.php'],
        ['name' => 'Journal', 'url' => BASE_URL . '/blog.php'],
    ])],
];
$currentNav = 'blog';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <div class="breadcrumb"><a href="<?= asset('index.php') ?>">Home</a> / <span>Journal</span></div>
    <div class="eyebrow" style="color:#D2A24C">Design Insights</div>
    <h1>Ideas, Guides and Trends.</h1>
    <p>Practical interior design advice for Pakistani homes and workplaces, from our studio's design team.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="filter-bar">
      <a href="<?= asset('blog.php') ?>" class="filter-btn <?= $activeCat === '' ? 'is-active' : '' ?>">All</a>
      <?php foreach ($categories as $cat): ?>
        <a href="<?= asset('blog.php?category=' . urlencode($cat['slug'])) ?>" class="filter-btn <?= $activeCat === $cat['slug'] ? 'is-active' : '' ?>"><?= e($cat['name']) ?></a>
      <?php endforeach; ?>
    </div>

    <?php if ($posts): ?>
    <div class="blog-grid">
      <?php foreach ($posts as $i => $post): ?>
        <a href="<?= asset('blog-detail.php?slug=' . urlencode($post['slug'])) ?>" class="blog-card reveal reveal-delay-<?= ($i % 3) + 1 ?>">
          <div class="blog-card__img"><img src="<?= e(img($post['featured_image'])) ?>" alt="<?= e($post['title']) ?>" loading="lazy"></div>
          <div class="blog-card__body">
            <span class="blog-card__cat"><?= e($post['category_name'] ?? 'Design') ?></span>
            <h3><?= e($post['title']) ?></h3>
            <p><?= e($post['excerpt']) ?></p>
            <div class="blog-card__meta">
              <span><i class="fa-regular fa-calendar"></i> <?= date('M j, Y', strtotime($post['published_at'])) ?></span>
              <span><i class="fa-regular fa-user"></i> <?= e($post['author_name']) ?></span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>

    <?php if ($totalPages > 1): ?>
    <div class="pagination">
      <?php for ($pg = 1; $pg <= $totalPages; $pg++): ?>
        <a href="<?= asset('blog.php?category=' . urlencode($activeCat) . '&p=' . $pg) ?>" class="<?= $pg === $currentPage ? 'is-active' : '' ?>"><?= $pg ?></a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
    <?php else: ?>
    <p class="text-center" style="padding:60px 0">No articles in this category yet.</p>
    <?php endif; ?>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
