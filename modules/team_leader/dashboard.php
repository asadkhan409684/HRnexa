<?php
// Include database connection (which now starts the session globally)
require_once __DIR__ . '/../../app/Config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: " . BASE_URL . "login");
    exit();
}

// Check if user has Team Leader role (role_id = 4)
if ($_SESSION['user_role'] != 4) {
    header("Location: " . BASE_URL . "views/auth/unauthorized.php");
    exit();
}

// Get user information from session
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Team Leader';
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'teamlead@hrnexa.com';

// Fetch current team leader ID and details
$tl_query = "SELECT id FROM employees WHERE email = '$user_email' LIMIT 1";
$tl_result = $conn->query($tl_query);
$tl_data = $tl_result->fetch_assoc();
$tl_id = $tl_data['id'] ?? 0;
$employee_id = $tl_id; // For consistency with self-service logic

// Handle Job Requisition Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_requisition'])) {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['message'] = "Security token mismatch. Please try again.";
        $_SESSION['message_type'] = "danger";
        header("Location: dashboard.php");
        exit();
    }

    $title = trim($_POST['title']);
    $dept_id = intval($_POST['department_id']);
    $positions = intval($_POST['positions']);
    $description = trim($_POST['description']);
    $requirements = trim($_POST['requirements']);
    $experience = trim($_POST['experience'] ?? '');
    $salary_range = trim($_POST['salary_range'] ?? '');
    $joining_date = !empty($_POST['expected_joining_date']) ? $_POST['expected_joining_date'] : null;
    $hiring_reason = trim($_POST['hiring_reason'] ?? '');
    $creator_id = $tl_id;

    $stmt = $conn->prepare("INSERT INTO job_requisitions (title, department_id, positions, description, requirements, experience, salary_range, expected_joining_date, hiring_reason, created_by, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')");
    $stmt->bind_param("siissssssi", $title, $dept_id, $positions, $description, $requirements, $experience, $salary_range, $joining_date, $hiring_reason, $creator_id);

    if ($stmt->execute()) {
        $_SESSION['message'] = "Job requisition submitted successfully for review!";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error: " . $stmt->error;
        $_SESSION['message_type'] = "danger";
    }
    header("Location: dashboard.php#job-requisition");
    exit();
}


// Fetch team members (subordinates)
$team_members_query = "SELECT e.*, d.name as dept_name, des.name as desig_name 
                      FROM employees e 
                      LEFT JOIN departments d ON e.department_id = d.id 
                      LEFT JOIN designations des ON e.designation_id = des.id 
                      WHERE e.team_leader_id = $tl_id";
$team_members_result = $conn->query($team_members_query);

// Fetch personal employee data
$emp_query = "SELECT e.*, d.name as dept_name, des.name as desig_name, 
              CONCAT(m.first_name, ' ', m.last_name) as manager_name
              FROM employees e 
              LEFT JOIN departments d ON e.department_id = d.id 
              LEFT JOIN designations des ON e.designation_id = des.id 
              LEFT JOIN employees m ON e.team_leader_id = m.id
              WHERE e.id = $tl_id LIMIT 1";
$emp_result = $conn->query($emp_query);
$emp_data = ($emp_result && $emp_result->num_rows > 0) ? $emp_result->fetch_assoc() : null;

// Fetch leave types
$leave_types_query = "SELECT id, name, max_days_per_year FROM leave_types";
$leave_types_result = $conn->query($leave_types_query);
$leave_types_data = [];
if ($leave_types_result) {
    while($row = $leave_types_result->fetch_assoc()) {
        $leave_types_data[$row['id']] = $row;
    }
}

// Fetch personal leave applications
$leave_apps_query = "SELECT la.*, lt.name as leave_type_name 
                    FROM leave_applications la 
                    JOIN leave_types lt ON la.leave_type_id = lt.id 
                    WHERE la.employee_id = $employee_id 
                    ORDER BY la.applied_at DESC";
$leave_apps_result = $conn->query($leave_apps_query);

// Categorize and Calculate Personal Leave Balances
$leave_balances = ['core' => [], 'special' => []];
$total_allocated_core = 0; $total_used_core = 0;
$core_types = ['Annual Leave', 'Sick Leave', 'Emergency Leave'];

foreach ($leave_types_data as $id => $type) {
    $type_id = $type['id']; $allocated = $type['max_days_per_year'];
    $used_q = "SELECT SUM(total_days) as used_days FROM leave_applications WHERE employee_id = $employee_id AND leave_type_id = $type_id AND status = 'approved' AND YEAR(start_date) = YEAR(CURDATE())";
    $used_res = $conn->query($used_q);
    $used = $used_res->fetch_assoc()['used_days'] ?? 0;
    $balance_info = ['id' => $type_id, 'name' => $type['name'], 'allocated' => $allocated, 'used' => $used, 'balance' => $allocated - $used];
    if (in_array($type['name'], $core_types)) {
        $leave_balances['core'][] = $balance_info; $total_allocated_core += $allocated; $total_used_core += $used;
    } else {
        $leave_balances['special'][] = $balance_info;
    }
}
$total_balance_core = $total_allocated_core - $total_used_core;

// Fetch personal documents
$docs_query = "SELECT * FROM employee_documents WHERE employee_id = $employee_id ORDER BY uploaded_at DESC";
$docs_result = $conn->query($docs_query);

// Fetch personal attendance record for today
$today_att_query = "SELECT * FROM attendance WHERE employee_id = $employee_id AND date = CURDATE() ORDER BY id DESC LIMIT 1";
$today_att_result = $conn->query($today_att_query);
$today_att = $today_att_result->fetch_assoc();

$punch_in_time = ($today_att && !empty($today_att['punch_in'])) ? date('h:i A', strtotime($today_att['punch_in'])) : 'Not Yet';
$punch_out_time = ($today_att && !empty($today_att['punch_out']) && $today_att['punch_out'] != '0000-00-00 00:00:00') ? date('h:i A', strtotime($today_att['punch_out'])) : 'Not Yet';
$hours_worked = ($today_att && $today_att['total_hours']) ? $today_att['total_hours'] . 'h' : '0h';

// Fetch personal payslips
$payslips_query = "SELECT p.*, pr.month, pr.year FROM payslips p JOIN payroll_runs pr ON p.payroll_run_id = pr.id WHERE p.employee_id = $employee_id ORDER BY pr.year DESC, pr.month DESC";
$payslips_result = $conn->query($payslips_query);

// Fetch personal emergency contact
$emergency_query = "SELECT * FROM employee_emergency_contacts WHERE employee_id = $employee_id LIMIT 1";
$emergency_result = $conn->query($emergency_query);
$emergency_data = ($emergency_result && $emergency_result->num_rows > 0) ? $emergency_result->fetch_assoc() : null;

// NEW: Fetch Personal Attendance Stats for KPI
$attendance_stats_query = "SELECT 
    COUNT(*) as total_days,
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days
    FROM attendance 
    WHERE employee_id = $employee_id AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())";
$att_stats_result = $conn->query($attendance_stats_query);
$att_stats = $att_stats_result->fetch_assoc();
$personal_attendance_percent = ($att_stats['total_days'] > 0) ? round(($att_stats['present_days'] / $att_stats['total_days']) * 100) : 0;

// NEW: Fetch Personal Monthly Attendance History
$month_att_query = "SELECT * FROM attendance 
                   WHERE employee_id = $employee_id 
                   AND MONTH(date) = MONTH(CURDATE()) 
                   AND YEAR(date) = YEAR(CURDATE()) 
                   ORDER BY date DESC";
$month_att_result = $conn->query($month_att_query);

// NEW: Fetch Personal Attendance Correction History
$correction_history_query = "SELECT * FROM attendance_corrections WHERE employee_id = $employee_id ORDER BY created_at DESC LIMIT 10";
$correction_history_result = $conn->query($correction_history_query);

// Original Team Management data fetching
// Fetch pending leave applications for subordinates
$pending_leaves_query = "SELECT la.*, e.first_name, e.last_name, lt.name as leave_type_name 
                        FROM leave_applications la 
                        JOIN employees e ON la.employee_id = e.id 
                        JOIN leave_types lt ON la.leave_type_id = lt.id 
                        WHERE e.team_leader_id = $tl_id AND la.status = 'pending'
                        ORDER BY la.applied_at DESC";
$pending_leaves_result = $conn->query($pending_leaves_query);
$pending_count = $pending_leaves_result->num_rows;

// Count Total Team Members
$total_team_members = $team_members_result->num_rows;

// Count Present Today
$present_query = "SELECT COUNT(DISTINCT employee_id) as present_count FROM attendance 
                  WHERE employee_id IN (SELECT id FROM employees WHERE team_leader_id = $tl_id) 
                  AND date = CURDATE()";
$present_result = $conn->query($present_query);
$present_count = $present_result->fetch_assoc()['present_count'] ?? 0;

// Count On Leave Today
$on_leave_query = "SELECT COUNT(DISTINCT employee_id) as leave_count FROM leave_applications 
                   WHERE employee_id IN (SELECT id FROM employees WHERE team_leader_id = $tl_id) 
                   AND status = 'approved' AND CURDATE() BETWEEN start_date AND end_date";
$on_leave_result = $conn->query($on_leave_query);
$on_leave_count = $on_leave_result->fetch_assoc()['leave_count'] ?? 0;

// NEW: Fetch Designation Distribution for Team Overview
$designation_dist_query = "SELECT des.name as designation, COUNT(*) as count 
                          FROM employees e 
                          JOIN designations des ON e.designation_id = des.id 
                          WHERE e.team_leader_id = $tl_id 
                          GROUP BY des.name";
$designation_dist_result = $conn->query($designation_dist_query);

// NEW: Fetch Status Distribution for Team Overview
$status_dist_query = "SELECT employment_status, COUNT(*) as count 
                     FROM employees 
                     WHERE team_leader_id = $tl_id 
                     GROUP BY employment_status";
$status_dist_result = $conn->query($status_dist_query);

// NEW: Fetch Team Attendance Summary (Current Month)
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');
$team_attendance_summary_query = "SELECT e.id, e.first_name, e.last_name, 
    SUM(CASE WHEN a.status = 'present' OR a.status = 'late' THEN 1 ELSE 0 END) as present_days,
    SUM(CASE WHEN a.status = 'absent' THEN 1 ELSE 0 END) as absent_days,
    SUM(CASE WHEN a.status = 'late' THEN 1 ELSE 0 END) as late_days
    FROM employees e
    LEFT JOIN attendance a ON e.id = a.employee_id AND a.date BETWEEN '$month_start' AND '$month_end'
    WHERE e.team_leader_id = $tl_id
    GROUP BY e.id";
$team_attendance_summary_result = $conn->query($team_attendance_summary_query);

// NEW: Fetch Monthly Leave Stats for Dashboard Widget
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');
$leaf_month_query = "SELECT 
    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
    FROM leave_applications 
    WHERE employee_id IN (SELECT id FROM employees WHERE team_leader_id = $tl_id)
    AND start_date BETWEEN '$month_start' AND '$month_end'";
$leaf_month_result = $conn->query($leaf_month_query);
$leaf_month_stats = $leaf_month_result->fetch_assoc();

// NEW: Fetch Team Leave Summary (Current Year)
$year_start = date('Y-01-01');
$year_end = date('Y-12-31');
$team_leave_summary_query = "SELECT e.id, e.first_name, e.last_name,
    (SELECT SUM(max_days_per_year) FROM leave_types) as total_entitled,
    SUM(CASE WHEN la.status = 'approved' THEN la.total_days ELSE 0 END) as used_days,
    SUM(CASE WHEN la.status = 'pending' THEN la.total_days ELSE 0 END) as pending_days
    FROM employees e
    LEFT JOIN leave_applications la ON e.id = la.employee_id AND la.start_date BETWEEN '$year_start' AND '$year_end'
    WHERE e.team_leader_id = $tl_id
    GROUP BY e.id";
$team_leave_summary_result = $conn->query($team_leave_summary_query);

// NEW: Fetch Team Pending Attendance Corrections
$pending_corrections_query = "SELECT ac.*, e.first_name, e.last_name 
                             FROM attendance_corrections ac 
                             JOIN employees e ON ac.employee_id = e.id 
                             WHERE e.team_leader_id = $tl_id AND ac.status = 'pending' 
                             ORDER BY ac.created_at DESC";
$pending_corrections_result = $conn->query($pending_corrections_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Leader Dashboard - HRnexa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e5490;
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
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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
        }

        .topbar-left h5 {
            margin: 0;
            color: var(--primary-color);
            font-weight: 700;
        }

        .sidebar-toggle {
            display: none;
            font-size: 20px;
            cursor: pointer;
            color: var(--primary-color);
            margin-right: 15px;
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
            min-height: 100vh; /* Maintain height during section transitions */
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

        .info-box {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            border-left: 4px solid var(--secondary-color);
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .info-box .badge {
            padding: 8px 12px;
            border-radius: 6px;
            font-weight: 500;
        }

        /* Data Table */
        .data-table {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .data-table table {
            font-size: 14px;
        }

        .data-table table:not(.table-sm) {
            min-width: 600px;
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

        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-radius: 15px;
            padding: 35px;
            color: white;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(30, 84, 144, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -50px;
            right: -50px;
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }

        .welcome-banner h1 {
            font-size: 32px;
            font-weight: 800;
            margin-bottom: 10px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .welcome-banner p {
            opacity: 0.9;
            font-size: 16px;
            max-width: 600px;
            margin-bottom: 0;
        }

        .banner-date {
            position: absolute;
            right: 30px;
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
                width: 100%;
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
            
            .welcome-banner h1 {
                font-size: 22px;
            }

            .banner-date {
                position: absolute;
                right: 15px;
                bottom: 15px;
                font-size: 12px;
                padding: 4px 12px;
                margin-top: 0;
                display: flex;
                background: rgba(255,255,255,0.15);
                border-radius: 20px;
                backdrop-filter: blur(5px);
            }

            .topbar {
                padding: 10px 15px;
            }

            .page-header h1 {
                font-size: 22px;
            }

            .kpi-card {
                margin-bottom: 20px;
            }
        }

        @media (max-width: 576px) {
            .welcome-banner p {
                font-size: 14px;
            }
            
            .kpi-value {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-users"></i>
            <h5>HRnexa</h5>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-section">Team Dashboard</li>
            <li><a href="#dashboard" class="nav-link active" data-section="dashboard"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
            <li><a href="#job-requisition" class="nav-link" data-section="job-requisition"><i class="fas fa-file-contract"></i><span>Job Requisition</span></a></li>
            <li><a href="#team-overview" class="nav-link" data-section="team-overview"><i class="fas fa-users-rectangle"></i><span>Team Overview</span></a></li>
            <li><a href="#team-members" class="nav-link" data-section="team-members"><i class="fas fa-user-group"></i><span>Team Members</span></a></li>
            <li><a href="#attendance-summary" class="nav-link" data-section="attendance-summary"><i class="fas fa-clock"></i><span>Attendance Summary</span></a></li>
            <li><a href="#leave-summary" class="nav-link" data-section="leave-summary"><i class="fas fa-calendar-check"></i><span>Leave Summary</span></a></li>
            
            <li class="menu-section">Profile Management</li>
            <li><a href="#profile" class="nav-link" data-section="profile"><i class="fas fa-user"></i><span>View Profile</span></a></li>
            <li><a href="#documents" class="nav-link" data-section="documents"><i class="fas fa-file-upload"></i><span>Documents</span></a></li>

            <li class="menu-section">Personal Attendance</li>
            <li><a href="#personal-attendance" class="nav-link" data-section="personal-attendance"><i class="fas fa-history"></i><span>View Attendance</span></a></li>
            <li><a href="#punch" class="nav-link" data-section="punch"><i class="fas fa-hand-paper"></i><span>Punch In/Out</span></a></li>
            <li><a href="#correction" class="nav-link" data-section="correction"><i class="fas fa-edit"></i><span>Attendance Request</span></a></li>

            <li class="menu-section">Personal Leave</li>
            <li><a href="#apply-leave" class="nav-link" data-section="apply-leave"><i class="fas fa-calendar-plus"></i><span>Apply Leave</span></a></li>
            <li><a href="#leave-status" class="nav-link" data-section="leave-status"><i class="fas fa-calendar-check"></i><span>Leave Status</span></a></li>
            <li><a href="#leave-balance" class="nav-link" data-section="leave-balance"><i class="fas fa-chart-pie"></i><span>Leave Balance</span></a></li>

            <li class="menu-section">Approvals</li>
            <li><a href="#leave-approval" class="nav-link" data-section="leave-approval"><i class="fas fa-check-circle"></i><span>Leave Approval Refer</span></a></li>
            <li><a href="#attendance-approval" class="nav-link" data-section="attendance-approval"><i class="fas fa-tasks"></i><span>Attendance Correction Refer</span></a></li>
            <li><a href="#performance-approval" class="nav-link" data-section="performance-approval"><i class="fas fa-star"></i><span>Performance Review Refer</span></a></li>
            
            <li class="menu-section">Payroll</li>
            <li><a href="#payslips" class="nav-link" data-section="payslips"><i class="fas fa-receipt"></i><span>View Payslips</span></a></li>

            <li class="menu-section">Team Performance</li>
            <li><a href="#kpi-monitoring" class="nav-link" data-section="kpi-monitoring"><i class="fas fa-chart-line"></i><span>KPI Monitoring</span></a></li>
            <li><a href="#appraisal-recommendation" class="nav-link" data-section="appraisal-recommendation"><i class="fas fa-thumbs-up"></i><span>Appraisal Recommendation</span></a></li>
            
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
                <h5 id="topbar-title">Dashboard</h5>
            </div>
            <div class="topbar-right">
                <div class="user-avatar"><?php echo strtoupper(substr($user_name, 0, 2)); ?></div>
                <div style="margin-left: 10px;">
                    <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($user_name); ?></div>
                    <div style="font-size: 12px; color: #999;">Team Leader</div>
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
                    <h1>Hello, <?php echo explode(' ', $user_name)[0]; ?>!</h1>
                    <p>Welcome back! Here's what's happening with your team today.</p>
                    <div class="banner-date">
                        <i class="far fa-calendar-alt me-2"></i><?php echo date('l, d F Y'); ?>
                    </div>
                </div>

                <div class="page-header d-none">
                    <div>
                        <h1>Team Leader Dashboard</h1>
                        <p style="color: #999; margin-top: 5px;">Team Management, Approvals & Performance Monitoring</p>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fas fa-users-rectangle"></i></div>
                            <div class="kpi-value"><?php echo $total_team_members; ?></div>
                            <div class="kpi-label">Team Members</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fas fa-check-circle"></i></div>
                            <div class="kpi-value"><?php echo $present_count; ?></div>
                            <div class="kpi-label">Present Today</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fas fa-hourglass-half"></i></div>
                            <div class="kpi-value"><?php echo $on_leave_count; ?></div>
                            <div class="kpi-label">On Leave Today</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="kpi-card">
                            <div class="kpi-icon"><i class="fas fa-exclamation-circle"></i></div>
                            <div class="kpi-value"><?php echo $pending_count; ?></div>
                            <div class="kpi-label">Pending Approvals</div>
                        </div>
                    </div>
                </div>

                <!-- Team Attendance & Leave Summary -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="section-card">
                            <div class="section-title">
                                <i class="fas fa-clock"></i>
                                Today's Attendance
                            </div>
                            <div class="data-table">
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <td style="border: none;"><strong>Present:</strong></td>
                                        <td style="border: none;" class="text-end"><span class="badge bg-success"><?php echo $present_count; ?></span></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none;"><strong>On Leave:</strong></td>
                                        <td style="border: none;" class="text-end"><span class="badge bg-warning"><?php echo $on_leave_count; ?></span></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none;"><strong>Absent:</strong></td>
                                        <td style="border: none;" class="text-end"><span class="badge bg-danger"><?php echo ($total_team_members - $present_count - $on_leave_count); ?></span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="section-card">
                            <div class="section-title">
                                <i class="fas fa-calendar-check"></i>
                                Leave Summary (This Month)
                            </div>
                            <div class="data-table">
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <td style="border: none;"><strong>Approved:</strong></td>
                                        <td style="border: none;" class="text-end"><span class="badge bg-success"><?php echo $leaf_month_stats['approved_count'] ?? 0; ?></span></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none;"><strong>Pending:</strong></td>
                                        <td style="border: none;" class="text-end"><span class="badge bg-warning"><?php echo $leaf_month_stats['pending_count'] ?? 0; ?></span></td>
                                    </tr>
                                    <tr>
                                        <td style="border: none;"><strong>Rejected:</strong></td>
                                        <td style="border: none;" class="text-end"><span class="badge bg-danger"><?php echo $leaf_month_stats['rejected_count'] ?? 0; ?></span></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Approvals Widget -->
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-clock"></i>
                        Pending Actions
                    </div>
                    <div class="data-table">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Member</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($pending_leaves_result && $pending_leaves_result->num_rows > 0): ?>
                                    <?php 
                                    $count = 0;
                                    $pending_leaves_result->data_seek(0);
                                    while($leaf = $pending_leaves_result->fetch_assoc()): 
                                        if($count >= 5) break;
                                    ?>
                                    <tr>
                                        <td><i class="fas fa-calendar-check text-primary"></i> Leave Request</td>
                                        <td><?php echo htmlspecialchars($leaf['first_name'] . ' ' . $leaf['last_name']); ?></td>
                                        <td><?php echo date('d-M-Y', strtotime($leaf['applied_at'])); ?></td>
                                        <td><span class="badge bg-warning">Pending</span></td>
                                    </tr>
                                    <?php $count++; endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted">No pending actions.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Team Overview Section -->
            <div id="team-overview-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Team Overview</h1>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="section-card">
                            <div class="section-title"><i class="fas fa-users-rectangle"></i> Team Statistics</div>
                            <div class="data-table">
                                <table class="table table-sm mb-0">
                                    <tr>
                                        <td style="border: none;"><strong>Total Members:</strong></td>
                                        <td style="border: none;" class="text-end"><strong><?php echo $total_team_members; ?></strong></td>
                                    </tr>
                                    <?php while($status = $status_dist_result->fetch_assoc()): 
                                        $badge_class = ($status['employment_status'] == 'active') ? 'bg-success' : 'bg-info';
                                    ?>
                                    <tr>
                                        <td style="border: none;"><strong><?php echo ucfirst($status['employment_status']); ?>:</strong></td>
                                        <td style="border: none;" class="text-end"><span class="badge <?php echo $badge_class; ?>"><?php echo $status['count']; ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="section-card">
                            <div class="section-title"><i class="fas fa-chart-pie"></i> Designation Distribution</div>
                            <div class="data-table">
                                <table class="table table-sm mb-0">
                                    <?php while($desig = $designation_dist_result->fetch_assoc()): ?>
                                    <tr>
                                        <td style="border: none;"><strong><?php echo htmlspecialchars($desig['designation']); ?>:</strong></td>
                                        <td style="border: none;" class="text-end"><?php echo $desig['count']; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            // Include extracted component files
            include('team_members.php');
            include('team_attendance.php');
            include('team_leaves.php');
            include('leave_approval.php');
            include('attendance_review.php');
            include('team_expenses.php');
            include('performance_review.php');
            include('team_performance.php');
            include('my_profile.php');
            include('my_attendance.php');
            include('my_leave.php');
            include('my_payroll.php');
            include('requisition.php');
            ?>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar Toggle for Mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.querySelector('.sidebar');
        
        if (sidebarToggle) {
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
            });
        }

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

        function switchSection(section) {
            const selectedSection = document.getElementById(section + '-section');
            if (!selectedSection) return;

            // Update URL hash for persistence without jumping
            if (window.location.hash !== '#' + section) {
                window.history.replaceState(null, null, '#' + section);
            }

            // Show selected section first (prevents height collapse)
            selectedSection.style.display = 'block';

            // Hide all OTHER sections
            document.querySelectorAll('.section-content').forEach(el => {
                if (el !== selectedSection) {
                    el.style.display = 'none';
                }
            });

            // Close sidebar on mobile after clicking
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
                'job-requisition': 'Job Requisition',
                'team-overview': 'Team Overview',
                'team-members': 'Team Members',
                'attendance-summary': 'Attendance Summary',
                'leave-summary': 'Leave Summary',
                'profile': 'My Profile',
                'documents': 'My Documents',
                'personal-attendance': 'My Attendance',
                'punch': 'Punch In / Out',
                'correction': 'Attendance Correction Request',
                'apply-leave': 'Apply Leave',
                'leave-status': 'Leave Status',
                'leave-balance': 'Leave Balance',
                'leave-approval': 'Leave Approval',
                'attendance-approval': 'Attendance Correction',
                'expense-approval': 'Expense Approval',
                'performance-approval': 'Performance Review Approval',
                'payslips': 'My Payslips',
                'kpi-monitoring': 'KPI Monitoring',
                'appraisal-recommendation': 'Appraisal Recommendation'
            };
            document.getElementById('topbar-title').textContent = titles[section] || 'Dashboard';
        }

        // Section & Scroll Persistence on Load
        window.addEventListener('DOMContentLoaded', () => {
            // Restore Section
            const hash = window.location.hash.substring(1);
            if (hash) {
                switchSection(hash);
            } else {
                switchSection('dashboard');
            }

            // Restore Scroll Position
            const scrollPos = sessionStorage.getItem('scrollPos');
            if (scrollPos) {
                window.scrollTo(0, parseInt(scrollPos));
                sessionStorage.removeItem('scrollPos');
            }
        });

        // Helper to reload with scroll persistence
        function reloadWithScroll() {
            sessionStorage.setItem('scrollPos', window.scrollY);
            location.reload();
        }


        // Time updates and Leave Calculation logic
        function updateTime() {
            const now = new Date();
            const timeEl = document.getElementById('current-time');
            if (timeEl) timeEl.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
        }
        setInterval(updateTime, 60000); updateTime();

        function calculateLeaveDays() {
            const startStr = document.getElementById('leave_from_date').value;
            const endStr = document.getElementById('leave_to_date').value;
            if (startStr && endStr) {
                const start = new Date(startStr); const end = new Date(endStr);
                start.setHours(0,0,0,0); end.setHours(0,0,0,0);
                if (end >= start) {
                    const diffDays = Math.round((end.getTime() - start.getTime()) / (1000 * 3600 * 24)) + 1;
                    document.getElementById('total_days_display').value = diffDays;
                } else { document.getElementById('total_days_display').value = 0; }
            }
        }
        ['leave_from_date', 'leave_to_date'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', calculateLeaveDays);
        });

        // AJAX Global Alert Function
        function showGlobalAlert(message, type = 'success') {
            const topbar = document.querySelector('.topbar');
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show mb-0" role="alert" style="padding: 5px 35px 5px 15px; font-size: 13px; position: absolute; right: 20px; top: 15px; z-index: 1001; min-width: 250px;">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 10px;"></button>
                </div>
            `;
            const alertPlaceholder = document.createElement('div');
            alertPlaceholder.innerHTML = alertHtml;
            const alertElement = alertPlaceholder.firstElementChild;
            topbar.appendChild(alertElement);
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alertElement);
                if (bsAlert) bsAlert.close();
            }, 5000);
        }

        function handlePunch(action) {
            const msgDiv = document.getElementById('punch-message');
            msgDiv.style.display = 'block'; 
            msgDiv.className = 'text-center mt-2 small text-info'; 
            msgDiv.textContent = 'Processing...';
            
            const formData = new FormData(); 
            formData.append('action', action);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>'); // Add CSRF token
            
            fetch('../employee/process_punch.php', { method: 'POST', body: formData })
            .then(r => r.json()).then(data => {
                msgDiv.textContent = data.message;
                msgDiv.className = 'text-center mt-2 small ' + (data.status === 'success' ? 'text-success' : 'text-danger');
                if (data.status === 'success') setTimeout(() => reloadWithScroll(), 1500);
            }).catch(e => { msgDiv.textContent = 'Error occurred.'; msgDiv.className = 'text-center mt-2 small text-danger'; });
        }

        // Form handlers for Leave and Correction
        const leaveForm = document.getElementById('leaveApplicationForm');
        if (leaveForm) {
            leaveForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const msg = document.getElementById('leaveFormMessage');
                msg.style.display = 'block'; msg.className = 'alert alert-info'; msg.textContent = 'Submitting...';
                
                const formData = new FormData(this);
                formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>'); // Add CSRF token
                
                fetch('../employee/process_leave.php', { method: 'POST', body: formData })
                .then(r => r.json()).then(data => {
                    msg.className = 'alert ' + (data.status === 'success' ? 'alert-success' : 'alert-danger');
                    msg.textContent = data.message;
                    if (data.status === 'success') setTimeout(() => reloadWithScroll(), 1500);
                });
            });
        }

        const correctionForm = document.getElementById('attendanceCorrectionForm');
        if (correctionForm) {
            correctionForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const msg = document.getElementById('correctionFormMessage');
                msg.style.display = 'block'; msg.className = 'alert alert-info'; msg.textContent = 'Submitting...';
                
                const formData = new FormData(this);
                formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>'); // Add CSRF token
                
                fetch('../employee/process_correction.php', { method: 'POST', body: formData })
                .then(r => r.json()).then(data => {
                    msg.className = 'alert ' + (data.status === 'success' ? 'alert-success' : 'alert-danger');
                    msg.textContent = data.message;
                    if (data.status === 'success') setTimeout(() => reloadWithScroll(), 1500);
                });
            });
        }

        // Personal Profile & Document Handlers
        document.addEventListener('submit', function(e) {
            if (e.target.id === 'emergencyContactForm') {
                e.preventDefault();
                const btn = e.target.querySelector('button[type="submit"]');
                btn.disabled = true;
                
                const formData = new FormData(e.target);
                formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>'); // Add CSRF token
                
                fetch('api/update_emergency_contact.php', { method: 'POST', body: formData })
                .then(r => r.json()).then(data => {
                    showGlobalAlert(data.message, data.status === 'success' ? 'success' : 'danger');
                    if (data.status === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('editEmergencyModal')).hide();
                        setTimeout(() => reloadWithScroll(), 1500);
                    }
                }).finally(() => btn.disabled = false);
            }

            if (e.target.id === 'uploadDocumentForm') {
                e.preventDefault();
                const btn = e.target.querySelector('button[type="submit"]');
                btn.disabled = true;
                
                const formData = new FormData(e.target);
                formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>'); // Add CSRF token
                
                fetch('api/upload_document.php', { method: 'POST', body: formData })
                .then(r => r.json()).then(data => {
                    showGlobalAlert(data.message, data.status === 'success' ? 'success' : 'danger');
                    if (data.status === 'success') {
                        bootstrap.Modal.getInstance(document.getElementById('uploadDocModal')).hide();
                        setTimeout(() => reloadWithScroll(), 1500);
                    }
                }).finally(() => btn.disabled = false);
            }
        });

        // Team Approval Handlers (Refer/Reject)
        window.handleTeamAction = function(type, id, status, btn) {
            const originalText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
            
            const formData = new FormData();
            formData.append('action_type', type);
            formData.append('id', id);
            formData.append('status', status);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>'); // Add CSRF token
            
            fetch('api/update_status.php', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                showGlobalAlert(data.message, data.status === 'success' ? 'success' : 'danger');
                if (data.status === 'success') {
                    const row = btn.closest('tr');
                    if (row) row.style.opacity = '0.5';
                    setTimeout(() => reloadWithScroll(), 1000);
                }
            })
            .catch(e => showGlobalAlert('Error processing request', 'danger'))
            .finally(() => {
                btn.disabled = false;
                btn.innerHTML = originalText;
            });
        };
    </script>
</body>
</html>
