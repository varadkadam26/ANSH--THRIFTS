<?php
/**
 * ANSH THRIFTS — Single Product Details Page (product.php)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/GoogleSheetsService.php';

$slug = trim($_GET['slug'] ?? '');
if ($slug === '') {
    redirect('/shop.php');
}

$sheets = new GoogleSheetsService();
$product = $sheets->getProductBySlug($slug);

if (!$product) {
    http_response_code(404);
    $pageTitle = 'Product Not Found · ' . APP_NAME;
    require_once __DIR__ . '/includes/header.php';
    echo '<div class="container text-center py-5" style="margin-top: 100px;">
            <h2>Product Not Found</h2>
            <p class="text-muted">The product you are looking for is no longer available.</p>
            <a href="/shop.php" class="btn btn-primary mt-3">Back to Shop</a>
          </div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

$pageTitle = ($product['Name'] ?? 'Product') . ' · ' . APP_NAME;
$activeNav = 'shop';

require_once __DIR__ . '/includes/header.php';
?>

<div class="container page-content" style="margin-top: 100px;">
  <div class="row g-4 align-items-start">
    <!-- Product Image -->
    <div class="col-12 col-md-6 text-center">
      <div class="product-image">
        <img src="/<?= e(resolveProductImage($product['Image File'] ?? '')) ?>"
             alt="<?= e($product['Name']) ?>" class="img-fluid rounded-3 w-100 shadow-sm"
             style="object-fit: cover; max-height: 520px;">
      </div>
    </div>

    <!-- Product Details -->
    <div class="col-12 col-md-6 ps-md-4">
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="/index.php">Home</a></li>
          <li class="breadcrumb-item"><a href="/shop.php">Shop</a></li>
          <?php if (!empty($product['Category'])): ?>
            <li class="breadcrumb-item"><a href="/shop.php?category=<?= urlencode($product['Category']) ?>"><?= e($product['Category']) ?></a></li>
          <?php endif; ?>
          <li class="breadcrumb-item active" aria-current="page"><?= e($product['Name']) ?></li>
        </ol>
      </nav>

      <h1 class="product-name"><?= e($product['Name']) ?></h1>
      
      <div class="mb-3">
        <span class="badge bg-secondary"><?= e($product['Category'] ?? 'Vintage') ?></span>
        <span class="badge bg-success ms-1">Available in Stock</span>
      </div>

      <h2 style="color: #d4a855; font-family: 'Inter', sans-serif; font-weight: 700; margin-bottom: 20px;">
        ₹<?= e($product['Price']) ?>
      </h2>

      <div class="mb-4">
        <h5 class="fw-bold" style="font-family: 'Inter', sans-serif;">Description & Condition</h5>
        <p class="text-muted" style="font-size: 1.05rem; line-height: 1.8;">
          <?= nl2br(e($product['Details'] ?? 'No details provided.')) ?>
        </p>
      </div>

      <!-- Add to Cart Form -->
      <form action="/cart.php" method="POST" class="mb-4">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?= e($product['ID']) ?>">
        
        <div class="d-flex align-items-center gap-3 mb-3">
          <label for="quantity" class="fw-semibold">Quantity:</label>
          <input type="number" id="quantity" name="quantity" value="1" min="1" max="10" 
                 class="form-control text-center" style="width: 80px;">
        </div>

        <div class="d-flex flex-wrap gap-2">
          <button type="submit" class="btn btn-primary btn-lg">Add to Cart</button>
          <button type="submit" name="buy_now" value="1" class="btn btn-secondary btn-lg">Buy Now</button>
          <a href="/shop.php" class="btn btn-white-outline btn-lg" style="border-color: #ddd; color: #333;">Continue Shopping</a>
        </div>
      </form>

      <div class="border-top pt-3 text-muted small">
        <p class="mb-1"><strong>✓ Sustainable Choice:</strong> 100% genuine pre-loved verified piece.</p>
        <p class="mb-1"><strong>✓ Fast Shipping:</strong> Dispatched across India with reliable tracking.</p>
        <p class="mb-0"><strong>✓ Secure Payment:</strong> Encrypted online checkout via Razorpay or Cash on Delivery.</p>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

