<?php
/**
 * ANSH THRIFTS — Admin: Delete Product
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
        redirect('/admin/index.php?tab=products');
    }

    $id = trim($_POST['id'] ?? '');

    if ($id !== '') {
        try {
            $db = getDb();
            $stmt = $db->prepare("DELETE FROM `products` WHERE id = :id");
            $stmt->execute([':id' => $id]);
            setFlash('success', "Product ID {$id} was deleted successfully.");
        } catch (Throwable $e) {
            setFlash('danger', 'Error deleting product: ' . $e->getMessage());
        }
    }
}

redirect('/admin/index.php?tab=products');
