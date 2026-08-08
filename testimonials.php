<?php
require_once __DIR__ . '/includes/bootstrap.php';
log_event('page_view', 'testimonials');

$testimonials = db()->query('SELECT * FROM testimonials WHERE is_published = 1 ORDER BY sort_order DESC, created_at DESC')->fetchAll();
$avgRating = 0;
if ($testimonials) {
    $avgRating = round(array_sum(array_column($testimonials, 'rating')) / count($testimonials), 1);
}

$page = [
    'title' => 'Client Testimonials — ' . setting('company_name'),
    'description' => 'Read what our clients say about working with ' . setting('company_name') . ' on their interior design projects.',
    'canonical' => BASE_URL . '/testimonials.php',
    'schema' => [schema_breadcrumbs([
        ['name' => 'Home', 'url' => BASE_URL . '/index.php'],
        ['name' => 'Testimonials', 'url' => BASE_URL . '/testimonials.php'],
    ])],
];
$currentNav = '';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <div class="breadcrumb"><a href="<?= asset('index.php') ?>">Home</a> / <span>Testimonials</span></div>
    <div class="eyebrow" style="color:#D2A24C">Client Experience</div>
    <h1>What Our Clients Say.</h1>
    <?php if ($testimonials): ?>
      <p><i class="fa-solid fa-star" style="color:#D2A24C"></i> <?= e((string) $avgRating) ?> average rating from <?= count($testimonials) ?> reviewed projects.</p>
    <?php endif; ?>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="testimonial-track" style="grid-template-columns:repeat(3,1fr)">
      <?php foreach ($testimonials as $i => $t): ?>
        <div class="testimonial-card reveal reveal-delay-<?= ($i % 3) + 1 ?>">
          <div class="stars"><?php for ($s = 0; $s < (int) $t['rating']; $s++) echo '<i class="fa-solid fa-star"></i> '; ?></div>
          <p class="quote">&ldquo;<?= e($t['review']) ?>&rdquo;</p>
          <div class="testimonial-card__who">
            <?php if ($t['photo_path']): ?>
              <img class="testimonial-card__avatar" src="<?= e(img($t['photo_path'])) ?>" alt="<?= e($t['client_name']) ?>">
            <?php else: ?>
              <span class="testimonial-card__avatar"></span>
            <?php endif; ?>
            <div>
              <b><?= e($t['client_name']) ?></b>
              <span><?= e($t['project_type']) ?><?= $t['location'] ? ' · ' . e($t['location']) : '' ?></span>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div class="project-cta reveal mt-40">
      <h2>Become Our Next Success Story</h2>
      <p>Join the growing list of clients who trusted us to design a space they love.</p>
      <div class="btn-row">
        <a href="<?= asset('contact.php') ?>#consult" class="btn btn--gradient">Book a Free Consultation</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
