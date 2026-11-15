<?php
/**
 * ANSH THRIFTS — Logout (logout.php)
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

unset($_SESSION['admin_logged_in'], $_SESSION['admin_username'], $_SESSION['admin_login_time']);
setFlash('info', 'You have been logged out securely.');
redirect('/login.php');

