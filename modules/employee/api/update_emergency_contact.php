<?php
session_start();
require_once __DIR__ . '/../../../app/Config/database.php';
require_once __DIR__ . '/../../../app/Controllers/EmployeeController.php';

header('Content-Type: application/json');

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
    $controller = new EmployeeController($conn);
    $emp_id = intval($_POST['employee_id']);
    $response = $controller->saveEmergencyContact($emp_id, $_POST);
    echo json_encode($response);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>
