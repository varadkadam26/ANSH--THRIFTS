<?php
/**
 * ANSH THRIFTS — About Us Page (about.php)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About Us · ' . APP_NAME;
$activeNav = 'about';

require_once __DIR__ . '/includes/header.php';
?>

<!-- About Hero Section -->
<div class="hero" style="background: url('/static/images/login-img.jpg') no-repeat center center/cover; min-height: 45vh;">
  <div class="container text-center">
    <div class="intro-excerpt mx-auto" style="max-width: 600px;">
      <h1>About Us</h1>
      <p>Discover the story behind our sustainable thrift store.</p>
    </div>
  </div>
</div>

<!-- Who We Are -->
<div class="container my-5 about-content">
  <div class="row g-4 align-items-center">
    <div class="col-12 col-md-6">
      <img src="/static/images/login-img.jpg" alt="Our Store"
           class="img-fluid rounded-3 shadow-sm" style="object-fit: cover; max-height: 420px; width: 100%;">
    </div>
    <div class="col-12 col-md-6 ps-md-4">
      <h2 class="section-heading">Who We Are</h2>
      <p class="text-muted">At <strong><?= e(APP_NAME) ?></strong>, we believe in promoting sustainable fashion by giving pre-loved clothes a second life. Every item in our collection has a story, waiting to be continued by you.</p>
      <p class="text-muted">Our mission is simple: to make eco-friendly shopping affordable, trendy, and accessible to everyone.</p>
      <p class="text-muted">We partner with local communities to source unique, high-quality vintage and streetwear finds that help reduce textile waste and support a circular wardrobe economy.</p>
      <div class="mt-4">
        <a href="/shop.php" class="btn btn-primary">Browse Collection</a>
        <a href="/contact.php" class="btn btn-white-outline text-dark border-secondary ms-2">Get in Touch</a>
      </div>
    </div>
  </div>
</div>

<!-- Core Values -->
<div class="container my-5 py-4 border-top">
  <h2 class="section-heading text-center mb-5">Our Core Values</h2>
  <div class="row g-4 text-center">
    <div class="col-12 col-md-4">
      <div class="p-3">
        <div style="font-size: 2.8rem; margin-bottom: 14px;">🌿</div>
        <h4 style="font-family: 'Inter', sans-serif; font-weight: 600;">Sustainability</h4>
        <p class="text-muted">We focus on reducing textile waste by offering high quality pre-loved fashion a purposeful new chapter.</p>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="p-3">
        <div style="font-size: 2.8rem; margin-bottom: 14px;">🤝</div>
        <h4 style="font-family: 'Inter', sans-serif; font-weight: 600;">Community First</h4>
        <p class="text-muted">We collaborate with regional thrift curators to hand-select authentic pieces with genuine charm.</p>
      </div>
    </div>
    <div class="col-12 col-md-4">
      <div class="p-3">
        <div style="font-size: 2.8rem; margin-bottom: 14px;">💛</div>
        <h4 style="font-family: 'Inter', sans-serif; font-weight: 600;">Accessible Style</h4>
        <p class="text-muted">Eco-friendly shopping should never cost a fortune. We guarantee fair pricing on every piece.</p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

