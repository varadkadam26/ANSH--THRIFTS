<?php
/**
 * ANSH THRIFTS — Application Configuration
 * Compatible with PHP 8.0+ and Hostinger Web Hosting.
 */

declare(strict_types=1);

// Error reporting for development (Hostinger production can set this in .env)
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Session setup with security headers
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_samesite', 'Lax');
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        ini_set('session.cookie_secure', '1');
    }
    session_start();
}

// Load .env helper if .env exists
function loadEnv(string $path): void {
    if (!file_exists($path)) {
        return;
    }
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$name, $value] = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            // Remove wrapping quotes if present
            if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                $value = substr($value, 1, -1);
            }
            if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
                putenv("$name=$value");
                $_ENV[$name] = $value;
                $_SERVER[$name] = $value;
            }
        }
    }
}

// Check if .env exists in parent directory (outside public_html) or local directory
if (file_exists(__DIR__ . '/../../.env')) {
    loadEnv(__DIR__ . '/../../.env');
} else {
    loadEnv(__DIR__ . '/../.env');
}

// Read config.json params as fallback
$configJsonPath = __DIR__ . '/../config.json';
$configParams = [];
if (file_exists($configJsonPath)) {
    $decoded = json_decode(file_get_contents($configJsonPath), true);
    if (isset($decoded['params'])) {
        $configParams = $decoded['params'];
    }
}

function env(string $key, mixed $default = null): mixed {
    $val = getenv($key);
    if ($val !== false) {
        return $val;
    }
    if (isset($_ENV[$key])) {
        return $_ENV[$key];
    }
    if (isset($_SERVER[$key])) {
        return $_SERVER[$key];
    }
    return $default;
}

// Base configuration constants
define('APP_NAME', env('APP_NAME', $configParams['blog_name'] ?? 'ANSH THRIFTS'));
define('APP_TAGLINE', env('APP_TAGLINE', $configParams['tag_line'] ?? 'Pre-Loved Fashion, New Stories.'));
define('APP_ENV', env('APP_ENV', 'development'));

// Admin Credentials
define('ADMIN_USERNAME', env('ADMIN_USERNAME', $configParams['admin_username'] ?? 'admin'));
define('ADMIN_PASSWORD', env('ADMIN_PASSWORD', $configParams['admin_password'] ?? 'admin123'));

// Razorpay Keys
define('RAZORPAY_KEY_ID', env('RAZORPAY_KEY_ID', $configParams['razorpay_key_id'] ?? 'rzp_test_SIIeZtDjS4VRsM'));
define('RAZORPAY_KEY_SECRET', env('RAZORPAY_KEY_SECRET', $configParams['razorpay_key_secret'] ?? 'jChLBRshmM64ATJ7lLVMEj6T'));

// Database Configuration (MySQL / MariaDB for Hostinger)
define('DB_HOST', (string)env('DB_HOST', 'localhost'));
define('DB_PORT', (int)env('DB_PORT', 3306));
define('DB_NAME', (string)env('DB_NAME', 'ansh_thrifts'));
define('DB_USER', (string)env('DB_USER', 'root'));
define('DB_PASS', (string)env('DB_PASS', ''));
define('DB_CHARSET', (string)env('DB_CHARSET', 'utf8mb4'));

// Upload directory (absolute & web relative)
define('UPLOAD_DIR', __DIR__ . '/../static/images/uploads');
define('UPLOAD_URL', 'static/images/uploads');

// Ensure upload dir exists
if (!is_dir(UPLOAD_DIR)) {
    @mkdir(UPLOAD_DIR, 0755, true);
}

// Mail & SMTP Configuration
define('MAIL_SERVER', (string)env('MAIL_SERVER', 'smtp.gmail.com'));
define('MAIL_PORT', (int)env('MAIL_PORT', 465));
define('MAIL_USERNAME', (string)env('MAIL_USERNAME', 'mitramsolutions@gmail.com'));
define('MAIL_PASSWORD', (string)env('MAIL_PASSWORD', ''));
define('MAIL_FROM_ADDRESS', (string)env('MAIL_FROM_ADDRESS', MAIL_USERNAME));
define('MAIL_FROM_NAME', (string)env('MAIL_FROM_NAME', APP_NAME));
define('CONTACT_RECIPIENT', (string)env('CONTACT_RECIPIENT', 'mitramsolutions@gmail.com'));
