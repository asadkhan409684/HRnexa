<?php
/**
 * AuthMiddleware - Ensures user is authenticated
 */
class AuthMiddleware {
    /**
     * Reusable handle method for auth check
     * @param string $loginPath Redirect path if not logged in
     */
    public static function handle($loginPath = '../../views/auth/login.php') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            header("Location: $loginPath");
            exit();
        }
    }
}