<?php
// Include database connection (which now starts the session globally)
require_once __DIR__ . '/../../app/Config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: " . BASE_URL . "login");
    exit();
}

// Check if user has HR Manager role (role_id = 3) or Admin (role_id = 2) or Superadmin (role_id = 1)
$allowed_roles = [1, 2, 3];
if (!in_array($_SESSION['user_role'], $allowed_roles)) {
    header("Location: " . BASE_URL . "views/auth/unauthorized.php");
    exit();
}

// Get user information from session
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'HR Manager';
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'hr@hrnexa.com';

// Additional controllers and models
require_once __DIR__ . '/../../app/Controllers/PayrollController.php';
require_once __DIR__ . '/../../app/Models/Payroll.php';

$payrollModel = new Payroll($conn);
$payrollController = new PayrollController($conn);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_employee'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch. Please try again.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }

    // Auto generate employee code
    $employee_code = 'EMP' . date('Y') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);

    $first_name     = $_POST['first_name'];
    $last_name      = $_POST['last_name'];
    $email          = $_POST['email'];
    $phone          = $_POST['phone'] ?? null;
    $date_of_birth  = $_POST['date_of_birth'] ?? null;
    $gender         = $_POST['gender'] ?? null;
    $address        = $_POST['address'] ?? null;
    $department_id  = $_POST['department_id'] ?? null;
    $designation_id = $_POST['designation_id'] ?? null;
    $team_leader_id = !empty($_POST['team_leader_id']) ? $_POST['team_leader_id'] : null;

    // Check if department and designation exist using prepared statements
    $dept_stmt = $conn->prepare("SELECT id FROM departments WHERE id = ?");
    $dept_stmt->bind_param("i", $department_id);
    $dept_stmt->execute();
    if ($dept_stmt->get_result()->num_rows === 0) {
        $_SESSION['message'] = "Error: Selected department does not exist.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $dept_stmt->close();
    
    $desig_stmt = $conn->prepare("SELECT id FROM designations WHERE id = ?");
    $desig_stmt->bind_param("i", $designation_id);
    $desig_stmt->execute();
    if ($desig_stmt->get_result()->num_rows === 0) {
        $_SESSION['message'] = "Error: Selected designation does not exist.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $desig_stmt->close();
    
    // ... rest of your code
    $hire_date = !empty($_POST['hire_date']) ? $_POST['hire_date'] : date('Y-m-d');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validate passwords
    if (empty($password)) {
        $_SESSION['message'] = "Error: Password is required.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }

    if ($password !== $confirm_password) {
        $_SESSION['message'] = "Error: Passwords do not match.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }

    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $_SESSION['message'] = "Error: First Name, Last Name, and Email are required.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }


    $sql = "INSERT INTO employees (
                    employee_code,
                    first_name,
                    last_name,
                    email,
                    password_hash,
                    phone,
                    date_of_birth,
                    gender,
                    address,
                    department_id,
                    designation_id,
                    team_leader_id,
                    hire_date
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param(
            "sssssssssiiis",
            $employee_code,
            $first_name,
            $last_name,
            $email,
            $password_hash,
            $phone,
            $date_of_birth,
            $gender,
            $address,
            $department_id,
            $designation_id,
            $team_leader_id,
            $hire_date
        );
    if ($stmt->execute()) {
        $_SESSION['message'] = "Employee Added successfully | Code: " . $employee_code;
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Database Error: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php");
    exit();
}

// Handle Employment Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_employment_status'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $emp_id = intval($_POST['employee_id']);
    $new_status = $_POST['employment_status'];
    $prob_start = !empty($_POST['probation_start_date']) ? $_POST['probation_start_date'] : null;
    $prob_end = !empty($_POST['probation_end_date']) ? $_POST['probation_end_date'] : null;
    
    $update_sql = "UPDATE employees SET employment_status = ?, probation_start_date = ?, probation_end_date = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("sssi", $new_status, $prob_start, $prob_end, $emp_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Employment status updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating status: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php#employment-status");
    exit();
}

// Handle Document Upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_document'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    
    $employee_id = intval($_POST['employee_id']);
    $doc_type = $_POST['document_type'];
    $file = $_FILES['document_file'];
    
    if ($file['error'] === 0) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            $upload_dir = '../upload/employee_document/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Get employee code for naming using prepared statement
            $emp_stmt = $conn->prepare("SELECT employee_code FROM employees WHERE id = ?");
            $emp_stmt->bind_param("i", $employee_id);
            $emp_stmt->execute();
            $emp_data = $emp_stmt->get_result()->fetch_assoc();
            $emp_code = $emp_data['employee_code'];
            $emp_stmt->close();
            
            $new_filename = $emp_code . '-' . date('YmdHis') . '.pdf';
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $ins_sql = "INSERT INTO employee_documents (employee_id, document_type, file_path) VALUES (?, ?, ?)";
                $stmt = $conn->prepare($ins_sql);
                $stmt->bind_param("iss", $employee_id, $doc_type, $target_path);
                
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Document uploaded successfully!";
                    $_SESSION['message_type'] = "success";
                } else {
                    $_SESSION['message'] = "Database error: " . $stmt->error;
                    $_SESSION['message_type'] = "danger";
                }
            } else {
                $_SESSION['message'] = "Failed to move uploaded file.";
                $_SESSION['message_type'] = "danger";
            }
        } else {
            $_SESSION['message'] = "Only PDF files are allowed.";
            $_SESSION['message_type'] = "danger";
        }
    } else {
        $_SESSION['message'] = "File upload error: " . $file['error'];
        $_SESSION['message_type'] = "danger";
    }
    
    header("Location: dashboard.php#documents");
    exit();
}

// Handle Document Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_doc_status'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $doc_id = intval($_POST['doc_id']);
    $new_status = $_POST['verification_status'];
    
    // Fetch current file path for moving logic
    $doc_stmt = $conn->prepare("SELECT file_path FROM employee_documents WHERE id = ?");
    $doc_stmt->bind_param("i", $doc_id);
    $doc_stmt->execute();
    $doc_row = $doc_stmt->get_result()->fetch_assoc();
    $current_path = $doc_row['file_path'];
    $doc_stmt->close();

    // Move file from pending to main folder if verified
    if ($new_status === 'verified' && strpos($current_path, '/pending/') !== false) {
        $new_path = str_replace('/pending/', '/', $current_path);
        
        // Ensure destination directory exists
        $main_dir = dirname($new_path) . '/';
        if (!is_dir($main_dir)) {
            mkdir($main_dir, 0777, true);
        }

        if (file_exists($current_path)) {
            if (rename($current_path, $new_path)) {
                $current_path = $new_path;
            }
        }
    }
    
    $update_sql = "UPDATE employee_documents SET verification_status = ?, file_path = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("ssi", $new_status, $current_path, $doc_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Document status updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating document status: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php#documents");
    exit();
}

// Handle Leave Application Action (Approve/Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['approve_leave']) || isset($_POST['reject_leave']))) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $leave_id = intval($_POST['leave_id']);
    $status = isset($_POST['approve_leave']) ? 'approved' : 'rejected';
    
    $update_leave_sql = "UPDATE leave_applications SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_leave_sql);
    $stmt->bind_param("si", $status, $leave_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Leave application " . ucfirst($status) . " successfully!";
        $_SESSION['message_type'] = $status == 'approved' ? "success" : "warning";
    } else {
        $_SESSION['message'] = "Error updating leave status: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php#leave-applications");
    exit();
}

// Handle Attendance Correction Action (Approve/Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && (isset($_POST['approve_correction']) || isset($_POST['reject_correction']))) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $req_id = intval($_POST['request_id']);
    $action = isset($_POST['approve_correction']) ? 'approve' : 'reject';
    
    if ($action === 'approve') {
        // Fetch the correction details first
        $req_stmt = $conn->prepare("SELECT * FROM attendance_corrections WHERE id = ?");
        $req_stmt->bind_param("i", $req_id);
        $req_stmt->execute();
        $req_result = $req_stmt->get_result();
        
        if ($req_result && $req_result->num_rows > 0) {
            $req_data = $req_result->fetch_assoc();
            $emp_id = $req_data['employee_id'];
            $att_date = $req_data['date'];
            $new_in = $req_data['corrected_punch_in'];
            $new_out = $req_data['corrected_punch_out'];
            
            // Format for calculation
            $in_time = strtotime($new_in);
            $out_time = strtotime($new_out);
            
            $total_hours = 0;
            if ($in_time && $out_time) {
                $total_hours = round(abs($out_time - $in_time) / 3600, 2);
            }

            // Update the attendance table using prepared statements
            $check_att_stmt = $conn->prepare("SELECT id FROM attendance WHERE employee_id = ? AND date = ?");
            $check_att_stmt->bind_param("is", $emp_id, $att_date);
            $check_att_stmt->execute();
            $check_res = $check_att_stmt->get_result();
            
            $success = false;
            // Use REPLACE or UPDATE. Logic: If exists, update. If not, insert.
            if ($check_res && $check_res->num_rows > 0) {
                $att_row = $check_res->fetch_assoc();
                $att_id = $att_row['id'];
                $update_att_stmt = $conn->prepare("UPDATE attendance SET punch_in = ?, punch_out = ?, total_hours = ?, status = 'present' WHERE id = ?");
                $update_att_stmt->bind_param("ssdi", $new_in, $new_out, $total_hours, $att_id);
                $success = $update_att_stmt->execute();
                $update_att_stmt->close();
            } else {
                $insert_att_stmt = $conn->prepare("INSERT INTO attendance (employee_id, date, punch_in, punch_out, total_hours, status) VALUES (?, ?, ?, ?, ?, 'present')");
                $insert_att_stmt->bind_param("isssd", $emp_id, $att_date, $new_in, $new_out, $total_hours);
                $success = $insert_att_stmt->execute();
                $insert_att_stmt->close();
            }
            $check_att_stmt->close();
            
            if ($success) {
                $status_stmt = $conn->prepare("UPDATE attendance_corrections SET status = 'approved' WHERE id = ?");
                $status_stmt->bind_param("i", $req_id);
                $status_stmt->execute();
                $status_stmt->close();
                $_SESSION['message'] = "Attendance correction approved and updated.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error updating attendance record.";
                $_SESSION['message_type'] = "danger";
            }
        } else {
            $_SESSION['message'] = "Request not found.";
            $_SESSION['message_type'] = "danger";
        }
    } elseif ($action === 'reject') {
         $status_stmt = $conn->prepare("UPDATE attendance_corrections SET status = 'rejected' WHERE id = ?");
         $status_stmt->bind_param("i", $req_id);
         $status_stmt->execute();
         $status_stmt->close();
         $_SESSION['message'] = "Attendance correction rejected.";
         $_SESSION['message_type'] = "warning";
    }
    header("Location: dashboard.php#attendance-approval");
    exit();
}

// Handle Payroll Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_run'])) {
        // Verify CSRF Token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message'] = "Security token mismatch.";
            $_SESSION['message_type'] = "danger";
            header("Location: dashboard.php");
            exit();
        }
        $month = intval($_POST['month']);
        $year = intval($_POST['year']);
        $run_id = $payrollController->initiateRun($month, $year);
        if ($run_id) {
            $_SESSION['message'] = "Payroll run created successfully for " . date('F', mktime(0, 0, 0, $month, 10)) . " $year.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: Payroll run already exists or could not be created.";
            $_SESSION['message_type'] = "danger";
        }
        header("Location: dashboard.php#payroll");
        exit();
    }

    if (isset($_POST['process_run'])) {
        // Verify CSRF Token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message'] = "Security token mismatch.";
            $_SESSION['message_type'] = "danger";
            header("Location: dashboard.php");
            exit();
        }
        $run_id = intval($_POST['run_id']);
        $level = $_POST['designation_level'] ?? null;
        if ($payrollController->processRun($run_id, $level)) {
            $_SESSION['message'] = "Payroll processing completed" . ($level ? " for $level level" : "") . ". You can now review the payslips.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error processing payroll.";
            $_SESSION['message_type'] = "danger";
        }
        header("Location: dashboard.php#payroll");
        exit();
    }

    if (isset($_POST['finalize_run'])) {
        // Verify CSRF Token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message'] = "Security token mismatch.";
            $_SESSION['message_type'] = "danger";
            header("Location: dashboard.php");
            exit();
        }
        $run_id = intval($_POST['run_id']);
        if ($payrollController->finalizeRun($run_id)) {
            $_SESSION['message'] = "Payroll run finalized and locked.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error finalizing payroll.";
            $_SESSION['message_type'] = "danger";
        }
        header("Location: dashboard.php#payslips");
        exit();
    }

    // Handle Expense Claim Action (Approve/Reject)
    if (isset($_POST['approve_expense']) || isset($_POST['reject_expense'])) {
        // Verify CSRF Token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message'] = "Security token mismatch.";
            $_SESSION['message_type'] = "danger";
            header("Location: dashboard.php");
            exit();
        }
        $claim_id = intval($_POST['claim_id']);
        $status = isset($_POST['approve_expense']) ? 'approved' : 'rejected';
        $approved_by = $_SESSION['user_id'] ?? null;
        
        $update_expense_sql = "UPDATE expense_claims SET status = ?, approved_by = ?, approved_at = NOW() WHERE id = ?";
        $stmt = $conn->prepare($update_expense_sql);
        $stmt->bind_param("sii", $status, $approved_by, $claim_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Expense claim " . ucfirst($status) . " successfully!";
            $_SESSION['message_type'] = $status == 'approved' ? "success" : "warning";
        } else {
            $_SESSION['message'] = "Error updating expense status: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        header("Location: dashboard.php#expenses");
        exit();
    }

    // Handle New Asset
    if (isset($_POST['add_asset'])) {
        // Verify CSRF Token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message'] = "Security token mismatch.";
            $_SESSION['message_type'] = "danger";
            header("Location: dashboard.php");
            exit();
        }
        $asset_code = trim($_POST['asset_code']);
        $name = trim($_POST['name']);
        $category = $_POST['category'] ?? 'Other';
        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $serial = trim($_POST['serial_number'] ?? '');
        $p_date = !empty($_POST['purchase_date']) ? $_POST['purchase_date'] : null;
        $w_date = !empty($_POST['warranty_expiry']) ? $_POST['warranty_expiry'] : null;
        $location = trim($_POST['location'] ?? '');
        
        $sql = "INSERT INTO assets (asset_code, name, category, brand, model, serial_number, purchase_date, warranty_expiry, location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssssss", $asset_code, $name, $category, $brand, $model, $serial, $p_date, $w_date, $location);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Asset '$name' added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding asset: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        header("Location: dashboard.php#assets");
        exit();
    }

    // Handle Asset Assignment
    if (isset($_POST['assign_asset'])) {
        // Verify CSRF Token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message'] = "Security token mismatch.";
            $_SESSION['message_type'] = "danger";
            header("Location: dashboard.php");
            exit();
        }
        $asset_id = intval($_POST['asset_id']);
        $employee_id = intval($_POST['employee_id']);
        $asg_date = !empty($_POST['assigned_date']) ? $_POST['assigned_date'] : date('Y-m-d');
        $condition = trim($_POST['condition'] ?? '');
        
        $conn->begin_transaction();
        try {
            // Add assignment
            $ins_sql = "INSERT INTO asset_assignments (asset_id, employee_id, assigned_date, condition_at_assignment, status) VALUES (?, ?, ?, ?, 'assigned')";
            $stmt = $conn->prepare($ins_sql);
            $stmt->bind_param("iiss", $asset_id, $employee_id, $asg_date, $condition);
            $stmt->execute();
            
            // Update asset status
            $conn->query("UPDATE assets SET status = 'assigned' WHERE id = $asset_id");
            
            $conn->commit();
            $_SESSION['message'] = "Asset assigned successfully!";
            $_SESSION['message_type'] = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['message'] = "Error assigning asset: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
        header("Location: dashboard.php#assets");
        exit();
    }

    // Handle New Training Program
    if (isset($_POST['add_program'])) {
        // Verify CSRF Token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message'] = "Security token mismatch.";
            $_SESSION['message_type'] = "danger";
            header("Location: dashboard.php");
            exit();
        }
        $name = trim($_POST['name']);
        $description = trim($_POST['description'] ?? '');
        $duration = intval($_POST['duration'] ?? 0);
        $trainer = trim($_POST['trainer'] ?? '');
        $capacity = intval($_POST['capacity'] ?? 0);
        $s_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $e_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        
        $sql = "INSERT INTO training_programs (name, description, duration, trainer, capacity, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisiss", $name, $description, $duration, $trainer, $capacity, $s_date, $e_date);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Training Program '$name' created successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error creating program: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        header("Location: dashboard.php#training");
        exit();
    }

    // Handle Employee Enrollment
    if (isset($_POST['enroll_employee'])) {
        // Verify CSRF Token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            $_SESSION['message'] = "Security token mismatch.";
            $_SESSION['message_type'] = "danger";
            header("Location: dashboard.php");
            exit();
        }
        $prog_id = intval($_POST['program_id']);
        $emp_id = intval($_POST['employee_id']);
        $enr_date = date('Y-m-d');
        
        // Check if already enrolled
        $check = $conn->query("SELECT id FROM employee_trainings WHERE employee_id = $emp_id AND training_program_id = $prog_id");
        if ($check->num_rows > 0) {
            $_SESSION['message'] = "Employee is already enrolled in this program.";
            $_SESSION['message_type'] = "warning";
        } else {
            $sql = "INSERT INTO employee_trainings (employee_id, training_program_id, enrollment_date, status) VALUES (?, ?, ?, 'enrolled')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iis", $emp_id, $prog_id, $enr_date);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Employee enrolled successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error enrolling employee: " . $stmt->error;
                $_SESSION['message_type'] = "danger";
            }
        }
        header("Location: dashboard.php#training");
        exit();
    }

    // Handle Assign Employee Deduction
    if (isset($_POST['assign_deduction'])) {
        $emp_id = intval($_POST['employee_id']);
        $ded_id = intval($_POST['deduction_id']);
        $amount = floatval($_POST['amount']);
        $from = $_POST['effective_from'];
        $to = !empty($_POST['effective_to']) ? $_POST['effective_to'] : null;

        $sql = "INSERT INTO employee_deductions (employee_id, deduction_id, amount, effective_from, effective_to, is_active) VALUES (?, ?, ?, ?, ?, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iidss", $emp_id, $ded_id, $amount, $from, $to);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Deduction assigned successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error assigning deduction: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        header("Location: dashboard.php#deduction-management");
        exit();
    }

    // Handle Edit Employee Deduction Assignment
    if (isset($_POST['edit_assignment'])) {
        $id = intval($_POST['assignment_id']);
        $amount = floatval($_POST['amount']);
        $from = $_POST['effective_from'];
        $to = !empty($_POST['effective_to']) ? $_POST['effective_to'] : null;
        $active = isset($_POST['active']) ? 1 : 0;

        $sql = "UPDATE employee_deductions SET amount = ?, effective_from = ?, effective_to = ?, is_active = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("dssii", $amount, $from, $to, $active, $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Assignment updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating assignment: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        header("Location: dashboard.php#deduction-management");
        exit();
    }
}

// Handle Delete Employee Deduction Assignment
if (isset($_GET['delete_assignment'])) {
    $id = intval($_GET['delete_assignment']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM employee_deductions WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Assignment removed successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error removing assignment: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#deduction-management");
    exit();
}

// Handle Create Payroll Run
if (isset($_POST['create_run'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $month = intval($_POST['month']);
    $year = intval($_POST['year']);
    $run_id = $payrollController->initiateRun($month, $year);
    if ($run_id) {
        $_SESSION['message'] = "Payroll run created successfully for " . date('F', mktime(0, 0, 0, $month, 10)) . " $year.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: Payroll run already exists or could not be created.";
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php#payroll");
    exit();
}

// Handle Process Payroll Run
if (isset($_POST['process_run'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $run_id = intval($_POST['run_id']);
    $level = $_POST['designation_level'] ?? null;
    if ($payrollController->processRun($run_id, $level)) {
        $_SESSION['message'] = "Payroll processing completed" . ($level ? " for $level level" : "") . ". You can now review the payslips.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error processing payroll.";
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php?run_id=$run_id#payroll");
    exit();
}

// Handle Finalize Payroll Run
if (isset($_POST['finalize_run'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $run_id = intval($_POST['run_id']);
    if ($payrollController->finalizeRun($run_id)) {
        $_SESSION['message'] = "Payroll run finalized and submitted for approval.";
        $_SESSION['message_type'] = "success";
        
        // Redirect to Admin dashboard if user is also an Admin
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 2) {
            header("Location: " . BASE_URL . "modules/admin/dashboard.php#payroll-approvals");
            exit();
        }
    } else {
        $_SESSION['message'] = "Error finalizing payroll.";
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php?run_id=$run_id#payroll");
    exit();
}

// Handle Update Payslip
if (isset($_POST['update_payslip'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $payslip_id = intval($_POST['payslip_id']);
    $run_id = intval($_POST['run_id']);
    
    // Fetch current payslip to get basic and existing breakdown
    $stmt = $conn->prepare("SELECT basic_salary, breakdown FROM payslips WHERE id = ?");
    $stmt->bind_param("i", $payslip_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $ps_current = $res->fetch_assoc();
    $basic = $ps_current['basic_salary'];
    $old_breakdown = json_decode($ps_current['breakdown'], true);

    $total_allowances = floatval($_POST['total_allowances']);
    $total_deductions = floatval($_POST['total_deductions']);
    $net_salary = $basic + $total_allowances - $total_deductions;

    // Update breakdown JSON to reflect overrides
    $new_breakdown = $old_breakdown;
    if (!isset($new_breakdown['attendance_summary'])) $new_breakdown['attendance_summary'] = [];
    $new_breakdown['attendance_summary']['effective_absent_total'] = floatval($_POST['total_absent']);
    $new_breakdown['attendance_summary']['total_leave'] = intval($_POST['total_leave']);
    $new_breakdown['attendance_summary']['total_late'] = intval($_POST['total_late']);
    $new_breakdown['attendance_summary']['half_days'] = intval($_POST['half_days']);
    
    if (!isset($new_breakdown['summary'])) $new_breakdown['summary'] = [];
    $new_breakdown['summary']['total_allowances'] = $total_allowances;
    $new_breakdown['summary']['total_deductions'] = $total_deductions;
    $new_breakdown['summary']['net'] = $net_salary;
    $new_breakdown['manual_adjustment'] = true;

    $data = [
        'total_leave' => intval($_POST['total_leave']),
        'total_absent' => floatval($_POST['total_absent']),
        'total_late' => intval($_POST['total_late']),
        'half_days' => intval($_POST['half_days']),
        'total_allowances' => $total_allowances,
        'total_deductions' => $total_deductions,
        'net_salary' => $net_salary,
        'breakdown' => json_encode($new_breakdown)
    ];

    if ($payrollModel->updatePayslipData($payslip_id, $data)) {
        $_SESSION['message'] = "Payslip updated successfully. Manual adjustment recorded.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating payslip.";
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php?run_id=$run_id#payroll");
    exit();
}

// Handle Report Export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_report'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $report_year = $_POST['report_year'] ?? date('Y');
    $report_month = $_POST['report_month'] ?? '';
    $report_search = $_POST['report_search'] ?? '';
    $format = $_POST['export_format'] ?? 'csv';

    $exp_query = "SELECT la.id, e.employee_code, e.first_name, e.last_name, lt.name as leave_type, 
                         la.start_date, la.end_date, la.total_days, la.reason, la.status
                  FROM leave_applications la
                  JOIN employees e ON la.employee_id = e.id
                  LEFT JOIN leave_types lt ON la.leave_type_id = lt.id
                  WHERE YEAR(la.start_date) = ?";
    
    $params = [$report_year];
    $types = "s";

    if (!empty($report_month)) {
        $exp_query .= " AND MONTH(la.start_date) = ?";
        $params[] = $report_month;
        $types .= "s";
    }
    
    if (!empty($report_search)) {
        $exp_query .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_code LIKE ?)";
        $search_val = "%$report_search%";
        $params[] = $search_val;
        $params[] = $search_val;
        $params[] = $search_val;
        $types .= "sss";
    }
    
    $exp_query .= " ORDER BY la.start_date DESC";
    $exp_stmt = $conn->prepare($exp_query);
    $exp_stmt->bind_param($types, ...$params);
    $exp_stmt->execute();
    $exp_result = $exp_stmt->get_result();
    $filename = 'leave_report_' . date('Ymd_His');

    if ($format === 'excel') {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename.xls\"");
        
        echo "<table border='1'>";
        echo "<tr><th>ID</th><th>Employee Code</th><th>First Name</th><th>Last Name</th><th>Leave Type</th><th>Start Date</th><th>End Date</th><th>Days</th><th>Reason</th><th>Status</th></tr>";
        if ($exp_result && $exp_result->num_rows > 0) {
            while ($row = $exp_result->fetch_assoc()) {
                echo "<tr>";
                foreach ($row as $cell) {
                    echo "<td>" . htmlspecialchars($cell ?? '') . "</td>";
                }
                echo "</tr>";
            }
        }
        echo "</table>";
    } else {
        // Default CSV
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"$filename.csv\"");

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Employee Code', 'First Name', 'Last Name', 'Leave Type', 'Start Date', 'End Date', 'Days', 'Reason', 'Status']);

        if ($exp_result && $exp_result->num_rows > 0) {
            while ($row = $exp_result->fetch_assoc()) {
                fputcsv($output, $row);
            }
        }
        fclose($output);
    }
    exit();
}

// Handle Attendance Export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_attendance'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $att_year = $_POST['att_year'] ?? date('Y');
    $att_month = $_POST['att_month'] ?? '';
    $att_search = $_POST['att_search'] ?? '';
    $format = $_POST['export_format'] ?? 'csv';

    $exp_query = "SELECT e.employee_code, e.first_name, e.last_name, 
                         a.date, a.punch_in, a.punch_out, a.total_hours, a.status
                  FROM attendance a
                  JOIN employees e ON a.employee_id = e.id
                  WHERE YEAR(a.date) = ?";
    
    $params = [$att_year];
    $types = "s";

    if (!empty($att_month)) {
        $exp_query .= " AND MONTH(a.date) = ?";
        $params[] = $att_month;
        $types .= "s";
    }
    
    if (!empty($att_search)) {
        $exp_query .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_code LIKE ?)";
        $search_val = "%$att_search%";
        $params[] = $search_val;
        $params[] = $search_val;
        $params[] = $search_val;
        $types .= "sss";
    }
    
    $exp_query .= " ORDER BY a.date DESC, e.first_name ASC";
    $exp_stmt = $conn->prepare($exp_query);
    $exp_stmt->bind_param($types, ...$params);
    $exp_stmt->execute();
    $exp_result = $exp_stmt->get_result();
    $filename = 'attendance_report_' . date('Ymd_His');

    if ($format === 'excel') {
        header("Content-Type: application/vnd.ms-excel");
        header("Content-Disposition: attachment; filename=\"$filename.xls\"");
        
        echo "<table border='1'>";
        echo "<tr><th>Employee Code</th><th>Name</th><th>Date</th><th>Punch In</th><th>Punch Out</th><th>Total Hours</th><th>Status</th></tr>";
        if ($exp_result && $exp_result->num_rows > 0) {
            while ($row = $exp_result->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['employee_code']) . "</td>";
                echo "<td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>";
                echo "<td>" . $row['date'] . "</td>";
                echo "<td>" . ($row['punch_in'] ? date('h:i A', strtotime($row['punch_in'])) : '-') . "</td>";
                echo "<td>" . ($row['punch_out'] ? date('h:i A', strtotime($row['punch_out'])) : '-') . "</td>";
                echo "<td>" . ($row['total_hours'] ? $row['total_hours'] : '0') . "</td>";
                echo "<td>" . ucfirst($row['status']) . "</td>";
                echo "</tr>";
            }
        }
        echo "</table>";
    } else {
        // Default CSV
        header('Content-Type: text/csv');
        header("Content-Disposition: attachment; filename=\"$filename.csv\"");

        $output = fopen('php://output', 'w');
        fputcsv($output, ['Employee Code', 'Name', 'Date', 'Punch In', 'Punch Out', 'Total Hours', 'Status']);

        if ($exp_result && $exp_result->num_rows > 0) {
            while ($row = $exp_result->fetch_assoc()) {
                $csv_row = [
                    $row['employee_code'],
                    $row['first_name'] . ' ' . $row['last_name'],
                    $row['date'],
                    $row['punch_in'] ? date('h:i A', strtotime($row['punch_in'])) : '-',
                    $row['punch_out'] ? date('h:i A', strtotime($row['punch_out'])) : '-',
                    $row['total_hours'] ? $row['total_hours'] : '0',
                    ucfirst($row['status'])
                ];
                fputcsv($output, $csv_row);
            }
        }
        fclose($output);
    }
    exit();
}

// Handle New Job Requisition
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_requisition'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $title = trim($_POST['title']);
    $dept_id = intval($_POST['department_id']);
    $positions = intval($_POST['positions']);
    $description = trim($_POST['description']);
    $experience = trim($_POST['experience'] ?? '');
    $salary_range = trim($_POST['salary_range'] ?? '');
    $joining_date = !empty($_POST['expected_joining_date']) ? $_POST['expected_joining_date'] : null;
    $hiring_reason = trim($_POST['hiring_reason'] ?? '');
    $creator_id = 1; // Default or fetch from session if available

    $stmt = $conn->prepare("INSERT INTO job_requisitions (title, department_id, positions, description, requirements, experience, salary_range, expected_joining_date, hiring_reason, created_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("siissssssi", $title, $dept_id, $positions, $description, $requirements, $experience, $salary_range, $joining_date, $hiring_reason, $creator_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Job requisition created successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php#job-requisition");
    exit();
}

// Handle Update Requisition Status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_requisition_status'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $req_id = intval($_POST['requisition_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE job_requisitions SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $req_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Requisition status updated!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php#job-requisition");
    exit();
}

// Handle New Onboarding
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_onboarding'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $emp_id = intval($_POST['employee_id']);
    $orient_date = $_POST['orientation_date'];
    $tasks = json_encode([
        ['task' => 'IT Setup', 'status' => 'pending'],
        ['task' => 'HR Orientation', 'status' => 'pending'],
        ['task' => 'ID Card Issue', 'status' => 'pending']
    ]);

    $stmt = $conn->prepare("INSERT INTO onboarding (employee_id, orientation_date, checklist_tasks) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $emp_id, $orient_date, $tasks);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Onboarding process initiated!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php#onboarding");
    exit();
}

// Handle Update Onboarding
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_onboarding'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $on_id = intval($_POST['onboarding_id']);
    $status = $_POST['status'];
    $asset_status = $_POST['asset_assignment_status'];

    $stmt = $conn->prepare("UPDATE onboarding SET status = ?, asset_assignment_status = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $asset_status, $on_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Onboarding updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php#onboarding");
    exit();
}

// Handle New Offboarding
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_offboarding'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $emp_id = intval($_POST['employee_id']);
    $resign_date = $_POST['resignation_date'];
    $last_day = $_POST['last_working_day'];
    $reason = trim($_POST['exit_reason']);
    $clearance = json_encode([
        ['item' => 'IT Assets Return', 'status' => 'pending'],
        ['item' => 'ID Card Return', 'status' => 'pending'],
        ['item' => 'Keys Return', 'status' => 'pending']
    ]);

    $stmt = $conn->prepare("INSERT INTO offboarding (employee_id, resignation_date, last_working_day, exit_reason, clearance_checklist) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $emp_id, $resign_date, $last_day, $reason, $clearance);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Offboarding process initiated!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php#offboarding");
    exit();
}

// Handle Generic Reports Export
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['export_generic_report'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $report_type = $_POST['report_type'] ?? '';
    $format = $_POST['export_format'] ?? 'csv';
    $filename = $report_type . '_report_' . date('Ymd_His');
    
    $query = "";
    $headers = [];
    
    switch ($report_type) {
        case 'headcount':
            $query = "SELECT e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as full_name, 
                             d.name as department, des.name as designation, e.employment_status, e.joining_date
                      FROM employees e
                      LEFT JOIN departments d ON e.department_id = d.id
                      LEFT JOIN designations des ON e.designation_id = des.id
                      ORDER BY e.employee_code ASC";
            $headers = ['Emp Code', 'Full Name', 'Department', 'Designation', 'Status', 'Joining Date'];
            break;
            
        case 'department':
            $query = "SELECT d.name as department, COUNT(e.id) as total_employees
                      FROM departments d
                      LEFT JOIN employees e ON d.id = e.department_id AND e.employment_status = 'active'
                      GROUP BY d.id
                      ORDER BY total_employees DESC";
            $headers = ['Department', 'Active Employees'];
            break;
            
        case 'payroll':
            $query = "SELECT e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as full_name,
                             p.net_salary, p.payment_date, p.payment_status
                      FROM payslips p
                      JOIN employees e ON p.employee_id = e.id
                      ORDER BY p.payment_date DESC";
            $headers = ['Emp Code', 'Full Name', 'Net Salary', 'Payment Date', 'Status'];
            break;
            
        case 'recruitment':
            $query = "SELECT c.first_name, c.last_name, c.email, jr.title as job_title, ca.status as application_status
                      FROM candidate_applications ca
                      JOIN candidates c ON ca.candidate_id = c.id
                      JOIN job_requisitions jr ON ca.job_requisition_id = jr.id
                      ORDER BY ca.created_at DESC";
            $headers = ['First Name', 'Last Name', 'Email', 'Job Title', 'Status'];
            break;
            
        case 'movement':
            $query = "SELECT e.employee_code, CONCAT(e.first_name, ' ', e.last_name) as full_name,
                             'New Hire' as movement_type, e.joining_date as date
                      FROM employees e
                      WHERE e.joining_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
                      ORDER BY e.joining_date DESC";
            $headers = ['Emp Code', 'Full Name', 'Movement Type', 'Date'];
            break;
    }
    
    if (!empty($query)) {
        $result = $conn->query($query);
        
        if ($format === 'excel') {
            header("Content-Type: application/vnd.ms-excel");
            header("Content-Disposition: attachment; filename=\"$filename.xls\"");
            echo "<table border='1'><tr>";
            foreach($headers as $h) echo "<th>" . htmlspecialchars($h) . "</th>";
            echo "</tr>";
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    foreach ($row as $cell) echo "<td>" . htmlspecialchars($cell ?? '') . "</td>";
                    echo "</tr>";
                }
            }
            echo "</table>";
        } else {
            header('Content-Type: text/csv');
            header("Content-Disposition: attachment; filename=\"$filename.csv\"");
            $output = fopen('php://output', 'w');
            fputcsv($output, $headers);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    fputcsv($output, $row);
                }
            }
            fclose($output);
        }
        exit();
    }
}

// Handle New Performance Appraisal
if (isset($_POST['add_appraisal'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $emp_id = intval($_POST['employee_id']);
    $cycle_id = intval($_POST['cycle_id']);
    $reviewer_id = $_SESSION['user_id']; 
    
    $check_stmt = $conn->prepare("SELECT id FROM performance_reviews WHERE employee_id = ? AND appraisal_cycle_id = ?");
    $check_stmt->bind_param("ii", $emp_id, $cycle_id);
    $check_stmt->execute();
    if ($check_stmt->get_result()->num_rows > 0) {
        $_SESSION['message'] = "An appraisal for this employee in this cycle already exists.";
        $_SESSION['message_type'] = "warning";
    } else {
        $stmt = $conn->prepare("INSERT INTO performance_reviews (employee_id, appraisal_cycle_id, reviewer_id, status) VALUES (?, ?, ?, 'pending')");
        $stmt->bind_param("iii", $emp_id, $cycle_id, $reviewer_id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "New appraisal initiated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error initiating appraisal: " . $stmt->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    $check_stmt->close();
    header("Location: dashboard.php#performance");
    exit();
}

// Handle Update Performance Appraisal (Finalize)
if (isset($_POST['update_appraisal'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }
    $rev_id = intval($_POST['review_id']);
    $m_rating = floatval($_POST['manager_rating']);
    $f_rating = floatval($_POST['final_rating']);
    $comments = trim($_POST['comments']);
    $status = $_POST['status'] ?? 'finalized';
    
    $stmt = $conn->prepare("UPDATE performance_reviews SET manager_rating = ?, final_rating = ?, comments = ?, status = ? WHERE id = ?");
    $stmt->bind_param("ddssi", $m_rating, $f_rating, $comments, $status, $rev_id);
    
    if ($stmt->execute()) {
        $_SESSION['message'] = "Appraisal updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating appraisal: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php#performance");
    exit();
}

// Fetch departments and designations for the form
$dept_result = $conn->query("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name ASC");
$desig_result = $conn->query("SELECT id, name FROM designations WHERE is_active = 1 ORDER BY name ASC");
$tl_result = $conn->query("SELECT e.id, e.first_name, e.last_name, e.employee_code 
                           FROM employees e 
                           JOIN users u ON e.email = u.email 
                           WHERE u.role_id = 4 AND e.employment_status = 'active'
                           ORDER BY e.first_name ASC");

// Fetch all employees for management and status
$employees_list_query = "SELECT e.*, d.name as dept_name, des.name as desig_name, 
                         CONCAT(m.first_name, ' ', m.last_name) as manager_name
                         FROM employees e 
                         LEFT JOIN departments d ON e.department_id = d.id 
                         LEFT JOIN designations des ON e.designation_id = des.id 
                         LEFT JOIN employees m ON e.team_leader_id = m.id
                         ORDER BY e.first_name ASC";
$employees_list_result = $conn->query($employees_list_query);

// For status management specifically (if different query needed, otherwise reuse)
$status_emp_result = $conn->query("SELECT e.* FROM employees e ORDER BY e.first_name ASC"); 

// Fetch all documents
$docs_query = "SELECT ed.*, e.first_name, e.last_name, e.employee_code 
               FROM employee_documents ed 
               JOIN employees e ON ed.employee_id = e.id 
               ORDER BY ed.uploaded_at DESC";
$docs_result = $conn->query($docs_query);

// Fetch Leave Applications (Employees 'referred', 'approved', 'rejected' OR Team Leaders All)
$leave_apps_query = "SELECT la.*, e.first_name, e.last_name, e.employee_code, u.role_id, lt.name as leave_type 
                     FROM leave_applications la
                     JOIN employees e ON la.employee_id = e.id
                     LEFT JOIN users u ON e.email = u.email
                     LEFT JOIN leave_types lt ON la.leave_type_id = lt.id
                     WHERE 
                        (u.role_id = 4) 
                        OR 
                        (la.status IN ('referred', 'approved', 'rejected'))
                     ORDER BY 
                        CASE WHEN la.status IN ('pending', 'referred') THEN 0 ELSE 1 END,
                        la.id DESC";
$leave_apps_result = $conn->query($leave_apps_query);

// Fetch Attendance Correction Requests (All for history)
$correction_query = "SELECT ac.*, e.first_name, e.last_name, e.employee_code, u.role_id 
                     FROM attendance_corrections ac
                     JOIN employees e ON ac.employee_id = e.id
                     LEFT JOIN users u ON e.email = u.email
                     WHERE (u.role_id = 4) OR (ac.status != 'pending')
                     ORDER BY FIELD(ac.status, 'pending', 'referred', 'approved', 'rejected'), ac.id DESC";
$correction_result = $conn->query($correction_query);

// Fetch Leave Report Data based on filters
$filter_year = $_GET['report_year'] ?? date('Y');
$filter_month = $_GET['report_month'] ?? '';
$filter_search = $_GET['report_search'] ?? '';

$report_query = "SELECT la.*, e.first_name, e.last_name, e.employee_code, lt.name as leave_type 
                 FROM leave_applications la
                 JOIN employees e ON la.employee_id = e.id
                 LEFT JOIN leave_types lt ON la.leave_type_id = lt.id
                 WHERE YEAR(la.start_date) = ?";

$rep_params = [$filter_year];
$rep_types = "s";

if (!empty($filter_month)) {
    $report_query .= " AND MONTH(la.start_date) = ?";
    $rep_params[] = $filter_month;
    $rep_types .= "s";
}

if (!empty($filter_search)) {
    $report_query .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_code LIKE ?)";
    $s_val = "%$filter_search%";
    $rep_params[] = $s_val;
    $rep_params[] = $s_val;
    $rep_params[] = $s_val;
    $rep_types .= "sss";
}

$report_query .= " ORDER BY la.start_date DESC";
$report_stmt = $conn->prepare($report_query);
$report_stmt->bind_param($rep_types, ...$rep_params);
$report_stmt->execute();
$report_result = $report_stmt->get_result();

// Fetch Attendance Data based on filters
$att_filter_year = $_GET['att_year'] ?? date('Y');
$att_filter_month = $_GET['att_month'] ?? '';
$att_filter_search = $_GET['att_search'] ?? '';

$att_query = "SELECT a.*, e.first_name, e.last_name, e.employee_code, ac.status as correction_status 
              FROM attendance a
              JOIN employees e ON a.employee_id = e.id
              LEFT JOIN attendance_corrections ac ON a.employee_id = ac.employee_id AND a.date = ac.date AND ac.status = 'approved'
              WHERE YEAR(a.date) = ?";

$att_params = [$att_filter_year];
$att_types = "s";

if (!empty($att_filter_month)) {
    $att_query .= " AND MONTH(a.date) = ?";
    $att_params[] = $att_filter_month;
    $att_types .= "s";
}

if (!empty($att_filter_search)) {
    $att_query .= " AND (e.first_name LIKE ? OR e.last_name LIKE ? OR e.employee_code LIKE ?)";
    $as_val = "%$att_filter_search%";
    $att_params[] = $as_val;
    $att_params[] = $as_val;
    $att_params[] = $as_val;
    $att_types .= "sss";
}

$att_query .= " ORDER BY a.date DESC LIMIT 100"; 
$att_stmt = $conn->prepare($att_query);
$att_stmt->bind_param($att_types, ...$att_params);
$att_stmt->execute();
$att_result = $att_stmt->get_result();

// Fetch Job Requisitions with Department name
$requisitions_query = "SELECT jr.*, d.name as department_name, CONCAT(e.first_name, ' ', e.last_name) as requester_name 
                      FROM job_requisitions jr
                      JOIN departments d ON jr.department_id = d.id
                      LEFT JOIN employees e ON jr.created_by = e.id
                      ORDER BY jr.created_at DESC";
$requisitions_result = $conn->query($requisitions_query);

// Fetch Active Job Postings (requisitions with 'open' status)
$postings_query = "SELECT jr.*, d.name as department_name,
                  (SELECT COUNT(*) FROM candidate_applications WHERE job_requisition_id = jr.id) as applicant_count
                  FROM job_requisitions jr
                  JOIN departments d ON jr.department_id = d.id
                  WHERE jr.status = 'open'
                  ORDER BY jr.created_at DESC";
$postings_result = $conn->query($postings_query);

// Fetch Interviews
$interviews_query = "SELECT i.*, c.name as candidate_name, jr.title as job_title,
                    CONCAT(e.first_name, ' ', e.last_name) as interviewer_name
                    FROM interviews i
                    JOIN candidates c ON i.candidate_id = c.id
                    JOIN job_requisitions jr ON i.job_requisition_id = jr.id
                    LEFT JOIN employees e ON i.interviewer_id = e.id
                    ORDER BY i.scheduled_at DESC";
$interviews_result = $conn->query($interviews_query);

// Fetch Offer Letters
$offers_query = "SELECT ol.*, c.name as candidate_name, jr.title as job_title 
                FROM offer_letters ol
                JOIN candidates c ON ol.candidate_id = c.id
                JOIN job_requisitions jr ON ol.job_requisition_id = jr.id
                ORDER BY ol.generated_at DESC";
$offers_result = $conn->query($offers_query);

// Fetch Asset Data
$assets_query = "SELECT a.*, 
                 CONCAT(e.first_name, ' ', e.last_name) as assigned_to,
                 e.employee_code as emp_code
                 FROM assets a
                 LEFT JOIN asset_assignments aa ON a.id = aa.asset_id AND aa.status = 'assigned'
                 LEFT JOIN employees e ON aa.employee_id = e.id
                 ORDER BY a.asset_code ASC";
$assets_result = $conn->query($assets_query);

// Fetch Onboarding Cases
$onboarding_query = "SELECT o.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name, e.hire_date, e.probation_end_date
                    FROM onboarding o
                    JOIN employees e ON o.employee_id = e.id
                    ORDER BY o.created_at DESC";
$onboarding_result = $conn->query($onboarding_query);

// Fetch Offboarding Cases
$offboarding_query = "SELECT ob.*, CONCAT(e.first_name, ' ', e.last_name) as employee_name
                     FROM offboarding ob
                     JOIN employees e ON ob.employee_id = e.id
                     ORDER BY ob.created_at DESC";
$offboarding_result = $conn->query($offboarding_query);

// Fetch Training Programs with participant count
$training_query = "SELECT tp.*, 
                  (SELECT COUNT(*) FROM employee_trainings WHERE training_program_id = tp.id) as participant_count
                  FROM training_programs tp
                  ORDER BY tp.start_date DESC";
$training_result = $conn->query($training_query);

// Fetch Expense Claims
$expenses_query = "SELECT ec.*, e.first_name, e.last_name, e.employee_code, cat.name as category_name 
                  FROM expense_claims ec
                  JOIN employees e ON ec.employee_id = e.id
                  JOIN expense_categories cat ON ec.category_id = cat.id
                  ORDER BY ec.claim_date DESC";
$expenses_result = $conn->query($expenses_query);

// Fetch active Appraisal Cycles
$cycles_result = $conn->query("SELECT id, name FROM appraisal_cycles WHERE status = 'active' ORDER BY created_at DESC");

// Dashboard KPI Data
$total_employees = $conn->query("SELECT COUNT(*) as count FROM employees")->fetch_assoc()['count'] ?? 0;
$on_leave_today = $conn->query("SELECT COUNT(*) as count FROM leave_applications WHERE status = 'approved' AND CURDATE() BETWEEN start_date AND end_date")->fetch_assoc()['count'] ?? 0;
$pending_leaves = $conn->query("SELECT COUNT(*) as count FROM leave_applications WHERE status IN ('pending', 'referred')")->fetch_assoc()['count'] ?? 0;
$pending_corrections = $conn->query("SELECT COUNT(*) as count FROM attendance_corrections WHERE status IN ('pending', 'referred')")->fetch_assoc()['count'] ?? 0;
$total_pending_approvals = $pending_leaves + $pending_corrections;
$pending_expenses = $conn->query("SELECT COUNT(*) as count FROM expense_claims WHERE status = 'submitted'")->fetch_assoc()['count'] ?? 0;
$open_requisitions = $conn->query("SELECT COUNT(*) as count FROM job_requisitions WHERE status = 'open'")->fetch_assoc()['count'] ?? 0;
$total_candidates = $conn->query("SELECT COUNT(*) as count FROM candidates")->fetch_assoc()['count'] ?? 0;

// Recent Activities combined
$recent_activities_query = "
    (SELECT 'Leave Application' as type, CONCAT(e.first_name, ' ', e.last_name) as name, la.start_date as date, la.status, la.id as sort_col 
     FROM leave_applications la 
     JOIN employees e ON la.employee_id = e.id)
    UNION ALL
    (SELECT 'Attendance Correction' as type, CONCAT(e.first_name, ' ', e.last_name) as name, ac.date, ac.status, ac.id as sort_col 
     FROM attendance_corrections ac 
     JOIN employees e ON ac.employee_id = e.id)
    UNION ALL
    (SELECT 'Document Upload' as type, CONCAT(e.first_name, ' ', e.last_name) as name, ed.uploaded_at as date, ed.verification_status as status, ed.id as sort_col 
     FROM employee_documents ed 
     JOIN employees e ON ed.employee_id = e.id)
    UNION ALL
    (SELECT 'Expense Claim' as type, CONCAT(e.first_name, ' ', e.last_name) as name, ec.claim_date as date, ec.status, ec.id as sort_col 
     FROM expense_claims ec
     JOIN employees e ON ec.employee_id = e.id)
    UNION ALL
    (SELECT 'Job Requisition' as type, jr.title as name, jr.created_at as date, jr.status, jr.id as sort_col 
     FROM job_requisitions jr)
    UNION ALL
    (SELECT 'Candidate Application' as type, c.name, ca.applied_at as date, ca.status, ca.id as sort_col 
     FROM candidate_applications ca
     JOIN candidates c ON ca.candidate_id = c.id)
    UNION ALL
    (SELECT 'Onboarding' as type, CONCAT(e.first_name, ' ', e.last_name) as name, o.created_at as date, o.status, o.id as sort_col 
     FROM onboarding o
     JOIN employees e ON o.employee_id = e.id)
    UNION ALL
    (SELECT 'Offboarding' as type, CONCAT(e.first_name, ' ', e.last_name) as name, ob.created_at as date, ob.status, ob.id as sort_col 
     FROM offboarding ob
     JOIN employees e ON ob.employee_id = e.id)
    ORDER BY date DESC LIMIT 5";
$recent_activities_result = $conn->query($recent_activities_query);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Manager Dashboard - HRnexa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        :root {
            --primary-color: #1a5490;
            --secondary-color: #2874a6;
            --success-color: #27ae60;
            --danger-color: #dc3545;
            --warning-color: #f39c12;
            --info-color: #3498db;
            --light-bg: #f8f9fa;
            --sidebar-width: 280px;
        }


        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: #333;
        }

        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, #1a252f 100%);
            color: white;
            z-index: 1000;
            overflow-y: auto;
            box-shadow: 4px 0 10px rgba(0,0,0,0.1);
        }

        .sidebar-brand {
            padding: 25px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-brand h5 {
            margin: 0;
            font-weight: 700;
            font-size: 18px;
        }

        .sidebar-brand i {
            font-size: 24px;
            color: var(--secondary-color);
        }

        .sidebar-menu {
            list-style: none;
            padding: 15px 0;
        }

        .sidebar-menu .menu-section {
            padding: 15px 15px 5px;
            font-size: 11px;
            font-weight: 600;
            color: rgba(255,255,255,0.5);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 10px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 3px solid transparent;
        }

        .sidebar-menu a:hover {
            background-color: rgba(52, 152, 219, 0.1);
            color: white;
            border-left-color: var(--secondary-color);
            padding-left: 25px;
        }

        .sidebar-menu a.active {
            background-color: var(--secondary-color);
            color: white;
            border-left-color: #fff;
            font-weight: 600;
        }

        .sidebar-menu i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 0;
        }

        .topbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .topbar-left h5 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 700;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-color), var(--info-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 30px;
        }

        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-header h1 {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 0;
        }

        /* KPI Cards */
        .kpi-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid var(--secondary-color);
        }

        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .kpi-icon {
            font-size: 32px;
            color: var(--secondary-color);
            margin-bottom: 10px;
        }

        .kpi-value {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 10px 0;
        }

        .kpi-label {
            font-size: 14px;
            color: #999;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 600;
        }

        /* Section Card */
        .section-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        .section-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--secondary-color);
            font-size: 22px;
        }

        /* Data Table */
        .data-table table {
            font-size: 14px;
        }

        .data-table thead {
            background-color: var(--light-bg);
        }

        .data-table th {
            font-weight: 600;
            color: var(--primary-color);
            border: none;
            padding: 12px;
        }

        .data-table td {
            padding: 12px;
            vertical-align: middle;
        }

        .data-table tbody tr:hover {
            background-color: rgba(40, 116, 166, 0.05);
        }

        .btn-primary {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(40, 116, 166, 0.25);
        }

        .badge-custom {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #1a5490 0%, #2874a6 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .welcome-banner::after {
            content: '';
            position: absolute;
            right: -50px;
            top: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
        }

        .welcome-banner h2 {
            font-weight: 700;
            margin-bottom: 10px;
            font-size: 32px;
            position: relative;
            z-index: 1;
        }

        .welcome-banner p {
            margin: 0;
            opacity: 0.9;
            font-size: 16px;
            position: relative;
            z-index: 1;
        }

        .banner-date {
            position: absolute;
            right: 20px;
            bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.15);
            padding: 5px 15px;
            border-radius: 20px;
            backdrop-filter: blur(5px);
            z-index: 1;
        }

        .sidebar-toggle {
            display: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--primary-color);
            margin-right: 15px;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -280px;
                width: 280px;
                transition: all 0.3s ease;
            }

            .sidebar.active {
                left: 0;
            }

            .main-content {
                margin-left: 0;
            }

            .sidebar-toggle {
                display: block;
            }

            .dashboard-content {
                padding: 15px;
            }

            .welcome-banner {
                padding: 20px;
                padding-bottom: 60px;
            }

            .welcome-banner h2 {
                font-size: 22px;
            }

            .welcome-banner p {
                font-size: 14px;
            }

            .banner-date {
                right: 15px;
                bottom: 15px;
                font-size: 12px;
                padding: 4px 12px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-people-arrows"></i>
            <h5>HRnexa</h5>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-section">Main</li>
            <li><a href="#dashboard" class="nav-link active" data-section="dashboard"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
            
            <li class="menu-section">Employee Management</li>
            <li><a href="#employees" class="nav-link" data-section="employees"><i class="fas fa-users"></i><span>Employee List</span></a></li>
            <li><a href="#add-employee" class="nav-link" data-section="add-employee"><i class="fas fa-user-plus"></i><span>Add Employee</span></a></li>
            <li><a href="#documents" class="nav-link" data-section="documents"><i class="fas fa-file-upload"></i><span>Documents</span></a></li>
            <li><a href="#employment-status" class="nav-link" data-section="employment-status"><i class="fas fa-briefcase"></i><span>Employment Status</span></a></li>
            
            <li class="menu-section">Recruitment & ATS</li>
            <li><a href="#job-requisition" class="nav-link" data-section="job-requisition"><i class="fas fa-file-contract"></i><span>Job Requisition</span></a></li>
            <li><a href="#job-posting" class="nav-link" data-section="job-posting"><i class="fas fa-bullhorn"></i><span>Job Posting</span></a></li>
            <li><a href="#candidates" class="nav-link" data-section="candidates"><i class="fas fa-address-book"></i><span>Candidates</span></a></li>
            <li><a href="#interviews" class="nav-link" data-section="interviews"><i class="fas fa-video"></i><span>Interviews</span></a></li>
            <li><a href="#offers" class="nav-link" data-section="offers"><i class="fas fa-envelope-open-text"></i><span>Offer Letters</span></a></li>
            
            <li class="menu-section">Onboarding & Offboarding</li>
            <li><a href="#onboarding" class="nav-link" data-section="onboarding"><i class="fas fa-sign-in-alt"></i><span>Onboarding</span></a></li>
            <li><a href="#offboarding" class="nav-link" data-section="offboarding"><i class="fas fa-sign-out-alt"></i><span>Offboarding</span></a></li>
            
            <li class="menu-section">Attendance & Shifts</li>
            <li><a href="#attendance" class="nav-link" data-section="attendance"><i class="fas fa-clock"></i><span>Attendance</span></a></li>
            <li><a href="#attendance-approval" class="nav-link" data-section="attendance-approval"><i class="fas fa-check-circle"></i><span>Attendance Approval</span></a></li>
            <li><a href="#shift-management" class="nav-link" data-section="shift-management"><i class="fas fa-calendar-alt"></i><span>Shift Management</span></a></li>
            
            <li class="menu-section">Leave Management</li>
            <li><a href="#leave-applications" class="nav-link" data-section="leave-applications"><i class="fas fa-calendar-check"></i><span>Leave Applications</span></a></li>
            <li><a href="#leave-reports" class="nav-link" data-section="leave-reports"><i class="fas fa-chart-bar"></i><span>Leave Reports</span></a></li>
            
            <li class="menu-section">Payroll</li>
            <li><a href="#payroll" class="nav-link" data-section="payroll"><i class="fas fa-money-bill-wave"></i><span>Generate Payroll</span></a></li>
            <li><a href="#deduction-management" class="nav-link" data-section="deduction-management"><i class="fas fa-hand-holding-usd"></i><span>Deduction Management</span></a></li>
            <li><a href="#payslips" class="nav-link" data-section="payslips"><i class="fas fa-receipt"></i><span>Payslips</span></a></li>
            
            <li class="menu-section">Performance & Training</li>
            <li><a href="#performance" class="nav-link" data-section="performance"><i class="fas fa-star"></i><span>Performance</span></a></li>
            <li><a href="#training" class="nav-link" data-section="training"><i class="fas fa-graduation-cap"></i><span>Training Programs</span></a></li>
            
            <li class="menu-section">Expenses & Assets</li>
            <li><a href="#expenses" class="nav-link" data-section="expenses"><i class="fas fa-receipt"></i><span>Expenses</span></a></li>
            <li><a href="#assets" class="nav-link" data-section="assets"><i class="fas fa-cube"></i><span>Assets</span></a></li>
            
            <li class="menu-section">Reports</li>
            <li><a href="#reports" class="nav-link" data-section="reports"><i class="fas fa-chart-line"></i><span>HR Reports</span></a></li>
            
            <li class="menu-section">Account</li>
            <li><a href="../../views/auth/logout.php" class="nav-link" data-external="true"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Topbar -->
        <div class="topbar">
            <div class="topbar-left">
                <div class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </div>
                <h5 id="topbar-title">Dashboard</h5>
            </div>
            <div class="topbar-right">
                <div class="user-avatar"><?php echo strtoupper(substr($user_name, 0, 2)); ?></div>
                <div style="margin-left: 10px;">
                    <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($user_name); ?></div>
                    <div style="font-size: 12px; color: #999;">HR Manager</div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php 
                    $icon = $_SESSION['message_type'] === 'success' ? 'check-circle' : 'exclamation-circle';
                    echo "<i class='fas fa-$icon me-2'></i>" . $_SESSION['message']; 
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php 
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            endif; ?>

            <!-- Dashboard Section -->
            <div id="dashboard-section" class="section-content">
                <div class="welcome-banner">
                    <h2>Hello, <?php echo htmlspecialchars($user_name); ?>!</h2>
                    <p>Welcome back! Here's what's happening with your team today.</p>
                    <div class="banner-date">
                        <i class="far fa-calendar-alt"></i>
                        <?php echo date('l, d F Y'); ?>
                    </div>
                </div>

                <!-- Attendance & Bonus Calculator -->
                <div class="card mb-4" style="border-left: 4px solid #27ae60; background: linear-gradient(135deg, #f0f9ff 0%, #e0f7ff 100%);">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h5 style="color: #27ae60; margin: 0;"><i class="fas fa-calculator"></i> Attendance & Bonus Calculator</h5>
                                <p style="margin-top: 5px; color: #666; font-size: 14px;">Calculate monthly attendance bonuses (500 টাকা) and deductions automatically for all employees</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <button class="btn btn-success" onclick="openAttendanceCalculatorModal()">
                                    <i class="fas fa-play-circle"></i> Run Calculation
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Attendance Calculator Modal -->
                <div class="modal fade" id="attendanceCalculatorModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header" style="background-color: #27ae60; color: white;">
                                <h5 class="modal-title"><i class="fas fa-calculator"></i> Attendance Bonus & Deduction Calculator</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Select Month and Year:</label>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <select id="calcMonth" class="form-control">
                                                <option value="1" <?php echo date('m') == 1 ? 'selected' : ''; ?>>January</option>
                                                <option value="2" <?php echo date('m') == 2 ? 'selected' : ''; ?>>February</option>
                                                <option value="3" <?php echo date('m') == 3 ? 'selected' : ''; ?>>March</option>
                                                <option value="4" <?php echo date('m') == 4 ? 'selected' : ''; ?>>April</option>
                                                <option value="5" <?php echo date('m') == 5 ? 'selected' : ''; ?>>May</option>
                                                <option value="6" <?php echo date('m') == 6 ? 'selected' : ''; ?>>June</option>
                                                <option value="7" <?php echo date('m') == 7 ? 'selected' : ''; ?>>July</option>
                                                <option value="8" <?php echo date('m') == 8 ? 'selected' : ''; ?>>August</option>
                                                <option value="9" <?php echo date('m') == 9 ? 'selected' : ''; ?>>September</option>
                                                <option value="10" <?php echo date('m') == 10 ? 'selected' : ''; ?>>October</option>
                                                <option value="11" <?php echo date('m') == 11 ? 'selected' : ''; ?>>November</option>
                                                <option value="12" <?php echo date('m') == 12 ? 'selected' : ''; ?>>December</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <input type="number" id="calcYear" class="form-control" value="<?php echo date('Y'); ?>" min="2020" max="2099">
                                        </div>
                                    </div>
                                </div>
                                <div id="calcResults" style="max-height: 400px; overflow-y: auto; display: none;">
                                    <div class="alert alert-info">
                                        <div id="calcStatus"></div>
                                    </div>
                                </div>
                                <div id="calcLoading" class="text-center" style="display: none;">
                                    <div class="spinner-border text-success" role="status">
                                        <span class="visually-hidden">Processing...</span>
                                    </div>
                                    <p class="mt-2">Processing attendance records...</p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-success" id="runCalcBtn" onclick="runAttendanceCalculation()">
                                    <i class="fas fa-play"></i> Process
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fas fa-users"></i></div>
                            <div class="kpi-value"><?php echo number_format($total_employees); ?></div>
                            <div class="kpi-label">Total Employees</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fas fa-calendar-check"></i></div>
                            <div class="kpi-value"><?php echo $on_leave_today; ?></div>
                            <div class="kpi-label">On Leave Today</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fas fa-user-tie"></i></div>
                            <div class="kpi-value"><?php echo $total_pending_approvals; ?></div>
                            <div class="kpi-label">Pending Approvals</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fas fa-file-invoice-dollar"></i></div>
                            <div class="kpi-value"><?php echo $pending_expenses; ?></div>
                            <div class="kpi-label">Pending Expense Claims</div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-history"></i>
                        Recent Activities
                    </div>
                    <div class="data-table">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Activity</th>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_activities_result && $recent_activities_result->num_rows > 0): ?>
                                    <?php while($activity = $recent_activities_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($activity['type']); ?></td>
                                        <td><?php echo htmlspecialchars($activity['name']); ?></td>
                                        <td><?php echo date('d M Y', strtotime($activity['date'])); ?></td>
                                        <td>
                                            <?php 
                                                $act_badge = 'bg-info';
                                                if ($activity['status'] == 'approved' || $activity['status'] == 'verified') $act_badge = 'bg-success';
                                                if ($activity['status'] == 'pending' || $activity['status'] == 'referred') $act_badge = 'bg-warning';
                                                if ($activity['status'] == 'rejected') $act_badge = 'bg-danger';
                                            ?>
                                            <span class="badge <?php echo $act_badge; ?>"><?php echo ucfirst($activity['status']); ?></span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center">No recent activities.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <?php include 'employees.php'; ?>
            <?php include 'employee_add.php'; ?>
            <?php include 'documents.php'; ?>
            <?php include 'employment_status.php'; ?>
            <?php include 'recruitment_requisition.php'; ?>
            <?php include 'recruitment_posting.php'; ?>
            <?php include 'recruitment_candidates.php'; ?>
            <?php include 'recruitment_interviews.php'; ?>
            <?php include 'recruitment_offers.php'; ?>
            <?php include 'onboarding.php'; ?>
            <?php include 'offboarding.php'; ?>
            <?php include 'attendance.php'; ?>
            <?php include 'attendance_approval.php'; ?>
            <?php include 'attendance_shifts.php'; ?>
            <?php include 'attendance_reports.php'; ?>
            <?php include 'leave_applications.php'; ?>
            <?php include 'leave_reports.php'; ?>
            <?php include 'payroll_generate.php'; ?>
            <div id="deduction-management-section" class="section-content" style="display:none;">
                <?php include 'employee_deductions.php'; ?>
            </div>
            <?php include 'payroll_payslips.php'; ?>
            <?php include 'performance_reviews.php'; ?>
            <?php include 'training_programs.php'; ?>
            <?php include 'expenses.php'; ?>
            <?php include 'assets.php'; ?>
            <?php include 'reports_overview.php'; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navigation handler
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function (e) {
                // Allow external links (like logout) to work normally
                if (this.dataset.external === 'true') {
                    return;
                }
                
                const section = this.dataset.section;
                if (!section) return;
                
                e.preventDefault();
                switchSection(section);
            });
        });

        function setFormat(format) {
            document.getElementById('exportFormatInput').value = format;
        }

        function downloadPDF() {
            // Ensure the leave reports section is visible so html2pdf can render all rows
            const section = document.getElementById('leave-reports-section');
            let prevDisplay = null;
            if (section) {
                prevDisplay = section.style.display;
                section.style.display = 'block';
            }

            const element = document.getElementById('reportTableContainer');
            // Clone to avoid modifying the view
            const clone = element.cloneNode(true);
            
             // Add a title to the PDF
            const title = document.createElement('h3');
            title.innerText = 'Leave Report - ' + '<?php echo $filter_year; ?>';
            title.style.marginBottom = '20px';
            title.style.textAlign = 'center';
            
            const container = document.createElement('div');
            container.appendChild(title);
            container.appendChild(clone);

            // Restore previous display state
            if (section) {
                section.style.display = prevDisplay;
            }

            var opt = {
              margin:       0.5,
              filename:     'leave_report_<?php echo date('Ymd_His'); ?>.pdf',
              image:        { type: 'jpeg', quality: 0.98 },
              html2canvas:  { scale: 2 },
              jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(container).save();
        }

        function setAttFormat(format) {
            document.getElementById('attExportFormatInput').value = format;
        }

        function downloadAttPDF() {
            const element = document.getElementById('attTableContainer');
            const clone = element.cloneNode(true);
            
            const title = document.createElement('h3');
            title.innerText = 'Attendance Report - ' + '<?php echo $att_filter_year; ?>';
            title.style.marginBottom = '20px';
            title.style.textAlign = 'center';
            
            const container = document.createElement('div');
            container.appendChild(title);
            container.appendChild(clone);

            var opt = {
              margin:       0.5,
              filename:     'attendance_report_' + new Date().toISOString().slice(0,10) + '.pdf',
              image:        { type: 'jpeg', quality: 0.98 },
              html2canvas:  { scale: 2 },
              jsPDF:        { unit: 'in', format: 'letter', orientation: 'landscape' }
            };

            html2pdf().set(opt).from(container).save();
        }

        // Check URL params for report filters to keep tab open (only for form submissions)
        document.addEventListener('DOMContentLoaded', function() {
            // Check for section in URL
            const urlParams = new URLSearchParams(window.location.search);
            const hash = window.location.hash.substring(1);
            
            if (urlParams.has('report_year')) {
                switchSection('leave-reports');
            } else if (urlParams.has('att_year')) {
                switchSection('attendance');
            } else if (hash) {
                switchSection(hash);
            } else {
                switchSection('dashboard');
            }
        });

        function switchSection(section) {
            const selectedSection = document.getElementById(section + '-section');
            if (!selectedSection) return;

            // Hide all sections
            document.querySelectorAll('.section-content').forEach(el => {
                el.style.display = 'none';
            });

            // Show selected section
            selectedSection.style.display = 'block';

            // Close sidebar on mobile after clicking
            const sidebar = document.querySelector('.sidebar');
            if (window.innerWidth <= 768 && sidebar) {
                sidebar.classList.remove('active');
            }

            // Update sidebar active state
            document.querySelectorAll('.nav-link').forEach(l => {
                if (l.dataset.section === section) {
                    l.classList.add('active');
                } else {
                    l.classList.remove('active');
                }
            });

            // Update URL to reflect current section without query params (unless it's the initial load)
            // We use replaceState to avoid cluttering history, or pushState if we want back button support.
            // Using replaceState with a simple hash is safer for this SPA-like behavior.
            if (history.pushState) {
                const newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + '#' + section;
                window.history.pushState({path:newurl},'',newurl);
            }

            // Update topbar title
            const titles = {
                'dashboard': 'Dashboard',
                'employees': 'Employee List',
                'add-employee': 'Add New Employee',
                'documents': 'Employee Documents',
                'employment-status': 'Employment Status',
                'job-requisition': 'Job Requisition',
                'job-posting': 'Job Postings',
                'candidates': 'Candidate Database',
                'interviews': 'Interview Management',
                'offers': 'Offer Letters',
                'onboarding': 'Onboarding Process',
                'offboarding': 'Offboarding Process',
                'attendance': 'Attendance Management',
                'shift-management': 'Shift Management',
                'leave-applications': 'Leave Applications',
                'leave-reports': 'Leave Reports',
                'payroll': 'Payroll Management',
                'deduction-management': 'Employee Deduction Management',
                'payslips': 'Payslip Management',
                'performance': 'Performance Review',
                'training': 'Training Programs',
                'expenses': 'Expense Management',
                'assets': 'Asset Management',
                'reports': 'HR Reports',
                'attendance-approval': 'Attendance Approval'
            };
            const titleEl = document.getElementById('topbar-title');
            if (titleEl) {
                titleEl.textContent = titles[section] || 'Dashboard';
            }
        }

        // Sidebar Toggle for Mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }
        
        function viewEmployeeDetails(emp) {
            const modalBody = document.querySelector('#employeeDetailModal .modal-body');
            const content = `
                <div class="row">
                    <div class="col-md-6 mb-3"><strong>Full Name:</strong><br>${emp.first_name} ${emp.last_name}</div>
                    <div class="col-md-6 mb-3"><strong>Employee Code:</strong><br>${emp.employee_code}</div>
                    <div class="col-md-6 mb-3"><strong>Email:</strong><br>${emp.email}</div>
                    <div class="col-md-6 mb-3"><strong>Phone:</strong><br>${emp.phone || 'N/A'}</div>
                    <div class="col-md-6 mb-3"><strong>Date of Birth:</strong><br>${emp.date_of_birth || 'N/A'}</div>
                    <div class="col-md-6 mb-3"><strong>Gender:</strong><br>${emp.gender || 'N/A'}</div>
                    <div class="col-md-12 mb-3"><strong>Address:</strong><br>${emp.address || 'N/A'}</div>
                    <div class="col-md-6 mb-3"><strong>Department:</strong><br>${emp.dept_name || 'N/A'}</div>
                    <div class="col-md-6 mb-3"><strong>Designation:</strong><br>${emp.desig_name || 'N/A'}</div>
                    <div class="col-md-6 mb-3"><strong>Hire Date:</strong><br>${emp.hire_date}</div>
                    <div class="col-md-6 mb-3"><strong>Status:</strong><br><span class="badge bg-info">${emp.employment_status.toUpperCase()}</span></div>
                    <div class="col-md-6 mb-3"><strong>Manager/Leader:</strong><br>${emp.manager_name || 'Self / None'}</div>
                </div>
            `;
            modalBody.innerHTML = content;
            
            // Store current employee ID and name for allowance/deduction modals
            document.getElementById('employeeDetailModal').dataset.empId = emp.id;
            document.getElementById('employeeDetailModal').dataset.empName = emp.first_name + ' ' + emp.last_name;
            
            new bootstrap.Modal(document.getElementById('employeeDetailModal')).show();
        }

        function viewStatusUpdate(emp) {
            document.getElementById('status_emp_id').value = emp.id;
            document.getElementById('status_emp_name').textContent = emp.first_name + ' ' + emp.last_name;
            document.getElementById('update_emp_status').value = emp.employment_status;
            document.getElementById('update_prob_start').value = emp.probation_start_date || '';
            document.getElementById('update_prob_end').value = emp.probation_end_date || '';
            
            new bootstrap.Modal(document.getElementById('statusUpdateModal')).show();
        }

        // Allowance Management Functions
        function openAllowanceModal() {
            const modal = document.getElementById('employeeDetailModal');
            const empId = modal.dataset.empId;
            const empName = modal.dataset.empName;
            
            if (!empId) {
                alert('Please select an employee first');
                return;
            }
            
            document.getElementById('allowance_emp_name').textContent = empName;
            document.getElementById('allowance_select').dataset.empId = empId;
            document.getElementById('allowance_amount').value = '';
            document.getElementById('allowance_from').value = new Date().toISOString().split('T')[0];
            
            // Fetch available allowances
            loadAllowancesDropdown();
            // Fetch employee's current allowances
            loadEmployeeAllowances(empId);
            
            new bootstrap.Modal(document.getElementById('allowanceModal')).show();
        }

        function loadAllowancesDropdown() {
            fetch('process_allowance_deduction.php?action=get_allowances&csrf_token=<?php echo $_SESSION['csrf_token']; ?>', {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('allowance_select');
                select.innerHTML = '<option value="">-- Choose Allowance --</option>';
                if (data.success && data.allowances) {
                    data.allowances.forEach(alw => {
                        select.innerHTML += `<option value="${alw.id}">${alw.name} (${alw.type})</option>`;
                    });
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function loadEmployeeAllowances(empId) {
            fetch('process_allowance_deduction.php?action=get_emp_allowances&emp_id=' + empId + '&csrf_token=<?php echo $_SESSION['csrf_token']; ?>', {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('allowance_tbody');
                tbody.innerHTML = '';
                if (data.success && data.allowances && data.allowances.length > 0) {
                    data.allowances.forEach(alw => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${alw.allowance_name}</td>
                                <td>${parseFloat(alw.amount).toFixed(2)}</td>
                                <td>${alw.effective_from}</td>
                                <td>${alw.effective_to || 'Ongoing'}</td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="removeAllowance(${alw.id}, ${empId})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No allowances assigned</td></tr>';
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function addAllowanceToEmployee() {
            const modal = document.getElementById('allowanceModal');
            const empId = document.getElementById('allowance_select').dataset.empId;
            const allowanceId = document.getElementById('allowance_select').value;
            const amount = document.getElementById('allowance_amount').value;
            const fromDate = document.getElementById('allowance_from').value;

            if (!allowanceId || !amount || !fromDate) {
                alert('Please fill in all fields');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'add_allowance');
            formData.append('emp_id', empId);
            formData.append('allowance_id', allowanceId);
            formData.append('amount', amount);
            formData.append('effective_from', fromDate);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

            fetch('process_allowance_deduction.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Allowance added successfully');
                    document.getElementById('allowance_select').value = '';
                    document.getElementById('allowance_amount').value = '';
                    loadEmployeeAllowances(empId);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function removeAllowance(allowanceId, empId) {
            if (!confirm('Are you sure you want to remove this allowance?')) return;

            const formData = new FormData();
            formData.append('action', 'remove_allowance');
            formData.append('id', allowanceId);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

            fetch('process_allowance_deduction.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Allowance removed successfully');
                    loadEmployeeAllowances(empId);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        // Deduction Management Functions
        function openDeductionModal() {
            const modal = document.getElementById('employeeDetailModal');
            const empId = modal.dataset.empId;
            const empName = modal.dataset.empName;
            
            if (!empId) {
                alert('Please select an employee first');
                return;
            }
            
            document.getElementById('deduction_emp_name').textContent = empName;
            document.getElementById('deduction_select').dataset.empId = empId;
            document.getElementById('deduction_amount').value = '';
            document.getElementById('deduction_from').value = new Date().toISOString().split('T')[0];
            
            // Fetch available deductions
            loadDeductionsDropdown();
            // Fetch employee's current deductions
            loadEmployeeDeductions(empId);
            
            new bootstrap.Modal(document.getElementById('deductionModal')).show();
        }

        function loadDeductionsDropdown() {
            fetch('process_allowance_deduction.php?action=get_deductions&csrf_token=<?php echo $_SESSION['csrf_token']; ?>', {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                const select = document.getElementById('deduction_select');
                select.innerHTML = '<option value="">-- Choose Deduction --</option>';
                if (data.success && data.deductions) {
                    data.deductions.forEach(ded => {
                        select.innerHTML += `<option value="${ded.id}">${ded.name} (${ded.type})</option>`;
                    });
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function loadEmployeeDeductions(empId) {
            fetch('process_allowance_deduction.php?action=get_emp_deductions&emp_id=' + empId + '&csrf_token=<?php echo $_SESSION['csrf_token']; ?>', {
                method: 'GET'
            })
            .then(response => response.json())
            .then(data => {
                const tbody = document.getElementById('deduction_tbody');
                tbody.innerHTML = '';
                if (data.success && data.deductions && data.deductions.length > 0) {
                    data.deductions.forEach(ded => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${ded.deduction_name}</td>
                                <td>${parseFloat(ded.amount).toFixed(2)}</td>
                                <td>${ded.effective_from}</td>
                                <td>${ded.effective_to || 'Ongoing'}</td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="removeDeduction(${ded.id}, ${empId})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">No deductions assigned</td></tr>';
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function addDeductionToEmployee() {
            const modal = document.getElementById('deductionModal');
            const empId = document.getElementById('deduction_select').dataset.empId;
            const deductionId = document.getElementById('deduction_select').value;
            const amount = document.getElementById('deduction_amount').value;
            const fromDate = document.getElementById('deduction_from').value;

            if (!deductionId || !amount || !fromDate) {
                alert('Please fill in all fields');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'add_deduction');
            formData.append('emp_id', empId);
            formData.append('deduction_id', deductionId);
            formData.append('amount', amount);
            formData.append('effective_from', fromDate);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

            fetch('process_allowance_deduction.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Deduction added successfully');
                    document.getElementById('deduction_select').value = '';
                    document.getElementById('deduction_amount').value = '';
                    loadEmployeeDeductions(empId);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function removeDeduction(deductionId, empId) {
            if (!confirm('Are you sure you want to remove this deduction?')) return;

            const formData = new FormData();
            formData.append('action', 'remove_deduction');
            formData.append('id', deductionId);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

            fetch('process_allowance_deduction.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Deduction removed successfully');
                    loadEmployeeDeductions(empId);
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function openUpdateDocStatusModal(doc) {
            document.getElementById('update_doc_id').value = doc.id;
            document.getElementById('update_doc_emp_name').textContent = doc.first_name + ' ' + doc.last_name;
            document.getElementById('update_doc_type_disp').textContent = doc.document_type;
            document.getElementById('update_doc_status_val').value = doc.verification_status;
            
            new bootstrap.Modal(document.getElementById('updateDocStatusModal')).show();
        }

        function openUploadModal() {
            new bootstrap.Modal(document.getElementById('uploadDocumentModal')).show();
        }

        // Attendance Calculator Functions
        function openAttendanceCalculatorModal() {
            document.getElementById('calcResults').style.display = 'none';
            document.getElementById('calcLoading').style.display = 'none';
            document.getElementById('runCalcBtn').disabled = false;
            new bootstrap.Modal(document.getElementById('attendanceCalculatorModal')).show();
        }

        function runAttendanceCalculation() {
            const month = document.getElementById('calcMonth').value;
            const year = document.getElementById('calcYear').value;
            
            document.getElementById('calcLoading').style.display = 'block';
            document.getElementById('calcResults').style.display = 'none';
            document.getElementById('runCalcBtn').disabled = true;
            
            fetch('calculate_attendance_bonus_deduction.php?month=' + month + '&year=' + year + '&csrf_token=<?php echo $_SESSION['csrf_token']; ?>')
                .then(response => response.json())
                .then(data => {
                    document.getElementById('calcLoading').style.display = 'none';
                    document.getElementById('calcResults').style.display = 'block';
                    
                    let html = '<div class="alert alert-' + (data.success ? 'success' : 'danger') + '">';
                    html += '<h6>' + data.message + '</h6>';
                    
                    if (data.success && data.results && data.results.length > 0) {
                        html += '<div style="max-height: 300px; overflow-y: auto;">';
                        html += '<table class="table table-sm table-striped">';
                        html += '<thead><tr><th>Employee</th><th>Attendance</th><th>Bonus</th><th>Deduction</th><th>Status</th></tr></thead>';
                        html += '<tbody>';
                        
                        data.results.forEach(result => {
                            const attendanceStr = `Present: ${result.present}, Late: ${result.late}, Absent: ${result.absent}`;
                            const bonusStr = result.allowance_added ? `✓ ৳${result.allowance_amount}` : '-';
                            const deductionStr = result.deduction_added ? `✓ ৳${result.deduction_amount}` : '-';
                            const statusStr = result.message || 'Processed';
                            
                            html += `<tr>
                                <td><small>${result.emp_name}</small></td>
                                <td><small>${attendanceStr}</small></td>
                                <td><small>${bonusStr}</small></td>
                                <td><small>${deductionStr}</small></td>
                                <td><small>${statusStr}</small></td>
                            </tr>`;
                        });
                        
                        html += '</tbody></table></div>';
                    }
                    
                    html += '</div>';
                    document.getElementById('calcStatus').innerHTML = html;
                    document.getElementById('runCalcBtn').disabled = false;
                })
                .catch(error => {
                    document.getElementById('calcLoading').style.display = 'none';
                    document.getElementById('calcResults').style.display = 'block';
                    document.getElementById('calcStatus').innerHTML = '<div class="alert alert-danger">Error: ' + error.message + '</div>';
                    document.getElementById('runCalcBtn').disabled = false;
                });
        }
    </script>

</body>
</html>
