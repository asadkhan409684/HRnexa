<?php
/**
 * Checks if a user is currently logged in.
 * @return bool
 */
function isLoggedIn() {
    return isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true;
}

/**
 * Checks if the logged-in user has a specific role.
 * @param array|int $required_roles Single role ID or array of IDs.
 * @return bool
 */
function hasRole($required_roles) {
    if (!isLoggedIn()) return false;
    
    $user_role = $_SESSION['user_role'] ?? null;
    if (is_array($required_roles)) {
        return in_array($user_role, $required_roles);
    }
    return $user_role == $required_roles;
}

/**
 * Ensures user is logged in, otherwise redirects.
 * @param string $login_path Path to the login page.
 */
function requireLogin($login_path = '../../views/auth/login.php') {
    if (!isLoggedIn()) {
        header("Location: $login_path");
        exit();
    }
}

/**
 * Ensures user has a specific role, otherwise redirects to unauthorized page.
 * @param array|int $roles
 * @param string $unauthorized_path
 */
function requireRole($roles, $unauthorized_path = '../../views/auth/unauthorized.php') {
    requireLogin();
    if (!hasRole($roles)) {
        header("Location: $unauthorized_path");
        exit();
    }
}
