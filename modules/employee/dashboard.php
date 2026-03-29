<?php
// Include database connection (which now starts the session globally)
require_once __DIR__ . '/../../app/Config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: " . BASE_URL . "login");
    exit();
}

// Check if user has Employee role (role_id = 5)
if ($_SESSION['user_role'] != 5) {
    header("Location: " . BASE_URL . "views/auth/unauthorized.php");
    exit();
}

// Get user information from session
$user_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : 'Employee';
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'employee@hrnexa.com';

$emp_query = "SELECT e.*, d.name as dept_name, des.name as desig_name, 
              CONCAT(m.first_name, ' ', m.last_name) as manager_name
              FROM employees e 
              LEFT JOIN departments d ON e.department_id = d.id 
              LEFT JOIN designations des ON e.designation_id = des.id 
              LEFT JOIN employees m ON e.team_leader_id = m.id
              WHERE e.email = '$user_email' LIMIT 1";
$emp_result = $conn->query($emp_query);
$emp_data = ($emp_result && $emp_result->num_rows > 0) ? $emp_result->fetch_assoc() : null;

// Fetch leave types - fetching all to ensure "optional" types are available if needed
$leave_types_query = "SELECT id, name, max_days_per_year FROM leave_types";
$leave_types_result = $conn->query($leave_types_query);
$leave_types_data = [];
if ($leave_types_result) {
    while($row = $leave_types_result->fetch_assoc()) {
        $leave_types_data[$row['id']] = $row;
    }
}

// Fetch leave applications for the employee
$employee_id = $emp_data['id'] ?? 0;
$leave_apps_query = "SELECT la.*, lt.name as leave_type_name 
                    FROM leave_applications la 
                    JOIN leave_types lt ON la.leave_type_id = lt.id 
                    WHERE la.employee_id = $employee_id 
                    ORDER BY la.applied_at DESC";
$leave_apps_result = $conn->query($leave_apps_query);

// Count Pending/Referred Leaves for Dashboard
$pending_leaves_count = 0;
if ($leave_apps_result) {
    while($row = $leave_apps_result->fetch_assoc()) {
        if ($row['status'] == 'pending' || $row['status'] == 'referred') {
            $pending_leaves_count++;
        }
    }
    // Reset result set for later use in the history table
    $leave_apps_result->data_seek(0);
}

// Categorize and Calculate Leave Balances
$leave_balances = [
    'core' => [],
    'special' => []
];
$total_allocated_core = 0;
$total_used_core = 0;

// Define core leave types by name for flexibility
$core_types = ['Annual Leave', 'Sick Leave', 'Emergency Leave'];

foreach ($leave_types_data as $id => $type) {
    $type_id = $type['id'];
    $allocated = $type['max_days_per_year'];
    
    $used_query = "SELECT SUM(total_days) as used_days FROM leave_applications 
                   WHERE employee_id = $employee_id AND leave_type_id = $type_id 
                   AND status = 'approved' AND YEAR(start_date) = YEAR(CURDATE())";
    $used_result = $conn->query($used_query);
    $used_row = $used_result->fetch_assoc();
    $used = $used_row['used_days'] ?? 0;
    
    $balance_info = [
        'id' => $type_id,
        'name' => $type['name'],
        'allocated' => $allocated,
        'used' => $used,
        'balance' => $allocated - $used
    ];

    if (in_array($type['name'], $core_types)) {
        $leave_balances['core'][] = $balance_info;
        $total_allocated_core += $allocated;
        $total_used_core += $used;
    } else {
        $leave_balances['special'][] = $balance_info;
    }
}
$total_balance_core = $total_allocated_core - $total_used_core;

// Fetch Attendance Stats
$attendance_stats_query = "SELECT 
    COUNT(*) as total_days,
    SUM(CASE WHEN status = 'present' THEN 1 ELSE 0 END) as present_days
    FROM attendance 
    WHERE employee_id = $employee_id AND MONTH(date) = MONTH(CURDATE()) AND YEAR(date) = YEAR(CURDATE())";
$att_stats_result = $conn->query($attendance_stats_query);
$att_stats = $att_stats_result->fetch_assoc();
$attendance_percent = ($att_stats['total_days'] > 0) ? round(($att_stats['present_days'] / $att_stats['total_days']) * 100) : 0;

// Fetch Last Performance Rating
$rating_query = "SELECT final_rating FROM performance_reviews WHERE employee_id = $employee_id AND status = 'finalized' ORDER BY id DESC LIMIT 1";
$rating_result = $conn->query($rating_query);
$rating_row = $rating_result->fetch_assoc();
$last_rating = $rating_row['final_rating'] ?? 'N/A';

// Fetch Payslips
$payslips_query = "SELECT p.*, pr.month, pr.year 
                  FROM payslips p 
                  JOIN payroll_runs pr ON p.payroll_run_id = pr.id 
                  WHERE p.employee_id = $employee_id AND p.status = 'approved'
                  ORDER BY pr.year DESC, pr.month DESC";
$payslips_result = $conn->query($payslips_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard - HRnexa</title>
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
            transition: all 0.3s ease;
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

        .topbar-left {
            display: flex;
            align-items: center;
        }

        .sidebar-toggle {
            display: none;
            font-size: 20px;
            color: var(--primary-color);
            margin-right: 15px;
            cursor: pointer;
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
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-height: 140px;
        }

        .kpi-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }
        
        .kpi-card::after {
            content: '';
            position: absolute;
            right: -20px;
            bottom: -20px;
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            font-size: 80px;
            opacity: 0.1;
            transition: all 0.3s ease;
        }
        
        .kpi-card.card-primary { border-top: 5px solid var(--primary-color); }
        .kpi-card.card-success { border-top: 5px solid var(--success-color); }
        .kpi-card.card-info { border-top: 5px solid var(--info-color); }
        .kpi-card.card-warning { border-top: 5px solid var(--warning-color); }

        .kpi-card.card-primary::after { content: '\f274'; } /* calendar-check */
        .kpi-card.card-success::after { content: '\f017'; } /* clock */
        .kpi-card.card-info::after { content: '\f570'; }    /* file-invoice */
        .kpi-card.card-warning::after { content: '\f005'; } /* star */

        .kpi-value {
            font-size: 32px;
            font-weight: 800;
            color: #2c3e50;
            margin: 5px 0;
            z-index: 2;
        }

        .kpi-label {
            font-size: 13px;
            color: #7f8c8d;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            z-index: 2;
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
            position: relative;
            z-index: 1;
        }
        
        .welcome-banner p {
            opacity: 0.9;
            font-size: 16px;
            max-width: 600px;
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

        /* Data Table */
        .data-table {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .data-table table {
            font-size: 14px;
            min-width: 600px; /* Force minimum width to allow scrolling instead of squeezing */
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

        .quick-action-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid #eee;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .quick-action-card:hover {
            border-color: var(--secondary-color);
            background-color: rgba(40, 116, 166, 0.05);
            transform: translateY(-3px);
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }

        .quick-action-card i {
            font-size: 24px;
            color: var(--secondary-color);
        }

        .quick-action-card span {
            font-weight: 600;
            font-size: 14px;
            color: #444;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -280px;
                width: 280px;
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
                padding: 25px 20px;
            }
            
            .welcome-banner h1 {
                font-size: 24px;
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
            .kpi-card {
                padding: 20px 15px;
                min-height: 120px;
            }

            .kpi-value {
                font-size: 26px;
            }
            
            .quick-action-card {
                padding: 15px 10px;
            }
            
            .quick-action-card span {
                font-size: 11px;
            }
            
            .welcome-banner {
                padding: 20px 15px;
            }
            
            .welcome-banner h1 {
                font-size: 22px;
            }

            .welcome-banner p {
                font-size: 14px;
                padding-bottom: 20px;
            }

            .banner-date {
                position: absolute;
                right: 15px;
                bottom: 15px;
                font-size: 12px;
                padding: 4px 12px;
                background: rgba(255,255,255,0.15);
                border-radius: 20px;
                backdrop-filter: blur(5px);
                display: flex;
                align-items: center;
                gap: 8px;
                z-index: 1;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-user-circle"></i>
            <h5>HRnexa</h5>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-section">Main</li>
            <li><a href="#dashboard" class="nav-link active" data-section="dashboard"><i class="fas fa-home"></i><span>Dashboard</span></a></li>
            
            <li class="menu-section">My Profile</li>
            <li><a href="#profile" class="nav-link" data-section="profile"><i class="fas fa-user"></i><span>View Profile</span></a></li>
            <li><a href="#documents" class="nav-link" data-section="documents"><i class="fas fa-file-upload"></i><span>Documents</span></a></li>
            
            <li class="menu-section">Attendance & Time</li>
            <li><a href="#attendance" class="nav-link" data-section="attendance"><i class="fas fa-clock"></i><span>Attendance</span></a></li>
            <li><a href="#punch" class="nav-link" data-section="punch"><i class="fas fa-hand-paper"></i><span>Punch In/Out</span></a></li>
            <li><a href="#correction" class="nav-link" data-section="correction"><i class="fas fa-edit"></i><span>Attendance Request</span></a></li>
            
            <li class="menu-section">Leave</li>
            <li><a href="#apply-leave" class="nav-link" data-section="apply-leave"><i class="fas fa-calendar-plus"></i><span>Apply Leave</span></a></li>
            <li><a href="#leave-status" class="nav-link" data-section="leave-status"><i class="fas fa-calendar-check"></i><span>Leave Status</span></a></li>
            <li><a href="#leave-balance" class="nav-link" data-section="leave-balance"><i class="fas fa-chart-pie"></i><span>Leave Balance</span></a></li>
            
            <li class="menu-section">Payroll & Finance</li>
            <li><a href="#payslips" class="nav-link" data-section="payslips"><i class="fas fa-receipt"></i><span>Payslips</span></a></li>
            <li><a href="#my-deductions" class="nav-link" data-section="my-deductions"><i class="fas fa-hand-holding-usd"></i><span>My Deductions</span></a></li>
            
            <li class="menu-section">Growth</li>
            <li><a href="#goals" class="nav-link" data-section="goals"><i class="fas fa-bullseye"></i><span>Goals</span></a></li>
            <li><a href="#appraisals" class="nav-link" data-section="appraisals"><i class="fas fa-star"></i><span>Appraisal History</span></a></li>
            <li><a href="#training" class="nav-link" data-section="training"><i class="fas fa-graduation-cap"></i><span>Training</span></a></li>
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
                <?php if (isset($_SESSION['message'])): ?>
                    <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show mb-0 me-3" role="alert" style="padding: 5px 35px 5px 15px; font-size: 13px;">
                        <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 10px;"></button>
                    </div>
                <?php endif; ?>
                <div class="user-avatar"><?php echo strtoupper(substr($user_name, 0, 2)); ?></div>
                <div style="margin-left: 10px;">
                    <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($user_name); ?></div>
                    <div style="font-size: 12px; color: #999;">Employee</div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
            <!-- Dashboard Section -->
            <div id="dashboard-section" class="section-content">
                <div class="welcome-banner">
                    <h1>Hello, <?php echo explode(' ', $user_name)[0]; ?>!</h1>
                    <p>Welcome back to your workspace. Have a productive day ahead!</p>
                    <div class="banner-date">
                        <i class="far fa-calendar-alt"></i>
                        <?php echo date('l, d F Y'); ?>
                    </div>
                </div>
                
                <!-- KPI Cards -->
                <div class="row">
                    <div class="col-md-3 col-sm-6">
                        <div class="kpi-card card-primary">
                            <div class="kpi-value"><?php echo $total_balance_core; ?></div>
                            <div class="kpi-label">Core Leaves left</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="kpi-card card-success">
                            <div class="kpi-value"><?php echo $attendance_percent; ?>%</div>
                            <div class="kpi-label">Attendance Score</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="kpi-card card-info">
                            <div class="kpi-value"><?php echo $payslips_result ? $payslips_result->num_rows : 0; ?></div>
                            <div class="kpi-label">Total Payslips</div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="kpi-card card-warning">
                            <div class="kpi-value"><?php echo $last_rating; ?><?php if(is_numeric($last_rating)) echo "/5"; ?></div>
                            <div class="kpi-label">Performance Rating</div>
                        </div>
                    </div>
                </div>

                <!-- Announcements & Notifications -->
                <div class="row">
                    <div class="col-lg-6">
                        <div class="section-card">
                            <div class="section-title">
                                <i class="fas fa-megaphone"></i>
                                Announcements
                            </div>
                            <div class="data-table">
                                <div class="list-group list-group-flush">
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Company Foundation Day Celebration</h6>
                                            <small>Today</small>
                                        </div>
                                        <p class="mb-1">Join us for our annual celebration on 25th January 2026</p>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">New HR Policy Updated</h6>
                                            <small>Yesterday</small>
                                        </div>
                                        <p class="mb-1">Please review the updated work from home policy</p>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Mandatory Training Session</h6>
                                            <small>2 days ago</small>
                                        </div>
                                        <p class="mb-1">Compliance training on 27th January 2026</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="section-card">
                            <div class="section-title">
                                <i class="fas fa-bell"></i>
                                Notifications
                            </div>
                            <div class="data-table">
                                <div class="list-group list-group-flush">
                                    <?php 
                                    $notif_count = 0;
                                    
                                    // 1. Leave Check
                                    if ($leave_apps_result && $leave_apps_result->num_rows > 0) {
                                        $latest_app = $leave_apps_result->fetch_assoc();
                                        $status_badge = ($latest_app['status'] == 'approved') ? 'bg-success' : (($latest_app['status'] == 'pending' || $latest_app['status'] == 'referred') ? 'bg-warning' : 'bg-danger');
                                        
                                        echo '<div class="list-group-item">';
                                        echo '<div class="d-flex w-100 justify-content-between">';
                                        echo '<h6 class="mb-1"><span class="badge '.$status_badge.'">Leave</span> Application Update</h6>';
                                        echo '<small>Recent</small>';
                                        echo '</div>';
                                        echo '<p class="mb-1">Your leave request for '.date('d M', strtotime($latest_app['start_date'])).' is currently <strong>'.$latest_app['status'].'</strong>.</p>';
                                        echo '</div>';
                                        $notif_count++;
                                        $leave_apps_result->data_seek(0);
                                    }

                                    // 2. Payslip Check
                                    if ($payslips_result && $payslips_result->num_rows > 0) {
                                        $latest_pay = $payslips_result->fetch_assoc();
                                        echo '<div class="list-group-item">';
                                        echo '<div class="d-flex w-100 justify-content-between">';
                                        echo '<h6 class="mb-1"><span class="badge bg-info">Payroll</span> New Payslip</h6>';
                                        echo '<small>Latest</small>';
                                        echo '</div>';
                                        echo '<p class="mb-1">Your payslip for '.date('F Y', mktime(0, 0, 0, $latest_pay['month'], 1, $latest_pay['year'])).' is available for download.</p>';
                                        echo '</div>';
                                        $notif_count++;
                                        $payslips_result->data_seek(0);
                                    }

                                    // 3. Fallback or generic
                                    if ($notif_count < 3) {
                                        echo '<div class="list-group-item">';
                                        echo '<div class="d-flex w-100 justify-content-between">';
                                        echo '<h6 class="mb-1"><span class="badge bg-secondary">Doc</span> Documentation</h6>';
                                        echo '<small>Reminder</small>';
                                        echo '</div>';
                                        echo '<p class="mb-1">Ensure your personal documents are up to date in the Documents section.</p>';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-star"></i>
                        Quick Actions
                    </div>
                    <div class="row">
                        <div class="col-md-3 col-6 mb-3">
                            <div class="quick-action-card" onclick="switchSection('apply-leave')">
                                <i class="fas fa-calendar-plus"></i>
                                <span>Apply Leave</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="quick-action-card" onclick="switchSection('punch')">
                                <i class="fas fa-hand-paper"></i>
                                <span>Punch In/Out</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="quick-action-card" onclick="switchSection('documents')">
                                <i class="fas fa-file-upload"></i>
                                <span>Documents</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6 mb-3">
                            <div class="quick-action-card" onclick="switchSection('payslips')">
                                <i class="fas fa-file-invoice-dollar"></i>
                                <span>View Payslip</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'profile.php'; ?>
            <?php include 'documents.php'; ?>
            <?php include 'attendance.php'; ?>
            <?php include 'punch.php'; ?>
            <?php include 'attendance_requests.php'; ?>
            <?php include 'leave_apply.php'; ?>
            <?php include 'leave_history.php'; ?>
            <?php include 'leave_balance.php'; ?>
            <?php include 'payslips.php'; ?>
            <?php include 'my_deductions.php'; ?>
            <?php include 'performance.php'; ?>
            <?php include 'appraisals.php'; ?>
            <?php include 'training.php'; ?>

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

                // Close sidebar on mobile after clicking
                if (window.innerWidth <= 768 && sidebar) {
                    sidebar.classList.remove('active');
                }
            });
        });

        function switchSection(section) {
            // Hide all sections
            document.querySelectorAll('.section-content').forEach(el => {
                el.style.display = 'none';
            });

            // Show selected section
            const selectedSection = document.getElementById(section + '-section');
            if (selectedSection) {
                selectedSection.style.display = 'block';
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
                'profile': 'My Profile',
                'documents': 'My Documents',
                'attendance': 'My Attendance',
                'punch': 'Punch In / Out',
                'correction': 'Attendance Correction Request',
                'apply-leave': 'Apply Leave',
                'leave-status': 'Leave Status',
                'leave-balance': 'Leave Balance',
                'payslips': 'My Payslips',
                'my-deductions': 'My Deductions',
                'goals': 'My Goals',
                'appraisals': 'Appraisal History',
                'training': 'Training & Development',
            };
            document.getElementById('topbar-title').textContent = titles[section] || 'Dashboard';
        }

        // Reload helper that removes URL hash before reloading
        function reloadWithoutHash() {
            const url = window.location.protocol + '//' + window.location.host + window.location.pathname + window.location.search;
            window.location.href = url;
        }

        // Update current time
        function updateTime() {
            const now = new Date();
            const timeEl = document.getElementById('current-time');
            if (timeEl) {
                timeEl.textContent = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
            }
        }

        // Update time every minute
        setInterval(updateTime, 60000);
        updateTime();

        // Auto-calculate leave days
        function calculateLeaveDays() {
            const startStr = document.getElementById('leave_from_date').value;
            const endStr = document.getElementById('leave_to_date').value;
            
            if (startStr && endStr) {
                const start = new Date(startStr);
                const end = new Date(endStr);
                
                // Set both to midnight to avoid time-of-day issues
                start.setHours(0, 0, 0, 0);
                end.setHours(0, 0, 0, 0);
                
                if (end >= start) {
                    const diffTime = end.getTime() - start.getTime();
                    const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24)) + 1;
                    document.getElementById('total_days_display').value = diffDays;
                } else {
                    document.getElementById('total_days_display').value = 0;
                }
            } else {
                document.getElementById('total_days_display').value = '';
            }
        }

        // Use standard checks before adding listeners to avoid errors if elements missing (though they are included now)
        const fromDateEl = document.getElementById('leave_from_date');
        const toDateEl = document.getElementById('leave_to_date');
        if (fromDateEl) {
            fromDateEl.addEventListener('change', calculateLeaveDays);
            fromDateEl.addEventListener('input', calculateLeaveDays);
        }
        if (toDateEl) {
            toDateEl.addEventListener('change', calculateLeaveDays);
            toDateEl.addEventListener('input', calculateLeaveDays);
        }

        // Leave application form submission
        const leaveForm = document.getElementById('leaveApplicationForm');
        if (leaveForm) {
            leaveForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>'); // Add CSRF token
                const messageDiv = document.getElementById('leaveFormMessage');
                const submitBtn = this.querySelector('button[type="submit"]');
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Submitting...';
                
                fetch('process_leave.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    messageDiv.style.display = 'block';
                    messageDiv.className = 'alert ' + (data.status === 'success' ? 'alert-success' : 'alert-danger');
                    messageDiv.textContent = data.message;
                    
                    if (data.status === 'success') {
                        this.reset();
                        document.getElementById('total_days_display').value = '';
                        // Reload page after 1.5 seconds to show updated balances and history (without preserving URL hash)
                        setTimeout(() => {
                            reloadWithoutHash();
                        }, 1500);
                    }
                })
                .catch(error => {
                    messageDiv.style.display = 'block';
                    messageDiv.className = 'alert alert-danger';
                    messageDiv.textContent = 'An error occurred. Please try again.';
                    console.error('Error:', error);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Application';
                });
            });
        }

        // Punch In/Out Handler
        function handlePunch(action) {
            const btnIn = document.getElementById('btn-punch-in');
            const btnOut = document.getElementById('btn-punch-out');
            const msgDiv = document.getElementById('punch-message');
            
            msgDiv.style.display = 'block';
            msgDiv.className = 'text-center mt-2 small text-info';
            msgDiv.textContent = 'Processing...';

            const formData = new FormData();
            formData.append('action', action);
            formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>'); // Add CSRF token

            fetch('process_punch.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.text().then(text => {
                    try {
                        return JSON.parse(text);
                    } catch (e) {
                         throw new Error('Server Error: ' + text);
                    }
                });
            })
            .then(data => {
                msgDiv.textContent = data.message;
                msgDiv.className = 'text-center mt-2 small ' + (data.status === 'success' ? 'text-success' : 'text-danger');
                
                if (data.status === 'success') {
                    // Refresh after a short delay to update UI (without preserving URL hash)
                    setTimeout(() => reloadWithoutHash(), 1500);
                }
            })
            .catch(error => {
                msgDiv.innerHTML = 'An error occurred: ' + error.message;
                msgDiv.className = 'text-center mt-2 small text-danger';
                console.error('Error:', error);
            });
        }


        // Attendance Correction Form Handler
        const correctionForm = document.getElementById('attendanceCorrectionForm');
        if (correctionForm) {
            correctionForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>'); // Add CSRF token
                const messageDiv = document.getElementById('correctionFormMessage');
                const submitBtn = this.querySelector('button[type="submit"]');
                
                messageDiv.style.display = 'block';
                messageDiv.className = 'alert alert-info';
                messageDiv.textContent = 'Submitting...';
                
                submitBtn.disabled = true;

                fetch('process_correction.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    messageDiv.className = 'alert ' + (data.status === 'success' ? 'alert-success' : 'alert-danger');
                    messageDiv.textContent = data.message;
                    
                    if (data.status === 'success') {
                        this.reset();
                        setTimeout(() => reloadWithoutHash(), 1500);
                    }
                })
                .catch(error => {
                    messageDiv.className = 'alert alert-danger';
                    messageDiv.textContent = 'An error occurred. Please try again.';
                    console.error('Error:', error);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                });
            });
        }

        // AJAX Global Alert Function
        function showGlobalAlert(message, type = 'success') {
            const topbarRight = document.querySelector('.topbar-right');
            const alertHtml = `
                <div class="alert alert-${type} alert-dismissible fade show mb-0 me-3" role="alert" style="padding: 5px 35px 5px 15px; font-size: 13px; position: absolute; right: 80px; top: 15px; z-index: 1001;">
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close" style="padding: 10px;"></button>
                </div>
            `;
            const alertPlaceholder = document.createElement('div');
            alertPlaceholder.innerHTML = alertHtml;
            const alertElement = alertPlaceholder.firstElementChild;
            topbarRight.insertBefore(alertElement, topbarRight.firstChild);
            
            setTimeout(() => {
                const bsAlert = new bootstrap.Alert(alertElement);
                bsAlert.close();
            }, 5000);
        }

        // Emergency Contact AJAX Handler
        const emergencyForm = document.getElementById('emergencyContactForm');
        if (emergencyForm) {
            emergencyForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                formData.append('csrf_token', '<?php echo $_SESSION['csrf_token']; ?>'); // Add CSRF token
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Saving...';

                fetch('api/update_emergency_contact.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showGlobalAlert(data.message, data.status === 'success' ? 'success' : 'danger');
                    if (data.status === 'success') {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('editEmergencyModal'));
                        modal.hide();
                        // Optional: Update the UI text without reload
                        setTimeout(() => reloadWithoutHash(), 1500);
                    }
                })
                .catch(error => {
                    showGlobalAlert('An error occurred while updating emergency contact.', 'danger');
                    console.error('Error:', error);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
            });
        }

        // Document Upload AJAX Handler
        const uploadForm = document.getElementById('uploadDocumentForm');
        if (uploadForm) {
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalBtnText = submitBtn.innerHTML;
                
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Uploading...';

                fetch('api/upload_document.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    showGlobalAlert(data.message, data.status === 'success' ? 'success' : 'danger');
                    if (data.status === 'success') {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('uploadDocModal'));
                        modal.hide();
                        uploadForm.reset();
                        setTimeout(() => reloadWithoutHash(), 1500);
                    }
                })
                .catch(error => {
                    showGlobalAlert('An error occurred during file upload.', 'danger');
                    console.error('Error:', error);
                })
                .finally(() => {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                });
            });
        }
// Initial section handling
        window.addEventListener('DOMContentLoaded', (event) => {
            const hash = window.location.hash.replace('#', '');
            if (hash) {
                switchSection(hash);
            }
        });
    </script>
</body>
</html>