<?php
/**
 * ANSH THRIFTS — Common Helpers and Security
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

function e(mixed $string): string {
    return htmlspecialchars((string)($string ?? ''), ENT_QUOTES, 'UTF-8');
}

function setFlash(string $type, string $message): void {
    if (!isset($_SESSION['flash'])) {
        $_SESSION['flash'] = [];
    }
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function getFlashMessages(): array {
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function redirect(string $url): never {
    header("Location: {$url}");
    exit;
}

function jsonResponse(array $data, int $statusCode = 200): never {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data);
    exit;
}

/**
 * Generate CSRF Token for form submissions
 */
function getCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF Token
 */
function validateCsrfToken(?string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function requireAdmin(): void {
    if (!isAdminLoggedIn()) {
        setFlash('warning', 'Please log in as administrator to access this area.');
        redirect('/login.php');
    }
}

function isAdminLoggedIn(): bool {
    return !empty($_SESSION['admin_logged_in']) && 
           isset($_SESSION['admin_username']) && 
           $_SESSION['admin_username'] === ADMIN_USERNAME;
}

/**
 * Verify Admin Password using password_verify() or secure fallback
 */
function verifyAdminPassword(string $inputPassword): bool {
    $stored = ADMIN_PASSWORD;
    // If stored password is a bcrypt/argon2 hash (starts with $2y$, $2a$, or $argon2)
    if (str_starts_with($stored, '$2y$') || str_starts_with($stored, '$2a$') || str_starts_with($stored, '$argon2')) {
        return password_verify($inputPassword, $stored);
    }
    // Plain-text constant fallback (timing-attack resistant comparison)
    return hash_equals($stored, $inputPassword);
}

function getCart(): array {
    return $_SESSION['cart'] ?? [];
}

function getCartCount(): int {
    $cart = getCart();
    $total = 0;
    foreach ($cart as $item) {
        $total += (int)($item['quantity'] ?? 1);
    }
    return $total;
}

function getCartTotal(): float {
    $cart = getCart();
    $total = 0.0;
    foreach ($cart as $item) {
        $total += (float)($item['price'] ?? 0) * (int)($item['quantity'] ?? 1);
    }
    return $total;
}

function addToCart(array $product, int $quantity = 1): void {
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }
    $id = (string)$product['ID'];
    foreach ($_SESSION['cart'] as &$item) {
        if ($item['id'] === $id) {
            $item['quantity'] += $quantity;
            return;
        }
    }
    $_SESSION['cart'][] = [
        'id' => $id,
        'name' => $product['Name'] ?? 'Product',
        'price' => (float)($product['Price'] ?? 0),
        'image' => $product['Image File'] ?? '',
        'slug' => $product['Slug'] ?? '',
        'quantity' => $quantity,
    ];
}

function removeFromCart(string $productId): void {
    if (!isset($_SESSION['cart'])) {
        return;
    }
    $_SESSION['cart'] = array_values(array_filter(
        $_SESSION['cart'],
        fn($item) => (string)$item['id'] !== (string)$productId
    ));
}

function updateCartQuantity(string $productId, int $quantity): void {
    if (!isset($_SESSION['cart'])) {
        return;
    }
    if ($quantity <= 0) {
        removeFromCart($productId);
        return;
    }
    foreach ($_SESSION['cart'] as &$item) {
        if ((string)$item['id'] === (string)$productId) {
            $item['quantity'] = $quantity;
            return;
        }
    }
}

function clearCart(): void {
    unset($_SESSION['cart']);
}

function resolveProductImage(string $imageFile): string {
    $imageFile = trim($imageFile);
    if ($imageFile === '') {
        return 'static/images/shirt.jpg';
    }
    if (str_starts_with($imageFile, 'http://') || str_starts_with($imageFile, 'https://')) {
        return $imageFile;
    }
    if (str_starts_with($imageFile, 'static/')) {
        return $imageFile;
    }
    if (str_starts_with($imageFile, 'uploads/')) {
        return 'static/images/' . $imageFile;
    }
    if (file_exists(__DIR__ . '/../static/images/uploads/' . $imageFile)) {
        return 'static/images/uploads/' . $imageFile;
    }
    return 'static/images/' . $imageFile;
}

function generateSlug(string $title): string {
    $slug = preg_replace('~[^\pL\d]+~u', '-', $title);
    $slug = iconv('utf-8', 'us-ascii//TRANSLIT', (string)$slug);
    $slug = preg_replace('~[^-\w]+~', '', (string)$slug);
    $slug = trim((string)$slug, '-');
    $slug = preg_replace('~-+~', '-', (string)$slug);
    return strtolower($slug ?: 'product-' . time());
}

/**
 * Robust image upload validator: MIME check, size limit (5MB), extension whitelist
 */
function validateAndSaveUpload(array $file): array {
    if (!isset($file['error']) || is_array($file['error'])) {
        return ['success' => false, 'error' => 'Invalid file parameter'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return match ($file['error']) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => ['success' => false, 'error' => 'Uploaded file exceeds max size limit.'],
            UPLOAD_ERR_NO_FILE => ['success' => false, 'error' => 'No file was uploaded.'],
            default => ['success' => false, 'error' => 'Unknown upload error occurred.'],
        };
    }

    // Size limit: 5MB
    $maxBytes = 5 * 1024 * 1024;
    if ($file['size'] > $maxBytes) {
        return ['success' => false, 'error' => 'File size exceeds maximum allowed 5MB.'];
    }

    // Strict MIME check via Fileinfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/avif' => 'avif'
    ];

    if (!isset($allowedMimes[$mime])) {
        return ['success' => false, 'error' => 'Invalid image type. Allowed formats: JPG, PNG, WEBP, AVIF.'];
    }

    $ext = $allowedMimes[$mime];
    $newFileName = bin2hex(random_bytes(16)) . '.' . $ext;
    $targetPath = UPLOAD_DIR . '/' . $newFileName;

    if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => false, 'error' => 'Failed to save uploaded file to destination.'];
    }

    // Set safe permissions
    @chmod($targetPath, 0644);

    return ['success' => true, 'filename' => 'uploads/' . $newFileName];
}
