<?php
/**
 * ANSH THRIFTS — Admin: Edit Product
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/GoogleSheetsService.php';

requireAdmin();

$sheets = new GoogleSheetsService();
$id = trim($_GET['id'] ?? '');

$product = $sheets->getRecordById('Products', $id);
if (!$product) {
    setFlash('danger', 'Product not found in Google Sheets.');
    redirect('/admin/index.php?tab=products');
}

$rowNum = (int)$product['_row_number'];

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
        $updatedData = [
            'ID' => $product['ID'],
            'Name' => $name,
            'Slug' => $product['Slug'],
            'Price' => $price,
            'Category' => $category,
            'Details' => $details,
            'Image File' => $imageFile,
            'Status' => $status,
            'Created At' => $product['Created At'] ?? date('Y-m-d H:i:s'),
        ];

        $sheets->updateRecordByRow('Products', $rowNum, $updatedData);

        setFlash('success', "Product '{$name}' updated in Google Sheets!");
        redirect('/admin/index.php?tab=products');
    } catch (Throwable $e) {
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
              <option value="Hidden" <?= ($product['Status'] ?? '') === 'Hidden' ? 'selected' : '' ?>>Hidden</option>
              <option value="Sold Out" <?= ($product['Status'] ?? '') === 'Sold Out' ? 'selected' : '' ?>>Sold Out</option>
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
