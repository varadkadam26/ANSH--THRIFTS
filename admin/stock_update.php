<?php
/**
 * ANSH THRIFTS — Admin: Quick Stock Update
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
        redirect('/admin/index.php?tab=inventory');
    }

    $productId = trim($_POST['product_id'] ?? '');
    $newStock = max(0, (int)($_POST['new_stock'] ?? 0));

    if ($productId !== '') {
        try {
            $sheets = new GoogleSheetsService();
            $invRecords = $sheets->getAllRecords('Inventory');
            foreach ($invRecords as $inv) {
                if ((string)$inv['Product ID'] === $productId) {
                    $rowNum = (int)$inv['_row_number'];
                    $reserved = (int)($inv['Reserved Quantity'] ?? 0);
                    $available = max(0, $newStock - $reserved);

                    $updated = $inv;
                    $updated['Stock Quantity'] = $newStock;
                    $updated['Available Quantity'] = $available;
                    $updated['Last Updated'] = date('Y-m-d H:i:s');
                    unset($updated['_row_number']);

                    $sheets->updateRecordByRow('Inventory', $rowNum, $updated);
                    setFlash('success', "Stock updated for {$inv['Product Name']}.");
                    break;
                }
            }
        } catch (Throwable $e) {
            setFlash('danger', 'Error updating stock: ' . $e->getMessage());
        }
    }
}

redirect('/admin/index.php?tab=inventory');
