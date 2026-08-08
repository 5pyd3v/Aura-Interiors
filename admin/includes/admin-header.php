<?php
/**
 * Shared admin <head> + shell open. Expects $adminPageTitle and $adminActive
 * to be set before include. Requires auth.php to already be included.
 */
$adminPageTitle = $adminPageTitle ?? 'Dashboard';
$adminActive = $adminActive ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<title><?= e($adminPageTitle) ?> — Admin | <?= e(setting('company_name')) ?></title>
<link rel="icon" href="<?= e(img(setting('favicon_path'), 'assets/images/favicon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Fraunces:opsz,wght@9..144,400;9..144,500;9..144,600&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>?v=<?= filemtime(BASE_PATH . '/assets/css/style.css') ?>">
<link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>?v=<?= filemtime(BASE_PATH . '/assets/css/admin.css') ?>">
</head>
<body class="admin-body">
<div class="admin-shell">
  <aside class="admin-sidebar" id="adminSidebar">
    <div class="admin-sidebar__brand">
      <span class="brand__mark">A</span>
      <span><?= e(setting('company_name')) ?></span>
    </div>
    <nav class="admin-nav">
      <div class="admin-nav-group">
        <div class="admin-nav-group__title">Overview</div>
        <a href="dashboard.php" class="<?= $adminActive === 'dashboard' ? 'is-active' : '' ?>"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
        <a href="analytics.php" class="<?= $adminActive === 'analytics' ? 'is-active' : '' ?>"><i class="fa-solid fa-chart-line"></i> Analytics</a>
        <a href="inquiries.php" class="<?= $adminActive === 'inquiries' ? 'is-active' : '' ?>"><i class="fa-solid fa-envelope-open-text"></i> Inquiries</a>
      </div>
      <div class="admin-nav-group">
        <div class="admin-nav-group__title">Content</div>
        <a href="projects.php" class="<?= $adminActive === 'projects' ? 'is-active' : '' ?>"><i class="fa-solid fa-layer-group"></i> Projects</a>
        <a href="services.php" class="<?= $adminActive === 'services' ? 'is-active' : '' ?>"><i class="fa-solid fa-briefcase"></i> Services</a>
        <a href="gallery.php" class="<?= $adminActive === 'gallery' ? 'is-active' : '' ?>"><i class="fa-solid fa-images"></i> Gallery</a>
        <a href="before-after.php" class="<?= $adminActive === 'before-after' ? 'is-active' : '' ?>"><i class="fa-solid fa-left-right"></i> Before / After</a>
        <a href="testimonials.php" class="<?= $adminActive === 'testimonials' ? 'is-active' : '' ?>"><i class="fa-solid fa-star"></i> Testimonials</a>
        <a href="team.php" class="<?= $adminActive === 'team' ? 'is-active' : '' ?>"><i class="fa-solid fa-people-group"></i> Team</a>
        <a href="blog.php" class="<?= $adminActive === 'blog' ? 'is-active' : '' ?>"><i class="fa-solid fa-newspaper"></i> Blog</a>
      </div>
      <div class="admin-nav-group">
        <div class="admin-nav-group__title">System</div>
        <a href="settings.php" class="<?= $adminActive === 'settings' ? 'is-active' : '' ?>"><i class="fa-solid fa-gear"></i> Settings</a>
        <a href="<?= asset('index.php') ?>" target="_blank"><i class="fa-solid fa-arrow-up-right-from-square"></i> View Website</a>
        <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
      </div>
    </nav>
  </aside>

  <div class="admin-main">
    <header class="admin-topbar">
      <div style="display:flex;align-items:center;gap:14px">
        <button id="adminSidebarToggle" style="display:none;background:none;border:none;font-size:1.2rem" aria-label="Toggle menu"><i class="fa-solid fa-bars"></i></button>
        <h1><?= e($adminPageTitle) ?></h1>
      </div>
      <div class="admin-topbar__right">
        <span style="font-size:.86rem;color:var(--ink-soft)"><?= e($currentAdmin['name']) ?></span>
        <div class="admin-avatar"><?= e(strtoupper(substr($currentAdmin['name'], 0, 1))) ?></div>
      </div>
    </header>
    <div class="admin-content">
      <?php if ($msg = flash_get('success')): ?><div class="alert alert--success"><?= e($msg) ?></div><?php endif; ?>
      <?php if ($msg = flash_get('error')): ?><div class="alert alert--error"><?= e($msg) ?></div><?php endif; ?>
