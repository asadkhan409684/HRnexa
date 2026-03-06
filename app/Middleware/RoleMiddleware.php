<?php
/**
 * RoleMiddleware - Ensures user has required permissions
 */
class RoleMiddleware {
    /**
     * Reusable handle method for role check
     * @param array|int $allowedRoles Array of role IDs or single ID
     * @param string $unauthorizedPath Redirect path if access denied
     */
    public static function handle($allowedRoles, $unauthorizedPath = null) {
        if ($unauthorizedPath === null) {
            $unauthorizedPath = BASE_URL . 'views/auth/unauthorized.php';
        }
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Must be logged in first
        if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
            header("Location: " . BASE_URL . "views/auth/login.php");
            exit();
        }

        $userRole = $_SESSION['user_role'] ?? null;
        $isAllowed = false;

        if (is_array($allowedRoles)) {
            $isAllowed = in_array($userRole, $allowedRoles);
        } else {
            $isAllowed = ($userRole == $allowedRoles);
        }

        if (!$isAllowed) {
            header("Location: $unauthorizedPath");
            exit();
        }
    }
}