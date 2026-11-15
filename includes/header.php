<?php
/**
 * ANSH THRIFTS — Reusable Header Component
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? APP_NAME;
$activeNav = $activeNav ?? 'home';
$cartCount = getCartCount();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="ANSH THRIFTS — Pre-loved fashion, new stories. Sustainable thrift shopping.">
  <link rel="shortcut icon" href="/static/images/favicon.png">

  <!-- Google Fonts: Inter & Playfair Display -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">

  <!-- Bootstrap 5 CSS -->
  <link href="/static/css/bootstrap.min.css" rel="stylesheet">
  <!-- Global Optimized CSS -->
  <link href="/static/css/style.css" rel="stylesheet">

  <title><?= e($pageTitle) ?></title>
  <?php if (!empty($extraHead)) echo $extraHead; ?>
</head>
<body>

  <!-- Navbar -->
  <nav class="custom-navbar navbar navbar-expand-md navbar-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="/index.php"><?= e(APP_NAME) ?><span>.</span></a>

      <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
        data-bs-target="#navbarMain" aria-controls="navbarMain"
        aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse justify-content-between" id="navbarMain">
        <ul class="navbar-nav ms-md-5 mb-2 mb-md-0">
          <li class="nav-item">
            <a class="nav-link <?= $activeNav === 'home' ? 'active' : '' ?>" href="/index.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $activeNav === 'shop' ? 'active' : '' ?>" href="/shop.php">Shop</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $activeNav === 'about' ? 'active' : '' ?>" href="/about.php">About</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $activeNav === 'contact' ? 'active' : '' ?>" href="/contact.php">Contact</a>
          </li>
          <?php if (isAdminLoggedIn()): ?>
          <li class="nav-item">
            <a class="nav-link text-warning" href="/admin/index.php">Admin Panel</a>
          </li>
          <?php endif; ?>
        </ul>

        <ul class="navbar-nav align-items-center">
          <li class="nav-item">
            <a class="nav-link" href="<?= isAdminLoggedIn() ? '/admin/index.php' : '/login.php' ?>" title="Admin / Account">
              <img src="/static/images/user.svg" alt="Account">
            </a>
          </li>
          <li class="nav-item position-relative">
            <a class="nav-link" href="/cart.php" title="Cart">
              <img src="/static/images/cart.svg" alt="Cart">
              <?php if ($cartCount > 0): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.7rem;">
                  <?= $cartCount ?>
                </span>
              <?php endif; ?>
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- Flash Messages -->
  <?php $flashMsgs = getFlashMessages(); ?>
  <?php if (!empty($flashMsgs)): ?>
  <div class="container" style="margin-top: 85px; margin-bottom: -60px;">
    <?php foreach ($flashMsgs as $flash): ?>
      <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

