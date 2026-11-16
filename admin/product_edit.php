<?php
/**
 * ANSH THRIFTS — Admin: Edit Product
 * Powered by MySQL / MariaDB with PDO prepared statements.
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/Database.php';

requireAdmin();

$db = getDb();
$id = trim($_GET['id'] ?? '');

$stmt = $db->prepare("
    SELECT 
        id AS `ID`, name AS `Name`, slug AS `Slug`, price AS `Price`, 
        category AS `Category`, details AS `Details`, image_file AS `Image File`, 
        status AS `Status`, created_at AS `Created At`
    FROM `products` 
    WHERE id = :id 
    LIMIT 1
");
$stmt->execute([':id' => $id]);
$product = $stmt->fetch();

if (!$product) {
    setFlash('danger', 'Product not found in database.');
    redirect('/admin/index.php?tab=products');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        setFlash('danger', 'Security validation failed (Invalid CSRF token).');
        redirect('/admin/product_edit.php?id=' . urlencode($id));
    }

    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $details = trim($_POST['details'] ?? '');
    $category = trim($_POST['category'] ?? 'General');
    $status = trim($_POST['status'] ?? 'Active');
    if (!in_array($status, ['Active', 'Archived'])) {
        $status = 'Active';
    }

    $imageFile = $product['Image File']; // default to existing
    if (isset($_FILES['img_file']) && $_FILES['img_file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $uploadRes = validateAndSaveUpload($_FILES['img_file']);
        if (!$uploadRes['success']) {
            setFlash('danger', $uploadRes['error']);
            redirect('/admin/product_edit.php?id=' . urlencode($id));
        }
        $imageFile = $uploadRes['filename'];
    }

    try {
        Database::beginTransaction();

        $upStmt = $db->prepare("
            UPDATE `products`
            SET 
                `name` = :name,
                `price` = :price,
                `category` = :category,
                `details` = :details,
                `image_file` = :image_file,
                `status` = :status,
                `updated_at` = NOW()
            WHERE `id` = :id
        ");
        $upStmt->execute([
            ':name' => $name,
            ':price' => (float)$price,
            ':category' => $category,
            ':details' => $details,
            ':image_file' => $imageFile,
            ':status' => $status,
            ':id' => $id,
        ]);

        // Keep product_images in sync
        $imgStmt = $db->prepare("
            INSERT INTO `product_images` (`product_id`, `image_file`, `is_primary`, `sort_order`, `created_at`)
            VALUES (:product_id, :image_file, 1, 0, NOW())
            ON DUPLICATE KEY UPDATE `is_primary` = 1
        ");
        $imgStmt->execute([
            ':product_id' => $id,
            ':image_file' => $imageFile,
        ]);

        Database::commit();

        setFlash('success', "Product '{$name}' updated in database successfully!");
        redirect('/admin/index.php?tab=products');
    } catch (Throwable $e) {
        Database::rollBack();
        setFlash('danger', 'Update failed: ' . $e->getMessage());
    }
}

$pageTitle = 'Edit Product · Admin';
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
      <h2 class="text-center mb-4 font-playfair fw-bold">Edit Product</h2>

      <?php $flashMsgs = getFlashMessages(); ?>
      <?php foreach ($flashMsgs as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> py-2 small" role="alert">
          <?= e($flash['message']) ?>
        </div>
      <?php endforeach; ?>

      <form action="/admin/product_edit.php?id=<?= urlencode($id) ?>" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">

        <div class="mb-3">
          <label class="form-label fw-semibold">Product Name *</label>
          <input type="text" name="name" class="form-control" value="<?= e($product['Name']) ?>" required>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Price (₹) *</label>
            <input type="number" name="price" class="form-control" value="<?= e($product['Price']) ?>" min="1" step="any" required>
          </div>
          <div class="col-md-6">
            <label class="form-label fw-semibold">Status</label>
            <select name="status" class="form-select">
              <option value="Active" <?= ($product['Status'] ?? '') === 'Active' ? 'selected' : '' ?>>Active</option>
              <option value="Archived" <?= ($product['Status'] ?? '') === 'Archived' ? 'selected' : '' ?>>Archived</option>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Category *</label>
          <select name="category" class="form-select" required>
            <?php 
            $cats = ['Jackets', 'T-Shirts', 'Jeans', 'Shirts', 'Hoodies', 'Accessories', 'General'];
            foreach ($cats as $cat): 
            ?>
              <option value="<?= $cat ?>" <?= ($product['Category'] ?? '') === $cat ? 'selected' : '' ?>><?= $cat ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label fw-semibold">Product Details & Condition *</label>
          <textarea name="details" class="form-control" rows="4" required><?= e($product['Details']) ?></textarea>
        </div>

        <div class="mb-4">
          <label class="form-label fw-semibold">Product Image</label>
          <div class="d-flex align-items-center gap-3 mb-2">
            <img src="/<?= e(resolveProductImage($product['Image File'] ?? '')) ?>"
                 alt="" width="80" height="80" class="img-thumbnail" style="object-fit: cover;">
            <small class="text-muted">Current: <?= e($product['Image File']) ?></small>
          </div>
          <input type="file" name="img_file" class="form-control" accept="image/jpeg,image/png,image/webp,image/avif">
          <small class="text-muted">Leave blank to keep existing photo (Max 5MB).</small>
        </div>

        <div class="d-flex gap-2 justify-content-end">
          <a href="/admin/index.php?tab=products" class="btn btn-outline-secondary">Cancel</a>
          <button type="submit" class="btn btn-secondary">Update Product</button>
        </div>
      </form>
    </div>
  </div>

  <script src="/static/js/bootstrap.bundle.min.js"></script>
</body>
</html>
