<?php
/**
 * ANSH THRIFTS — API: Verify Razorpay Payment Signature
 * - Verifies HMAC-SHA256 signature
 * - Idempotency guard: Prevents duplicate execution & double stock deduction
 * - Deducts inventory stock strictly upon verified payment
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/GoogleSheetsService.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    jsonResponse(['error' => 'Invalid JSON input'], 400);
}

$orderId = trim($input['order_id'] ?? '');
$rzpOrderId = trim($input['razorpay_order_id'] ?? '');
$rzpPaymentId = trim($input['razorpay_payment_id'] ?? '');
$rzpSignature = trim($input['razorpay_signature'] ?? '');

if ($rzpOrderId === '' || $rzpPaymentId === '' || $rzpSignature === '') {
    jsonResponse(['error' => 'Missing verification parameters.'], 400);
}

// 1. Verify HMAC-SHA256 Signature
$expectedSignature = hash_hmac('sha256', "{$rzpOrderId}|{$rzpPaymentId}", RAZORPAY_KEY_SECRET);

if (!hash_equals($expectedSignature, $rzpSignature)) {
    jsonResponse(['error' => 'Payment signature verification failed. Untrusted callback.'], 400);
}

try {
    $sheets = new GoogleSheetsService();
    
    // 2. Fetch the order from Google Sheets
    $order = null;
    if ($orderId !== '') {
        $order = $sheets->getRecordById('Orders', $orderId, 'Order ID');
    }
    if (!$order && $rzpOrderId !== '') {
        $order = $sheets->getRecordByField('Orders', 'Razorpay Order ID', $rzpOrderId);
        if ($order) {
            $orderId = (string)($order['Order ID'] ?? '');
        }
    }

    if (!$order) {
        jsonResponse(['error' => 'Order record not found in system.'], 404);
    }

    // 3. IDEMPOTENCY CHECK: Check if already verified and paid
    $currentPaymentStatus = (string)($order['Payment Status'] ?? '');
    $existingPaymentId = (string)($order['Razorpay Payment ID'] ?? '');

    if (str_contains(strtolower($currentPaymentStatus), 'paid') || $existingPaymentId === $rzpPaymentId) {
        // Already processed earlier; return success without deducting stock a second time
        clearCart();
        jsonResponse([
            'success' => true,
            'message' => 'Payment already verified for this order.',
            'idempotent' => true
        ]);
    }

    // 4. Update Order Status in Google Sheets
    $rowNum = (int)$order['_row_number'];
    $updated = $order;
    $updated['Payment Status'] = 'Paid (Verified)';
    $updated['Razorpay Payment ID'] = $rzpPaymentId;
    $updated['Order Status'] = 'Confirmed';
    unset($updated['_row_number']);

    $sheets->updateRecordByRow('Orders', $rowNum, $updated);

    // 5. DEDUCT INVENTORY STOCK STRICTLY AFTER VERIFIED PAYMENT
    $pendingItems = $_SESSION['pending_order_' . $orderId] ?? getCart();
    if (!empty($pendingItems) && is_array($pendingItems)) {
        foreach ($pendingItems as $item) {
            if (isset($item['id'], $item['quantity'])) {
                $sheets->adjustStock((string)$item['id'], -(int)$item['quantity']);
            }
        }
    }

    // Clean up session pending records
    unset($_SESSION['pending_order_' . $orderId], $_SESSION['pending_rzp_' . $rzpOrderId]);
    clearCart();

    jsonResponse([
        'success' => true,
        'message' => 'Payment verified and inventory synchronized successfully.'
    ]);

} catch (Throwable $e) {
    jsonResponse(['error' => 'Error verifying payment: ' . $e->getMessage()], 500);
}
