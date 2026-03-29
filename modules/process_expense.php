<?php
// Include database connection (which now starts the session globally)
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

$category_id = $_POST['category_id'] ?? '';
$amount = $_POST['amount'] ?? '';
$description = $_POST['description'] ?? '';
$claim_date = $_POST['claim_date'] ?? date('Y-m-d');

if (empty($category_id) || empty($amount) || empty($description)) {
    echo json_encode(['status' => 'error', 'message' => 'Please fill in all required fields.']);
    exit();
}

$receipt_path = null;
if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../upload/expense_receipts/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_ext = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));
    // Security check for file extension
    if (!in_array($file_ext, ['jpg', 'jpeg', 'png', 'pdf'])) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file type. Only JPG, PNG, and PDF are allowed.']);
        exit();
    }
    
    $file_name = 'EXP-' . $employee_id . '-' . time() . '.' . $file_ext;
    $target_file = $upload_dir . $file_name;
    
    if (move_uploaded_file($_FILES['receipt']['tmp_name'], $target_file)) {
        $receipt_path = 'upload/expense_receipts/' . $file_name;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to upload receipt.']);
        exit();
    }
}

$stmt = $conn->prepare("INSERT INTO expense_claims (employee_id, category_id, amount, description, claim_date, receipt_path, status) VALUES (?, ?, ?, ?, ?, ?, 'submitted')");
$stmt->bind_param("isdsss", $employee_id, $category_id, $amount, $description, $claim_date, $receipt_path);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success', 'message' => 'Expense claim submitted successfully.']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Failed to submit claim.']);
}

$stmt->close();
$conn->close();
?>
