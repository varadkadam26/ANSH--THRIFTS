<?php
/**
 * ANSH THRIFTS — API: Create Order
 * Integrates Razorpay + MySQL/MariaDB database with ACID transactions.
 * Enforces atomic stock pre-checks and prevents negative inventory.
 * Stock deduction occurs:
 * - Immediately inside database transaction for Cash on Delivery (COD)
 * - ONLY after verified signature in verify_payment.php for Razorpay.
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

$db = getDb();

try {
    Database::beginTransaction();

    // 1. Pre-check stock for all cart items with FOR UPDATE to prevent race conditions
    foreach ($cart as $item) {
        $pid = (string)$item['id'];
        $qtyRequested = (int)($item['quantity'] ?? 1);

        $stmt = $db->prepare("SELECT available_quantity FROM `inventory` WHERE product_id = :id FOR UPDATE");
        $stmt->execute([':id' => $pid]);
        $inv = $stmt->fetch();

        if ($inv) {
            $available = (int)$inv['available_quantity'];
            if ($available < $qtyRequested) {
                Database::rollBack();
                jsonResponse([
                    'error' => "Sorry, '{$item['name']}' has only {$available} item(s) available in stock."
                ], 400);
            }
        }
    }

    $totalAmount = getCartTotal();
    $amountInPaise = (int)round($totalAmount * 100);
    $orderId = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
    $razorpayOrderId = '';

    // 2. Razorpay Order Creation if chosen
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
        $rzpRes = json_decode((string)curl_exec($ch), true);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400 || !isset($rzpRes['id'])) {
            Database::rollBack();
            jsonResponse([
                'error' => 'Razorpay order creation failed: ' . ($rzpRes['error']['description'] ?? 'Check Razorpay credentials.')
            ], 500);
        }
        $razorpayOrderId = $rzpRes['id'];
    }

    // 3. Customer record: Upsert or fetch
    $custStmt = $db->prepare("SELECT id, total_orders, total_spent FROM `customers` WHERE email = :email LIMIT 1");
    $custStmt->execute([':email' => $email]);
    $existingCust = $custStmt->fetch();

    $customerDbId = null;
    if ($existingCust) {
        $customerDbId = (int)$existingCust['id'];
        $upCust = $db->prepare("
            UPDATE `customers`
            SET full_name = :name, phone = :phone, total_orders = total_orders + 1, total_spent = total_spent + :amount, last_active = NOW()
            WHERE id = :id
        ");
        $upCust->execute([
            ':name' => $customerName,
            ':phone' => $phone,
            ':amount' => $totalAmount,
            ':id' => $customerDbId
        ]);
    } else {
        $newCustId = 'CUST-' . rand(1000, 9999);
        $insCust = $db->prepare("
            INSERT INTO `customers` (`customer_id`, `full_name`, `email`, `phone`, `total_orders`, `total_spent`, `first_order_date`, `last_active`)
            VALUES (:cid, :name, :email, :phone, 1, :amount, NOW(), NOW())
        ");
        $insCust->execute([
            ':cid' => $newCustId,
            ':name' => $customerName,
            ':email' => $email,
            ':phone' => $phone,
            ':amount' => $totalAmount
        ]);
        $customerDbId = (int)$db->lastInsertId();
    }

    // 4. Insert Order
    $paymentStatus = ($paymentMethod === 'COD' ? 'Pending (COD)' : 'Awaiting Payment');
    $insOrder = $db->prepare("
        INSERT INTO `orders` (
            `id`, `customer_id`, `customer_name`, `email`, `phone`, `total_amount`, 
            `payment_method`, `payment_status`, `razorpay_order_id`, `razorpay_payment_id`, 
            `shipping_address`, `order_status`, `created_at`
        ) VALUES (
            :id, :customer_id, :customer_name, :email, :phone, :total_amount,
            :payment_method, :payment_status, :razorpay_order_id, '',
            :shipping_address, 'Placed', NOW()
        )
    ");
    $insOrder->execute([
        ':id' => $orderId,
        ':customer_id' => $customerDbId,
        ':customer_name' => $customerName,
        ':email' => $email,
        ':phone' => $phone,
        ':total_amount' => $totalAmount,
        ':payment_method' => $paymentMethod,
        ':payment_status' => $paymentStatus,
        ':razorpay_order_id' => $razorpayOrderId ?: null,
        ':shipping_address' => $address,
    ]);

    // 5. Insert Order Items
    $insItem = $db->prepare("
        INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `price`, `quantity`, `created_at`)
        VALUES (:order_id, :product_id, :product_name, :price, :quantity, NOW())
    ");
    $cartSummary = [];
    foreach ($cart as $ci) {
        $insItem->execute([
            ':order_id' => $orderId,
            ':product_id' => (string)$ci['id'],
            ':product_name' => (string)$ci['name'],
            ':price' => (float)$ci['price'],
            ':quantity' => (int)$ci['quantity'],
        ]);
        $cartSummary[] = "{$ci['name']} (Qty: {$ci['quantity']}, ₹{$ci['price']})";
    }

    // 6. Insert Delivery Tracking record
    $deliveryId = 'DEL-' . substr($orderId, 4);
    $insDel = $db->prepare("
        INSERT INTO `deliveries` (
            `id`, `order_id`, `recipient_name`, `recipient_phone`, `shipping_address`,
            `carrier`, `tracking_number`, `status`, `estimated_delivery`, `notes`, `created_at`
        ) VALUES (
            :id, :order_id, :recipient_name, :recipient_phone, :shipping_address,
            'Standard Post / Surface Logistics', :tracking_number, 'Processing', :est_delivery, :notes, NOW()
        )
    ");
    $insDel->execute([
        ':id' => $deliveryId,
        ':order_id' => $orderId,
        ':recipient_name' => $customerName,
        ':recipient_phone' => $phone,
        ':shipping_address' => $address,
        ':tracking_number' => 'TRK-' . strtoupper(bin2hex(random_bytes(4))),
        ':est_delivery' => date('Y-m-d', strtotime('+5 days')),
        ':notes' => implode('; ', $cartSummary),
    ]);

    // 7. Stock Deduction Logic:
    // For COD: Order is finalized now -> deduct stock inside transaction and clear cart
    // For Razorpay: Stock is deducted strictly in verify_payment.php upon verified HMAC signature
    if ($paymentMethod === 'COD') {
        foreach ($cart as $item) {
            $deducted = adjustStock((string)$item['id'], -(int)$item['quantity']);
            if (!$deducted) {
                Database::rollBack();
                jsonResponse(['error' => "Stock deduction failed for {$item['name']}."], 400);
            }
        }
        clearCart();
    } else {
        // Store the items in session associated with this order
        $_SESSION['pending_order_' . $orderId] = $cart;
        $_SESSION['pending_rzp_' . $razorpayOrderId] = $orderId;
    }

    Database::commit();

    jsonResponse([
        'success' => true,
        'order_id' => $orderId,
        'razorpay_order_id' => $razorpayOrderId,
        'amount' => $amountInPaise,
        'currency' => 'INR',
        'key_id' => RAZORPAY_KEY_ID,
    ]);

} catch (Throwable $e) {
    Database::rollBack();
    error_log("Order creation error: " . $e->getMessage());
    jsonResponse(['error' => 'Order processing failed: ' . $e->getMessage()], 500);
}
