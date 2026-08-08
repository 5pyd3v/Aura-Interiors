<?php
/**
 * Shared <head> + top navigation. Expects an optional $page array (see seo.php)
 * to be defined before include. $currentNav can be set to highlight the active link.
 */
$currentNav = $currentNav ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#F5F2EC">
<?php seo_render($page ?? []); ?>
<link rel="icon" href="<?= e(img(setting('favicon_path'), 'assets/images/favicon.png')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,300;0,9..144,400;0,9..144,500;0,9..144,600;1,9..144,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer">
<link rel="stylesheet" href="<?= asset('assets/css/style.css') ?>?v=<?= filemtime(BASE_PATH . '/assets/css/style.css') ?>">
<?php if (setting('google_analytics_id')): ?>
<script async src="https://www.googletagmanager.com/gtag/js?id=<?= e(setting('google_analytics_id')) ?>"></script>
<script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= e(setting('google_analytics_id')) ?>');</script>
<?php endif; ?>
</head>
<body>

<a href="#main-content" class="skip-link">Skip to content</a>

<header class="site-header" id="siteHeader">
  <div class="container site-header__inner">
    <a href="<?= asset('index.php') ?>" class="brand">
      <?php if (setting('logo_path')): ?>
        <img src="<?= e(img(setting('logo_path'))) ?>" alt="<?= e(setting('company_name')) ?>" class="brand__logo">
      <?php else: ?>
        <span class="brand__mark">A</span>
        <span class="brand__name"><?= e(setting('company_name')) ?></span>
      <?php endif; ?>
    </a>

    <nav class="main-nav" id="mainNav" aria-label="Primary">
      <ul>
        <li><a href="<?= asset('index.php') ?>" class="<?= $currentNav === 'home' ? 'is-active' : '' ?>">Home</a></li>
        <li><a href="<?= asset('about.php') ?>" class="<?= $currentNav === 'about' ? 'is-active' : '' ?>">About</a></li>
        <li><a href="<?= asset('services.php') ?>" class="<?= $currentNav === 'services' ? 'is-active' : '' ?>">Services</a></li>
        <li><a href="<?= asset('projects.php') ?>" class="<?= $currentNav === 'projects' ? 'is-active' : '' ?>">Projects</a></li>
        <li><a href="<?= asset('gallery.php') ?>" class="<?= $currentNav === 'gallery' ? 'is-active' : '' ?>">Gallery</a></li>
        <li><a href="<?= asset('blog.php') ?>" class="<?= $currentNav === 'blog' ? 'is-active' : '' ?>">Journal</a></li>
        <li><a href="<?= asset('contact.php') ?>" class="<?= $currentNav === 'contact' ? 'is-active' : '' ?>">Contact</a></li>
      </ul>
    </nav>

    <div class="site-header__actions">
      <a href="<?= tel_link() ?>" class="icon-action" aria-label="Call now" onclick="trackEvent('call_click','header')">
        <i class="fa-solid fa-phone"></i>
      </a>
      <a href="<?= e(whatsapp_link()) ?>" target="_blank" rel="noopener" class="icon-action icon-action--whatsapp" aria-label="WhatsApp us" onclick="trackEvent('whatsapp_click','header')">
        <i class="fa-brands fa-whatsapp"></i>
      </a>
      <a href="<?= asset('contact.php') ?>#consult" class="btn btn--gradient btn--sm">Get a Consultation</a>
      <button class="nav-toggle" id="navToggle" aria-label="Open menu" aria-expanded="false" aria-controls="mainNav">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
</header>

<div class="mobile-nav-backdrop" id="mobileNavBackdrop"></div>

<main id="main-content">
