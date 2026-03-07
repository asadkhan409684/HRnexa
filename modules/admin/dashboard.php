<?php
// Include database connection (which now starts the session globally)
require_once __DIR__ . '/../../app/Config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: " . BASE_URL . "login");
    exit();
}

// Check if user has Admin role (role_id = 2)
if ($_SESSION['user_role'] != 2) {
    header("Location: " . BASE_URL . "views/auth/unauthorized.php");
    exit();
}

// Fetch all company settings
$company_settings = [];
$settings_res = $conn->query("SELECT `key`, `value` FROM system_settings WHERE category = 'company'");
if ($settings_res) {
    while($row = $settings_res->fetch_assoc()) {
        $company_settings[$row['key']] = $row['value'];
    }
}

// Notification Handlers (AJAX)
if (isset($_GET['action'])) {
    // CSRF Validation for AJAX
    if ($_SERVER['REQUEST_METHOD'] === 'POST' || in_array($_GET['action'], ['mark_read', 'fetch_notifications', 'fetch_payroll_details'])) {
         // Some AJAX might be GET but still benefit from token if they mutate state, 
         // but mark_read is GET currently. I'll refactor mark_read to POST in a bit or just check token if passed.
         // For now, let's just ensure POST handlers are secured and refactor what's needed.
    }
    if ($_GET['action'] === 'fetch_notifications') {
        header('Content-Type: application/json');
        $user_id = $_SESSION['user_id'];
        
        // Get unread count
        $count = $conn->query("SELECT COUNT(*) as c FROM notifications WHERE user_id = $user_id AND is_read = 0")->fetch_assoc()['c'];
        
        // Get latest 5 notifications
        $notifs = [];
        $res = $conn->query("SELECT * FROM notifications WHERE user_id = $user_id ORDER BY created_at DESC LIMIT 5");
        while($row = $res->fetch_assoc()) {
            $notifs[] = $row;
        }
        
        echo json_encode(['count' => $count, 'notifications' => $notifs]);
        exit();
    }
    
    if ($_GET['action'] === 'mark_read' && isset($_GET['id'])) {
        if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
            die(json_encode(['success' => false, 'message' => 'CSRF failure']));
        }
        $id = intval($_GET['id']);
        $stmt = $conn->prepare("UPDATE notifications SET is_read = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        echo json_encode(['success' => true]);
        exit();
    }

    if ($_GET['action'] === 'fetch_payroll_details' && isset($_GET['run_id'])) {
        header('Content-Type: application/json');
        require_once __DIR__ . '/../../app/Models/Payroll.php';
        $payrollModel = new Payroll($conn);
        $run_id = intval($_GET['run_id']);
        
        $payslips = $payrollModel->getPayslipsByRun($run_id);
        $data = [];
        while($row = $payslips->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode(['success' => true, 'payslips' => $data]);
        exit();
    }
}

// Handle Company Profile Update
// POST Request Handlers
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection for all POST requests
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die("CSRF token validation failed.");
    }

    if (isset($_POST['update_company_profile'])) {
    $fields = ['company_name', 'company_reg_no', 'company_email', 'company_phone', 'company_address'];
    $errors = 0;

    foreach ($fields as $field) {
        if (isset($_POST[$field])) {
            $value = trim($_POST[$field]);
            $stmt = $conn->prepare("UPDATE system_settings SET `value` = ? WHERE `key` = ?");
            $stmt->bind_param("ss", $value, $field);
            if (!$stmt->execute()) {
                $errors++;
            }
            $stmt->close();
        }
    }

    if ($errors === 0) {
        $_SESSION['message'] = "Company profile updated successfully!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error updating company profile.";
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php#company-profile");
    exit();
}
    // Handle Add Designation
    if (isset($_POST['add_designation'])) {
        $desig_name = trim($_POST['desig_name']);
        $desig_desc = trim($_POST['desig_description']);
        $desig_level = $_POST['desig_level'];
        $dept_id = intval($_POST['department_id']);

        if (!empty($desig_name) && $dept_id > 0) {
            $stmt = $conn->prepare("INSERT INTO designations (name, description, level, department_id, is_active) VALUES (?, ?, ?, ?, 1)");
            $stmt->bind_param("sssi", $desig_name, $desig_desc, $desig_level, $dept_id);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Designation '$desig_name' added successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error adding designation: " . $conn->error;
                $_SESSION['message_type'] = "danger";
            }
            $stmt->close();
        } else {
            $_SESSION['message'] = "All fields are required.";
            $_SESSION['message_type'] = "warning";
        }
        header("Location: dashboard.php#designations");
        exit();
    }

// Handle Edit Designation
if (isset($_POST['edit_designation'])) {
    $desig_id = intval($_POST['desig_id']);
    $desig_name = trim($_POST['desig_name']);
    $desig_desc = trim($_POST['desig_description']);
    $desig_level = $_POST['desig_level'];
    $dept_id = intval($_POST['department_id']);

    if ($desig_id > 0 && !empty($desig_name) && $dept_id > 0) {
        $stmt = $conn->prepare("UPDATE designations SET name = ?, description = ?, level = ?, department_id = ? WHERE id = ?");
        $stmt->bind_param("sssii", $desig_name, $desig_desc, $desig_level, $dept_id, $desig_id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Designation updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating designation: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#designations");
    exit();
}

// Handle Add Department
if (isset($_POST['add_department'])) {
    $dept_name = trim($_POST['dept_name']);
    $dept_desc = trim($_POST['dept_description']);
    $dept_status = intval($_POST['dept_status']);

    if (!empty($dept_name)) {
        $stmt = $conn->prepare("INSERT INTO departments (name, description, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $dept_name, $dept_desc, $dept_status);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Department '$dept_name' added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding department: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = "Department name is required.";
        $_SESSION['message_type'] = "warning";
    }
    header("Location: dashboard.php#departments");
    exit();
}

    // Handle Edit Department
    if (isset($_POST['edit_department'])) {
        $dept_id = intval($_POST['dept_id']);
        $dept_name = trim($_POST['dept_name']);
        $dept_desc = trim($_POST['dept_description']);
        $dept_status = intval($_POST['dept_status']);

        if ($dept_id > 0 && !empty($dept_name)) {
            $stmt = $conn->prepare("UPDATE departments SET name = ?, description = ?, is_active = ? WHERE id = ?");
            $stmt->bind_param("ssii", $dept_name, $dept_desc, $dept_status, $dept_id);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Department updated successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error updating department: " . $conn->error;
                $_SESSION['message_type'] = "danger";
            }
            $stmt->close();
        }
        header("Location: dashboard.php#departments");
        exit();
    }

// Handle Delete Department
if (isset($_GET['delete_dept'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $dept_id = intval($_GET['delete_dept']);
    
    if ($dept_id > 0) {
        // Check if there are employees in this department
        $check = $conn->query("SELECT id FROM employees WHERE department_id = $dept_id LIMIT 1");
        if ($check->num_rows > 0) {
            $_SESSION['message'] = "Cannot delete department: It still has employees assigned to it.";
            $_SESSION['message_type'] = "warning";
        } else {
            $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
            $stmt->bind_param("i", $dept_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Department deleted successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error deleting department: " . $conn->error;
                $_SESSION['message_type'] = "danger";
            }
            $stmt->close();
        }
    }
    header("Location: dashboard.php#departments");
    exit();
}

    // Handle Assign Reporting (Hierarchy)
    if (isset($_POST['assign_reporting'])) {
        $employee_id = intval($_POST['employee_id']);
        $manager_id = intval($_POST['manager_id']);

        if ($employee_id > 0 && $employee_id !== $manager_id) {
            $conn->begin_transaction();
            try {
                // 1. Update/Insert into reporting_hierarchy
                $check = $conn->prepare("SELECT id FROM reporting_hierarchy WHERE employee_id = ?");
                $check->bind_param("i", $employee_id);
                $check->execute();
                $res_check = $check->get_result();
                
                if ($res_check->num_rows > 0) {
                    $stmt = $conn->prepare("UPDATE reporting_hierarchy SET manager_id = ?, effective_from = CURDATE() WHERE employee_id = ?");
                    $stmt->bind_param("ii", $manager_id, $employee_id);
                } else {
                    $stmt = $conn->prepare("INSERT INTO reporting_hierarchy (employee_id, manager_id, effective_from) VALUES (?, ?, CURDATE())");
                    $stmt->bind_param("ii", $employee_id, $manager_id);
                }
                $stmt->execute();

                // 2. Update employees table for consistency
                $upd_emp = $conn->prepare("UPDATE employees SET team_leader_id = ? WHERE id = ?");
                $upd_emp->bind_param("ii", $manager_id, $employee_id);
                $upd_emp->execute();

                $conn->commit();
                $_SESSION['message'] = "Reporting relationship updated successfully!";
                $_SESSION['message_type'] = "success";
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['message'] = "Error updating reporting: " . $e->getMessage();
                $_SESSION['message_type'] = "danger";
            }
        } else {
            $_SESSION['message'] = "Invalid selection. Employee cannot report to themselves.";
            $_SESSION['message_type'] = "warning";
        }
        header("Location: dashboard.php#reporting-hierarchy");
        exit();
    }

// Handle Remove Reporting Link
if (isset($_GET['remove_reporting'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $link_id = intval($_GET['remove_reporting']);
    if ($link_id > 0) {
        // Fetch employee_id before deleting to update subordinates
        $res = $conn->query("SELECT employee_id FROM reporting_hierarchy WHERE id = $link_id");
        if ($res && $res->num_rows > 0) {
            $e_id = $res->fetch_assoc()['employee_id'];
            
            $conn->begin_transaction();
            try {
                // Delete from hierarchy
                $conn->query("DELETE FROM reporting_hierarchy WHERE id = $link_id");
                
                // Reset team_leader_id in employees table
                $conn->query("UPDATE employees SET team_leader_id = NULL WHERE id = $e_id");
                
                $conn->commit();
                $_SESSION['message'] = "Reporting link removed successfully.";
                $_SESSION['message_type'] = "success";
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['message'] = "Error removing link: " . $e->getMessage();
                $_SESSION['message_type'] = "danger";
            }
        }
    }
    header("Location: dashboard.php#reporting-hierarchy");
    exit();
}

// Handle Add Holiday
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_holiday'])) {
    $title = trim($_POST['holiday_title']);
    $date = $_POST['holiday_date'];
    $type = $_POST['holiday_type'] ?? 'National';
    $duration = intval($_POST['duration'] ?? 1);
    $desc = trim($_POST['holiday_description'] ?? '');

    if (!empty($title) && !empty($date)) {
        $stmt = $conn->prepare("INSERT INTO system_holidays (title, holiday_date, type, duration, description) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssis", $title, $date, $type, $duration, $desc);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Holiday '$title' added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding holiday: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#holiday-calendar");
    exit();
}

// Handle Delete Holiday
if (isset($_GET['delete_holiday'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $id = intval($_GET['delete_holiday']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM system_holidays WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Holiday deleted successfully!";
            $_SESSION['message_type'] = "success";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#holiday-calendar");
    exit();
}

// Handle Add Policy
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_policy'])) {
    $title = trim($_POST['policy_title']);
    $content = trim($_POST['policy_content']);
    $category = $_POST['policy_category'];
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    if (!empty($title) && !empty($category)) {
        $stmt = $conn->prepare("INSERT INTO system_policies (title, description, category, is_active) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $title, $content, $category, $is_active);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Policy saved successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error saving policy: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    $redirect_hash = strtolower(str_replace(' ', '-', $category)) . '-policies';
    if ($category == 'Leave') $redirect_hash = 'leave-policy';
    if ($category == 'Attendance') $redirect_hash = 'attendance-policy';
    if ($category == 'Payroll') $redirect_hash = 'payroll-policy';
    
    header("Location: dashboard.php#" . $redirect_hash);
    exit();
}

// Handle Add Allowance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_allowance'])) {
    $name = trim($_POST['alw_name']);
    $type = $_POST['alw_type'];
    $amt_type = $_POST['alw_amount_type'];
    $amt = floatval($_POST['alw_amount']);
    $level = $_POST['alw_level'];
    $basic_salary = floatval($_POST['alw_basic_salary'] ?? 0);
    $taxable = isset($_POST['alw_taxable']) ? 1 : 0;
    $active = isset($_POST['alw_active']) ? 1 : 0;

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO allowances (name, type, amount_type, default_amount, designation_level, basic_salary, is_taxable, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssdsdii", $name, $type, $amt_type, $amt, $level, $basic_salary, $taxable, $active);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Allowance '$name' added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding allowance: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#allowance-management");
    exit();
}

// Handle Edit Allowance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_allowance'])) {
    $id = intval($_POST['alw_id']);
    $name = trim($_POST['alw_name']);
    $type = $_POST['alw_type'];
    $amt_type = $_POST['alw_amount_type'];
    $amt = floatval($_POST['alw_amount']);
    $level = $_POST['alw_level'];
    $basic_salary = floatval($_POST['alw_basic_salary'] ?? 0);
    $taxable = isset($_POST['alw_taxable']) ? 1 : 0;
    $active = isset($_POST['alw_active']) ? 1 : 0;

    if ($id > 0 && !empty($name)) {
        $stmt = $conn->prepare("UPDATE allowances SET name = ?, type = ?, amount_type = ?, default_amount = ?, designation_level = ?, basic_salary = ?, is_taxable = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("sssdsdiii", $name, $type, $amt_type, $amt, $level, $basic_salary, $taxable, $active, $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Allowance updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating allowance: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#allowance-management");
    exit();
}

// Handle Delete Allowance
if (isset($_GET['delete_allowance'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $id = intval($_GET['delete_allowance']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM allowances WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Allowance deleted successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error deleting allowance: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#allowance-management");
    exit();
}

// Handle Add Deduction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_deduction'])) {
    $name = trim($_POST['ded_name']);
    $code = trim($_POST['ded_code']);
    $type = $_POST['ded_type'];
    $basis = $_POST['ded_basis'] ?? 'Basic';
    $amt_type = $_POST['ded_amount_type'];
    $amt = floatval($_POST['ded_amount']);
    $max_limit = !empty($_POST['ded_max_limit']) ? floatval($_POST['ded_max_limit']) : null;
    $level = $_POST['ded_level'] ?? 'All';
    $mandatory = isset($_POST['ded_mandatory']) ? 1 : 0;
    $active = isset($_POST['ded_active']) ? 1 : 0;

    if (!empty($name) && !empty($code)) {
        $stmt = $conn->prepare("INSERT INTO deductions (deduction_code, name, type, amount_type, calculation_basis, default_amount, max_limit, designation_level, is_mandatory, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssddsii", $code, $name, $type, $amt_type, $basis, $amt, $max_limit, $level, $mandatory, $active);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Deduction rule '$name' added successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error adding deduction rule: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#deduction-rules");
    exit();
}

// Handle Edit Deduction
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_deduction'])) {
    $id = intval($_POST['ded_id']);
    $name = trim($_POST['ded_name']);
    $type = $_POST['ded_type'];
    $basis = $_POST['ded_basis'] ?? 'Basic';
    $amt_type = $_POST['ded_amount_type'];
    $amt = floatval($_POST['ded_amount']);
    $max_limit = !empty($_POST['ded_max_limit']) ? floatval($_POST['ded_max_limit']) : null;
    $level = $_POST['ded_level'] ?? 'All';
    $mandatory = isset($_POST['ded_mandatory']) ? 1 : 0;
    $active = isset($_POST['ded_active']) ? 1 : 0;

    if ($id > 0 && !empty($name)) {
        $stmt = $conn->prepare("UPDATE deductions SET name = ?, type = ?, amount_type = ?, calculation_basis = ?, default_amount = ?, max_limit = ?, designation_level = ?, is_mandatory = ?, is_active = ? WHERE id = ?");
        $stmt->bind_param("ssssddii i i", $name, $type, $amt_type, $basis, $amt, $max_limit, $level, $mandatory, $active, $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Deduction rule updated successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating deduction rule: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#deduction-rules");
    exit();
}

// Handle Delete Deduction
if (isset($_GET['delete_deduction'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $id = intval($_GET['delete_deduction']);
    if ($id > 0) {
        // Check if deduction is in use
        $check_stmt = $conn->prepare("SELECT id FROM employee_deductions WHERE deduction_id = ? LIMIT 1");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_res = $check_stmt->get_result();
        
        if ($check_res->num_rows > 0) {
            $_SESSION['message'] = "Cannot delete this deduction rule because it is currently assigned to one or more employees. Please deactivate it instead.";
            $_SESSION['message_type'] = "warning";
        } else {
            $stmt = $conn->prepare("DELETE FROM deductions WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Deduction rule deleted successfully!";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error deleting deduction rule: " . $conn->error;
                $_SESSION['message_type'] = "danger";
            }
            $stmt->close();
        }
        $check_stmt->close();
    }
    header("Location: dashboard.php#deduction-rules");
    exit();
}


// Handle Add Appraisal Cycle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_appraisal_cycle'])) {
    $name = trim($_POST['cycle_name']);
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $created_by = $_SESSION['user_id'];

    if (!empty($name)) {
        $stmt = $conn->prepare("INSERT INTO appraisal_cycles (name, start_date, end_date, status, created_by) VALUES (?, ?, ?, 'draft', ?)");
        $stmt->bind_param("sssi", $name, $start, $end, $created_by);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Appraisal cycle created successfully!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error creating appraisal cycle: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#appraisal-cycles");
    exit();
}

// Handle Edit Appraisal Cycle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_appraisal_cycle'])) {
    $id = intval($_POST['cycle_id']);
    $name = trim($_POST['cycle_name']);
    $start = $_POST['start_date'];
    $end = $_POST['end_date'];
    $status = $_POST['status'];

    if ($id > 0 && !empty($name)) {
        $stmt = $conn->prepare("UPDATE appraisal_cycles SET name = ?, start_date = ?, end_date = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $start, $end, $status, $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Appraisal cycle updated!";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error updating cycle: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#appraisal-cycles");
    exit();
}

// Handle Delete Appraisal Cycle
if (isset($_GET['delete_appraisal_cycle'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $id = intval($_GET['delete_appraisal_cycle']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM appraisal_cycles WHERE id = ? AND (status = 'draft' OR status = 'cancelled')");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['message'] = "Appraisal cycle deleted.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Only Draft or Cancelled cycles can be deleted.";
                $_SESSION['message_type'] = "warning";
            }
        } else {
            $_SESSION['message'] = "Error deleting cycle: " . $conn->error;
            $_SESSION['message_type'] = "danger";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#appraisal-cycles");
    exit();
}

// Handle Launch Appraisal Cycle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['launch_cycle'])) {
    $id = intval($_POST['cycle_id']);
    if ($id > 0) {
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE appraisal_cycles SET status = 'active' WHERE id = ? AND status = 'draft'");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            
            if ($stmt->affected_rows > 0) {
                $emp_res = $conn->query("SELECT id FROM employees WHERE is_active = 1");
                $stmt_rev = $conn->prepare("INSERT INTO performance_reviews (employee_id, appraisal_cycle_id, status) VALUES (?, ?, 'pending')");
                
                while($emp = $emp_res->fetch_assoc()) {
                    $eid = $emp['id'];
                    $stmt_rev->bind_param("ii", $eid, $id);
                    $stmt_rev->execute();
                }
                $conn->commit();
                $_SESSION['message'] = "Appraisal cycle launched and review records created!";
                $_SESSION['message_type'] = "success";
            } else {
                $conn->rollback();
                $_SESSION['message'] = "Cycle must be in Draft status to launch.";
                $_SESSION['message_type'] = "warning";
            }
        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['message'] = "Error launching cycle: " . $e->getMessage();
            $_SESSION['message_type'] = "danger";
        }
    }
    header("Location: dashboard.php#appraisal-cycles");
    exit();
}

// Handle Approve Payroll Run
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['approve_payroll'])) {
    require_once __DIR__ . '/../../app/Controllers/PayrollController.php';
    require_once __DIR__ . '/../../app/Models/Payroll.php';
    $payrollController = new PayrollController($conn);
    
    $run_id = intval($_POST['run_id']);
    if ($payrollController->approveRun($run_id)) {
        $_SESSION['message'] = "Payroll run approved successfully! Payslips are now visible to employees.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error approving payroll run.";
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php#payroll-approvals");
    exit();
}

// Handle Attendance Settings Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_attendance_settings'])) {
    $start = $_POST['shift_start'];
    $end = $_POST['shift_end'];
    
    $settings = [
        'office_start_time' => $start,
        'office_end_time' => $end
    ];
    
    $errors = 0;
    foreach ($settings as $key => $val) {
        $stmt = $conn->prepare("UPDATE system_settings SET `value` = ? WHERE `key` = ?");
        $stmt->bind_param("ss", $val, $key);
        if (!$stmt->execute()) $errors++;
        $stmt->close();
    }
    
    $_SESSION['message'] = $errors === 0 ? "Attendance settings updated!" : "Error updating settings.";
    $_SESSION['message_type'] = $errors === 0 ? "success" : "danger";
    header("Location: dashboard.php#attendance-policy");
    exit();
}

    // Handle Weekly Off Update
    if (isset($_POST['update_weekly_off'])) {
        $days = isset($_POST['off_days']) ? json_encode($_POST['off_days']) : json_encode([]);
        // Use Upsert logic in case the key doesn't exist
        $stmt = $conn->prepare("INSERT INTO system_settings (`key`, `value`, `category`) VALUES ('weekly_off_days', ?, 'attendance') ON DUPLICATE KEY UPDATE `value` = ?");
        $stmt->bind_param("ss", $days, $days);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Weekly off configuration saved!";
            $_SESSION['message_type'] = "success";
        }
        $stmt->close();
        header("Location: dashboard.php#weekly-off");
        exit();
    }

} // End of Main POST Block (Starts on line 87)

// --- GET Request Handlers (Deletions & Actions) ---
if (isset($_GET['delete_desig'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $desig_id = intval($_GET['delete_desig']);
    if ($desig_id > 0) {
        $check = $conn->query("SELECT id FROM employees WHERE designation_id = $desig_id LIMIT 1");
        if ($check->num_rows > 0) {
            $_SESSION['message'] = "Cannot delete designation: It is still assigned to employees.";
            $_SESSION['message_type'] = "warning";
        } else {
            $stmt = $conn->prepare("DELETE FROM designations WHERE id = ?");
            $stmt->bind_param("i", $desig_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Designation deleted successfully!";
                $_SESSION['message_type'] = "success";
            }
            $stmt->close();
        }
    }
    header("Location: dashboard.php#designations");
    exit();
}

if (isset($_GET['delete_dept'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $dept_id = intval($_GET['delete_dept']);
    if ($dept_id > 0) {
        $check = $conn->query("SELECT id FROM employees WHERE department_id = $dept_id LIMIT 1");
        if ($check->num_rows > 0) {
            $_SESSION['message'] = "Cannot delete department: It still has employees assigned to it.";
            $_SESSION['message_type'] = "warning";
        } else {
            $stmt = $conn->prepare("DELETE FROM departments WHERE id = ?");
            $stmt->bind_param("i", $dept_id);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Department deleted successfully!";
                $_SESSION['message_type'] = "success";
            }
            $stmt->close();
        }
    }
    header("Location: dashboard.php#departments");
    exit();
}

if (isset($_GET['remove_reporting'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $link_id = intval($_GET['remove_reporting']);
    if ($link_id > 0) {
        $res = $conn->prepare("SELECT employee_id FROM reporting_hierarchy WHERE id = ?");
        $res->bind_param("i", $link_id);
        $res->execute();
        $res_match = $res->get_result();
        if ($res_match && $res_match->num_rows > 0) {
            $e_id = $res_match->fetch_assoc()['employee_id'];
            $conn->begin_transaction();
            try {
                $stmt1 = $conn->prepare("DELETE FROM reporting_hierarchy WHERE id = ?");
                $stmt1->bind_param("i", $link_id);
                $stmt1->execute();
                $stmt2 = $conn->prepare("UPDATE employees SET team_leader_id = NULL WHERE id = ?");
                $stmt2->bind_param("i", $e_id);
                $stmt2->execute();
                $conn->commit();
                $_SESSION['message'] = "Reporting link removed successfully.";
                $_SESSION['message_type'] = "success";
            } catch (Exception $e) {
                $conn->rollback();
                $_SESSION['message'] = "Error removing link: " . $e->getMessage();
                $_SESSION['message_type'] = "danger";
            }
        }
    }
    header("Location: dashboard.php#reporting-hierarchy");
    exit();
}

if (isset($_GET['delete_holiday'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $id = intval($_GET['delete_holiday']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM system_holidays WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Holiday deleted successfully!";
            $_SESSION['message_type'] = "success";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#holiday-calendar");
    exit();
}

if (isset($_GET['delete_allowance'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $id = intval($_GET['delete_allowance']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM allowances WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Allowance deleted successfully!";
            $_SESSION['message_type'] = "success";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#allowance-management");
    exit();
}

if (isset($_GET['delete_deduction'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $id = intval($_GET['delete_deduction']);
    if ($id > 0) {
        $check_stmt = $conn->prepare("SELECT id FROM employee_deductions WHERE deduction_id = ? LIMIT 1");
        $check_stmt->bind_param("i", $id);
        $check_stmt->execute();
        $check_res = $check_stmt->get_result();
        if ($check_res->num_rows > 0) {
            $_SESSION['message'] = "Cannot delete deduction: Assigned to employees.";
            $_SESSION['message_type'] = "warning";
        } else {
            $stmt = $conn->prepare("DELETE FROM deductions WHERE id = ?");
            $stmt->bind_param("i", $id);
            if ($stmt->execute()) {
                $_SESSION['message'] = "Deduction rule deleted!";
                $_SESSION['message_type'] = "success";
            }
            $stmt->close();
        }
    }
    header("Location: dashboard.php#deduction-rules");
    exit();
}

if (isset($_GET['delete_appraisal_cycle'])) {
    if (!isset($_GET['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_GET['csrf_token'])) {
        die("CSRF token validation failed.");
    }
    $id = intval($_GET['delete_appraisal_cycle']);
    if ($id > 0) {
        $stmt = $conn->prepare("DELETE FROM appraisal_cycles WHERE id = ? AND (status = 'draft' OR status = 'cancelled')");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $_SESSION['message'] = "Appraisal cycle deleted.";
            $_SESSION['message_type'] = "success";
        }
        $stmt->close();
    }
    header("Location: dashboard.php#appraisal-cycles");
    exit();
}

// Fetch KPI Metrics
$total_depts = $conn->query("SELECT COUNT(*) as count FROM departments")->fetch_assoc()['count'] ?? 0;
$total_desigs = $conn->query("SELECT COUNT(*) as count FROM designations")->fetch_assoc()['count'] ?? 0;
$total_holidays = $conn->query("SELECT COUNT(*) as count FROM system_holidays WHERE YEAR(holiday_date) = YEAR(CURDATE())")->fetch_assoc()['count'] ?? 0;
$total_policies = $conn->query("SELECT COUNT(*) as count FROM system_policies WHERE is_active = 1")->fetch_assoc()['count'] ?? 0;

// Fetch Lists for Fragments
$depts_result = $conn->query("
    SELECT d.*, 
           CONCAT(e.first_name, ' ', e.last_name) as head_name,
           (SELECT COUNT(*) FROM employees emp WHERE emp.department_id = d.id) as emp_count 
    FROM departments d
    LEFT JOIN employees e ON d.head_id = e.id
    ORDER BY d.name ASC");
$desigs_result = $conn->query("SELECT ds.*, d.name as department_name, (SELECT COUNT(*) FROM employees e WHERE e.designation_id = ds.id) as emp_count FROM designations ds JOIN departments d ON ds.department_id = d.id ORDER BY ds.name ASC");

// Recent Admin Activities (Combined Recent Departments and Designations)
$recent_admin_activities_query = "
    (SELECT 'New Department' as action, name as item, id as sort_id FROM departments)
    UNION ALL
    (SELECT 'New Designation' as action, name as item, id as sort_id FROM designations)
    ORDER BY sort_id DESC LIMIT 5";
$recent_admin_activities_result = $conn->query($recent_admin_activities_query);

// Fetch all employees for dropdowns and hierarchy
$employees_all_result = $conn->query("SELECT id, first_name, last_name, employee_code FROM employees WHERE is_active = 1 ORDER BY first_name ASC");
$employees_list = [];
if ($employees_all_result) {
    while($row = $employees_all_result->fetch_assoc()) {
        $employees_list[] = $row;
    }
}

// Fetch hierarchy links
$hierarchy_query = "
    SELECT rh.*, 
           e.first_name as emp_fname, e.last_name as emp_lname, e.employee_code as emp_code,
           m.first_name as mgr_fname, m.last_name as mgr_lname, m.employee_code as mgr_code,
           d.name as emp_desig
    FROM reporting_hierarchy rh
    JOIN employees e ON rh.employee_id = e.id
    LEFT JOIN employees m ON rh.manager_id = m.id
    LEFT JOIN designations d ON e.designation_id = d.id
    ORDER BY mgr_fname ASC, emp_fname ASC";
$hierarchy_result = $conn->query($hierarchy_query);

// --- Fetch Data for Charts ---
// 1. Employees per Department
$dept_chart_query = "SELECT d.name, COUNT(e.id) as count FROM departments d LEFT JOIN employees e ON d.id = e.department_id GROUP BY d.id";
$dept_chart_res = $conn->query($dept_chart_query);
$dept_chart_labels = [];
$dept_chart_counts = [];
if ($dept_chart_res) {
    while($row = $dept_chart_res->fetch_assoc()) {
        $dept_chart_labels[] = $row['name'];
        $dept_chart_counts[] = (int)$row['count'];
    }
}

// 2. Allowance Breakdown
$alw_chart_query = "SELECT name, default_amount FROM allowances WHERE is_active = 1 LIMIT 6";
$alw_chart_res = $conn->query($alw_chart_query);
$alw_chart_labels = [];
$alw_chart_counts = [];
if ($alw_chart_res) {
    while($row = $alw_chart_res->fetch_assoc()) {
        $alw_chart_labels[] = $row['name'];
        $alw_chart_counts[] = (float)$row['default_amount'];
    }
}

// Get user information from session
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Administrator';
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'admin@hrnexa.com';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - HRnexa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #2980b9;
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

        /* Sidebar Styling */
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

        .sidebar-menu li {
            padding: 0;
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

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
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
            font-size: 28px;
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
            border-left: 4px solid;
        }

        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .kpi-card.primary {
            border-left-color: var(--secondary-color);
        }

        .kpi-card.success {
            border-left-color: var(--success-color);
        }

        .kpi-card.danger {
            border-left-color: var(--danger-color);
        }

        .kpi-card.warning {
            border-left-color: var(--warning-color);
        }

        .kpi-icon {
            font-size: 32px;
            margin-bottom: 10px;
            color: var(--secondary-color);
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
            border-color: #eee;
        }

        .data-table tbody tr:hover {
            background-color: rgba(30, 60, 114, 0.05);
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
            box-shadow: 0 0 0 0.2rem rgba(42, 82, 152, 0.25);
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
            <i class="fas fa-building"></i>
            <h5>HRnexa</h5>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-section">Main</li>
            <li><a href="#dashboard" class="nav-link active" data-section="dashboard"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
            
            <li class="menu-section">Organization Setup</li>
            <li><a href="#company-profile" class="nav-link" data-section="company-profile"><i class="fas fa-address-card"></i><span>Company Profile</span></a></li>
            <li><a href="#departments" class="nav-link" data-section="departments"><i class="fas fa-project-diagram"></i><span>Departments</span></a></li>
            <li><a href="#designations" class="nav-link" data-section="designations"><i class="fas fa-briefcase"></i><span>Designations</span></a></li>
            <li><a href="#reporting-hierarchy" class="nav-link" data-section="reporting-hierarchy"><i class="fas fa-sitemap"></i><span>Reporting Hierarchy</span></a></li>
            
            <li class="menu-section">Policy Management</li>
            <li><a href="#company-policies" class="nav-link" data-section="company-policies"><i class="fas fa-file-contract"></i><span>Company Policies</span></a></li>
            <li><a href="#hr-policies" class="nav-link" data-section="hr-policies"><i class="fas fa-file-alt"></i><span>HR Policies</span></a></li>
            <li><a href="#leave-policy" class="nav-link" data-section="leave-policy"><i class="fas fa-calendar-check"></i><span>Leave Policy</span></a></li>
            <li><a href="#attendance-policy" class="nav-link" data-section="attendance-policy"><i class="fas fa-clipboard-list"></i><span>Attendance Policy</span></a></li>
            <li><a href="#payroll-policy" class="nav-link" data-section="payroll-policy"><i class="fas fa-money-bill-wave"></i><span>Payroll Policy</span></a></li>
            <li><a href="#payroll-approvals" class="nav-link" data-section="payroll-approvals"><i class="fas fa-check-double"></i><span>Payroll Approvals</span></a></li>
            <li><a href="#allowance-management" class="nav-link" data-section="allowance-management"><i class="fas fa-coins"></i><span>Allowance Management</span></a></li>
            <li><a href="#deduction-rules" class="nav-link" data-section="deduction-rules"><i class="fas fa-minus-circle"></i><span>Deduction Rules</span></a></li>
            <li><a href="#appraisal-cycles" class="nav-link" data-section="appraisal-cycles"><i class="fas fa-sync-alt"></i><span>Appraisal Cycles</span></a></li>
            <li><a href="#notice" class="nav-link" data-section="notice"><i class="fas fa-bullhorn"></i><span>Notice Board</span></a></li>
            
            <li class="menu-section">Holiday & Calendar</li>
            <li><a href="#holiday-calendar" class="nav-link" data-section="holiday-calendar"><i class="fas fa-calendar-alt"></i><span>Holiday Calendar</span></a></li>
            <li><a href="#weekly-off" class="nav-link" data-section="weekly-off"><i class="fas fa-calendar-times"></i><span>Weekly Off</span></a></li>
            <li><a href="#festival-calendar" class="nav-link" data-section="festival-calendar"><i class="fas fa-snowflake"></i><span>Festival Calendar</span></a></li>
            
            <li class="menu-section">Account</li>
            <li><a href="<?php echo BASE_URL; ?>views/auth/logout.php" class="nav-link" data-external="true"><i class="fas fa-sign-out-alt"></i><span>Logout</span></a></li>
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
                <h5 id="topbar-title" style="margin: 0; color: var(--primary-color); font-weight: 700;">Dashboard</h5>
                <div class="search-box ms-4 d-none d-md-flex" style="position: relative;">
                    <i class="fas fa-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999;"></i>
                    <input type="text" class="form-control" placeholder="Search..." style="padding-left: 35px; width: 300px; border-radius: 20px; border: 1px solid #eee; background: #f8f9fa;">
                </div>
            </div>
            <div class="topbar-right">
                <!-- Notification Icon -->
                <div class="dropdown me-3">
                    <a href="#" class="text-secondary position-relative" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-bell fa-lg"></i>
                        <span id="notification-badge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px; display: none;">
                            0
                        </span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg" aria-labelledby="notificationDropdown" style="width: 320px; border: none; border-radius: 12px; max-height: 400px; overflow-y: auto;">
                        <li class="dropdown-header border-bottom py-2">
                            <h6 class="mb-0">Notifications</h6>
                        </li>
                        <div id="notification-list">
                            <li class="text-center py-3 text-muted"><small>Loading...</small></li>
                        </div>
                        <li class="dropdown-footer text-center border-top py-2 bg-light">
                            <a href="#" class="text-decoration-none small">View All</a>
                        </li>
                    </ul>
                </div>

                <div class="user-profile">
                    <div class="user-avatar"><?php echo strtoupper(substr($user_name, 0, 2)); ?></div>
                    <div>
                        <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($user_name ?? ''); ?></div>
                        <div style="font-size: 12px; color: #999;">Admin</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content" style="padding: 20px;">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                    <?php 
                        echo $_SESSION['message']; 
                        unset($_SESSION['message']);
                        unset($_SESSION['message_type']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
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
                <div class="page-header">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                        <i class="fas fa-plus"></i> New Department
                    </button>
                </div>

                <!-- KPI Cards -->
                <div class="row">
                    <div class="col-md-3 col-sm-12">
                        <div class="kpi-card primary" onclick="switchSection('departments')" style="cursor: pointer;">
                            <div class="kpi-icon"><i class="fas fa-project-diagram"></i></div>
                            <div class="kpi-value text-primary"><?php echo number_format($total_depts); ?></div>
                            <div class="kpi-label">Total Departments</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <div class="kpi-card success" onclick="switchSection('designations')" style="cursor: pointer;">
                            <div class="kpi-icon text-success"><i class="fas fa-briefcase"></i></div>
                            <div class="kpi-value text-success"><?php echo number_format($total_desigs); ?></div>
                            <div class="kpi-label">Designations</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <div class="kpi-card warning" onclick="switchSection('holiday-calendar')" style="cursor: pointer;">
                            <div class="kpi-icon text-warning"><i class="fas fa-calendar-alt"></i></div>
                            <div class="kpi-value text-warning"><?php echo number_format($total_holidays); ?></div>
                            <div class="kpi-label">Holidays This Year</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-12">
                        <div class="kpi-card danger" onclick="switchSection('company-policies')" style="cursor: pointer;">
                            <div class="kpi-icon text-danger"><i class="fas fa-file-alt"></i></div>
                            <div class="kpi-value text-danger"><?php echo number_format($total_policies); ?></div>
                            <div class="kpi-label">Active Policies</div>
                        </div>
                    </div>
                </div>

                <!-- Dashboard Charts Row -->
                <div class="row mb-4">
                    <div class="col-md-7">
                        <div class="section-card h-100">
                            <div class="section-title">
                                <i class="fas fa-chart-bar"></i> Employees per Department
                            </div>
                            <div style="height: 300px;">
                                <canvas id="deptChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="section-card h-100">
                            <div class="section-title">
                                <i class="fas fa-chart-pie"></i> Allowance Breakdown
                            </div>
                            <div style="height: 300px;">
                                <canvas id="alwChart"></canvas>
                            </div>
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
                                    <th>Action</th>
                                    <th>Item</th>
                                    <th>Admin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_admin_activities_result && $recent_admin_activities_result->num_rows > 0): ?>
                                    <?php while($activity = $recent_admin_activities_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($activity['action'] ?? ''); ?></td>
                                        <td><strong><?php echo htmlspecialchars($activity['item'] ?? ''); ?></strong></td>
                                        <td>Admin</td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center">No recent activities.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <?php include 'company_profile.php'; ?>
            <?php include 'departments.php'; ?>
            <?php include 'designations.php'; ?>
            <?php include 'hierarchy.php'; ?>
            <?php include 'policies_company.php'; ?>
            <?php include 'policies_hr.php'; ?>
            <?php include 'policies_leave.php'; ?>
            <?php include 'policies_attendance.php'; ?>
            <?php include 'policies_payroll.php'; ?>
            <?php include 'calendar_holidays.php'; ?>
            <?php include 'calendar_weekly.php'; ?>
            <?php include 'calendar_festival.php'; ?>
            <?php include 'allowances.php'; ?>
            <?php include 'deductions.php'; ?>
            <?php include 'appraisal_cycles.php'; ?>
            
            <!-- Notice Board Section -->
            <div id="notice-section" class="section-content" style="display: none;">
                <?php include 'notice_management.php'; ?>
            </div>
            
            <!-- Payroll Approvals Section -->
            <div id="payroll-approvals-section" class="section-content" style="display:none;">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Payroll Approvals</h3>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Period</th>
                                        <th>Generated Date</th>
                                        <th>Status</th>
                                        <th>Details</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    // Fetch pending payroll runs
                                    $pending_runs = $conn->query("SELECT * FROM payroll_runs WHERE status = 'pending_approval' ORDER BY year DESC, month DESC");
                                    if ($pending_runs && $pending_runs->num_rows > 0):
                                        while($run = $pending_runs->fetch_assoc()):
                                    ?>
                                    <tr>
                                        <td>
                                            <span class="fw-bold"><?php echo date('F Y', mktime(0, 0, 0, $run['month'], 1)); ?></span>
                                        </td>
                                        <td><?php echo date('d M, Y', strtotime($run['created_at'])); ?></td>
                                        <td><span class="badge bg-warning text-dark">Pending Approval</span></td>
                                        <td>
                                            <button type="button" class="btn btn-primary btn-sm view-payroll-btn" data-run-id="<?php echo $run['id']; ?>" data-period="<?php echo date('F Y', mktime(0, 0, 0, $run['month'], 1)); ?>">
                                                <i class="fas fa-eye me-1"></i> View
                                            </button>
                                        </td>
                                        <td>
                                            <form method="POST" onsubmit="return confirm('Are you sure you want to approve this payroll run? Payslips will be released to employees.');">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="run_id" value="<?php echo $run['id']; ?>">
                                                <button type="submit" name="approve_payroll" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check-circle me-1"></i> Approve
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; else: ?>
                                    <tr><td colspan="4" class="text-center py-4 text-muted">No pending payroll runs for approval.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            </div>

        </div>
    </div>

    <!-- Payroll Details Modal -->
    <div class="modal fade" id="payrollDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-list-alt me-2"></i> Payroll Details - <span id="modalPeriod"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Employee</th>
                                    <th>Designation</th>
                                    <th class="text-end">Basic</th>
                                    <th class="text-end">Allowances</th>
                                    <th class="text-end">Deductions</th>
                                    <th class="text-end fw-bold">Net Salary</th>
                                </tr>
                            </thead>
                            <tbody id="payrollDetailsBody">
                                <!-- Data populated via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Notice Add/Edit Modal -->
    <div class="modal fade" id="noticeModal" tabindex="-1" aria-labelledby="noticeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="noticeModalLabel">Add New Notice</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="noticeForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="id" id="notice_id">
                    <div class="modal-body p-4">
                        <div id="noticeFormAlert" class="alert d-none"></div>
                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label fw-bold">Notice Title</label>
                                <input type="text" class="form-control" name="title" id="notice_title" required placeholder="Enter notice title">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold">Category</label>
                                <select class="form-select" name="category" id="notice_category">
                                    <option value="important">Important</option>
                                    <option value="update">General Update</option>
                                    <option value="holiday">Holiday Notice</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Notice Content</label>
                            <textarea class="form-control" name="content" id="notice_content" rows="4" required placeholder="Enter the main content of the notice"></textarea>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Footer Label</label>
                                <input type="text" class="form-control" name="footer_label" id="notice_footer_label" placeholder="e.g., Effective, By, Closed">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Footer Value</label>
                                <input type="text" class="form-control" name="footer_value" id="notice_footer_value" placeholder="e.g., Feb 22, 2026 (2 AM - 4 AM)">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Button Text</label>
                                <input type="text" class="form-control" name="button_text" id="notice_button_text" placeholder="e.g., Read More">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Button Link</label>
                                <input type="text" class="form-control" name="button_link" id="notice_button_link" placeholder="e.g., # or URL">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select class="form-select" name="status" id="notice_status">
                                <option value="active">Active</option>
                                <option value="inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light p-3">
                        <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-4" id="saveNoticeBtn">Save Notice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Policy Modal -->
    <div class="modal fade" id="viewPolicyModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="viewPolicyTitle">Policy Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="viewPolicyContent" style="white-space: pre-wrap; line-height: 1.6; color: #444;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navigation handler
        document.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', function (e) {
                // Allow external links (like logout) to work normally
                if (this.dataset.external === 'true' || this.getAttribute('href').includes('logout.php')) {
                    return;
                }
                
                const section = this.dataset.section;
                if (!section) return;
                
                e.preventDefault();
                // Update URL hash without jumping
                history.pushState(null, null, '#' + section);
                switchSection(section);
            });
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

            // Update topbar title
            const titles = {
                'dashboard': 'Dashboard',
                'company-profile': 'Company Profile',
                'departments': 'Department Management',
                'designations': 'Designation Management',
                'reporting-hierarchy': 'Reporting Hierarchy',
                'company-policies': 'Company Policies',
                'hr-policies': 'HR Policy Configuration',
                'leave-policy': 'Leave Policy Configuration',
                'attendance-policy': 'Attendance Policy Configuration',
                'payroll-policy': 'Payroll Policy Configuration',
                'payroll-approvals': 'Payroll Approvals',
                'allowance-management': 'Allowance Management',
                'deduction-rules': 'Deduction Rules',
                'appraisal-cycles': 'Appraisal Cycle Management',
                'holiday-calendar': 'Holiday Calendar',
                'weekly-off': 'Weekly Off Configuration',
                'festival-calendar': 'Festival Calendar',
                'notice': 'Notice Board'
            };
            document.getElementById('topbar-title').textContent = titles[section] || 'Dashboard';
        }

        // Sidebar Toggle for Mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }

        // Hash Navigation Handler
        function handleHash() {
            const hash = window.location.hash.substring(1); // Remove the #
            if (hash) {
                switchSection(hash);
            } else {
                switchSection('dashboard');
            }
        }

        // Listen for hash changes (e.g., from browser back/forward or manual edits)
        window.addEventListener('hashchange', handleHash);

        // Run on page load
        document.addEventListener('DOMContentLoaded', function() {
            handleHash();
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert');
                alerts.forEach(function(alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                });
            }, 5000);

            // View Policy Handler
            const viewPolicyModal = document.getElementById('viewPolicyModal');
            if (viewPolicyModal) {
                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('.view-policy-btn');
                    if (btn) {
                        const title = btn.dataset.title;
                        const content = btn.dataset.description;
                        document.getElementById('viewPolicyTitle').textContent = title;
                        document.getElementById('viewPolicyContent').textContent = content;
                        new bootstrap.Modal(viewPolicyModal).show();
                    }
                });
            }

            // Notification System
            function fetchNotifications() {
                fetch('dashboard.php?action=fetch_notifications')
                    .then(response => response.json())
                    .then(data => {
                        const badge = document.getElementById('notification-badge');
                        const list = document.getElementById('notification-list');
                        
                        // Update Badge
                        if (data.count > 0) {
                            badge.textContent = data.count;
                            badge.style.display = 'block';
                        } else {
                            badge.style.display = 'none';
                        }

                        // Update List
                        list.innerHTML = '';
                        if (data.notifications.length === 0) {
                            list.innerHTML = '<li class="text-center py-3 text-muted"><small>No new notifications</small></li>';
                        } else {
                            data.notifications.forEach(n => {
                                const bgClass = n.is_read == 0 ? 'bg-light' : '';
                                const iconColor = n.type === 'error' ? 'text-danger' : (n.type === 'success' ? 'text-success' : 'text-primary');
                                const icon = n.type === 'error' ? 'fa-exclamation-circle' : (n.type === 'success' ? 'fa-check-circle' : 'fa-info-circle');
                                
                                const item = `
                                    <li>
                                        <a class="dropdown-item d-flex align-items-start py-2 ${bgClass}" href="${n.link || '#'}" onclick="markAsRead(${n.id}, '${n.link}')">
                                            <div class="me-3 mt-1 ${iconColor}"><i class="fas ${icon}"></i></div>
                                            <div>
                                                <div class="small fw-bold">${n.title}</div>
                                                <div class="small text-muted text-wrap">${n.message}</div>
                                                <div class="small text-muted mt-1" style="font-size: 10px;">${new Date(n.created_at).toLocaleString()}</div>
                                            </div>
                                        </a>
                                    </li>`;
                                list.innerHTML += item;
                            });
                        }
                    });
            }

            window.markAsRead = function(id, link) {
                fetch(`dashboard.php?action=mark_read&id=${id}&csrf_token=<?php echo $_SESSION['csrf_token']; ?>`)
                    .then(() => {
                        fetchNotifications(); // Refresh list
                        if (link && link !== '#' && link !== 'null') {
                            window.location.href = link;
                        }
                    });
            };

            // Initial Fetch and Polling
            fetchNotifications();
            setInterval(fetchNotifications, 30000); // Poll every 30 seconds

            // --- Dashboard Charts Initialization ---
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: { size: 12 }
                        }
                    }
                }
            };

            // 1. Employees per Department Chart
            const deptCtx = document.getElementById('deptChart').getContext('2d');
            new Chart(deptCtx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($dept_chart_labels); ?>,
                    datasets: [{
                        label: 'No. of Employees',
                        data: <?php echo json_encode($dept_chart_counts); ?>,
                        backgroundColor: 'rgba(52, 152, 219, 0.7)',
                        borderColor: 'rgba(52, 152, 219, 1)',
                        borderWidth: 1,
                        borderRadius: 5
                    }]
                },
                options: {
                    ...chartOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { stepSize: 1 }
                        }
                    }
                }
            });

            // 2. Allowance Breakdown Chart
            const alwCtx = document.getElementById('alwChart').getContext('2d');
            new Chart(alwCtx, {
                type: 'doughnut',
                data: {
                    labels: <?php echo json_encode($alw_chart_labels); ?>,
                    datasets: [{
                        data: <?php echo json_encode($alw_chart_counts); ?>,
                        backgroundColor: [
                            '#3498db', '#2ecc71', '#e74c3c', '#f1c40f', '#9b59b6', '#34495e'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    ...chartOptions,
                    cutout: '70%'
                }
            });
            // View Payroll Handler
            const payrollDetailsModal = new bootstrap.Modal(document.getElementById('payrollDetailsModal'));
            document.querySelectorAll('.view-payroll-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const runId = this.dataset.runId;
                    const period = this.dataset.period;
                    document.getElementById('modalPeriod').innerText = period;
                    document.getElementById('payrollDetailsBody').innerHTML = '<tr><td colspan="6" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></td></tr>';
                    payrollDetailsModal.show();
                    fetch(`dashboard.php?action=fetch_payroll_details&run_id=${runId}`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                let html = '';
                                if (data.payslips.length === 0) {
                                    html = '<tr><td colspan="6" class="text-center py-4">No data found.</td></tr>';
                                } else {
                                    data.payslips.forEach(p => {
                                        html += `<tr><td><div class="fw-bold">${p.first_name} ${p.last_name}</div><small class="text-muted">${p.employee_code}</small></td><td>${p.desig_name}</td><td class="text-end">৳${parseFloat(p.basic_salary).toLocaleString()}</td><td class="text-end text-success">+ ৳${parseFloat(p.total_allowances).toLocaleString()}</td><td class="text-end text-danger">- ৳${parseFloat(p.total_deductions).toLocaleString()}</td><td class="text-end fw-bold text-primary">৳${parseFloat(p.net_salary).toLocaleString()}</td></tr>`;
                                    });
                                }
                                document.getElementById('payrollDetailsBody').innerHTML = html;
                            } else { alert('Error fetching details.'); }
                        })
                        .catch(err => { console.error(err); alert('An error occurred.'); });
                });
            });

            // Real-time Search Implementation
            const searchInput = document.querySelector('.search-box input');
            if (searchInput) {
                searchInput.addEventListener('input', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    document.querySelectorAll('table tbody tr').forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                    document.querySelectorAll('.kpi-card').forEach(card => {
                        const text = card.textContent.toLowerCase();
                        card.closest('.col-md-3').style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                    document.querySelectorAll('.section-card').forEach(card => {
                        const text = card.textContent.toLowerCase();
                        card.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }

            // Notice Board JS Handlers
            const noticeModalEl = document.getElementById('noticeModal');
            const noticeModalObj = new bootstrap.Modal(noticeModalEl);

            noticeModalEl.addEventListener('show.bs.modal', function(event) {
                const button = event.relatedTarget;
                const noticeId = button.getAttribute('data-notice-id');
                const alertBox = document.getElementById('noticeFormAlert');
                alertBox.classList.add('d-none');

                document.getElementById('noticeForm').reset();
                
                if (noticeId) {
                    // Edit Mode
                    document.getElementById('noticeModalLabel').textContent = 'Loading Notice...';
                    fetch('process_notice.php?action=get&id=' + noticeId)
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                const n = data.notice;
                                document.getElementById('notice_id').value = n.id;
                                document.getElementById('notice_title').value = n.title;
                                document.getElementById('notice_content').value = n.content;
                                document.getElementById('notice_category').value = n.category;
                                document.getElementById('notice_footer_label').value = n.footer_label;
                                document.getElementById('notice_footer_value').value = n.footer_value;
                                document.getElementById('notice_button_text').value = n.button_text;
                                document.getElementById('notice_button_link').value = n.button_link;
                                document.getElementById('notice_status').value = n.status;
                                document.getElementById('noticeModalLabel').textContent = 'Edit Notice: ' + n.title;
                            } else {
                                alert('Error: ' + data.message);
                                noticeModalObj.hide();
                            }
                        });
                } else {
                    // Add Mode
                    document.getElementById('notice_id').value = '';
                    document.getElementById('noticeModalLabel').textContent = 'Add New Notice';
                }
            });

            window.deleteNotice = function(id) {
                if (confirm('Are you sure you want to delete this notice?')) {
                    const formData = new FormData();
                    formData.append('action', 'delete');
                    formData.append('id', id);
                    formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>');

                    fetch('process_notice.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    });
                }
            }

            const noticeForm = document.getElementById('noticeForm');
            if (noticeForm) {
                noticeForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    const btn = document.getElementById('saveNoticeBtn');
                    const alertBox = document.getElementById('noticeFormAlert');
                    const originalText = btn.innerHTML;
                    
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
                    
                    const formData = new FormData(this);
                    const noticeId = document.getElementById('notice_id').value;
                    formData.append('action', noticeId ? 'edit' : 'add');

                    fetch('process_notice.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        
                        alertBox.classList.remove('d-none', 'alert-success', 'alert-danger');
                        alertBox.classList.add(data.status === 'success' ? 'alert-success' : 'alert-danger');
                        alertBox.textContent = data.message;
                        
                        if (data.status === 'success') {
                            setTimeout(() => {
                                noticeModalObj.hide();
                                location.reload();
                            }, 1000);
                        }
                    })
                    .catch(error => {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                        alertBox.classList.remove('d-none', 'alert-success');
                        alertBox.classList.add('alert-danger');
                        alertBox.textContent = 'A system error occurred. Please try again.';
                        console.error('Error:', error);
                    });
                });
            }
        });
    </script>
</body>
</html>
