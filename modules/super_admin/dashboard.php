<?php
require_once('../../app/Config/database.php');

// Custom check to handle both session key types (from login.php and process_login.php)
$is_logged_in = isset($_SESSION['user_id']) && (isset($_SESSION['role_id']) || isset($_SESSION['user_role']));
$user_role = $_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0;

if (!$is_logged_in) {
    header("Location: " . BASE_URL . "login");
    exit();
}

// Check if user has Super Admin role (role_id = 1)
if ($user_role != 1) {
    header("Location: " . BASE_URL . "views/auth/unauthorized.php");
    exit();
}

// Get user information from session (support both key names)
$user_name = $_SESSION['username'] ?? $_SESSION['user_name'] ?? 'Super Admin';
$user_id = $_SESSION['user_id'];

// --- Fetch KPI Data ---
// Total Employees
$res_emp = $conn->query("SELECT COUNT(*) as total FROM employees");
$total_employees = $res_emp->fetch_assoc()['total'];

// Active Admins (Super Admin + Company Admin + HR Manager)
$res_admin = $conn->query("SELECT COUNT(*) as total FROM users WHERE role_id IN (1, 2, 3) AND status = 'active'");
$active_admins = $res_admin->fetch_assoc()['total'];

// Locked Accounts
$res_locked = $conn->query("SELECT COUNT(*) as total FROM users WHERE status = 'locked'");
$locked_accounts = $res_locked->fetch_assoc()['total'];

// Pending Approvals (Leave Applications)
$res_pending = $conn->query("SELECT COUNT(*) as total FROM leave_applications WHERE status = 'pending'");
$pending_approvals = $res_pending->fetch_assoc()['total'];

// --- Fetch Recent Activities (Audit Logs) ---
$recent_activities = [];
$res_act = $conn->query("SELECT a.*, u.username FROM audit_logs a LEFT JOIN users u ON a.user_id = u.id ORDER BY a.timestamp DESC LIMIT 5");
while ($row = $res_act->fetch_assoc()) {
    $recent_activities[] = $row;
}

// --- Fetch Latest Logins ---
$latest_logins = [];
$res_logins = $conn->query("SELECT a.*, u.username, u.email FROM audit_logs a JOIN users u ON a.user_id = u.id WHERE a.action = 'Login' ORDER BY a.timestamp DESC LIMIT 5");
while ($row = $res_logins->fetch_assoc()) {
    $latest_logins[] = $row;
}

// --- Fetch Notifications (Today's important events) ---
$today = date('Y-m-d');
$res_notif = $conn->query("SELECT COUNT(*) as total FROM audit_logs WHERE DATE(timestamp) = '$today' AND (action LIKE '%Delete%' OR action LIKE '%Lock%' OR action LIKE '%Update%')");
$notification_count = $res_notif->fetch_assoc()['total'];

// Get some brief details for the alert
$notif_text = "";
if ($notification_count > 0) {
    $res_notif_details = $conn->query("SELECT action, timestamp FROM audit_logs WHERE DATE(timestamp) = '$today' AND (action LIKE '%Delete%' OR action LIKE '%Lock%' OR action LIKE '%Update%') ORDER BY timestamp DESC LIMIT 3");
    while($n = $res_notif_details->fetch_assoc()) {
        $notif_text .= "- " . $n['action'] . " (" . date('H:i', strtotime($n['timestamp'])) . ")\n";
    }
} else {
    $notif_text = "No critical alerts for today.";
}

// Helper function for time ago
function time_ago($timestamp) {
    if (!$timestamp) return "N/A";
    $time_ago = strtotime($timestamp);
    $cur_time = time();
    $time_elapsed = $cur_time - $time_ago;
    $seconds = $time_elapsed ;
    $minutes = round($time_elapsed / 60 );
    $hours = round($time_elapsed / 3600);
    $days = round($time_elapsed / 86400 );
    $weeks = round($time_elapsed / 604800);
    $months = round($time_elapsed / 2600640 );
    $years = round($time_elapsed / 31207680 );

    if($seconds <= 60){ return "Just now"; }
    else if($minutes < 60){ return $minutes <= 1 ? "1 min ago" : "$minutes mins ago"; }
    else if($hours < 24){ return $hours <= 1 ? "an hour ago" : "$hours hours ago"; }
    else if($days < 7){ return $days <= 1 ? "yesterday" : "$days days ago"; }
    else if($weeks < 4.3){ return $weeks <= 1 ? "a week ago" : "$weeks weeks ago"; }
    else if($months < 12){ return $months <= 1 ? "a month ago" : "$months months ago"; }
    else { return $years <= 1 ? "one year ago" : "$years years ago"; }
}

// --- Fetch Data for Charts ---
// 1. User Distribution by Role
$role_dist_query = "SELECT r.name, COUNT(u.id) as count FROM roles r LEFT JOIN users u ON r.id = u.role_id GROUP BY r.id";
$role_dist_res = $conn->query($role_dist_query);
$role_labels = [];
$role_counts = [];
while ($row = $role_dist_res->fetch_assoc()) {
    $role_labels[] = $row['name'];
    $role_counts[] = (int)$row['count'];
}

// 2. Audit Activity Trend (Last 7 Days)
$activity_trend = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $trend_query = "SELECT COUNT(*) as count FROM audit_logs WHERE DATE(timestamp) = '$date'";
    $trend_res = $conn->query($trend_query);
    $count = $trend_res ? $trend_res->fetch_assoc()['count'] : 0;
    $activity_trend[date('D', strtotime($date))] = (int)$count;
}
$trend_labels = array_keys($activity_trend);
$trend_counts = array_values($activity_trend);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - HRnexa</title>
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
            --light-bg: #ecf0f1;
            --sidebar-width: 280px;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
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
            font-size: 12px;
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
            background-color: rgba(52, 152, 219, 0.2);
            color: white;
            border-left-color: var(--secondary-color);
            padding-left: 25px;
        }

        .sidebar-menu a.active {
            background-color: var(--secondary-color);
            color: white;
            border-left-color: white;
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

        .search-box {
            position: relative;
            width: 250px;
        }

        .search-box input {
            width: 100%;
            padding: 8px 15px 8px 35px;
            border: 1px solid #ddd;
            border-radius: 20px;
            font-size: 14px;
        }

        .search-box i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-color), #3498db);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 16px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border: 2px solid white;
        }

        .user-profile:hover .user-avatar {
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .user-info-text {
            display: flex;
            flex-direction: column;
        }

        @media (max-width: 576px) {
            .user-info-text {
                display: none;
            }
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

        .btn-group-custom {
            display: flex;
            gap: 10px;
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

        .kpi-card.info {
            border-left-color: var(--info-color);
        }

        .kpi-icon {
            font-size: 32px;
            margin-bottom: 10px;
        }

        .kpi-card.primary .kpi-icon {
            color: var(--secondary-color);
        }

        .kpi-card.success .kpi-icon {
            color: var(--success-color);
        }

        .kpi-card.danger .kpi-icon {
            color: var(--danger-color);
        }

        .kpi-card.warning .kpi-icon {
            color: var(--warning-color);
        }

        .kpi-card.info .kpi-icon {
            color: var(--info-color);
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

        .kpi-change {
            font-size: 12px;
            margin-top: 10px;
            font-weight: 600;
        }

        .kpi-change.positive {
            color: var(--success-color);
        }

        .kpi-change.negative {
            color: var(--danger-color);
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

        /* Quick Access Grid */
        .quick-access-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }

        .quick-access-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            color: white;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .quick-access-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }

        .quick-access-item.settings {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .quick-access-item.users {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .quick-access-item.audit {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .quick-access-item.backup {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        }

        .quick-access-icon {
            font-size: 28px;
            margin-bottom: 10px;
        }

        .quick-access-label {
            font-size: 14px;
            font-weight: 600;
        }

        /* Activity Table */
        .activity-table {
            margin-top: 20px;
        }

        .activity-table table {
            font-size: 14px;
        }

        .activity-table thead {
            background-color: var(--light-bg);
        }

        .activity-table th {
            font-weight: 600;
            color: var(--primary-color);
            border: none;
            padding: 12px;
        }

        .activity-table td {
            padding: 12px;
            vertical-align: middle;
            border-color: #eee;
        }

        .activity-table tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }

        .badge-custom {
            display: inline-block;
            white-space: nowrap;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }

        /* System Health */
        .health-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #eee;
        }

        .health-item:last-child {
            border-bottom: none;
        }

        .health-status {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        .health-status.online {
            background-color: var(--success-color);
            animation: pulse-green 2s infinite;
        }

        .health-status.warning {
            background-color: var(--warning-color);
            animation: pulse-yellow 2s infinite;
        }

        .health-status.offline {
            background-color: var(--danger-color);
        }

        @keyframes pulse-green {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        @keyframes pulse-yellow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }

        /* Notification Badge */
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
        }

        .icon-with-badge {
            position: relative;
            display: inline-block;
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

            .page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }

            .topbar {
                padding: 10px 15px;
            }

            .search-box {
                display: none;
            }

            .quick-access-grid {
                grid-template-columns: repeat(2, 1fr);
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
            <i class="fas fa-crown"></i>
            <h5>HRnexa</h5>
        </div>
        <ul class="sidebar-menu">
            <li class="menu-section">Main</li>
            <li><a href="#dashboard" class="nav-link active" data-section="dashboard"><i class="fas fa-dashboard"></i><span>Dashboard</span></a></li>
            
            <li class="menu-section">System Control</li>
            <li><a href="#system-settings" class="nav-link" data-section="system-settings"><i class="fas fa-cogs"></i><span>System Settings</span></a></li>
            <li><a href="#modules" class="nav-link" data-section="modules"><i class="fas fa-cube"></i><span>Module Control</span></a></li>
            
            <li class="menu-section">Access Control</li>
            <li><a href="#roles" class="nav-link" data-section="roles"><i class="fas fa-shield-alt"></i><span>Roles & Permissions</span></a></li>
            <li><a href="#users" class="nav-link" data-section="users"><i class="fas fa-users"></i><span>User Management</span></a></li>
            
            <li class="menu-section">Monitoring</li>
            <li><a href="#audit-log" class="nav-link" data-section="audit-log"><i class="fas fa-history"></i><span>Audit Log</span></a></li>
            <li><a href="#login-history" class="nav-link" data-section="login-history"><i class="fas fa-sign-in-alt"></i><span>Login History</span></a></li>
            <li><a href="#system-health" class="nav-link" data-section="system-health"><i class="fas fa-heartbeat"></i><span>System Health</span></a></li>
            
            <li class="menu-section">Maintenance</li>
            <li><a href="#team" class="nav-link" data-section="team"><i class="fas fa-users-cog"></i><span>Team Management</span></a></li>
            <li><a href="#notice" class="nav-link" data-section="notice"><i class="fas fa-bullhorn"></i><span>Notice Board</span></a></li>
            <li><a href="#backup" class="nav-link" data-section="backup"><i class="fas fa-database"></i><span>Backup & Restore</span></a></li>
            <li><a href="#compliance" class="nav-link" data-section="compliance"><i class="fas fa-file-alt"></i><span>Compliance</span></a></li>
            
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
            </div>
            <div class="topbar-right">
                <div class="search-box d-none d-md-flex">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search...">
                </div>
                <div class="icon-with-badge me-2" onclick="showNotifications()">
                    <i class="fas fa-bell" style="font-size: 20px; cursor: pointer; color: #666;"></i>
                    <?php if ($notification_count > 0): ?>
                        <span class="notification-badge"><?php echo $notification_count; ?></span>
                    <?php endif; ?>
                </div>
                <div class="user-profile">
                    <div class="user-avatar"><?php echo strtoupper(substr($user_name, 0, 2)); ?></div>
                    <div class="user-info-text">
                        <div style="font-weight: 600; font-size: 14px;"><?php echo htmlspecialchars($user_name ?? ''); ?></div>
                        <div style="font-size: 12px; color: #999;">Super Admin</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div class="dashboard-content">
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
                    <div class="btn-group-custom">
                        <button class="btn btn-primary shadow-sm px-4 rounded-pill" onclick="switchSection('users'); showAddUserModal();">
                            <i class="fas fa-plus-circle me-1"></i> New User
                        </button>
                        <button class="btn btn-outline-secondary shadow-sm px-4 rounded-pill" onclick="switchSection('system-settings')">
                            <i class="fas fa-cog me-1"></i> Settings
                        </button>
                    </div>
                </div>

                <!-- KPI Cards -->
                <?php include 'kpi_cards.php'; ?>

                <?php include 'quick_access.php'; ?>

                <?php include 'dashboard_charts.php'; ?>

                <?php include 'recent_activities.php'; ?>
            </div>

            <!-- Add/Edit User Modal -->
            <div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title" id="userModalLabel">Add New System User</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="userForm">
                            <input type="hidden" id="user_id" name="user_id">
                            <div class="modal-body p-4">
                                <div id="formAlert" class="alert d-none"></div>
                                <div class="mb-3">
                                    <label for="username" class="form-label fw-bold">Username</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter username" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-bold">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        <input type="email" class="form-control" id="email" name="email" placeholder="example@hrnexa.com" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="password" class="form-label fw-bold">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Leave blank to keep current">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('password')">
                                            <i class="fas fa-eye" id="password_toggle_icon"></i>
                                        </button>
                                    </div>
                                    <div id="password_help" class="form-text small">Use at least 8 characters with numbers and symbols.</div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="role_id" class="form-label fw-bold">System Role</label>
                                        <select class="form-select" id="role_id" name="role_id" required>
                                            <option value="">Select Role</option>
                                            <option value="1">Super Admin</option>
                                            <option value="2">Admin (Company)</option>
                                            <option value="3">HR Manager</option>
                                            <option value="4">Team Leader</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="status" class="form-label fw-bold">Account Status</label>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="active">Active</option>
                                            <option value="inactive">Inactive</option>
                                            <option value="locked">Locked</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer bg-light p-3">
                                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary px-4" id="saveUserBtn">Save User</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- System Settings Section -->
            <div id="system-settings-section" class="section-content" style="display: none;">
                <?php include 'system_settings.php'; ?>
            </div>

            <!-- User Management Section -->
            <div id="users-section" class="section-content" style="display: none;">
                <?php include 'user_management.php'; ?>
            </div>

            <!-- Roles & Permissions Section -->
            <div id="roles-section" class="section-content" style="display: none;">
                <?php include 'roles_permissions.php'; ?>
            </div>

            <!-- Audit Log Section -->
            <div id="audit-log-section" class="section-content" style="display: none;">
                <?php include 'audit_log.php'; ?>
            </div>

            <!-- Login History Section -->
            <div id="login-history-section" class="section-content" style="display: none;">
                <?php include 'login_history.php'; ?>
            </div>

            <!-- System Health Section -->
            <div id="system-health-section" class="section-content" style="display: none;">
                <?php include 'system_health.php'; ?>
            </div>

            <!-- Backup & Restore Section -->
            <div id="backup-section" class="section-content" style="display: none;">
                <?php include 'backup_restore.php'; ?>
            </div>

            <!-- Compliance Section -->
            <div id="compliance-section" class="section-content" style="display: none;">
                <?php include 'compliance.php'; ?>
            </div>

            <!-- Team Management Section -->
            <div id="team-section" class="section-content" style="display: none;">
                <?php include 'team_management.php'; ?>
            </div>

            <!-- Add/Edit Team Member Modal -->
            <div class="modal fade" id="teamModal" tabindex="-1" aria-labelledby="teamModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-info text-white">
                            <h5 class="modal-title" id="teamModalLabel">Add Team Member</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form id="teamForm" enctype="multipart/form-data">
                            <input type="hidden" id="team_member_id" name="id">
                            <input type="hidden" id="existing_image" name="existing_image">
                            <div class="modal-body p-4">
                                <div id="teamFormAlert" class="alert d-none"></div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Full Name</label>
                                        <input type="text" class="form-control" name="name" id="team_name" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Designation</label>
                                        <input type="text" class="form-control" name="designation" id="team_designation" required placeholder="e.g. CEO, Developer">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Email Address</label>
                                        <input type="email" class="form-control" name="email" id="team_email">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-bold">Sort Order</label>
                                        <input type="number" class="form-control" name="sort_order" id="team_sort_order" value="0">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Profile Photo</label>
                                    <input type="file" class="form-control" name="image" accept="image/*">
                                    <div id="current_image_preview" class="mt-2 d-none">
                                        <div class="text-muted small mb-1">Current Image:</div>
                                        <img src="" id="team_image_display" style="height: 60px; border-radius: 8px;">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">LinkedIn URL</label>
                                        <input type="text" class="form-control" name="linkedin" id="team_linkedin" placeholder="https://...">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Twitter URL</label>
                                        <input type="text" class="form-control" name="twitter" id="team_twitter" placeholder="https://...">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold">Instagram URL</label>
                                        <input type="text" class="form-control" name="instagram" id="team_instagram" placeholder="https://...">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label fw-bold">Status</label>
                                    <select class="form-select" name="status" id="team_status">
                                        <option value="active">Active</option>
                                        <option value="inactive">Inactive</option>
                                    </select>
                                </div>
                            </div>
                            <div class="modal-footer bg-light p-3">
                                <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-info text-white px-4" id="saveTeamBtn">Save Member</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>


            <!-- Modules Section -->
            <div id="modules-section" class="section-content" style="display: none;">
                <?php include 'module_control.php'; ?>
            </div>

            <!-- Team Management Section -->
            <div id="team-section" class="section-content" style="display: none;">
                <?php include 'team_management.php'; ?>
            </div>

            <!-- Notice Board Section -->
            <div id="notice-section" class="section-content" style="display: none;">
                <?php include '../admin/notice_management.php'; ?>
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
                'system-settings': 'System Settings',
                'modules': 'Module Control',
                'roles': 'Roles & Permissions',
                'users': 'User Management',
                'permissions': 'Permission Management',
                'audit-log': 'Audit Log',
                'login-history': 'Login History',
                'system-health': 'System Health & Monitoring',
                'backup': 'Backup & Restore',
                'compliance': 'Compliance & Reporting',
                'team': 'Team Management',
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

        // Backup function
        // function createBackup() {
        //     const backupName = document.querySelector('input[placeholder="Enter backup name"]').value;
        //     alert(`Backup "${backupName}" started!\n\nYou will receive an email notification once the backup is complete.`);
        // }

        // // Generate report function
        // function generateReport(type) {
        //     const reportNames = {
        //         'audit': 'Audit Trail Report',
        //         'user-access': 'User Access Report',
        //         'security': 'Security Report',
        //         'system': 'System Health Report'
        //     };
        //     alert(`Generating ${reportNames[type]}...\n\nThe report will be downloaded as a PDF file.`);
        // }

        // Real-time Search Functionality
        document.querySelector('.search-box input').addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const activeSection = Array.from(document.querySelectorAll('.section-content')).find(el => el.style.display === 'block');
            
            if (!activeSection) return;

            // Target tables in the active section
            const rows = activeSection.querySelectorAll('tbody tr');
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });

            // Special handling for module cards if in modules section
            const cards = activeSection.querySelectorAll('.col-md-6, .col-lg-4');
            if (cards.length > 0) {
                cards.forEach(card => {
                    const text = card.textContent.toLowerCase();
                    card.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            }
        });

        // Notification badge click
        function showNotifications() {
            const count = <?php echo $notification_count; ?>;
            const details = `<?php echo addslashes($notif_text); ?>`;
            alert(`You have ${count} notifications for today:\n\n${details}`);
        }

        // User Modal Helpers
        function showAddUserModal() {
            document.getElementById('userForm').reset();
            document.getElementById('user_id').value = '';
            document.getElementById('userModalLabel').textContent = 'Add New System User';
            document.getElementById('password_help').textContent = 'Use at least 8 characters with numbers and symbols.';
            document.getElementById('formAlert').classList.add('d-none');
            new bootstrap.Modal(document.getElementById('userModal')).show();
        }

        function togglePasswordVisibility(id) {
            const input = document.getElementById(id);
            const icon = document.getElementById(id + '_toggle_icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        function showEditUserModal(userId) {
            document.getElementById('formAlert').classList.add('d-none');
            fetch('process_user.php?action=get_user&id=' + userId)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const u = data.user;
                        document.getElementById('user_id').value = u.id;
                        document.getElementById('username').value = u.username;
                        document.getElementById('email').value = u.email;
                        document.getElementById('role_id').value = u.role_id;
                        document.getElementById('status').value = u.status;
                        document.getElementById('password').value = ''; // Don't show hashed password
                        document.getElementById('userModalLabel').textContent = 'Edit System User: ' + u.username;
                        document.getElementById('password_help').textContent = 'Leave empty to keep current password.';
                        new bootstrap.Modal(document.getElementById('userModal')).show();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        function deleteUser(userId) {
            if (confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                const formData = new FormData();
                formData.append('action', 'delete_user');
                formData.append('id', userId);

                fetch('process_user.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
            }
        }

        // Handle User Form Submission (Add/Edit)
        document.getElementById('userForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const btn = document.getElementById('saveUserBtn');
            const alertBox = document.getElementById('formAlert');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            
            const formData = new FormData(this);
            const userId = document.getElementById('user_id').value;
            formData.append('action', userId ? 'edit_user' : 'add_user');

            fetch('process_user.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = originalText;
                
                alertBox.classList.remove('d-none', 'alert-success', 'alert-danger');
                alertBox.classList.add(data.success ? 'alert-success' : 'alert-danger');
                alertBox.textContent = data.message;
                
                if (data.success) {
                    setTimeout(() => {
                        bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
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

        // Handle Team Member Deletion
        function deleteTeamMember(id) {
            if (confirm('Are you sure you want to delete this team member?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('process_team.php', {
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

        // JS for Team Management
        function showAddTeamModal() {
            document.getElementById('teamForm').reset();
            document.getElementById('team_member_id').value = '';
            document.getElementById('existing_image').value = '';
            document.getElementById('current_image_preview').classList.add('d-none');
            document.getElementById('teamModalLabel').textContent = 'Add Team Member';
            document.getElementById('teamFormAlert').classList.add('d-none');
            new bootstrap.Modal(document.getElementById('teamModal')).show();
        }

        function editTeamMember(id) {
            document.getElementById('teamFormAlert').classList.add('d-none');
            fetch('process_team.php?action=get&id=' + id)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        const m = data.member;
                        document.getElementById('team_member_id').value = m.id;
                        document.getElementById('team_name').value = m.name;
                        document.getElementById('team_designation').value = m.designation;
                        document.getElementById('team_email').value = m.email;
                        document.getElementById('team_sort_order').value = m.sort_order;
                        document.getElementById('team_linkedin').value = m.linkedin;
                        document.getElementById('team_twitter').value = m.twitter;
                        document.getElementById('team_instagram').value = m.instagram;
                        document.getElementById('team_status').value = m.status;
                        document.getElementById('existing_image').value = m.image_path;
                        
                        if (m.image_path) {
                            document.getElementById('team_image_display').src = '../../' + m.image_path;
                            document.getElementById('current_image_preview').classList.remove('d-none');
                        } else {
                            document.getElementById('current_image_preview').classList.add('d-none');
                        }

                        document.getElementById('teamModalLabel').textContent = 'Edit Member: ' + m.name;
                        new bootstrap.Modal(document.getElementById('teamModal')).show();
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }

        // Handle Team Form Submission
        document.getElementById('teamForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('saveTeamBtn');
            const alertBox = document.getElementById('teamFormAlert');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            
            const formData = new FormData(this);
            const memberId = document.getElementById('team_member_id').value;
            formData.append('action', memberId ? 'edit' : 'add');

            fetch('process_team.php', {
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
                        bootstrap.Modal.getInstance(document.getElementById('teamModal')).hide();
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
                fetch('../admin/process_notice.php?action=get&id=' + noticeId)
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

        // Global exposing only if needed for other parts (though we removed onclick)
        window.deleteNotice = function(id) {
            if (confirm('Are you sure you want to delete this notice?')) {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('id', id);

                fetch('../admin/process_notice.php', {
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

        document.getElementById('noticeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = document.getElementById('saveNoticeBtn');
            const alertBox = document.getElementById('noticeFormAlert');
            const originalText = btn.innerHTML;
            
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
            
            const formData = new FormData(this);
            const noticeId = document.getElementById('notice_id').value;
            formData.append('action', noticeId ? 'edit' : 'add');

            fetch('../admin/process_notice.php', {
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

        console.log('Dashboard initialized successfully!');
    </script>
</body>
</html>
