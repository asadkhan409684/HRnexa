<?php
session_start();
require_once __DIR__ . '/../../../app/Config/database.php';

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
    
    // Get employee_id using prepared statement
    $emp_stmt = $conn->prepare("SELECT id, employee_code FROM employees WHERE email = ? LIMIT 1");
    $emp_stmt->bind_param("s", $user_email);
    $emp_stmt->execute();
    $emp_result = $emp_stmt->get_result();
    $emp_data = $emp_result->fetch_assoc();
    $emp_id = $emp_data['id'] ?? 0;
    $emp_code = $emp_data['employee_code'] ?? 'UNK';
    $emp_stmt->close();

    if (!$emp_id) {
        echo json_encode(['status' => 'error', 'message' => 'Employee record not found.']);
        exit();
    }

    $doc_type = $_POST['document_type'] ?? '';
    $file = $_FILES['document_file'] ?? null;

    if ($file && $file['error'] === 0) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            $system_upload_dir = '../../../Upload/employee_document/pending/';
            if (!is_dir($system_upload_dir)) mkdir($system_upload_dir, 0777, true);

            $new_filename = $emp_code . '-TL-' . date('YmdHis') . '.pdf';
            $target_path = $system_upload_dir . $new_filename;
            $db_path = '../Upload/employee_document/pending/' . $new_filename;

            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $ins_sql = "INSERT INTO employee_documents (employee_id, document_type, file_path, verification_status) VALUES (?, ?, ?, 'pending')";
                $stmt = $conn->prepare($ins_sql);
                $stmt->bind_param("iss", $emp_id, $doc_type, $db_path);
                if ($stmt->execute()) {
                    echo json_encode(['status' => 'success', 'message' => 'Document uploaded! Waiting for HR approval.']);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Failed to record document.']);
                }
                $stmt->close();
            } else {
                echo json_encode(['status' => 'error', 'message' => 'Failed to move file.']);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Only PDFs allowed.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Upload error or no file provided.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request.']);
}
$conn->close();
?>
