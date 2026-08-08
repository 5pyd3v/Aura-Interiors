<?php
require_once __DIR__ . '/includes/bootstrap.php';
log_event('page_view', 'home');

$services      = db()->query('SELECT * FROM services WHERE is_published = 1 ORDER BY sort_order LIMIT 6')->fetchAll();
$projects      = db()->query('SELECT * FROM projects WHERE is_published = 1 ORDER BY is_featured DESC, sort_order LIMIT 6')->fetchAll();
$beforeAfters  = db()->query('SELECT * FROM before_after WHERE is_published = 1 ORDER BY sort_order LIMIT 2')->fetchAll();
$whyChooseUs   = db()->query('SELECT * FROM why_choose_us ORDER BY sort_order LIMIT 8')->fetchAll();
$processSteps  = db()->query('SELECT * FROM process_steps ORDER BY sort_order')->fetchAll();
$testimonials  = db()->query('SELECT * FROM testimonials WHERE is_published = 1 ORDER BY sort_order LIMIT 3')->fetchAll();
$blogPosts     = db()->query('SELECT bp.*, bc.name AS category_name FROM blog_posts bp LEFT JOIN blog_categories bc ON bc.id = bp.category_id WHERE bp.is_published = 1 ORDER BY bp.published_at DESC LIMIT 3')->fetchAll();

$page = [
    'title' => setting('meta_title'),
    'description' => setting('meta_description'),
    'canonical' => BASE_URL . '/index.php',
    'schema' => [schema_local_business()],
];
$currentNav = 'home';
require __DIR__ . '/includes/header.php';
?>

<!-- ===================== HERO ===================== -->
<section class="hero" style="background-image:linear-gradient(180deg, rgba(15,11,7,.15), rgba(15,11,7,.74)), url('<?= e(img(setting('hero_image'), 'assets/images/demo/hero.jpg')) ?>')">
  <div class="container hero__inner">
    <div class="hero__eyebrow"><span class="dot"></span> Premium Interior Design Studio — Pakistan</div>
    <h1><?= e(setting('hero_heading')) ?></h1>
    <p class="hero__sub"><?= e(setting('hero_subheading')) ?></p>
    <div class="btn-row">
      <a href="<?= asset('projects.php') ?>" class="btn btn--gradient">Explore Our Projects</a>
      <a href="<?= asset('contact.php') ?>#consult" class="btn btn--outline">Book a Free Consultation</a>
      <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="icon-action icon-action--whatsapp" aria-label="WhatsApp" onclick="trackEvent('whatsapp_click','hero')"><i class="fa-brands fa-whatsapp"></i></a>
      <a href="<?= tel_link() ?>" class="icon-action" aria-label="Call" onclick="trackEvent('call_click','hero')"><i class="fa-solid fa-phone"></i></a>
    </div>
    <div class="hero__stats">
      <div class="hero__stat"><b><?= e(setting('stat_years')) ?></b><span>Years Experience</span></div>
      <div class="hero__stat"><b><?= e(setting('stat_projects')) ?></b><span>Projects Completed</span></div>
      <div class="hero__stat"><b><?= e(setting('stat_clients')) ?></b><span>Happy Clients</span></div>
      <div class="hero__stat"><b><?= e(setting('stat_cities')) ?></b><span>Cities Served</span></div>
    </div>
  </div>
  <div class="hero__scroll">Scroll to explore</div>
</section>

<!-- ===================== TRUST ===================== -->
<section class="trust">
  <div class="container">
    <div class="trust__head reveal">
      <div class="eyebrow" style="justify-content:center;display:flex">Why Clients Trust Us</div>
      <h2>Designed With Purpose. Built With Precision.</h2>
    </div>
    <div class="stat-grid">
      <div class="stat-card reveal">
        <i class="fa-solid fa-calendar-check"></i>
        <b data-count="<?= (int) preg_replace('/\D/', '', setting('stat_years')) ?>+"><?= e(setting('stat_years')) ?></b>
        <span>Years of Experience</span>
      </div>
      <div class="stat-card reveal reveal-delay-1">
        <i class="fa-solid fa-layer-group"></i>
        <b data-count="<?= (int) preg_replace('/\D/', '', setting('stat_projects')) ?>+"><?= e(setting('stat_projects')) ?></b>
        <span>Projects Completed</span>
      </div>
      <div class="stat-card reveal reveal-delay-2">
        <i class="fa-solid fa-face-smile"></i>
        <b data-count="<?= (int) preg_replace('/\D/', '', setting('stat_clients')) ?>+"><?= e(setting('stat_clients')) ?></b>
        <span>Happy Clients</span>
      </div>
      <div class="stat-card reveal reveal-delay-3">
        <i class="fa-solid fa-city"></i>
        <b data-count="<?= (int) preg_replace('/\D/', '', setting('stat_cities')) ?>"><?= e(setting('stat_cities')) ?></b>
        <span>Cities Served</span>
      </div>
    </div>
  </div>
</section>

<!-- ===================== SERVICES ===================== -->
<section class="section section--tint">
  <div class="container">
    <div class="section-head reveal">
      <div class="eyebrow">What We Do</div>
      <h2>Interior Design Services Built Around You</h2>
      <p>From a single room to a full commercial fit-out, our team designs and delivers spaces that work as beautifully as they look.</p>
    </div>
    <div class="service-grid">
      <?php foreach ($services as $i => $s): ?>
        <div class="service-card reveal reveal-delay-<?= ($i % 3) + 1 ?>">
          <div class="service-card__media">
            <img src="<?= e(img($s['image_path'])) ?>" alt="<?= e($s['title']) ?>" loading="lazy">
          </div>
          <div class="service-card__icon"><i class="<?= e($s['icon_class'] ?: 'fa-solid fa-house') ?>"></i></div>
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

<!-- ===================== PORTFOLIO PREVIEW ===================== -->
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div class="eyebrow">Our Portfolio</div>
      <h2>Spaces We've Brought to Life</h2>
      <p>A selection of residential, commercial and hospitality projects completed across Pakistan.</p>
    </div>
    <div class="project-grid">
      <?php foreach ($projects as $i => $p): ?>
        <a href="<?= asset('project-detail.php?slug=' . urlencode($p['slug'])) ?>" class="project-card reveal reveal-delay-<?= ($i % 3) + 1 ?>" style="aspect-ratio: 4/3">
          <div class="project-card__img"><img src="<?= e(img($p['cover_image'])) ?>" alt="<?= e($p['title']) ?>" loading="lazy"></div>
          <span class="project-card__tag"><?= e($p['category']) ?></span>
          <span class="project-card__arrow"><i class="fa-solid fa-arrow-up-right"></i></span>
          <div class="project-card__body">
            <h3><?= e($p['title']) ?></h3>
            <span><i class="fa-solid fa-location-dot"></i> <?= e($p['location']) ?></span>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="text-center mt-40">
      <a href="<?= asset('projects.php') ?>" class="btn btn--outline-dark">View All Projects</a>
    </div>
  </div>
</section>

<!-- ===================== BEFORE / AFTER ===================== -->
<?php if ($beforeAfters): ?>
<section class="section section--tint">
  <div class="container">
    <div class="section-head section-head--center reveal">
      <div class="eyebrow" style="justify-content:center;display:flex">Real Transformations</div>
      <h2>Before &rarr; After</h2>
      <p>Drag the slider to see the difference our design and execution makes.</p>
    </div>
    <div class="ba-grid">
      <?php foreach ($beforeAfters as $ba): ?>
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
    <div class="text-center mt-40">
      <a href="<?= asset('before-after.php') ?>" class="btn btn--outline-dark">See More Transformations</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===================== WHY CHOOSE US ===================== -->
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div class="eyebrow">Why Choose Us</div>
      <h2>Expertise You Can Feel in Every Detail</h2>
    </div>
    <div class="why-grid">
      <?php foreach ($whyChooseUs as $i => $w): ?>
        <div class="why-card reveal reveal-delay-<?= ($i % 4) + 1 ?>">
          <i class="<?= e($w['icon_class']) ?>"></i>
          <h3><?= e($w['title']) ?></h3>
          <p><?= e($w['description']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== PROCESS ===================== -->
<section class="section section--dark">
  <div class="container">
    <div class="section-head section-head--center reveal">
      <div class="eyebrow" style="justify-content:center;display:flex">How We Work</div>
      <h2>A Simple, Transparent Process</h2>
      <p>From first conversation to final handover — five clear steps, no surprises.</p>
    </div>
    <div class="process-track">
      <?php foreach ($processSteps as $i => $step): ?>
        <div class="process-step reveal reveal-delay-<?= $i + 1 ?>">
          <div class="process-step__num">0<?= (int) $step['step_number'] ?></div>
          <h3><?= e($step['title']) ?></h3>
          <p><?= e($step['description']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ===================== TESTIMONIALS ===================== -->
<?php if ($testimonials): ?>
<section class="section">
  <div class="container">
    <div class="section-head reveal">
      <div class="eyebrow">Client Experience</div>
      <h2>What Our Clients Say</h2>
    </div>
    <div class="testimonial-track">
      <?php foreach ($testimonials as $i => $t): ?>
        <div class="testimonial-card reveal reveal-delay-<?= $i + 1 ?>">
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
    <div class="text-center mt-40">
      <a href="<?= asset('testimonials.php') ?>" class="btn btn--outline-dark">Read More Reviews</a>
    </div>
  </div>
</section>
<?php endif; ?>

<!-- ===================== BLOG PREVIEW ===================== -->
<?php if ($blogPosts): ?>
<section class="section section--tint">
  <div class="container">
    <div class="section-head reveal">
      <div class="eyebrow">Design Insights</div>
      <h2>From the Journal</h2>
    </div>
    <div class="blog-grid">
      <?php foreach ($blogPosts as $i => $post): ?>
        <a href="<?= asset('blog-detail.php?slug=' . urlencode($post['slug'])) ?>" class="blog-card reveal reveal-delay-<?= $i + 1 ?>">
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
  </div>
</section>
<?php endif; ?>

<!-- ===================== CONSULTATION FORM ===================== -->
<section class="section" id="consult">
  <div class="container">
    <div class="consult-wrap reveal">
      <div class="consult-info">
        <div class="eyebrow" style="color:#D2A24C">Start Your Project</div>
        <h2>Let's Create Your Space.</h2>
        <p>Tell us a little about your project and our design team will reach out within one business day.</p>
        <ul class="consult-info__list">
          <li><i class="fa-solid fa-check"></i> Free initial consultation</li>
          <li><i class="fa-solid fa-check"></i> Transparent, no-obligation quotation</li>
          <li><i class="fa-solid fa-check"></i> Dedicated project manager</li>
        </ul>
        <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="btn btn--whatsapp" onclick="trackEvent('whatsapp_click','consult_section')">
          <i class="fa-brands fa-whatsapp"></i> Prefer WhatsApp? Chat Now
        </a>
      </div>
      <div class="consult-form">
        <?php include __DIR__ . '/includes/consult-form.php'; ?>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
