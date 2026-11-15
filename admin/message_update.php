<?php
/**
 * ANSH THRIFTS — Admin: Update Message Status
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
        redirect('/admin/index.php?tab=contacts');
    }

    $messageId = trim($_POST['message_id'] ?? '');
    $status = trim($_POST['status'] ?? 'Responded');

    if ($messageId !== '') {
        try {
            $sheets = new GoogleSheetsService();
            $msg = $sheets->getRecordById('Contact Messages', $messageId, 'Message ID');
            if ($msg) {
                $rowNum = (int)$msg['_row_number'];
                $updated = $msg;
                $updated['Status'] = $status;
                if ($status === 'Responded') {
                    $updated['Responded At'] = date('Y-m-d H:i:s');
                }
                unset($updated['_row_number']);

                $sheets->updateRecordByRow('Contact Messages', $rowNum, $updated);
                setFlash('success', "Message #{$messageId} marked {$status}.");
            }
        } catch (Throwable $e) {
            setFlash('danger', 'Error updating message: ' . $e->getMessage());
        }
    }
}

redirect('/admin/index.php?tab=contacts');
