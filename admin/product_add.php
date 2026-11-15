<?php
/**
 * ANSH THRIFTS — Admin: Add New Product
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/GoogleSheetsService.php';

requireAdmin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        setFlash('danger', 'Security validation failed (Invalid CSRF token).');
        redirect('/admin/product_add.php');
    }

    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $details = trim($_POST['details'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $stock = max(1, (int)($_POST['stock'] ?? 10));
    $slug = trim($_POST['slug'] ?? '');
    if ($slug === '') {
        $slug = generateSlug($name);
    } else {
        $slug = generateSlug($slug);
    }

    $imageFile = 'shirt.jpg'; // default fallback
    if (isset($_FILES['img_file']) && $_FILES['img_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadRes = validateAndSaveUpload($_FILES['img_file']);
        if (!$uploadRes['success']) {
            setFlash('danger', $uploadRes['error']);
            redirect('/admin/product_add.php');
        }
        $imageFile = $uploadRes['filename'];
    }

    try {
        $sheets = new GoogleSheetsService();
        $productId = 'PRD-' . rand(2000, 9999);
        $now = date('Y-m-d H:i:s');

        // 1. Add to Products tab
        $sheets->insertRecord('Products', [
            'ID' => $productId,
            'Name' => $name,
            'Slug' => $slug,
            'Price' => $price,
            'Category' => $category,
            'Details' => $details,
            'Image File' => $imageFile,
            'Status' => 'Active',
            'Created At' => $now,
        ]);

        // 2. Add to Inventory tab
        $sku = 'SKU-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $slug), 0, 6)) . '-' . rand(10, 99);
        $sheets->insertRecord('Inventory', [
            'Product ID' => $productId,
            'Product Name' => $name,
            'SKU' => $sku,
            'Stock Quantity' => $stock,
            'Reserved Quantity' => 0,
            'Available Quantity' => $stock,
            'Low Stock Alert' => 3,
            'Last Updated' => $now,
        ]);

        setFlash('success', "Product '{$name}' created and added to Google Sheets successfully!");
        redirect('/admin/index.php?tab=products');
    } catch (Throwable $e) {
        setFlash('danger', 'Failed to add product: ' . $e->getMessage());
    }
}

$pageTitle = 'Add New Product · Admin';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="shortcut icon" href="/static/images/favicon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
  <link href="/static/css/bootstrap.min.css" rel="stylesheet">
  <link href="/static/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container py-5" style="max-width: 650px;">
    <div class="card p-4 shadow-sm border-0 rounded-3 bg-white">
      <h2 class="text-center mb-4 font-playfair fw-bold">Add New Product</h2>

      <?php $flashMsgs = getFlashMessages(); ?>
      <?php foreach ($flashMsgs as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> py-2 small" role="alert">
          <?= e($flash['message']) ?>
        </div>
      <?php endforeach; ?>

      <form action="/admin/product_add.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">

        <div class="mb-3">
          <label class="form-label fw-semibold">Product Name *</label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Vintage 90s Bomber Jacket" required>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Price (₹) *</label>
            <input type="number" name="price" class="form-control" placeholder="699" min="1" step="any" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Initial Stock Quantity *</label>
            <input type="number" name="stock" class="form-control" value="10" min="1" required>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Category *</label>
          <select name="category" class="form-select" required>
            <option value="Jackets">Jackets</option>
            <option value="T-Shirts">T-Shirts</option>
            <option value="Jeans">Jeans</option>
            <option value="Shirts">Shirts</option>
            <option value="Hoodies">Hoodies</option>
            <option value="Accessories">Accessories</option>
            <option value="General">General</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Slug (Optional, generated from name if blank)</label>
          <input type="text" name="slug" class="form-control" placeholder="vintage-90s-bomber-jacket">
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Product Details & Condition *</label>
          <textarea name="details" class="form-control" rows="4" placeholder="Mention size, material, color, and vintage condition notes..." required></textarea>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">Product Image *</label>
          <input type="file" name="img_file" class="form-control" accept="image/jpeg,image/png,image/webp,image/avif" required>
          <small class="text-muted">Allowed formats: JPG, PNG, WEBP, AVIF (Max 5MB).</small>
        </div>

        <div class="d-flex gap-2 justify-content-end">
          <a href="/admin/index.php" class="btn btn-outline-secondary">Cancel</a>
          <button type="submit" class="btn btn-secondary">Save to Google Sheets</button>
        </div>
      </form>
    </div>
  </div>

  <script src="/static/js/bootstrap.bundle.min.js"></script>
</body>
</html>
