<?php
require_once __DIR__ . '/includes/bootstrap.php';
log_event('page_view', 'about');

$team = db()->query('SELECT * FROM team_members WHERE is_published = 1 ORDER BY sort_order')->fetchAll();

$page = [
    'title' => 'About Us — ' . setting('company_name'),
    'description' => 'Learn about ' . setting('company_name') . ', a premium interior design studio serving clients across Pakistan with residential, commercial and hospitality design.',
    'canonical' => BASE_URL . '/about.php',
    'schema' => [schema_local_business(), schema_breadcrumbs([
        ['name' => 'Home', 'url' => BASE_URL . '/index.php'],
        ['name' => 'About', 'url' => BASE_URL . '/about.php'],
    ])],
];
$currentNav = 'about';
require __DIR__ . '/includes/header.php';
?>

<section class="page-hero">
  <div class="container page-hero__inner">
    <div class="breadcrumb"><a href="<?= asset('index.php') ?>">Home</a> / <span>About</span></div>
    <div class="eyebrow" style="color:#D2A24C">Our Studio</div>
    <h1>Designing Spaces That Reflect How You Live.</h1>
    <p>For over a decade, <?= e(setting('company_name')) ?> has been designing homes, offices and commercial spaces across Pakistan — grounded in craftsmanship, honesty and genuine care for how people use their spaces.</p>
  </div>
</section>

<section class="section">
  <div class="container">
    <div class="about-story">
      <div class="about-story__media reveal">
        <img src="<?= e(img(setting('about_image'), 'assets/images/demo/about-studio.jpg')) ?>" alt="Aura Interiors design studio at work">
        <div class="about-story__badge">
          <b><?= e(setting('stat_years')) ?></b>
          <span>Years of Experience</span>
        </div>
      </div>
      <div class="reveal reveal-delay-1">
        <div class="eyebrow">Our Story</div>
        <h2>Built on Craft, Trust and Genuine Design Thinking</h2>
        <p style="margin-top:18px">We started with a simple belief: interior design in Pakistan deserved the same rigour, honesty and craftsmanship as anywhere else in the world. What began as a small residential design practice has grown into a full-service studio trusted for homes, offices, restaurants and commercial spaces across multiple cities.</p>
        <p style="margin-top:14px">Every project starts the same way — by listening. We design around how you actually live and work, not around trends that fade in a year. The result is spaces that feel timeless, personal and genuinely well-built.</p>
        <div class="value-grid">
          <div class="value-card">
            <i class="fa-solid fa-bullseye"></i>
            <h3>Our Mission</h3>
            <p>To design spaces that improve how our clients live, work and welcome others — without compromise on quality.</p>
          </div>
          <div class="value-card">
            <i class="fa-solid fa-lightbulb"></i>
            <h3>Design Philosophy</h3>
            <p>Thoughtful, functional design first — beauty follows naturally when a space is planned around its people.</p>
          </div>
          <div class="value-card">
            <i class="fa-solid fa-handshake"></i>
            <h3>Why Clients Trust Us</h3>
            <p>Transparent pricing, realistic timelines and a single accountable team from concept to handover.</p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="section section--dark">
  <div class="container">
    <div class="section-head section-head--center reveal">
      <div class="eyebrow" style="justify-content:center;display:flex">Milestones</div>
      <h2>A Track Record Built Project by Project</h2>
    </div>
    <div class="stat-grid">
      <div class="stat-card reveal" style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.12)">
        <i class="fa-solid fa-calendar-check"></i>
        <b style="color:#fff" data-count="<?= (int) preg_replace('/\D/', '', setting('stat_years')) ?>+"><?= e(setting('stat_years')) ?></b>
        <span style="color:rgba(255,255,255,.7)">Years of Experience</span>
      </div>
      <div class="stat-card reveal reveal-delay-1" style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.12)">
        <i class="fa-solid fa-layer-group"></i>
        <b style="color:#fff" data-count="<?= (int) preg_replace('/\D/', '', setting('stat_projects')) ?>+"><?= e(setting('stat_projects')) ?></b>
        <span style="color:rgba(255,255,255,.7)">Projects Completed</span>
      </div>
      <div class="stat-card reveal reveal-delay-2" style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.12)">
        <i class="fa-solid fa-face-smile"></i>
        <b style="color:#fff" data-count="<?= (int) preg_replace('/\D/', '', setting('stat_clients')) ?>+"><?= e(setting('stat_clients')) ?></b>
        <span style="color:rgba(255,255,255,.7)">Happy Clients</span>
      </div>
      <div class="stat-card reveal reveal-delay-3" style="background:rgba(255,255,255,.05);border-color:rgba(255,255,255,.12)">
        <i class="fa-solid fa-city"></i>
        <b style="color:#fff" data-count="<?= (int) preg_replace('/\D/', '', setting('stat_cities')) ?>"><?= e(setting('stat_cities')) ?></b>
        <span style="color:rgba(255,255,255,.7)">Cities Served</span>
      </div>
    </div>
  </div>
</section>

<?php if ($team): ?>
<section class="section">
  <div class="container">
    <div class="section-head section-head--center reveal">
      <div class="eyebrow" style="justify-content:center;display:flex">Meet the Studio</div>
      <h2>The People Behind the Designs</h2>
    </div>
    <div class="team-grid">
      <?php foreach ($team as $i => $member): ?>
        <div class="team-card reveal reveal-delay-<?= ($i % 4) + 1 ?>">
          <div class="team-card__img">
            <img src="<?= e(img($member['photo_path'])) ?>" alt="<?= e($member['name']) ?>" loading="lazy">
            <div class="team-card__social">
              <?php if ($member['facebook_url']): ?><a href="<?= e($member['facebook_url']) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f"></i></a><?php endif; ?>
              <?php if ($member['instagram_url']): ?><a href="<?= e($member['instagram_url']) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-instagram"></i></a><?php endif; ?>
              <?php if ($member['linkedin_url']): ?><a href="<?= e($member['linkedin_url']) ?>" target="_blank" rel="noopener"><i class="fa-brands fa-linkedin-in"></i></a><?php endif; ?>
            </div>
          </div>
          <h3><?= e($member['name']) ?></h3>
          <span><?= e($member['position']) ?></span>
          <p style="font-size:.86rem;margin-top:8px"><?= e($member['bio']) ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section class="section section--tint">
  <div class="container">
    <div class="project-cta reveal" style="background:var(--gradient-dark)">
      <h2>Have a Project in Mind?</h2>
      <p>Tell us about your space — our team will get back to you within one business day with next steps.</p>
      <div class="btn-row">
        <a href="<?= asset('contact.php') ?>#consult" class="btn btn--gradient">Start Your Project</a>
        <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="btn btn--whatsapp" onclick="trackEvent('whatsapp_click','about_cta')"><i class="fa-brands fa-whatsapp"></i> WhatsApp Us</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
