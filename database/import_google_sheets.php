<?php
/**
 * ANSH THRIFTS — One-Time Google Sheets to MySQL Import Utility
 * 
 * Imports product catalog, inventory records, settings, and contact messages
 * into MySQL/MariaDB using PDO prepared statements and transactions.
 * 
 * Usage:
 *   php database/import_google_sheets.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';

echo "=====================================================\n";
echo "ANSH THRIFTS — Google Sheets to MySQL Migration\n";
echo "=====================================================\n\n";

try {
    $pdo = Database::getInstance();
    echo "[OK] Connected to MySQL database successfully.\n";
} catch (Throwable $e) {
    echo "[ERROR] Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

$backupDir = __DIR__ . '/../_backup';

// 1. Products Migration
$productsFile = $backupDir . '/products.json';
if (file_exists($productsFile)) {
    $products = json_decode(file_get_contents($productsFile), true);
    if (is_array($products)) {
        echo "\nMigrating " . count($products) . " Products...\n";
        $stmt = $pdo->prepare("
            INSERT INTO `products` (`id`, `name`, `slug`, `price`, `category`, `details`, `image_file`, `status`, `created_at`)
            VALUES (:id, :name, :slug, :price, :category, :details, :image_file, :status, NOW())
            ON DUPLICATE KEY UPDATE
                `name` = VALUES(`name`),
                `slug` = VALUES(`slug`),
                `price` = VALUES(`price`),
                `category` = VALUES(`category`),
                `details` = VALUES(`details`),
                `image_file` = VALUES(`image_file`),
                `status` = VALUES(`status`)
        ");

        $imgStmt = $pdo->prepare("
            INSERT INTO `product_images` (`product_id`, `image_file`, `is_primary`, `sort_order`, `created_at`)
            VALUES (:product_id, :image_file, 1, 0, NOW())
            ON DUPLICATE KEY UPDATE `image_file` = VALUES(`image_file`)
        ");

        foreach ($products as $p) {
            $stmt->execute([
                ':id' => $p['ID'],
                ':name' => $p['Name'],
                ':slug' => $p['Slug'],
                ':price' => (float)$p['Price'],
                ':category' => $p['Category'] ?? 'General',
                ':details' => $p['Details'] ?? '',
                ':image_file' => $p['Image File'] ?? 'shirt.jpg',
                ':status' => $p['Status'] ?? 'Active',
            ]);

            $imgStmt->execute([
                ':product_id' => $p['ID'],
                ':image_file' => $p['Image File'] ?? 'shirt.jpg',
            ]);

            echo "  [+] Imported Product: {$p['ID']} - {$p['Name']}\n";
        }
    }
}

// 2. Inventory Migration
$inventoryFile = $backupDir . '/inventory.json';
if (file_exists($inventoryFile)) {
    $inventory = json_decode(file_get_contents($inventoryFile), true);
    if (is_array($inventory)) {
        echo "\nMigrating " . count($inventory) . " Inventory Records...\n";
        $stmt = $pdo->prepare("
            INSERT INTO `inventory` (`product_id`, `sku`, `stock_quantity`, `reserved_quantity`, `available_quantity`, `low_stock_alert`, `updated_at`)
            VALUES (:product_id, :sku, :stock_quantity, :reserved_quantity, :available_quantity, :low_stock_alert, NOW())
            ON DUPLICATE KEY UPDATE
                `sku` = VALUES(`sku`),
                `stock_quantity` = VALUES(`stock_quantity`),
                `reserved_quantity` = VALUES(`reserved_quantity`),
                `available_quantity` = VALUES(`available_quantity`),
                `low_stock_alert` = VALUES(`low_stock_alert`),
                `updated_at` = NOW()
        ");

        foreach ($inventory as $inv) {
            $stmt->execute([
                ':product_id' => $inv['Product ID'],
                ':sku' => $inv['SKU'] ?? ('SKU-' . $inv['Product ID']),
                ':stock_quantity' => (int)($inv['Stock Quantity'] ?? 1),
                ':reserved_quantity' => (int)($inv['Reserved Quantity'] ?? 0),
                ':available_quantity' => (int)($inv['Available Quantity'] ?? 1),
                ':low_stock_alert' => $inv['Low Stock Alert'] ?? 'Yes',
            ]);
            echo "  [+] Imported Inventory: {$inv['Product ID']} (Available: {$inv['Available Quantity']})\n";
        }
    }
}

// 3. Settings Migration
$settingsFile = $backupDir . '/settings.json';
if (file_exists($settingsFile)) {
    $settings = json_decode(file_get_contents($settingsFile), true);
    if (is_array($settings)) {
        echo "\nMigrating " . count($settings) . " Settings...\n";
        $stmt = $pdo->prepare("
            INSERT INTO `settings` (`setting_key`, `setting_value`, `description`, `updated_at`)
            VALUES (:setting_key, :setting_value, :description, NOW())
            ON DUPLICATE KEY UPDATE
                `setting_value` = VALUES(`setting_value`),
                `description` = VALUES(`description`),
                `updated_at` = NOW()
        ");

        foreach ($settings as $st) {
            $stmt->execute([
                ':setting_key' => $st['Key'],
                ':setting_value' => $st['Value'],
                ':description' => $st['Description'] ?? '',
            ]);
            echo "  [+] Imported Setting: {$st['Key']} = {$st['Value']}\n";
        }
    }
}

// 4. Contact Messages Migration
$messagesFile = $backupDir . '/contact_messages.json';
if (file_exists($messagesFile)) {
    $messages = json_decode(file_get_contents($messagesFile), true);
    if (is_array($messages)) {
        echo "\nMigrating " . count($messages) . " Contact Messages...\n";
        $stmt = $pdo->prepare("
            INSERT INTO `contact_messages` (`id`, `sender_name`, `email`, `phone`, `subject`, `message`, `status`, `received_at`)
            VALUES (:id, :sender_name, :email, :phone, :subject, :message, :status, :received_at)
            ON DUPLICATE KEY UPDATE
                `sender_name` = VALUES(`sender_name`),
                `email` = VALUES(`email`),
                `phone` = VALUES(`phone`),
                `subject` = VALUES(`subject`),
                `message` = VALUES(`message`),
                `status` = VALUES(`status`)
        ");

        foreach ($messages as $msg) {
            $stmt->execute([
                ':id' => $msg['Message ID'],
                ':sender_name' => $msg['Sender Name'],
                ':email' => $msg['Email'],
                ':phone' => $msg['Phone'] ?? '',
                ':subject' => $msg['Subject'] ?? 'General Inquiry',
                ':message' => $msg['Message'] ?? '',
                ':status' => $msg['Status'] ?? 'New',
                ':received_at' => $msg['Received At'] ?? date('Y-m-d H:i:s'),
            ]);
            echo "  [+] Imported Message: {$msg['Message ID']} from {$msg['Sender Name']}\n";
        }
    }
}

// Verify counts
echo "\n=====================================================\n";
echo "Migration Summary:\n";
$tables = ['products', 'product_images', 'inventory', 'settings', 'contact_messages', 'admins'];
foreach ($tables as $tbl) {
    $cnt = $pdo->query("SELECT count(*) FROM `{$tbl}`")->fetchColumn();
    echo "  - Table `{$tbl}`: {$cnt} records\n";
}
echo "=====================================================\n";
echo "MIGRATION COMPLETED SUCCESSFULLY!\n";

