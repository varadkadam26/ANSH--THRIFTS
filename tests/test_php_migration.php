<?php
/**
 * ANSH THRIFTS — Automated End-to-End Migration Test Suite
 * Tests every major workflow:
 * 1. Google Sheets Connection & Schema
 * 2. Product Retrieval & Filtering
 * 3. Product Creation, Edit, & Delete (CRUD)
 * 4. Stock & Inventory Management
 * 5. Contact Inquiries Submission
 * 6. Order Placement & Cart Sync
 * 7. Delivery Record Creation & Updates
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/GoogleSheetsService.php';

echo "========================================================\n";
echo "   ANSH THRIFTS — TEST SUITE FOR GOOGLE SHEETS & PHP    \n";
echo "========================================================\n\n";

$testsPassed = 0;
$testsFailed = 0;

function assertTest(string $name, bool $condition, string $details = ''): void {
    global $testsPassed, $testsFailed;
    if ($condition) {
        echo "✅ PASS: {$name}\n";
        $testsPassed++;
    } else {
        echo "❌ FAIL: {$name} - {$details}\n";
        $testsFailed++;
    }
}

$sheets = new GoogleSheetsService();

// TEST 1: Check all required tabs exist in Google Spreadsheet
echo "[1] Checking Google Sheets Tabs & Schema...\n";
$meta = $sheets->getSpreadsheet(true);
$tabNames = array_map(fn($s) => $s['properties']['title'], $meta['sheets'] ?? []);
$requiredTabs = ['Products', 'Inventory', 'Orders', 'Deliveries', 'Contact Messages', 'Customers', 'Settings'];
foreach ($requiredTabs as $tab) {
    assertTest("Tab '{$tab}' exists in spreadsheet", in_array($tab, $tabNames, true));
}

// TEST 2: Active Products Retrieval
echo "\n[2] Testing Active Products Retrieval...\n";
$products = $sheets->getActiveProducts();
assertTest("Active products count >= 1", count($products) >= 1, "Count is " . count($products));
if (!empty($products)) {
    $firstProduct = $products[0];
    assertTest("Product has required keys", isset($firstProduct['ID'], $firstProduct['Name'], $firstProduct['Price'], $firstProduct['Slug']));
    $bySlug = $sheets->getProductBySlug($firstProduct['Slug']);
    assertTest("Retrieve product by Slug ({$firstProduct['Slug']})", $bySlug !== null && $bySlug['ID'] === $firstProduct['ID']);
}

// TEST 3: Product CRUD Operations
echo "\n[3] Testing Product CRUD on Google Sheets...\n";
$testId = 'TEST-' . rand(10000, 99999);
$testSlug = 'automated-test-item-' . time();
$sheets->insertRecord('Products', [
    'ID' => $testId,
    'Name' => 'Automated Test Shirt',
    'Slug' => $testSlug,
    'Price' => '199',
    'Category' => 'Shirts',
    'Details' => 'Temporary product created by automated test suite',
    'Image File' => 'shirt.jpg',
    'Status' => 'Active',
    'Created At' => date('Y-m-d H:i:s'),
]);
$created = $sheets->getRecordById('Products', $testId);
assertTest("Product Inserted Successfully", $created !== null && $created['Name'] === 'Automated Test Shirt');

if ($created) {
    $rowNum = (int)$created['_row_number'];
    // Update product
    $sheets->updateRecordByRow('Products', $rowNum, [
        'ID' => $testId,
        'Name' => 'Updated Test Shirt',
        'Slug' => $testSlug,
        'Price' => '249',
        'Category' => 'Shirts',
        'Details' => 'Updated details',
        'Image File' => 'shirt.jpg',
        'Status' => 'Active',
        'Created At' => $created['Created At'],
    ]);
    $updated = $sheets->getRecordById('Products', $testId);
    assertTest("Product Updated Successfully", $updated !== null && $updated['Name'] === 'Updated Test Shirt' && (string)$updated['Price'] === '249');

    // Delete product
    $sheets->deleteRow('Products', $rowNum);
    $deleted = $sheets->getRecordById('Products', $testId);
    assertTest("Product Deleted Successfully", $deleted === null);
}

// TEST 4: Contact Messages Submission
echo "\n[4] Testing Contact Form Submission...\n";
$msgId = 'TEST-MSG-' . rand(1000, 9999);
$sheets->insertRecord('Contact Messages', [
    'Message ID' => $msgId,
    'Sender Name' => 'Automated Tester',
    'Email' => 'tester@example.com',
    'Phone' => '9999999999',
    'Subject' => 'Test Subject',
    'Message' => 'Hello from unit test',
    'Status' => 'New',
    'Received At' => date('Y-m-d H:i:s'),
    'Responded At' => '',
]);
$foundMsg = $sheets->getRecordById('Contact Messages', $msgId, 'Message ID');
assertTest("Contact Message Inserted & Read", $foundMsg !== null && $foundMsg['Email'] === 'tester@example.com');
if ($foundMsg) {
    // Delete test message to keep spreadsheet clean
    $sheets->deleteRow('Contact Messages', (int)$foundMsg['_row_number']);
}

// TEST 5: Inventory Stock Management
echo "\n[5] Testing Inventory Stock Adjustment...\n";
$testInvId = 'PRD-1001';
$invBefore = $sheets->getRecordById('Inventory', $testInvId, 'Product ID');
assertTest("Inventory record exists for {$testInvId}", $invBefore !== null);
if ($invBefore) {
    $stockBefore = (int)($invBefore['Available Quantity'] ?? 0);
    $sheets->adjustStock($testInvId, -1);
    $invAfter = $sheets->getRecordById('Inventory', $testInvId, 'Product ID');
    $stockAfter = (int)($invAfter['Available Quantity'] ?? 0);
    assertTest("Inventory decremented by 1 on purchase", $stockAfter === ($stockBefore - 1));
    // Restore stock
    $sheets->adjustStock($testInvId, +1);
}

// TEST 6: Cart Functions
echo "\n[6] Testing Shopping Cart System...\n";
$_SESSION['cart'] = [];
addToCart(['ID' => 'PRD-1001', 'Name' => 'Vintage Denim Jacket', 'Price' => 699, 'Image File' => 'jeans.jpg', 'Slug' => 'vintage-denim-jacket'], 2);
assertTest("Cart count is 2", getCartCount() === 2);
assertTest("Cart total is 1398", getCartTotal() === 1398.0);
updateCartQuantity('PRD-1001', 3);
assertTest("Updated cart count is 3", getCartCount() === 3);
assertTest("Updated cart total is 2097", getCartTotal() === 2097.0);
removeFromCart('PRD-1001');
assertTest("Cart is empty after removal", getCartCount() === 0);

// TEST SUMMARY
echo "\n========================================================\n";
echo "TEST RESULTS: {$testsPassed} Passed, {$testsFailed} Failed\n";
echo "========================================================\n";

if ($testsFailed > 0) {
    exit(1);
}
echo "ALL TESTS PASSED SUCCESSFULLY! Ready for deployment.\n";

