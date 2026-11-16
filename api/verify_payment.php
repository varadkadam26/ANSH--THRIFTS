<?php
/**
 * ANSH THRIFTS — API: Verify Razorpay Payment Signature
 * - Verifies HMAC-SHA256 signature
 * - Idempotency guard: Prevents duplicate execution & double stock deduction
 * - Deducts inventory stock strictly upon verified payment within a database transaction
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/Database.php';

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

$db = getDb();

try {
    Database::beginTransaction();

    // 2. Fetch the order from MySQL with FOR UPDATE locking
    $stmt = $db->prepare("
        SELECT * FROM `orders` 
        WHERE id = :id OR razorpay_order_id = :rzp_id 
        LIMIT 1 
        FOR UPDATE
    ");
    $stmt->execute([
        ':id' => $orderId,
        ':rzp_id' => $rzpOrderId
    ]);
    $order = $stmt->fetch();

    if (!$order) {
        Database::rollBack();
        jsonResponse(['error' => 'Order record not found in system.'], 404);
    }

    $orderId = (string)$order['id'];

    // 3. IDEMPOTENCY CHECK: Check if already verified and paid
    $currentPaymentStatus = (string)($order['payment_status'] ?? '');
    $existingPaymentId = (string)($order['razorpay_payment_id'] ?? '');

    if (str_contains(strtolower($currentPaymentStatus), 'paid') || $existingPaymentId === $rzpPaymentId) {
        Database::rollBack();
        clearCart();
        jsonResponse([
            'success' => true,
            'message' => 'Payment already verified for this order.',
            'idempotent' => true
        ]);
    }

    // 4. Update Order Status in MySQL
    $upOrder = $db->prepare("
        UPDATE `orders` 
        SET 
            `payment_status` = 'Paid (Verified)',
            `razorpay_payment_id` = :payment_id,
            `order_status` = 'Confirmed',
            `updated_at` = NOW()
        WHERE `id` = :id
    ");
    $upOrder->execute([
        ':payment_id' => $rzpPaymentId,
        ':id' => $orderId
    ]);

    // 5. DEDUCT INVENTORY STOCK STRICTLY AFTER VERIFIED PAYMENT
    // Fetch items from order_items table or session
    $itemsStmt = $db->prepare("SELECT product_id, quantity FROM `order_items` WHERE order_id = :order_id");
    $itemsStmt->execute([':order_id' => $orderId]);
    $orderItems = $itemsStmt->fetchAll();

    if (!empty($orderItems)) {
        foreach ($orderItems as $item) {
            if (!empty($item['product_id'])) {
                adjustStock((string)$item['product_id'], -(int)$item['quantity']);
            }
        }
    } else {
        // Fallback to session items if order_items was somehow empty
        $pendingItems = $_SESSION['pending_order_' . $orderId] ?? getCart();
        if (!empty($pendingItems) && is_array($pendingItems)) {
            foreach ($pendingItems as $item) {
                if (isset($item['id'], $item['quantity'])) {
                    adjustStock((string)$item['id'], -(int)$item['quantity']);
                }
            }
        }
    }

    Database::commit();

    // Clean up session pending records
    unset($_SESSION['pending_order_' . $orderId], $_SESSION['pending_rzp_' . $rzpOrderId]);
    clearCart();

    jsonResponse([
        'success' => true,
        'message' => 'Payment verified and inventory synchronized successfully.'
    ]);

} catch (Throwable $e) {
    Database::rollBack();
    error_log("Payment verification error: " . $e->getMessage());
    jsonResponse(['error' => 'Error verifying payment: ' . $e->getMessage()], 500);
}
