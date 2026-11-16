<?php
/**
 * ANSH THRIFTS — Admin: Add New Product
 * Powered by MySQL / MariaDB with PDO prepared statements and transactions.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/Database.php';

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

    $db = getDb();

    try {
        Database::beginTransaction();

        $productId = 'PRD-' . rand(2000, 9999);

        // 1. Add to products table
        $prodStmt = $db->prepare("
            INSERT INTO `products` (`id`, `name`, `slug`, `price`, `category`, `details`, `image_file`, `status`, `created_at`)
            VALUES (:id, :name, :slug, :price, :category, :details, :image_file, 'Active', NOW())
        ");
        $prodStmt->execute([
            ':id' => $productId,
            ':name' => $name,
            ':slug' => $slug,
            ':price' => (float)$price,
            ':category' => $category,
            ':details' => $details,
            ':image_file' => $imageFile,
        ]);

        // 2. Add to product_images table
        $imgStmt = $db->prepare("
            INSERT INTO `product_images` (`product_id`, `image_file`, `is_primary`, `sort_order`, `created_at`)
            VALUES (:product_id, :image_file, 1, 0, NOW())
        ");
        $imgStmt->execute([
            ':product_id' => $productId,
            ':image_file' => $imageFile,
        ]);

        // 3. Add to inventory table
        $sku = 'SKU-' . strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $slug), 0, 6)) . '-' . rand(10, 99);
        $invStmt = $db->prepare("
            INSERT INTO `inventory` (`product_id`, `sku`, `stock_quantity`, `reserved_quantity`, `available_quantity`, `low_stock_alert`, `updated_at`)
            VALUES (:product_id, :sku, :stock_quantity, 0, :available_quantity, 'Yes', NOW())
        ");
        $invStmt->execute([
            ':product_id' => $productId,
            ':sku' => $sku,
            ':stock_quantity' => $stock,
            ':available_quantity' => $stock,
        ]);

        Database::commit();

        setFlash('success', "Product '{$name}' created and added to database successfully!");
        redirect('/admin/index.php?tab=products');
    } catch (Throwable $e) {
        Database::rollBack();
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
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="/static/css/bootstrap.min.css" rel="stylesheet">
  <link href="/static/css/style.css" rel="stylesheet">

  <style>
    body { background-color: #f4f6f8; }
    .form-card {
      background: #fff;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      padding: 30px;
      max-width: 700px;
      margin: 40px auto;
    }
  </style>
</head>
<body>

  <div class="container py-4">
    <div class="form-card">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Add New Product</h3>
        <a href="/admin/index.php" class="btn btn-outline-secondary btn-sm">&larr; Back to Dashboard</a>
      </div>

      <?php $flashMsgs = getFlashMessages(); ?>
      <?php foreach ($flashMsgs as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
          <?= e($flash['message']) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endforeach; ?>

      <form action="/admin/product_add.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">

        <div class="mb-3">
          <label class="form-label fw-semibold">Product Title *</label>
          <input type="text" name="name" class="form-control" placeholder="e.g. 90s Vintage Leather Jacket" required>
        </div>

        <div class="row g-3 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Price (INR ₹) *</label>
            <input type="number" step="0.01" name="price" class="form-control" placeholder="799" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Initial Stock *</label>
            <input type="number" name="stock" class="form-control" value="1" min="1" required>
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
          <button type="submit" class="btn btn-secondary">Save Product</button>
        </div>
      </form>
    </div>
  </div>

  <script src="/static/js/bootstrap.bundle.min.js"></script>
</body>
</html>
