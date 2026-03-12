<?php
session_start();
include_once '../../../app/Config/database.php';

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
    $user_email = $_SESSION['user_email'];
    $emp_stmt = $conn->prepare("SELECT id FROM employees WHERE email = ? LIMIT 1");
    $emp_stmt->bind_param("s", $user_email);
    $emp_stmt->execute();
    $emp_result = $emp_stmt->get_result();
    $emp_data = $emp_result->fetch_assoc();
    $emp_id = $emp_data['id'] ?? 0;
    $emp_stmt->close();

    if (!$emp_id) {
        echo json_encode(['status' => 'error', 'message' => 'Employee record not found.']);
        exit();
    }

    $name = $_POST['contact_name'];
    $relationship = $_POST['relationship'];
    $phone = $_POST['contact_phone'];
    $email = $_POST['contact_email'];
    $address = $_POST['contact_address'];

    // Use prepared statements for check and update/insert
    $check_stmt = $conn->prepare("SELECT id FROM employee_emergency_contacts WHERE employee_id = ?");
    $check_stmt->bind_param("i", $emp_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $exists = ($check_result && $check_result->num_rows > 0);
    $check_stmt->close();

    if ($exists) {
        $stmt = $conn->prepare("UPDATE employee_emergency_contacts SET name = ?, relationship = ?, phone = ?, email = ?, address = ? WHERE employee_id = ?");
        $stmt->bind_param("sssssi", $name, $relationship, $phone, $email, $address, $emp_id);
    } else {
        $stmt = $conn->prepare("INSERT INTO employee_emergency_contacts (employee_id, name, relationship, phone, email, address) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssss", $emp_id, $name, $relationship, $phone, $email, $address);
    }

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Emergency contact updated successfully!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update contact.']);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
$conn->close();
?>
