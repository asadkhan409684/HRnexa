<?php
/**
 * Process Allowance and Deduction Management for Employees
 * Handles AJAX requests for managing employee allowances and deductions
 */

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../app/Config/database.php';

// Simple debug logging (append-only) to help diagnose empty dropdowns
$logFile = __DIR__ . '/process_allowance_deduction.log';
function _log($msg) {
    global $logFile;
    file_put_contents($logFile, date('[Y-m-d H:i:s] ') . $msg . PHP_EOL, FILE_APPEND);
}
_log('REQUEST: ' . ($_SERVER['REQUEST_METHOD'] ?? '') . ' ' . ($_GET['action'] ?? $_POST['action'] ?? '') . ' | role=' . ($_SESSION['user_role'] ?? 'NONE'));

// Check authorization (allow Admin (2), HR (4) and Team Leader (3) roles)
if (!isset($_SESSION['user_logged_in']) || !in_array($_SESSION['user_role'], [2, 3, 4])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// CSRF Protection
$csrf_token = $_GET['csrf_token'] ?? $_POST['csrf_token'] ?? '';
if (empty($csrf_token) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrf_token)) {
    echo json_encode(['success' => false, 'message' => 'Security token mismatch']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    // GET REQUESTS
    case 'get_allowances':
        getAvailableAllowances();
        break;

    case 'get_deductions':
        getAvailableDeductions();
        break;

    case 'get_emp_allowances':
        getEmployeeAllowances($_GET['emp_id'] ?? 0);
        break;

    case 'get_emp_deductions':
        getEmployeeDeductions($_GET['emp_id'] ?? 0);
        break;

    // POST REQUESTS
    case 'add_allowance':
        addAllowance($_POST);
        break;

    case 'add_deduction':
        addDeduction($_POST);
        break;

    case 'remove_allowance':
        removeAllowance($_POST['id'] ?? 0);
        break;

    case 'remove_deduction':
        removeDeduction($_POST['id'] ?? 0);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

/**
 * Get all available allowances
 */
function getAvailableAllowances() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, name, type FROM allowances WHERE is_active = 1 ORDER BY name ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $allowances = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $allowances[] = $row;
        }
    }
    $stmt->close();
    
    echo json_encode(['success' => true, 'allowances' => $allowances]);
}

/**
 * Get all available deductions
 */
function getAvailableDeductions() {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, name, type FROM deductions WHERE is_active = 1 ORDER BY name ASC");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $deductions = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $deductions[] = $row;
        }
    }
    $stmt->close();
    
    echo json_encode(['success' => true, 'deductions' => $deductions]);
}

/**
 * Get employee's current allowances
 */
function getEmployeeAllowances($empId) {
    global $conn;
    
    $empId = intval($empId);
    
    $stmt = $conn->prepare("SELECT ea.id, ea.amount, ea.effective_from, ea.effective_to, 
                     a.name as allowance_name
              FROM employee_allowances ea
              JOIN allowances a ON ea.allowance_id = a.id
              WHERE ea.employee_id = ?
              ORDER BY ea.effective_from DESC");
    $stmt->bind_param("i", $empId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $allowances = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $allowances[] = $row;
        }
    }
    $stmt->close();
    
    echo json_encode(['success' => true, 'allowances' => $allowances]);
}

/**
 * Get employee's current deductions
 */
function getEmployeeDeductions($empId) {
    global $conn;
    
    $empId = intval($empId);
    
    $stmt = $conn->prepare("SELECT ed.id, ed.amount, ed.effective_from, ed.effective_to, 
                     d.name as deduction_name
              FROM employee_deductions ed
              JOIN deductions d ON ed.deduction_id = d.id
              WHERE ed.employee_id = ?
              ORDER BY ed.effective_from DESC");
    $stmt->bind_param("i", $empId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $deductions = [];
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $deductions[] = $row;
        }
    }
    $stmt->close();
    
    echo json_encode(['success' => true, 'deductions' => $deductions]);
}

/**
 * Add allowance to employee
 */
function addAllowance($data) {
    global $conn;
    
    $empId = intval($data['emp_id'] ?? 0);
    $allowanceId = intval($data['allowance_id'] ?? 0);
    $amount = floatval($data['amount'] ?? 0);
    $effectiveFrom = $data['effective_from'] ?? '';
    
    if (!$empId || !$allowanceId || !$amount || !$effectiveFrom) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
        return;
    }
    
    // Check if allowance already exists for this employee
    $checkStmt = $conn->prepare("SELECT id FROM employee_allowances 
                   WHERE employee_id = ? AND allowance_id = ? 
                   AND (effective_to IS NULL OR effective_to >= CURDATE())");
    $checkStmt->bind_param("ii", $empId, $allowanceId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult && $checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This allowance is already assigned to the employee']);
        $checkStmt->close();
        return;
    }
    $checkStmt->close();
    
    $stmt = $conn->prepare("INSERT INTO employee_allowances (employee_id, allowance_id, amount, effective_from) 
              VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $empId, $allowanceId, $amount, $effectiveFrom);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Allowance added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
}

/**
 * Add deduction to employee
 */
function addDeduction($data) {
    global $conn;
    
    $empId = intval($data['emp_id'] ?? 0);
    $deductionId = intval($data['deduction_id'] ?? 0);
    $amount = floatval($data['amount'] ?? 0);
    $effectiveFrom = $data['effective_from'] ?? '';
    
    if (!$empId || !$deductionId || !$amount || !$effectiveFrom) {
        echo json_encode(['success' => false, 'message' => 'Invalid data provided']);
        return;
    }
    
    // Check if deduction already exists for this employee
    $checkStmt = $conn->prepare("SELECT id FROM employee_deductions 
                   WHERE employee_id = ? AND deduction_id = ? 
                   AND (effective_to IS NULL OR effective_to >= CURDATE())");
    $checkStmt->bind_param("ii", $empId, $deductionId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult && $checkResult->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'This deduction is already assigned to the employee']);
        $checkStmt->close();
        return;
    }
    $checkStmt->close();
    
    $stmt = $conn->prepare("INSERT INTO employee_deductions (employee_id, deduction_id, amount, effective_from) 
              VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iids", $empId, $deductionId, $amount, $effectiveFrom);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Deduction added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
}

/**
 * Remove allowance from employee
 */
function removeAllowance($allowanceId) {
    global $conn;
    
    $allowanceId = intval($allowanceId);
    
    if (!$allowanceId) {
        echo json_encode(['success' => false, 'message' => 'Invalid allowance ID']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM employee_allowances WHERE id = ?");
    $stmt->bind_param("i", $allowanceId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Allowance removed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
}

/**
 * Remove deduction from employee
 */
function removeDeduction($deductionId) {
    global $conn;
    
    $deductionId = intval($deductionId);
    
    if (!$deductionId) {
        echo json_encode(['success' => false, 'message' => 'Invalid deduction ID']);
        return;
    }
    
    $stmt = $conn->prepare("DELETE FROM employee_deductions WHERE id = ?");
    $stmt->bind_param("i", $deductionId);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Deduction removed successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $stmt->error]);
    }
    $stmt->close();
}
?>
