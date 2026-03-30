<?php
// Include database connection (which now starts the session globally)
require_once __DIR__ . '/../../app/Config/database.php';
require_once __DIR__ . '/../../app/Controllers/AuthController.php';

global $conn;

$auth = new AuthController($conn);

// Get user ID and role before destroying session
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$role_id = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : null;

// Handle logout through controller
$auth->logout();

// Redirect to login page
header("Location: " . BASE_URL . "login");
exit();
?>
