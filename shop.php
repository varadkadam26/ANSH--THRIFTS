<?php
/**
 * ANSH THRIFTS — Shop & Catalog Page (shop.php)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/GoogleSheetsService.php';

$pageTitle = 'Shop Collection · ' . APP_NAME;
$activeNav = 'shop';

$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');

$sheets = new GoogleSheetsService();
$products = $sheets->getActiveProducts($search !== '' ? $search : null, $category !== '' ? $category : null);

require_once __DIR__ . '/includes/header.php';
?>

<!-- Shop Hero Banner -->
<div class="hero" style="background: url('/static/images/login-img.jpg') no-repeat center center/cover; min-height: 40vh;">
  <div class="container text-center">
    <div class="intro-excerpt mx-auto" style="max-width: 600px;">
      <h1>Our Collection</h1>
      <p>Find your next favourite pre-loved piece.</p>
    </div>
  </div>
</div>

<!-- Product Catalog -->
<div class="product-section page-content">
  <div class="container">
    <h2 class="section-heading text-center mb-4">Shop Our Products</h2>

    <!-- Search & Filter Controls -->
    <form method="GET" action="/shop.php" class="row g-2 mb-4 justify-content-center">
      <div class="col-12 col-sm-6 col-md-5">
        <input type="text" name="q" value="<?= e($search) ?>"
               class="form-control" placeholder="Search products, styles or categories…">
      </div>
      <div class="col-6 col-sm-4 col-md-3">
        <select name="category" class="form-select">
          <option value="">All categories</option>
          <option value="Jackets" <?= $category === 'Jackets' ? 'selected' : '' ?>>Jackets</option>
          <option value="T-Shirts" <?= $category === 'T-Shirts' ? 'selected' : '' ?>>T-Shirts</option>
          <option value="Jeans" <?= $category === 'Jeans' ? 'selected' : '' ?>>Jeans</option>
          <option value="Shirts" <?= $category === 'Shirts' ? 'selected' : '' ?>>Shirts</option>
          <option value="Hoodies" <?= $category === 'Hoodies' ? 'selected' : '' ?>>Hoodies</option>
        </select>
      </div>
      <div class="col-6 col-sm-2 col-md-2 d-grid">
        <button type="submit" class="btn btn-primary">Search</button>
      </div>
    </form>

    <?php if (empty($products)): ?>
      <div class="alert alert-info text-center py-4">
        No thrifted items matched your search criteria. Try a different keyword or browse all categories!
      </div>
    <?php else: ?>
      <div class="row product-grid">
        <?php foreach ($products as $product): ?>
        <div class="col-6 col-sm-6 col-md-4 col-lg-3 d-flex align-items-stretch">
          <a class="product-item w-100" href="/product.php?slug=<?= urlencode($product['Slug']) ?>">
            <img src="/<?= e(resolveProductImage($product['Image File'] ?? '')) ?>"
                 class="img-fluid product-thumbnail" alt="<?= e($product['Name']) ?>" loading="lazy">
            <h3 class="product-title"><?= e($product['Name']) ?></h3>
            <p class="card-text text-muted small" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
              <?= e($product['Details'] ?? '') ?>
            </p>
            <strong class="product-price">₹<?= e($product['Price']) ?></strong>
          </a>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

