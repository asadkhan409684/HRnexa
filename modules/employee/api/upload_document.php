<?php
session_start();
require_once __DIR__ . '/../../../app/Config/database.php';
require_once __DIR__ . '/../../../app/Controllers/DocumentController.php';

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
    $controller = new DocumentController($conn);
    $emp_id = intval($_POST['employee_id']);
    $doc_type = $_POST['document_type'] ?? '';
    $file = $_FILES['document_file'] ?? null;

    if ($file) {
        $response = $controller->uploadDocument($emp_id, $doc_type, $file);
        echo json_encode($response);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No file uploaded.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>
