<?php
/**
 * ANSH THRIFTS — Admin: Update Delivery Status
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
        redirect('/admin/index.php?tab=deliveries');
    }

    $deliveryId = trim($_POST['delivery_id'] ?? '');
    $status = trim($_POST['status'] ?? 'Processing');

    if ($deliveryId !== '') {
        try {
            $sheets = new GoogleSheetsService();
            $del = $sheets->getRecordById('Deliveries', $deliveryId, 'Delivery ID');
            if ($del) {
                $rowNum = (int)$del['_row_number'];
                $updated = $del;
                $updated['Status'] = $status;
                if ($status === 'Dispatched' && empty($updated['Dispatched At'])) {
                    $updated['Dispatched At'] = date('Y-m-d H:i:s');
                }
                if ($status === 'Delivered' && empty($updated['Delivered At'])) {
                    $updated['Delivered At'] = date('Y-m-d H:i:s');
                }
                unset($updated['_row_number']);

                $sheets->updateRecordByRow('Deliveries', $rowNum, $updated);
                setFlash('success', "Delivery #{$deliveryId} updated to {$status}.");
            }
        } catch (Throwable $e) {
            setFlash('danger', 'Error updating delivery: ' . $e->getMessage());
        }
    }
}

redirect('/admin/index.php?tab=deliveries');
