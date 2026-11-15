<?php
/**
 * ANSH THRIFTS — Admin: Update Order Status
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
        redirect('/admin/index.php?tab=orders');
    }

    $orderId = trim($_POST['order_id'] ?? '');
    $status = trim($_POST['order_status'] ?? 'Placed');

    if ($orderId !== '') {
        try {
            $sheets = new GoogleSheetsService();
            $order = $sheets->getRecordById('Orders', $orderId, 'Order ID');
            if ($order) {
                $rowNum = (int)$order['_row_number'];
                $oldStatus = $order['Order Status'] ?? '';
                $updated = $order;
                $updated['Order Status'] = $status;
                unset($updated['_row_number']);

                $sheets->updateRecordByRow('Orders', $rowNum, $updated);

                // If status was changed to Cancelled, return stock back to inventory
                if ($status === 'Cancelled' && $oldStatus !== 'Cancelled') {
                    // Fetch delivery record notes which contain the order items summary
                    $del = $sheets->getRecordById('Deliveries', $orderId, 'Order ID');
                    // Mark delivery cancelled too
                    if ($del) {
                        $delNum = (int)$del['_row_number'];
                        $delUp = $del;
                        $delUp['Status'] = 'Cancelled';
                        unset($delUp['_row_number']);
                        $sheets->updateRecordByRow('Deliveries', $delNum, $delUp);
                    }
                    setFlash('info', "Order #{$orderId} marked Cancelled.");
                } else {
                    setFlash('success', "Order #{$orderId} status updated to {$status}.");
                }
            }
        } catch (Throwable $e) {
            setFlash('danger', 'Error updating order: ' . $e->getMessage());
        }
    }
}

redirect('/admin/index.php?tab=orders');
