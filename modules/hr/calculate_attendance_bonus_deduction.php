<?php
/**
 * Automatic Attendance-Based Allowance and Deduction Calculator
 * Processes attendance records and automatically applies:
 * - 500 টাকা bonus for 30-day full attendance
 * - Converts 3 late days to 1 absent
 * - Deducts basic salary/30 for each absent day
 */

session_start();
require_once(__DIR__ . '/../../app/Config/database.php');
require_once(__DIR__ . '/../../app/Helpers/auth_helper.php');

// Check authorization - only HR, Admin, Superadmin, Team Leaders
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], [1, 2, 3, 4])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized access']));
}

// CSRF Protection
$csrf_token = $_GET['csrf_token'] ?? $_POST['csrf_token'] ?? '';
if (empty($csrf_token) || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf_token)) {
    die(json_encode(['success' => false, 'message' => 'Security token mismatch']));
}

// Get month and year from request or use current
$month = isset($_GET['month']) ? (int)$_GET['month'] : date('m');
$year = isset($_GET['year']) ? (int)$_GET['year'] : date('Y');

// Validate month range
if ($month < 1 || $month > 12) {
    die(json_encode(['success' => false, 'message' => 'Invalid month']));
}

// Get the last day of the month with fallback for cal_days_in_month
if (function_exists('cal_days_in_month')) {
    $days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
} else {
    $days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
}

try {
    // Start transaction
    $conn->begin_transaction();
    
    // Get all active employees using prepared statement
    $emp_stmt = $conn->prepare("SELECT e.id, e.first_name, e.last_name, ps.basic_salary 
                  FROM employees e
                  LEFT JOIN payslips ps ON e.id = ps.employee_id 
                  WHERE e.is_active = 1 
                  GROUP BY e.id 
                  ORDER BY e.id");
    $emp_stmt->execute();
    $emp_result = $emp_stmt->get_result();
    
    if (!$emp_result) {
        throw new Exception("Error fetching employees: " . $conn->error);
    }
    
    $results = [];
    $attendance_bonus_id = 9;  // ID for 500 টাকা full attendance bonus
    $absent_deduction_id = 25;  // ID for absent deduction
    
    while ($employee = $emp_result->fetch_assoc()) {
        $emp_id = $employee['id'];
        $basic_salary = $employee['basic_salary'] ?: 30000; // Default if not found
        $emp_name = $employee['first_name'] . ' ' . $employee['last_name'];
        
        // Get attendance for the month
        $att_query = "SELECT status, COUNT(*) as count 
                      FROM attendance 
                      WHERE employee_id = ? 
                      AND MONTH(date) = ? 
                      AND YEAR(date) = ?
                      GROUP BY status";
        
        $att_stmt = $conn->prepare($att_query);
        if (!$att_stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        $att_stmt->bind_param("iii", $emp_id, $month, $year);
        $att_stmt->execute();
        $att_result = $att_stmt->get_result();
        
        $present_count = 0;
        $late_count = 0;
        $absent_count = 0;
        
        while ($att = $att_result->fetch_assoc()) {
            if ($att['status'] == 'present') {
                $present_count = $att['count'];
            } elseif ($att['status'] == 'late') {
                $late_count = $att['count'];
            } elseif ($att['status'] == 'absent') {
                $absent_count = $att['count'];
            }
        }
        
        $att_stmt->close();
        
        $emp_result_data = [
            'emp_id' => $emp_id,
            'emp_name' => $emp_name,
            'basic_salary' => $basic_salary,
            'present' => $present_count,
            'late' => $late_count,
            'absent' => $absent_count,
            'allowance_added' => false,
            'deduction_added' => false,
            'allowance_amount' => null,
            'deduction_amount' => null,
            'message' => ''
        ];
        
        // Check if already processed for this month
        $check_query = "SELECT id FROM employee_allowances 
                       WHERE employee_id = ? 
                       AND allowance_id = ? 
                       AND DATE_FORMAT(effective_from, '%Y-%m') = ?";
        
        $check_stmt = $conn->prepare($check_query);
        $check_date = $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT);
        $check_stmt->bind_param("iis", $emp_id, $attendance_bonus_id, $check_date);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        $already_processed = $check_result->num_rows > 0;
        $check_stmt->close();
        
        // 1. Check for 30-day full attendance bonus (500 টাকা)
        if ($present_count >= 30 && !$already_processed) {
            $bonus_amount = 500;
            $effective_from = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
            
            $insert_allowance = "INSERT INTO employee_allowances 
                                (employee_id, allowance_id, amount, effective_from) 
                                VALUES (?, ?, ?, ?)";
            
            $allow_stmt = $conn->prepare($insert_allowance);
            if (!$allow_stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $allow_stmt->bind_param("iids", $emp_id, $attendance_bonus_id, $bonus_amount, $effective_from);
            if (!$allow_stmt->execute()) {
                throw new Exception("Insert allowance failed: " . $conn->error);
            }
            
            $allow_stmt->close();
            
            $emp_result_data['allowance_added'] = true;
            $emp_result_data['allowance_amount'] = $bonus_amount;
            $emp_result_data['message'] .= "✓ 500 টাকা full attendance bonus added. ";
        }
        
        // 2. Convert late days to absent (3 late = 1 absent)
        $converted_absent = intval($late_count / 3);
        $total_absent = $absent_count + $converted_absent;
        
        // 3. Apply deduction for each absent day
        if ($total_absent > 0) {
            $daily_rate = $basic_salary / 30;
            $deduction_amount = round($daily_rate * $total_absent, 2);
            
            // Check if deduction already processed
            $check_ded_query = "SELECT id FROM employee_deductions 
                               WHERE employee_id = ? 
                               AND deduction_id = ? 
                               AND DATE_FORMAT(effective_from, '%Y-%m') = ?";
            
            $check_ded_stmt = $conn->prepare($check_ded_query);
            $check_ded_stmt->bind_param("iis", $emp_id, $absent_deduction_id, $check_date);
            $check_ded_stmt->execute();
            $check_ded_result = $check_ded_stmt->get_result();
            $ded_already_processed = $check_ded_result->num_rows > 0;
            $check_ded_stmt->close();
            
            if (!$ded_already_processed) {
                $effective_from = date('Y-m-01', mktime(0, 0, 0, $month, 1, $year));
                
                $insert_deduction = "INSERT INTO employee_deductions 
                                    (employee_id, deduction_id, amount, effective_from) 
                                    VALUES (?, ?, ?, ?)";
                
                $ded_stmt = $conn->prepare($insert_deduction);
                if (!$ded_stmt) {
                    throw new Exception("Prepare failed: " . $conn->error);
                }
                
                $ded_stmt->bind_param("iids", $emp_id, $absent_deduction_id, $deduction_amount, $effective_from);
                if (!$ded_stmt->execute()) {
                    throw new Exception("Insert deduction failed: " . $conn->error);
                }
                
                $ded_stmt->close();
                
                $emp_result_data['deduction_added'] = true;
                $emp_result_data['deduction_amount'] = $deduction_amount;
                $emp_result_data['message'] .= "✓ " . $total_absent . " day(s) absent deduction (" . $deduction_amount . " টাকা) added. ";
            }
        }
        
        $results[] = $emp_result_data;
    }
    
    // Commit transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Attendance-based calculations completed for ' . $month . '/' . $year,
        'results' => $results
    ]);
    
} catch (Exception $e) {
    // Rollback transaction
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
