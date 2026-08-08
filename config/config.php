<?php
/**
 * Global configuration. Edit DB credentials for your hosting environment.
 * Nothing business-related lives here — that's all in the `settings` table,
 * editable from the admin panel.
 */
declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '0'); // set to '1' only while developing locally

// ---- Database ---------------------------------------------------------
define('DB_HOST', 'localhost');
define('DB_NAME', 'idms_interior');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// ---- Paths / URLs -------------------------------------------------------
define('BASE_PATH', dirname(__DIR__));
define('UPLOADS_PATH', BASE_PATH . '/uploads');

// BASE_URL is derived automatically so the site works from any subfolder.
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
// Normalize when config.php is included from /admin or /ajax subfolders
$root = $scriptDir;
foreach (['/admin', '/ajax'] as $sub) {
    if (str_ends_with($root, $sub)) {
        $root = substr($root, 0, -strlen($sub));
    }
}
define('BASE_URL', $scheme . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $root);

// ---- Uploads ------------------------------------------------------------
define('MAX_UPLOAD_BYTES', 5 * 1024 * 1024); // 5MB per image
define('ALLOWED_IMAGE_MIME', ['image/jpeg', 'image/png', 'image/webp']);
define('THUMB_WIDTH', 480);

// ---- Security -------------------------------------------------------------
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_MINUTES', 15);

// ---- Site meta fallback ---------------------------------------------------
define('SITE_ENV', 'production'); // 'local' enables verbose error output
if (SITE_ENV === 'local') {
    ini_set('display_errors', '1');
}
