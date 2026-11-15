<?php
/**
 * ANSH THRIFTS — Admin: Delete Product
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
        redirect('/admin/index.php?tab=products');
    }

    $id = trim($_POST['id'] ?? '');
    $row = (int)($_POST['row'] ?? 0);

    if ($id !== '' && $row > 1) {
        try {
            $sheets = new GoogleSheetsService();
            $sheets->deleteRow('Products', $row);
            setFlash('success', "Product ID {$id} was deleted from Google Sheets.");
        } catch (Throwable $e) {
            setFlash('danger', 'Error deleting product: ' . $e->getMessage());
        }
    }
}

redirect('/admin/index.php?tab=products');
