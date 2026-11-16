<?php
/**
 * ANSH THRIFTS — Admin: Quick Stock Update
 * Powered by MySQL / MariaDB with PDO prepared statements.
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
        redirect('/admin/index.php?tab=inventory');
    }

    $productId = trim($_POST['product_id'] ?? '');
    $newStock = max(0, (int)($_POST['new_stock'] ?? 0));

    if ($productId !== '') {
        try {
            $db = getDb();
            $stmt = $db->prepare("
                UPDATE `inventory`
                SET 
                    `stock_quantity` = :stock,
                    `available_quantity` = GREATEST(0, :stock - `reserved_quantity`),
                    `updated_at` = NOW()
                WHERE `product_id` = :id
            ");
            $stmt->execute([
                ':stock' => $newStock,
                ':id' => $productId,
            ]);

            setFlash('success', "Stock updated for Product ID {$productId}.");
        } catch (Throwable $e) {
            setFlash('danger', 'Error updating stock: ' . $e->getMessage());
        }
    }
}

redirect('/admin/index.php?tab=inventory');
