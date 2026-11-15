<?php
/**
 * ANSH THRIFTS — API: Create Order
 * Integrates Razorpay + Google Sheets sync
 * Enforces stock pre-checks and prevents negative inventory.
 * Stock deduction occurs:
 * - Immediately for Cash on Delivery (COD)
 * - ONLY after verified signature in verify_payment.php for Razorpay.
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

$cart = getCart();
if (empty($cart)) {
    jsonResponse(['error' => 'Your cart is empty. Please add items before checking out.'], 400);
}

$customerName = trim($input['customer_name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$address = trim($input['address'] ?? '');
$paymentMethod = trim($input['payment_method'] ?? 'Razorpay');

if ($customerName === '' || $email === '' || $phone === '' || $address === '') {
    jsonResponse(['error' => 'Please provide complete name, email, phone, and delivery address.'], 400);
}

try {
    $sheets = new GoogleSheetsService();

    // 1. Pre-check stock for all cart items to prevent overselling
    $inventoryRecords = $sheets->getAllRecords('Inventory');
    $invMap = [];
    foreach ($inventoryRecords as $inv) {
        $invMap[(string)$inv['Product ID']] = $inv;
    }

    foreach ($cart as $item) {
        $pid = (string)$item['id'];
        $qtyRequested = (int)($item['quantity'] ?? 1);
        if (isset($invMap[$pid])) {
            $available = (int)($invMap[$pid]['Available Quantity'] ?? 0);
            if ($available < $qtyRequested) {
                jsonResponse([
                    'error' => "Sorry, '{$item['name']}' has only {$available} item(s) available in stock."
                ], 400);
            }
        }
    }

    $totalAmount = getCartTotal();
    $amountInPaise = (int)round($totalAmount * 100);
    $orderId = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
    $now = date('Y-m-d H:i:s');
    $razorpayOrderId = '';

    // 2. Razorpay Order Creation
    if ($paymentMethod === 'Razorpay') {
        $ch = curl_init('https://api.razorpay.com/v1/orders');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, RAZORPAY_KEY_ID . ':' . RAZORPAY_KEY_SECRET);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            'amount' => $amountInPaise,
            'currency' => 'INR',
            'receipt' => $orderId,
            'payment_capture' => 1
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        $rzpRes = json_decode(curl_exec($ch), true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400 || !isset($rzpRes['id'])) {
            jsonResponse([
                'error' => 'Razorpay order creation failed: ' . ($rzpRes['error']['description'] ?? 'Check Razorpay credentials.')
            ], 500);
        }
        $razorpayOrderId = $rzpRes['id'];
    }

    // 3. Register Order in Google Sheets Orders tab
    // Format cart items summary for easy tracking
    $cartSummary = [];
    foreach ($cart as $ci) {
        $cartSummary[] = "{$ci['name']} (Qty: {$ci['quantity']}, ₹{$ci['price']})";
    }
    $cartItemsNotes = implode('; ', $cartSummary);

    $orderData = [
        'Order ID' => $orderId,
        'Customer Name' => $customerName,
        'Email' => $email,
        'Phone' => $phone,
        'Total Amount' => $totalAmount,
        'Payment Method' => $paymentMethod,
        'Payment Status' => ($paymentMethod === 'COD' ? 'Pending (COD)' : 'Awaiting Payment'),
        'Razorpay Order ID' => $razorpayOrderId,
        'Razorpay Payment ID' => '',
        'Shipping Address' => $address,
        'Order Status' => 'Placed',
        'Created At' => $now,
    ];
    $sheets->insertRecord('Orders', $orderData);

    // 4. Create Delivery Tracking record
    $deliveryId = 'DEL-' . substr($orderId, 4);
    $sheets->insertRecord('Deliveries', [
        'Delivery ID' => $deliveryId,
        'Order ID' => $orderId,
        'Recipient Name' => $customerName,
        'Recipient Phone' => $phone,
        'Shipping Address' => $address,
        'Carrier' => 'Standard Post / Surface Logistics',
        'Tracking Number' => 'TRK-' . strtoupper(bin2hex(random_bytes(4))),
        'Status' => 'Processing',
        'Estimated Delivery' => date('Y-m-d', strtotime('+5 days')),
        'Dispatched At' => '',
        'Delivered At' => '',
        'Notes' => $cartItemsNotes,
    ]);

    // 5. Update or Create Customer Record
    $customerRecord = $sheets->getRecordByField('Customers', 'Email', $email);
    if ($customerRecord) {
        $rowNum = (int)$customerRecord['_row_number'];
        $newCount = (int)($customerRecord['Total Orders'] ?? 0) + 1;
        $newSpent = (float)($customerRecord['Total Spent'] ?? 0) + $totalAmount;
        $updated = [
            'Customer ID' => $customerRecord['Customer ID'],
            'Full Name' => $customerName,
            'Email' => $email,
            'Phone' => $phone,
            'Total Orders' => $newCount,
            'Total Spent' => $newSpent,
            'First Order Date' => $customerRecord['First Order Date'],
            'Last Active' => $now,
        ];
        $sheets->updateRecordByRow('Customers', $rowNum, $updated);
    } else {
        $sheets->insertRecord('Customers', [
            'Customer ID' => 'CUST-' . rand(1000, 9999),
            'Full Name' => $customerName,
            'Email' => $email,
            'Phone' => $phone,
            'Total Orders' => 1,
            'Total Spent' => $totalAmount,
            'First Order Date' => $now,
            'Last Active' => $now,
        ]);
    }

    // 6. Stock Deduction Logic:
    // For COD: Order is placed immediately -> deduct stock now and clear cart
    // For Razorpay: Stock is deducted ONLY AFTER verified signature in verify_payment.php
    if ($paymentMethod === 'COD') {
        foreach ($cart as $item) {
            $sheets->adjustStock((string)$item['id'], -(int)$item['quantity']);
        }
        clearCart();
    } else {
        // Store the items in session associated with this order so verify_payment can deduct exact quantities
        $_SESSION['pending_order_' . $orderId] = $cart;
        $_SESSION['pending_rzp_' . $razorpayOrderId] = $orderId;
    }

    jsonResponse([
        'success' => true,
        'order_id' => $orderId,
        'razorpay_order_id' => $razorpayOrderId,
        'amount' => $amountInPaise,
        'currency' => 'INR',
        'key_id' => RAZORPAY_KEY_ID,
    ]);

} catch (Throwable $e) {
    jsonResponse(['error' => 'Order processing failed: ' . $e->getMessage()], 500);
}
