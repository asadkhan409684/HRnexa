<?php
// Include database connection (which now starts the session globally)
require_once __DIR__ . '/../app/Config/database.php';
require_once __DIR__ . '/../app/Controllers/AttendanceController.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

// Verify CSRF Token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['status' => 'error', 'message' => 'Security token mismatch.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller = new AttendanceController($conn);
    $action = $_POST['action'] ?? '';
    $response = $controller->handlePunch($_SESSION['user_email'], $action);
    echo json_encode($response);
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
