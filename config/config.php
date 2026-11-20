<?php
/**
 * config/config.php
 * Global constants + secure session bootstrap.
 * Every protected page includes this file FIRST.
 */

// ---- Secure session settings (must run before session_start) ----
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// ---- App constants ----
define('APP_NAME', 'Student Lost & Found - Miva Open University');
define('BASE_URL', '/student-lost-found');           // adjust if you deploy in a sub-folder
define('UPLOAD_DIR', __DIR__ . '/../uploads/items/'); // absolute filesystem path
define('UPLOAD_URL', BASE_URL . '/uploads/items/');   // browser-facing path
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024);          // 5 MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/webp']);

// Basic session timeout (30 minutes of inactivity)
define('SESSION_LIFETIME', 1800);
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > SESSION_LIFETIME) {
    session_unset();
    session_destroy();
    session_start();
}
$_SESSION['last_activity'] = time();

// ---- Error display (turn OFF display_errors in production) ----
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';
