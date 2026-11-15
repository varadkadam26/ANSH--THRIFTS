<?php
/**
 * ANSH THRIFTS — Home Page (index.php)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/GoogleSheetsService.php';

$pageTitle = APP_NAME . ' · ' . APP_TAGLINE;
$activeNav = 'home';

$sheets = new GoogleSheetsService();
$products = $sheets->getActiveProducts();

require_once __DIR__ . '/includes/header.php';
?>

<!-- Hero Section with Existing Background Picture -->
<div class="hero" style="background: url('/static/images/login-img.jpg') no-repeat center center/cover;">
  <div class="container">
    <div class="row">
      <div class="col-lg-7">
        <div class="intro-excerpt">
          <h1><?= e(APP_NAME) ?> <span><?= e(APP_TAGLINE) ?></span></h1>
          <p class="mb-4">Discover timeless thrifted pieces with character, quality, and sustainable charm for everyday style.</p>
          <p>
            <a href="/shop.php" class="btn btn-secondary me-2">Shop Now</a>
            <a href="/about.php" class="btn btn-white-outline">Learn More</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Newly Added Items Section -->
<div class="container my-5 page-content">
  <h2 class="section-heading text-center mb-4">Newly Added Items</h2>

  <?php if (empty($products)): ?>
    <div class="alert alert-info text-center">No thrift items available right now. Check back soon!</div>
  <?php else: ?>
    <!-- Carousel for Desktop & Tablets -->
    <div id="newItemsCarousel" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <?php 
        $chunks = array_chunk($products, 4);
        foreach ($chunks as $index => $chunk): 
        ?>
          <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
            <div class="row justify-content-center g-3">
              <?php foreach ($chunk as $product): ?>
                <div class="col-6 col-md-4 col-lg-3">
                  <div class="card mx-auto h-100">
                    <img src="/<?= e(resolveProductImage($product['Image File'] ?? '')) ?>"
                         class="card-img-top" alt="<?= e($product['Name']) ?>" loading="lazy">
                    <div class="card-body">
                      <h5 class="card-title text-truncate"><?= e($product['Name']) ?></h5>
                      <p class="card-text text-muted small" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        <?= e($product['Details'] ?? '') ?>
                      </p>
                      <p class="card-text text-danger"><strong>₹<?= e($product['Price']) ?></strong></p>
                      <a href="/product.php?slug=<?= urlencode($product['Slug']) ?>" class="btn btn-primary btn-sm w-100">View Product</a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <?php if (count($chunks) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#newItemsCarousel" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#newItemsCarousel" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span>
          <span class="visually-hidden">Next</span>
        </button>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>

<?php 
$extraScripts = '<script src="/static/js/carousel-init.js"></script>';
require_once __DIR__ . '/includes/footer.php'; 
?>

