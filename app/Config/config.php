<?php
/**
 * Main Application Configuration
 * HRnexa - Human Resource Management System
 */

// Basic App configuration
define('APP_NAME', 'HRnexa');
define('APP_VERSION', '1.0.0');

// Base URL configuration
// Dynamically determine the base URL and folder
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];

// Robust detection of project root folder relative to document root
$project_root = str_replace('\\', '/', realpath(dirname(dirname(__DIR__))));
$doc_root = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT']));

// Calculate the base folder
$base_folder = str_replace($doc_root, '', $project_root);
$base_folder = '/' . trim($base_folder, '/') . '/';
if ($base_folder === '//') $base_folder = '/';

define('BASE_FOLDER', $base_folder);
define('BASE_URL', $protocol . "://" . $host . $base_folder);

// Session Configuration
// This ensures that the session cookie is valid for all subfolders
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 3600,
        'path' => BASE_FOLDER,
        'domain' => null,
        'secure' => $protocol === 'https',
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Global CSRF Token Generation
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Load .env file
function loadEnv($path) {
    if (!file_exists($path)) return;
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
        putenv(sprintf('%s=%s', trim($name), trim($value)));
    }
}
loadEnv(dirname(dirname(__DIR__)) . '/.env');

define('APP_ENV', getenv('APP_ENV') ?: 'production');

// Security configuration
define('SESSION_LIFETIME', 3600); // 1 hour

// File System Paths
define('UPLOAD_DIR', dirname(dirname(__DIR__)) . '/Upload/');

// Display errors in local environment
if (APP_ENV === 'local') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
