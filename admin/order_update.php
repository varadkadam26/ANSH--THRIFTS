<?php
/**
 * ANSH THRIFTS — Admin: Update Order Status
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
        redirect('/admin/index.php?tab=orders');
    }

    $orderId = trim($_POST['order_id'] ?? '');
    $status = trim($_POST['order_status'] ?? 'Placed');

    if ($orderId !== '') {
        $db = getDb();
        try {
            Database::beginTransaction();

            // Fetch old order status
            $chk = $db->prepare("SELECT order_status FROM `orders` WHERE id = :id FOR UPDATE");
            $chk->execute([':id' => $orderId]);
            $oldOrder = $chk->fetch();

            if ($oldOrder) {
                $oldStatus = (string)$oldOrder['order_status'];

                // Update order status
                $up = $db->prepare("UPDATE `orders` SET `order_status` = :status, `updated_at` = NOW() WHERE `id` = :id");
                $up->execute([':status' => $status, ':id' => $orderId]);

                // If status was changed to Cancelled, return stock back to inventory and cancel delivery
                if ($status === 'Cancelled' && $oldStatus !== 'Cancelled') {
                    $upDel = $db->prepare("UPDATE `deliveries` SET `status` = 'Cancelled', `updated_at` = NOW() WHERE `order_id` = :oid");
                    $upDel->execute([':oid' => $orderId]);

                    // Fetch order items to restore stock
                    $itStmt = $db->prepare("SELECT product_id, quantity FROM `order_items` WHERE order_id = :oid");
                    $itStmt->execute([':oid' => $orderId]);
                    $items = $itStmt->fetchAll();

                    foreach ($items as $item) {
                        if (!empty($item['product_id'])) {
                            adjustStock((string)$item['product_id'], (int)$item['quantity']);
                        }
                    }

                    setFlash('info', "Order #{$orderId} marked Cancelled and stock restored.");
                } else {
                    setFlash('success', "Order #{$orderId} status updated to {$status}.");
                }
            }

            Database::commit();
        } catch (Throwable $e) {
            Database::rollBack();
            setFlash('danger', 'Error updating order: ' . $e->getMessage());
        }
    }
}

redirect('/admin/index.php?tab=orders');
