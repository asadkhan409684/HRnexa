<?php
/**
 * Security Helper - Handles CSRF tokens and other security utilities
 */

if (!function_exists('generateCsrfToken')) {
    function generateCsrfToken() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verifyCsrfToken')) {
    function verifyCsrfToken($token) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return !empty($token) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

/**
 * Clean data for output (XSS prevention)
 */
if (!function_exists('escape')) {
    function escape($data) {
        return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    }
}
