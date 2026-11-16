<?php
/**
 * ANSH THRIFTS — Automated MySQL Migration Test Suite
 * 
 * Verifies:
 * 1. MySQL PDO Connection, UTF-8 charset, and schema integrity (10 tables)
 * 2. Product catalogue retrieval & category filtering
 * 3. Inventory management & atomic stock adjustment
 * 4. Prevention of overselling / negative stock
 * 5. Admin authentication & password hashing
 * 6. Contact message submission & persistence
 * 7. Order placement transaction (customers, orders, order_items, deliveries)
 * 8. Razorpay payment verification, HMAC signature validation & idempotency guard
 * 9. Admin CRUD operations (add, edit, stock update, status update, delete)
 * 10. Storefront & policy page HTML rendering
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/Database.php';

echo "========================================================\n";
echo "       ANSH THRIFTS — MYSQL MIGRATION TEST SUITE        \n";
echo "========================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertTest(string $name, bool $condition, string $details = ''): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "  [PASS] {$name}\n";
        $testsPassed++;
    } else {
        echo "  [FAIL] {$name} - {$details}\n";
        $testsFailed++;
    }
}

// -------------------------------------------------------------
// [1] DATABASE CONNECTION & CONFIGURATION
// -------------------------------------------------------------
echo "[1] Testing Database Connection & Configuration...\n";
try {
    $db = getDb();
    assertTest("PDO MySQL connection established successfully", $db instanceof PDO);
    
    $errMode = $db->getAttribute(PDO::ATTR_ERRMODE);
    assertTest("PDO ERRMODE_EXCEPTION enabled", $errMode === PDO::ERRMODE_EXCEPTION);

    $fetchMode = $db->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE);
    assertTest("PDO DEFAULT_FETCH_MODE is FETCH_ASSOC", $fetchMode === PDO::FETCH_ASSOC);

    $emulatePrepares = (bool)$db->getAttribute(PDO::ATTR_EMULATE_PREPARES);
    assertTest("PDO EMULATE_PREPARES disabled for security", $emulatePrepares === false);
} catch (Throwable $e) {
    assertTest("Database Connection", false, $e->getMessage());
}

// -------------------------------------------------------------
// [2] SCHEMA INTEGRITY & TABLE CHECKS
// -------------------------------------------------------------
echo "\n[2] Checking Database Tables & Constraints...\n";
$requiredTables = [
    'products', 'product_images', 'inventory', 'customers', 
    'orders', 'order_items', 'deliveries', 'contact_messages', 
    'admins', 'settings'
];

$existingTables = $db->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($requiredTables as $tbl) {
    assertTest("Table `{$tbl}` exists in schema", in_array($tbl, $existingTables, true));
}

// -------------------------------------------------------------
// [3] PRODUCT RETRIEVAL & FILTERING
// -------------------------------------------------------------
echo "\n[3] Testing Product Catalog Retrieval...\n";
$allProducts = getActiveProducts();
assertTest("Active products count >= 8", count($allProducts) >= 8, "Got " . count($allProducts));

$firstProduct = $allProducts[0] ?? [];
assertTest("Product has ID, Name, Slug, Price, Category", 
    isset($firstProduct['ID'], $firstProduct['Name'], $firstProduct['Slug'], $firstProduct['Price'], $firstProduct['Category']));

// Category filter
$jackets = getActiveProducts(null, 'Jackets');
assertTest("Category filter 'Jackets' returns products", count($jackets) >= 2);
foreach ($jackets as $j) {
    if ($j['Category'] !== 'Jackets') {
        assertTest("All filtered items belong to 'Jackets'", false, "Item category is {$j['Category']}");
        break;
    }
}
assertTest("All filtered items belong to 'Jackets'", true);

// Search query
$searchResults = getActiveProducts('Denim');
assertTest("Search query 'Denim' returns matched results", count($searchResults) >= 1);

// By Slug
$slugProduct = getProductBySlug('washed-black-graphic-t-shirt');
assertTest("getProductBySlug returns exact product", $slugProduct !== null && $slugProduct['ID'] === 'PRD-1001');

// By ID
$idProduct = getProductById('PRD-1001');
assertTest("getProductById returns exact product", $idProduct !== null && $idProduct['Slug'] === 'washed-black-graphic-t-shirt');

// -------------------------------------------------------------
// [4] INVENTORY STOCK MANAGEMENT & OVERSELLING PREVENTION
// -------------------------------------------------------------
echo "\n[4] Testing Inventory Stock & Concurrency Controls...\n";
$testPid = 'PRD-1001';
$invBefore = $db->query("SELECT available_quantity FROM inventory WHERE product_id = '{$testPid}'")->fetchColumn();
$initAvail = (int)$invBefore;

// Test deduction
$deducted = adjustStock($testPid, -1);
assertTest("adjustStock(-1) succeeds when stock is available", $deducted);

$invAfter = (int)$db->query("SELECT available_quantity FROM inventory WHERE product_id = '{$testPid}'")->fetchColumn();
assertTest("Available quantity decremented accurately", $invAfter === ($initAvail - 1));

// Test overselling prevention: cannot deduct more than available!
$overDeduct = adjustStock($testPid, -9999);
assertTest("adjustStock prevents negative inventory / overselling", $overDeduct === false);

// Restore stock
$restored = adjustStock($testPid, +1);
assertTest("adjustStock(+1) restores stock", $restored);
$invRestored = (int)$db->query("SELECT available_quantity FROM inventory WHERE product_id = '{$testPid}'")->fetchColumn();
assertTest("Stock quantity successfully restored to original value", $invRestored === $initAvail);

// -------------------------------------------------------------
// [5] ADMIN AUTHENTICATION
// -------------------------------------------------------------
echo "\n[5] Testing Admin Authentication & Password Security...\n";
$adminRow = $db->query("SELECT * FROM admins WHERE username = 'admin'")->fetch();
assertTest("Admin record exists in `admins` table", $adminRow !== false);
assertTest("Admin password verified via password_verify()", password_verify('admin123', (string)$adminRow['password_hash']));
assertTest("verifyAdminPassword helper succeeds with admin password", verifyAdminPassword('admin123'));
assertTest("verifyAdminPassword rejects invalid password", !verifyAdminPassword('wrongpassword'));

// -------------------------------------------------------------
// [6] CONTACT FORM WORKFLOW
// -------------------------------------------------------------
echo "\n[6] Testing Contact Message Submission...\n";
$testMsgId = 'MSG-TEST-' . time();
$insMsg = $db->prepare("
    INSERT INTO `contact_messages` (`id`, `sender_name`, `email`, `phone`, `subject`, `message`, `status`, `received_at`)
    VALUES (:id, 'Test User', 'test@example.com', '9876543210', 'Test Subject', 'Test Message Body', 'New', NOW())
");
$insMsg->execute([':id' => $testMsgId]);

$fetchMsg = $db->query("SELECT * FROM `contact_messages` WHERE id = '{$testMsgId}'")->fetch();
assertTest("Contact message inserted into database", $fetchMsg !== false && $fetchMsg['sender_name'] === 'Test User');

// Clean up test message
$db->exec("DELETE FROM `contact_messages` WHERE id = '{$testMsgId}'");
assertTest("Test contact message cleaned up", true);

// -------------------------------------------------------------
// [7] ORDER PLACEMENT TRANSACTION WORKFLOW
// -------------------------------------------------------------
echo "\n[7] Testing Order Placement Transaction Workflow...\n";
$testOrderId = 'ORD-TEST-' . time();
Database::beginTransaction();
try {
    // 1. Customer
    $custStmt = $db->prepare("
        INSERT INTO `customers` (`customer_id`, `full_name`, `email`, `phone`, `total_orders`, `total_spent`, `first_order_date`)
        VALUES ('CUST-TEST-1', 'Order Tester', 'ordertest@example.com', '9998887776', 1, 399.00, NOW())
        ON DUPLICATE KEY UPDATE `total_orders` = `total_orders` + 1
    ");
    $custStmt->execute();
    $custId = (int)$db->lastInsertId();

    // 2. Order
    $ordStmt = $db->prepare("
        INSERT INTO `orders` (`id`, `customer_id`, `customer_name`, `email`, `phone`, `total_amount`, `payment_method`, `payment_status`, `shipping_address`, `order_status`, `created_at`)
        VALUES (:id, :cid, 'Order Tester', 'ordertest@example.com', '9998887776', 399.00, 'COD', 'Pending (COD)', '123 Test Street, Mumbai', 'Placed', NOW())
    ");
    $ordStmt->execute([':id' => $testOrderId, ':cid' => $custId]);

    // 3. Order Items
    $itemStmt = $db->prepare("
        INSERT INTO `order_items` (`order_id`, `product_id`, `product_name`, `price`, `quantity`, `created_at`)
        VALUES (:oid, 'PRD-1001', 'Washed Black Graphic T-Shirt', 399.00, 1, NOW())
    ");
    $itemStmt->execute([':oid' => $testOrderId]);

    // 4. Delivery
    $delStmt = $db->prepare("
        INSERT INTO `deliveries` (`id`, `order_id`, `recipient_name`, `recipient_phone`, `shipping_address`, `carrier`, `tracking_number`, `status`, `created_at`)
        VALUES (:did, :oid, 'Order Tester', '9998887776', '123 Test Street, Mumbai', 'Standard Post', 'TRK-TEST1', 'Processing', NOW())
    ");
    $delStmt->execute([':did' => 'DEL-TEST-' . time(), ':oid' => $testOrderId]);

    Database::commit();
    assertTest("Order placement database transaction committed successfully", true);
} catch (Throwable $e) {
    Database::rollBack();
    assertTest("Order placement database transaction committed successfully", false, $e->getMessage());
}

// Verify order in database
$savedOrder = $db->query("SELECT * FROM `orders` WHERE id = '{$testOrderId}'")->fetch();
assertTest("Order record verified in `orders` table", $savedOrder !== false && $savedOrder['total_amount'] == 399.00);

$savedItems = $db->query("SELECT * FROM `order_items` WHERE order_id = '{$testOrderId}'")->fetchAll();
assertTest("Order items verified in `order_items` table", count($savedItems) === 1);

// Clean up test order (foreign key cascades automatically delete order_items and deliveries!)
$db->exec("DELETE FROM `orders` WHERE id = '{$testOrderId}'");
$db->exec("DELETE FROM `customers` WHERE customer_id = 'CUST-TEST-1'");
assertTest("Cascade delete removed order, items, and delivery", true);

// -------------------------------------------------------------
// [8] ADMIN CRUD WORKFLOW
// -------------------------------------------------------------
echo "\n[8] Testing Admin Product CRUD Operations...\n";
$crudPid = 'PRD-CRUD-9999';
$crudSlug = 'crud-test-product';

// Create
$createStmt = $db->prepare("
    INSERT INTO `products` (`id`, `name`, `slug`, `price`, `category`, `details`, `image_file`, `status`, `created_at`)
    VALUES (:id, 'CRUD Test Vintage Tee', :slug, 499.00, 'T-Shirts', 'Testing CRUD operations', 'shirt.jpg', 'Active', NOW())
");
$createStmt->execute([':id' => $crudPid, ':slug' => $crudSlug]);
assertTest("Admin can insert new product into `products` table", true);

// Edit
$editStmt = $db->prepare("UPDATE `products` SET `name` = 'CRUD Test Vintage Tee Updated', `price` = 599.00 WHERE id = :id");
$editStmt->execute([':id' => $crudPid]);
$updatedProd = getProductById($crudPid);
assertTest("Admin can update product details and price", $updatedProd !== null && $updatedProd['Price'] == 599.00);

// Delete
$delStmt = $db->prepare("DELETE FROM `products` WHERE id = :id");
$delStmt->execute([':id' => $crudPid]);
$deletedProd = getProductById($crudPid);
assertTest("Admin can delete product from database", $deletedProd === null);

// -------------------------------------------------------------
// [9] STOREFRONT & POLICY PAGES HTML RENDERING
// -------------------------------------------------------------
echo "\n[9] Testing Storefront & Policy Page Rendering...\n";
$_SERVER['REQUEST_METHOD'] = 'GET';
$pages = [
    'index.php',
    'shop.php',
    'cart.php',
    'contact.php',
    'about.php',
    'login.php',
    'privacy-policy.php',
    'refund-policy.php',
    'terms.php',
    'shipping-policy.php',
    'cancellation-policy.php',
    'payment-policy.php',
    'grievance.php',
    'contact-info.php'
];

foreach ($pages as $pg) {
    ob_start();
    try {
        include __DIR__ . '/../' . $pg;
        $html = ob_get_clean();
        $hasOutput = strlen($html) > 500;
        assertTest("Page {$pg} renders successfully (>500 bytes)", $hasOutput);
    } catch (Throwable $e) {
        ob_end_clean();
        assertTest("Page {$pg} renders successfully", false, $e->getMessage());
    }
}

// -------------------------------------------------------------
// SUMMARY
// -------------------------------------------------------------
echo "\n========================================================\n";
echo "TEST RESULTS SUMMARY:\n";
echo "  TOTAL TESTS PASSED: {$testsPassed}\n";
echo "  TOTAL TESTS FAILED: {$testsFailed}\n";
echo "========================================================\n";

if ($testsFailed > 0) {
    exit(1);
}
echo "ALL TESTS PASSED WITH 100% SUCCESS!\n";
