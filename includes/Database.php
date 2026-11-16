<?php
/**
 * ANSH THRIFTS — Database Connection & Query Helper
 * 
 * Production-ready PDO wrapper supporting prepared statements,
 * database transactions, concurrency handling, and Hostinger compatibility.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

class Database {
    private static ?PDO $instance = null;

    /**
     * Get the singleton PDO instance
     */
    public static function getInstance(): PDO {
        if (self::$instance === null) {
            $host = defined('DB_HOST') && DB_HOST !== '' ? DB_HOST : (string)env('DB_HOST', 'localhost');
            $port = defined('DB_PORT') && DB_PORT !== 0 ? (int)DB_PORT : (int)env('DB_PORT', 3306);
            $dbname = defined('DB_DATABASE') && DB_DATABASE !== '' ? DB_DATABASE : (string)env('DB_DATABASE', '');
            $user = defined('DB_USERNAME') && DB_USERNAME !== '' ? DB_USERNAME : (string)env('DB_USERNAME', '');
            $pass = defined('DB_PASSWORD') ? DB_PASSWORD : (string)env('DB_PASSWORD', '');
            $charset = defined('DB_CHARSET') && DB_CHARSET !== '' ? DB_CHARSET : (string)env('DB_CHARSET', 'utf8mb4');

            if ($dbname === '' || $user === '') {
                throw new RuntimeException("Database configuration error: DB_DATABASE and DB_USERNAME are required.");
            }

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                error_log("Database connection error: " . $e->getMessage());
                throw new RuntimeException("Database connection failed. Please verify your database configuration.");
            }
        }

        return self::$instance;
    }

    /**
     * Reset the instance (useful for testing or reconnects)
     */
    public static function resetInstance(): void {
        self::$instance = null;
    }

    /**
     * Execute a prepared SELECT query and fetch all results
     */
    public static function fetchAll(string $sql, array $params = []): array {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Execute a prepared SELECT query and fetch a single record
     */
    public static function fetchOne(string $sql, array $params = []): ?array {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Execute an INSERT, UPDATE, or DELETE query and return affected row count
     */
    public static function execute(string $sql, array $params = []): int {
        $stmt = self::getInstance()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * Begin a database transaction
     */
    public static function beginTransaction(): bool {
        return self::getInstance()->beginTransaction();
    }

    /**
     * Commit a database transaction
     */
    public static function commit(): bool {
        return self::getInstance()->commit();
    }

    /**
     * Roll back a database transaction
     */
    public static function rollBack(): bool {
        if (self::getInstance()->inTransaction()) {
            return self::getInstance()->rollBack();
        }
        return false;
    }
}

/**
 * Global helper functions for common storefront & catalogue operations
 */

function getDb(): PDO {
    return Database::getInstance();
}

/**
 * Fetch active products with joined inventory and primary image
 */
function getActiveProducts(?string $search = null, ?string $category = null): array {
    $db = getDb();
    $sql = "
        SELECT 
            p.id AS `ID`,
            p.name AS `Name`,
            p.slug AS `Slug`,
            p.price AS `Price`,
            p.category AS `Category`,
            p.details AS `Details`,
            p.image_file AS `Image File`,
            p.status AS `Status`,
            p.created_at AS `Created At`,
            COALESCE(i.stock_quantity, 0) AS `Stock Quantity`,
            COALESCE(i.available_quantity, 0) AS `Available Quantity`,
            COALESCE(i.low_stock_alert, 'Yes') AS `Low Stock Alert`
        FROM `products` p
        LEFT JOIN `inventory` i ON p.id = i.product_id
        WHERE p.status = 'Active'
    ";

    $params = [];

    if ($search !== null && trim($search) !== '') {
        $sql .= " AND (p.name LIKE :s1 OR p.details LIKE :s2 OR p.category LIKE :s3)";
        $term = '%' . trim($search) . '%';
        $params[':s1'] = $term;
        $params[':s2'] = $term;
        $params[':s3'] = $term;
    }

    if ($category !== null && trim($category) !== '') {
        $sql .= " AND p.category = :category";
        $params[':category'] = trim($category);
    }

    $sql .= " ORDER BY p.created_at DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Fetch a single product by its URL slug
 */
function getProductBySlug(string $slug): ?array {
    $db = getDb();
    $sql = "
        SELECT 
            p.id AS `ID`,
            p.name AS `Name`,
            p.slug AS `Slug`,
            p.price AS `Price`,
            p.category AS `Category`,
            p.details AS `Details`,
            p.image_file AS `Image File`,
            p.status AS `Status`,
            p.created_at AS `Created At`,
            COALESCE(i.stock_quantity, 0) AS `Stock Quantity`,
            COALESCE(i.available_quantity, 0) AS `Available Quantity`,
            COALESCE(i.low_stock_alert, 'Yes') AS `Low Stock Alert`
        FROM `products` p
        LEFT JOIN `inventory` i ON p.id = i.product_id
        WHERE p.slug = :slug
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':slug' => $slug]);
    $res = $stmt->fetch();
    return $res !== false ? $res : null;
}

/**
 * Fetch a single product by its ID (e.g. PRD-1001)
 */
function getProductById(string $id): ?array {
    $db = getDb();
    $sql = "
        SELECT 
            p.id AS `ID`,
            p.name AS `Name`,
            p.slug AS `Slug`,
            p.price AS `Price`,
            p.category AS `Category`,
            p.details AS `Details`,
            p.image_file AS `Image File`,
            p.status AS `Status`,
            p.created_at AS `Created At`,
            COALESCE(i.stock_quantity, 0) AS `Stock Quantity`,
            COALESCE(i.available_quantity, 0) AS `Available Quantity`,
            COALESCE(i.low_stock_alert, 'Yes') AS `Low Stock Alert`
        FROM `products` p
        LEFT JOIN `inventory` i ON p.id = i.product_id
        WHERE p.id = :id
        LIMIT 1
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':id' => $id]);
    $res = $stmt->fetch();
    return $res !== false ? $res : null;
}

/**
 * Atomically adjust inventory stock.
 * If $quantityChange is negative (deduction), it enforces that stock does not go negative.
 */
function adjustStock(string $productId, int $quantityChange): bool {
    $db = getDb();

    if ($quantityChange < 0) {
        $needed = abs($quantityChange);
        $sql = "
            UPDATE `inventory`
            SET 
                `stock_quantity` = `stock_quantity` + :c1,
                `available_quantity` = `available_quantity` + :c2,
                `updated_at` = NOW()
            WHERE `product_id` = :id AND `available_quantity` >= :needed
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':c1' => $quantityChange,
            ':c2' => $quantityChange,
            ':id' => $productId,
            ':needed' => $needed
        ]);
        return $stmt->rowCount() > 0;
    } else {
        $sql = "
            UPDATE `inventory`
            SET 
                `stock_quantity` = `stock_quantity` + :c1,
                `available_quantity` = `available_quantity` + :c2,
                `updated_at` = NOW()
            WHERE `product_id` = :id
        ";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':c1' => $quantityChange,
            ':c2' => $quantityChange,
            ':id' => $productId
        ]);
        return $stmt->rowCount() > 0;
    }
}
