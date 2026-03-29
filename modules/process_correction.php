<?php
session_start();
require_once __DIR__ . '/../app/Config/database.php';

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit();
}

$user_email = $_SESSION['user_email'];

// Get employee_id using prepared statement
$emp_stmt = $conn->prepare("SELECT id FROM employees WHERE email = ? LIMIT 1");
$emp_stmt->bind_param("s", $user_email);
$emp_stmt->execute();
$emp_result = $emp_stmt->get_result();

if (!$emp_result || $emp_result->num_rows === 0) {
    echo json_encode(['status' => 'error', 'message' => 'Employee record not found.']);
    exit();
}
$emp_row = $emp_result->fetch_assoc();
$employee_id = $emp_row['id'];
$emp_stmt->close();

$date = $_POST['date'] ?? '';
$corrected_in = $_POST['punch_in'] ?? '';
$corrected_out = $_POST['punch_out'] ?? '';
$reason = $_POST['reason'] ?? '';

if (empty($date) || empty($corrected_in) || empty($corrected_out) || empty($reason)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
    exit();
}

// Get original punch data using prepared statement
$orig_stmt = $conn->prepare("SELECT punch_in, punch_out FROM attendance WHERE employee_id = ? AND date = ? LIMIT 1");
$orig_stmt->bind_param("is", $employee_id, $date);
$orig_stmt->execute();
$orig_result = $orig_stmt->get_result();
$orig_data = ($orig_result && $orig_result->num_rows > 0) ? $orig_result->fetch_assoc() : null;
$orig_stmt->close();

$orig_in = $orig_data['punch_in'] ?? '0000-00-00 00:00:00';
$orig_out = $orig_data['punch_out'] ?? '0000-00-00 00:00:00';

// Format corrected times to full timestamps
$corrected_in_ts = $date . ' ' . $corrected_in;
$corrected_out_ts = $date . ' ' . $corrected_out;

// Set requested_by to NULL if the requester is an employee (Role 5)
$requested_by = (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 5) ? null : $_SESSION['user_id'];

$stmt = $conn->prepare("INSERT INTO attendance_corrections (employee_id, date, original_punch_in, original_punch_out, corrected_punch_in, corrected_punch_out, reason, requested_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
$stmt->bind_param("issssssi", $employee_id, $date, $orig_in, $orig_out, $corrected_in_ts, $corrected_out_ts, $reason, $requested_by);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Attendance correction request submitted successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit request.']);
}

$stmt->close();
$conn->close();
?>
