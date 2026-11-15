<?php
/**
 * ANSH THRIFTS — Admin Login (login.php)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isAdminLoggedIn()) {
    redirect('/admin/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!validateCsrfToken($csrfToken)) {
        setFlash('danger', 'Security validation failed (Invalid CSRF token). Please try again.');
        redirect('/login.php');
    }

    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($username === ADMIN_USERNAME && verifyAdminPassword($password)) {
        // Prevent session fixation
        session_regenerate_id(true);

        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_login_time'] = time();
        setFlash('success', 'Welcome back, Administrator!');
        redirect('/admin/index.php');
    } else {
        setFlash('danger', 'Invalid username or password.');
    }
}

$pageTitle = 'Admin Login · ' . APP_NAME;
$activeNav = 'login';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($pageTitle) ?></title>
  <link rel="shortcut icon" href="/static/images/favicon.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
  <link href="/static/css/bootstrap.min.css" rel="stylesheet">
  <link href="/static/css/style.css" rel="stylesheet">

  <style>
    body {
      background: url('/static/images/login-img.jpg') no-repeat center center/cover;
      background-attachment: fixed;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
  </style>
</head>
<body>

  <!-- Minimal Navbar -->
  <nav class="custom-navbar navbar navbar-expand navbar-dark">
    <div class="container-fluid">
      <a class="navbar-brand" href="/index.php"><?= e(APP_NAME) ?><span>.</span></a>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="/index.php">Storefront</a></li>
        <li class="nav-item"><a class="nav-link" href="/shop.php">Shop</a></li>
      </ul>
    </div>
  </nav>

  <!-- Login Form Box -->
  <div class="contact-page-wrapper flex-grow-1 d-flex align-items-center justify-content-center" style="padding-top: 100px;">
    <div class="glass-card w-100" style="max-width: 420px;">
      <h2 style="font-family: 'Playfair Display', serif; margin-bottom: 24px;">Admin Portal</h2>

      <?php $flashMsgs = getFlashMessages(); ?>
      <?php foreach ($flashMsgs as $flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> py-2 small" role="alert">
          <?= e($flash['message']) ?>
        </div>
      <?php endforeach; ?>

      <form action="/login.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?= e(getCsrfToken()) ?>">

        <div class="mb-3 text-start">
          <label class="form-label text-white-50 small">Username</label>
          <input type="text" class="form-control" name="username" placeholder="Username" required autofocus>
        </div>
        <div class="mb-4 text-start">
          <label class="form-label text-white-50 small">Password</label>
          <input type="password" class="form-control" name="password" placeholder="Password" required>
        </div>
        <button type="submit" class="btn btn-custom w-100 py-3">Access Dashboard</button>

        <div class="login-options mt-4 d-flex justify-content-between">
          <a href="/shop.php" class="small text-white-50">← Back to shop</a>
          <a href="/contact.php" class="small text-white-50">Support inquiry</a>
        </div>
      </form>
    </div>
  </div>

  <footer class="footer-section py-3 text-center text-white-50 small">
    &copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. Secure Google Sheets Management System.
  </footer>

  <script src="/static/js/bootstrap.bundle.min.js"></script>
</body>
</html>
