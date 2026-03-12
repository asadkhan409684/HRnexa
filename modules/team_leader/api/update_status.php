<?php
session_start();
include_once '../../../app/Config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_role'] != 4) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access.']);
    exit();
}

// Verify CSRF Token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['status' => 'error', 'message' => 'Security token mismatch.']);
    exit();
}

$user_email = $_SESSION['user_email'];

// Get employee_id using prepared statement
$emp_stmt = $conn->prepare("SELECT id FROM employees WHERE email = ? LIMIT 1");
$emp_stmt->bind_param("s", $user_email);
$emp_stmt->execute();
$emp_result = $emp_stmt->get_result();
$tl_data = $emp_result->fetch_assoc();
$tl_id = $tl_data['id'] ?? 0;
$emp_stmt->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action_type']; // 'leave' or 'correction'
    $status = $_POST['status']; // 'referred' or 'rejected'
    $item_id = intval($_POST['id']);

    if (!in_array($status, ['referred', 'rejected'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid status change.']);
        exit();
    }

    if ($action === 'leave') {
        $sql = "UPDATE leave_applications SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $status, $tl_id, $item_id);
    } else if ($action === 'correction') {
        $sql = "UPDATE attendance_corrections SET status = ?, approved_by = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sii", $status, $tl_id, $item_id);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action type.']);
        exit();
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => "Request $status successfully!"]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
$conn->close();
?>
