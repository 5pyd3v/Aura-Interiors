<?php
require_once __DIR__ . '/includes/bootstrap.php';

$slug = $_GET['slug'] ?? '';
$stmt = db()->prepare('SELECT bp.*, bc.name AS category_name FROM blog_posts bp LEFT JOIN blog_categories bc ON bc.id = bp.category_id WHERE bp.slug = ? AND bp.is_published = 1');
$stmt->execute([$slug]);
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    $page = ['title' => 'Article Not Found — ' . setting('company_name')];
    $currentNav = 'blog';
    require __DIR__ . '/includes/header.php';
    echo '<section class="section text-center"><div class="container"><h1>Article Not Found</h1><a href="' . asset('blog.php') . '" class="btn btn--gradient mt-40">Back to Journal</a></div></section>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

db()->prepare('UPDATE blog_posts SET views = views + 1 WHERE id = ?')->execute([$post['id']]);
log_event('page_view', 'blog:' . $post['slug']);

$relStmt = db()->prepare('SELECT * FROM blog_posts WHERE is_published = 1 AND id != ? ORDER BY published_at DESC LIMIT 3');
$relStmt->execute([$post['id']]);
$related = $relStmt->fetchAll();

$page = [
    'title' => $post['seo_title'] ?: ($post['title'] . ' | ' . setting('company_name')),
    'description' => $post['seo_description'] ?: $post['excerpt'],
    'canonical' => BASE_URL . '/blog-detail.php?slug=' . urlencode($post['slug']),
    'image' => img($post['featured_image']),
    'type' => 'article',
    'schema' => [schema_article($post), schema_breadcrumbs([
        ['name' => 'Home', 'url' => BASE_URL . '/index.php'],
        ['name' => 'Journal', 'url' => BASE_URL . '/blog.php'],
        ['name' => $post['title'], 'url' => BASE_URL . '/blog-detail.php?slug=' . urlencode($post['slug'])],
    ])],
];
$currentNav = 'blog';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero" style="padding-bottom:60px">
  <div class="container page-hero__inner">
    <div class="breadcrumb"><a href="<?= asset('index.php') ?>">Home</a> / <a href="<?= asset('blog.php') ?>">Journal</a> / <span><?= e($post['title']) ?></span></div>
    <div class="eyebrow" style="color:#D2A24C"><?= e($post['category_name'] ?? 'Design') ?></div>
    <h1><?= e($post['title']) ?></h1>
    <p style="margin-top:18px">
      <i class="fa-regular fa-calendar"></i> <?= date('F j, Y', strtotime($post['published_at'])) ?>
      &nbsp;&middot;&nbsp; <i class="fa-regular fa-user"></i> <?= e($post['author_name']) ?>
    </p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="blog-detail">
      <div class="blog-detail__cover reveal">
        <img src="<?= e(img($post['featured_image'])) ?>" alt="<?= e($post['title']) ?>">
      </div>
      <div class="blog-detail__content reveal">
        <?= $post['content'] // trusted admin-authored HTML ?>
      </div>

      <div class="project-cta reveal mt-40" style="text-align:left;padding:44px">
        <h2 style="font-size:1.5rem">Thinking About Your Own Project?</h2>
        <p style="margin:10px 0 24px">Book a free consultation with our design team.</p>
        <div class="btn-row">
          <a href="<?= asset('contact.php') ?>#consult" class="btn btn--gradient btn--sm">Get a Free Consultation</a>
          <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="btn btn--whatsapp btn--sm" onclick="trackEvent('whatsapp_click','blog_detail')"><i class="fa-brands fa-whatsapp"></i> WhatsApp Us</a>
        </div>
      </div>
    </div>
  </div>
</section>

<?php if ($related): ?>
<section class="section section--tint">
  <div class="container">
    <div class="section-head reveal">
      <div class="eyebrow">Keep Reading</div>
      <h2>More From the Journal</h2>
    </div>
    <div class="blog-grid">
      <?php foreach ($related as $i => $rp): ?>
        <a href="<?= asset('blog-detail.php?slug=' . urlencode($rp['slug'])) ?>" class="blog-card reveal reveal-delay-<?= $i + 1 ?>">
          <div class="blog-card__img"><img src="<?= e(img($rp['featured_image'])) ?>" alt="<?= e($rp['title']) ?>" loading="lazy"></div>
          <div class="blog-card__body">
            <h3><?= e($rp['title']) ?></h3>
            <div class="blog-card__meta"><span><i class="fa-regular fa-calendar"></i> <?= date('M j, Y', strtotime($rp['published_at'])) ?></span></div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
