<?php
/**
 * ANSH THRIFTS — Admin: Update Message Status
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
        redirect('/admin/index.php?tab=contacts');
    }

    $messageId = trim($_POST['message_id'] ?? '');
    $status = trim($_POST['status'] ?? 'Responded');

    if ($messageId !== '') {
        try {
            $db = getDb();
            $respondedAt = ($status === 'Responded') ? date('Y-m-d H:i:s') : null;

            $stmt = $db->prepare("
                UPDATE `contact_messages`
                SET 
                    `status` = :status,
                    `responded_at` = :responded_at
                WHERE `id` = :id
            ");
            $stmt->execute([
                ':status' => $status,
                ':responded_at' => $respondedAt,
                ':id' => $messageId,
            ]);

            setFlash('success', "Message #{$messageId} marked {$status}.");
        } catch (Throwable $e) {
            setFlash('danger', 'Error updating message: ' . $e->getMessage());
        }
    }
}

redirect('/admin/index.php?tab=contacts');
