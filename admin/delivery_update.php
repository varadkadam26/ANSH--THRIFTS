<?php
/**
 * ANSH THRIFTS — Admin: Update Delivery Status
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
        redirect('/admin/index.php?tab=deliveries');
    }

    $deliveryId = trim($_POST['delivery_id'] ?? '');
    $status = trim($_POST['status'] ?? 'Processing');

    if ($deliveryId !== '') {
        try {
            $db = getDb();
            $stmt = $db->prepare("SELECT dispatched_at, delivered_at FROM `deliveries` WHERE id = :id");
            $stmt->execute([':id' => $deliveryId]);
            $del = $stmt->fetch();

            if ($del) {
                $dispatchedAt = $del['dispatched_at'];
                $deliveredAt = $del['delivered_at'];

                if ($status === 'Dispatched' && empty($dispatchedAt)) {
                    $dispatchedAt = date('Y-m-d H:i:s');
                }
                if ($status === 'Delivered' && empty($deliveredAt)) {
                    $deliveredAt = date('Y-m-d H:i:s');
                }

                $up = $db->prepare("
                    UPDATE `deliveries`
                    SET 
                        `status` = :status,
                        `dispatched_at` = :dispatched_at,
                        `delivered_at` = :delivered_at,
                        `updated_at` = NOW()
                    WHERE `id` = :id
                ");
                $up->execute([
                    ':status' => $status,
                    ':dispatched_at' => $dispatchedAt,
                    ':delivered_at' => $deliveredAt,
                    ':id' => $deliveryId,
                ]);

                setFlash('success', "Delivery #{$deliveryId} updated to {$status}.");
            }
        } catch (Throwable $e) {
            setFlash('danger', 'Error updating delivery: ' . $e->getMessage());
        }
    }
}

redirect('/admin/index.php?tab=deliveries');
