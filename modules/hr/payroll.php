<?php
session_start();

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

// Database connection
include_once __DIR__ . '/../../app/Config/database.php';
require_once __DIR__ . '/../../app/Controllers/PayrollController.php';
require_once __DIR__ . '/../../app/Models/Payroll.php';

$payrollModel = new Payroll($conn);
$payrollController = new PayrollController($conn);

$message = '';
$message_type = '';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['create_run'])) {
        $month = intval($_POST['month']);
        $year = intval($_POST['year']);
        $run_id = $payrollController->initiateRun($month, $year);
        if ($run_id) {
            $message = "Payroll run created successfully for " . date('F', mktime(0, 0, 0, $month, 10)) . " $year.";
            $message_type = "success";
        } else {
            $message = "Error: Payroll run already exists or could not be created.";
            $message_type = "danger";
        }
    }

    if (isset($_POST['process_run'])) {
        $run_id = intval($_POST['run_id']);
        $level = $_POST['designation_level'] ?? null;
        if ($payrollController->processRun($run_id, $level)) {
            $message = "Payroll processing completed" . ($level ? " for $level level" : "") . ". You can now review the payslips.";
            $message_type = "success";
        } else {
            $message = "Error processing payroll.";
            $message_type = "danger";
        }
    }

    if (isset($_POST['finalize_run'])) {
        $run_id = intval($_POST['run_id']);
        if ($payrollController->finalizeRun($run_id)) {
            $message = "Payroll run finalized and submitted for approval.";
            $message_type = "success";
            
            // Redirect to Admin dashboard if user is also an Admin
            if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 2) {
                header("Location: " . BASE_URL . "modules/admin/dashboard.php#payroll-approvals");
                exit();
            }
        } else {
            $message = "Error finalizing payroll.";
            $message_type = "danger";
        }
    }

    if (isset($_POST['update_payslip'])) {
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

        // Update breakdown JSON to reflect overrides (Simplified for manual edits)
        $new_breakdown = $old_breakdown;
        $new_breakdown['attendance_summary']['effective_absent_total'] = floatval($_POST['total_absent']);
        $new_breakdown['attendance_summary']['total_leave'] = intval($_POST['total_leave']);
        $new_breakdown['attendance_summary']['total_late'] = intval($_POST['total_late']);
        $new_breakdown['attendance_summary']['half_days'] = intval($_POST['half_days']);
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
            $message = "Payslip updated successfully. Manual adjustment recorded.";
            $message_type = "success";
        } else {
            $message = "Error updating payslip.";
            $message_type = "danger";
        }
        header("Location: payroll.php?view=$run_id&msg=$message&type=$message_type");
        exit();
    }
}

// Helper to display message from redirect
if (isset($_GET['msg'])) {
    $message = htmlspecialchars($_GET['msg']);
    $message_type = htmlspecialchars($_GET['type']);
}

// Fetch Data
$runs = $payrollModel->getAllPayrollRuns();
$view_run_id = isset($_GET['view']) ? intval($_GET['view']) : null;
$payslips = null;
$current_run = null;

if ($view_run_id) {
    $current_run = $payrollModel->getPayrollRun($view_run_id);
    $payslips = $payrollModel->getPayslipsByRun($view_run_id);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Management - HRnexa</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1a5490;
            --secondary-color: #2874a6;
            --sidebar-width: 250px;
        }
        body { background-color: #f5f7fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .sidebar { position: fixed; width: var(--sidebar-width); height: 100vh; background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); color: white; }
        .main-content { margin-left: var(--sidebar-width); padding: 30px; }
        .section-card { background: white; border-radius: 10px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); margin-bottom: 25px; }
        .badge-draft { background-color: #6c757d; }
        .badge-processing { background-color: #ffc107; color: #000; }
        .badge-completed { background-color: #28a745; }
        .badge-cancelled { background-color: #dc3545; }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="p-4 d-flex align-items-center gap-2">
        <i class="fas fa-rocket fa-2x text-warning"></i>
        <h4 class="mb-0 fw-bold">HRnexa</h4>
    </div>
    <div class="px-3">
        <hr class="text-white-50">
        <nav class="nav flex-column gap-2 mt-4">
            <a href="dashboard.php" class="nav-link text-white"><i class="fas fa-th-large me-2"></i> Dashboard</a>
            <a href="employees.php" class="nav-link text-white"><i class="fas fa-users me-2"></i> Employees</a>
            <a href="attendance.php" class="nav-link text-white"><i class="fas fa-clock me-2"></i> Attendance</a>
            <a href="payroll.php" class="nav-link text-white active bg-warning text-dark rounded"><i class="fas fa-file-invoice-dollar me-2"></i> Payroll</a>
            <a href="<?php echo BASE_URL; ?>views/auth/logout.php" class="nav-link text-white mt-5"><i class="fas fa-sign-out-alt me-2"></i> Logout</a>
        </nav>
    </div>
</div>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Payroll Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRunModal">
            <i class="fas fa-plus me-2"></i>New Payroll Run
        </button>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-<?php echo $view_run_id ? '4' : '12'; ?>">
            <div class="section-card">
                <h5 class="mb-4">Payroll Runs</h5>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Month/Year</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($run = $runs->fetch_assoc()): ?>
                                <tr class="<?php echo ($view_run_id == $run['id']) ? 'table-primary' : ''; ?>">
                                    <td><?php echo date('F', mktime(0, 0, 0, $run['month'], 10)) . " " . $run['year']; ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $run['status']; ?>">
                                            <?php echo ucfirst($run['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="?view=<?php echo $run['id']; ?>" class="btn btn-sm btn-info text-white">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($view_run_id && $current_run): ?>
        <div class="col-md-12"> <!-- Expanded to full width for more columns -->
            <div class="section-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5>Review: <?php echo date('F', mktime(0, 0, 0, $current_run['month'], 10)) . " " . $current_run['year']; ?></h5>
                    <div class="d-flex gap-2">
                        <?php if ($current_run['status'] === 'draft' || $current_run['status'] === 'processing'): ?>
                            <form method="POST" class="d-flex gap-2">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="run_id" value="<?php echo $view_run_id; ?>">
                                <select name="designation_level" class="form-select form-select-sm" style="width: 150px;">
                                    <option value="All">All Levels</option>
                                    <option value="Junior">Junior</option>
                                    <option value="Associate">Associate</option>
                                    <option value="Mid-Level">Mid-Level</option>
                                    <option value="Senior">Senior</option>
                                    <option value="Lead">Lead</option>
                                    <option value="Executive">Executive</option>
                                </select>
                                <button type="submit" name="process_run" class="btn btn-sm btn-warning">
                                    <i class="fas fa-sync me-1"></i>Process/Refresh
                                </button>
                                <button type="submit" name="finalize_run" class="btn btn-sm btn-success" onclick="return confirm('Are you sure you want to submit this payroll for Admin Approval?');">
                                    <i class="fas fa-paper-plane me-1"></i>Submit for Approval
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th>Designation</th>
                                <th class="text-center">Leave</th>
                                <th class="text-center">Absent</th>
                                <th class="text-center">Late</th>
                                <th class="text-center">Half Day</th>
                                <th class="text-end">Allowances</th>
                                <th class="text-end">Deductions</th>
                                <th class="text-end">Net Salary</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($ps = $payslips->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo $ps['first_name'] . ' ' . $ps['last_name']; ?></strong><br>
                                        <small class="text-muted"><?php echo $ps['employee_code']; ?></small>
                                    </td>
                                    <td><?php echo $ps['desig_name']; ?></td> <!-- Assuming desig_name is available from query -->
                                    <td class="text-center"><?php echo $ps['total_leave']; ?></td>
                                    <td class="text-center"><?php echo $ps['total_absent']; ?></td>
                                    <td class="text-center"><?php echo $ps['total_late']; ?></td>
                                    <td class="text-center"><?php echo $ps['half_days']; ?></td>
                                    <td class="text-end"><?php echo number_format($ps['total_allowances'], 2); ?></td>
                                    <td class="text-end"><?php echo number_format($ps['total_deductions'], 2); ?></td>
                                    <td class="text-end fw-bold text-success"><?php echo number_format($ps['net_salary'], 2); ?></td>
                                    <td class="text-center">
                                        <?php 
                                            $status_badges = [
                                                'draft' => 'secondary',
                                                'pending_approval' => 'warning',
                                                'approved' => 'success',
                                                'rejected' => 'danger'
                                            ];
                                            $badge_color = $status_badges[$ps['status']] ?? 'secondary';
                                        ?>
                                        <span class="badge bg-<?php echo $badge_color; ?>"><?php echo ucfirst(str_replace('_', ' ', $ps['status'])); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <button class="btn btn-primary" onclick='showPayslipDetails(<?php echo $ps['breakdown']; ?>, <?php echo json_encode($ps); ?>)' title="View Details">
                                                <i class="fas fa-eye me-1"></i> Details
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($payslips->num_rows === 0): ?>
                                <tr><td colspan="11" class="text-center py-4 text-muted">No payslips generated for this run yet. Click "Process/Refresh" to generate.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- New Run Modal -->
<div class="modal fade" id="newRunModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="modal-header">
                <h5 class="modal-title">New Payroll Run</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Month</label>
                    <select name="month" class="form-select" required>
                        <?php for($i=1; $i<=12; $i++): ?>
                            <option value="<?php echo $i; ?>" <?php echo (date('n') == $i) ? 'selected' : ''; ?>>
                                <?php echo date('F', mktime(0, 0, 0, $i, 10)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Year</label>
                    <select name="year" class="form-select" required>
                        <?php for($y=date('Y')-1; $y<=date('Y')+1; $y++): ?>
                            <option value="<?php echo $y; ?>" <?php echo (date('Y') == $y) ? 'selected' : ''; ?>><?php echo $y; ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="create_run" class="btn btn-primary">Create Draft Run</button>
            </div>
        </form>
    </div>
</div>

<!-- Details Modal -->
<div class="modal fade" id="detailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payslip Breakdown <span id="manualBadgeContainer"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="detailsContent">
                <!-- Data populated via JS -->
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
function showPayslipDetails(data, payslip) {
    let editButton = '';
    const status = payslip ? payslip.status : 'N/A';
    let manualBadge = data.manual_adjustment ? '<span class="badge bg-info ms-2"><i class="fas fa-edit me-1"></i> Manually Adjusted</span>' : '';
    
    if (payslip && (payslip.status === 'draft' || payslip.status === 'processing' || payslip.status === 'pending_approval')) {
        editButton = `
            <div class="mt-3 text-end">
                <button class="btn btn-warning" onclick='bootstrap.Modal.getInstance(document.getElementById("detailsModal")).hide(); editPayslip(${JSON.stringify(payslip)})'>
                    <i class="fas fa-edit me-1"></i> Correct/Edit Errors
                </button>
            </div>
        `;
    }

    let content = `
        <div class="row">
            <div class="col-md-6">
                <h6>Attendance & Penalty Summary</h6>
                <table class="table table-sm table-bordered">
                    <tr><td>Marked Absent</td><td>${data.attendance_summary.total_absent_marked} days</td></tr>
                    <tr><td>Late Days</td><td>${data.attendance_summary.total_late} days</td></tr>
                    <tr><td>Late Penalty (3 lates = 1 absent)</td><td class="text-danger">+${data.attendance_summary.late_penalty_absent} days</td></tr>
                    <tr><td>Leave Taken</td><td>${data.attendance_summary.total_leave} days</td></tr>
                    <tr><td>Approved Leave</td><td class="text-success">-${data.attendance_summary.approved_leave} days</td></tr>
                    <tr class="table-warning"><td>Unapproved Leave Penalty</td><td class="text-danger">+${data.attendance_summary.unapproved_leave_absent} days</td></tr>
                    <tr class="table-danger fw-bold"><td>Effective Absent Total</td><td>${data.attendance_summary.effective_absent_total} days</td></tr>
                    <tr><td>Half Days</td><td>${data.attendance_summary.half_days}</td></tr>
                    <tr><td>OT Hours</td><td>${data.attendance_summary.ot_hours}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Total Summary</h6>
                <table class="table table-sm table-bordered">
                    <tr><td>Basic Salary</td><td>${data.summary.basic.toFixed(2)}</td></tr>
                    <tr><td>Total Allowances</td><td>${data.summary.total_allowances.toFixed(2)}</td></tr>
                    <tr><td>Total Deductions</td><td>${data.summary.total_deductions.toFixed(2)}</td></tr>
                    <tr class="table-success"><th>Net Payable</th><th>${data.summary.net.toFixed(2)}</th></tr>
                </table>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h6>Allowances breakdown</h6>
                <ul class="list-group">
                    ${data.allowances.map(a => `<li class="list-group-item d-flex justify-content-between"><span>${a.name}</span><strong>${a.amount.toFixed(2)}</strong></li>`).join('')}
                    ${data.allowances.length === 0 ? '<li class="list-group-item text-muted">No allowances</li>' : ''}
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Deductions breakdown</h6>
                <ul class="list-group">
                    ${data.deductions.map(d => `<li class="list-group-item d-flex justify-content-between"><span>${d.name}</span><strong>${d.amount.toFixed(2)}</strong></li>`).join('')}
                    ${data.deductions.length === 0 ? '<li class="list-group-item text-muted">No deductions</li>' : ''}
                </ul>
            </div>
        </div>
        ${editButton}
    `;
    document.getElementById('manualBadgeContainer').innerHTML = manualBadge + ` <small class="text-muted">(Status: ${status})</small>`;
    document.getElementById('detailsContent').innerHTML = content;
    new bootstrap.Modal(document.getElementById('detailsModal')).show();
}

function editPayslip(payslip) {
    document.getElementById('edit_payslip_id').value = payslip.id;
    document.getElementById('edit_total_leave').value = payslip.total_leave;
    document.getElementById('edit_total_absent').value = payslip.total_absent; // Effective absent
    document.getElementById('edit_total_late').value = payslip.total_late;
    document.getElementById('edit_half_days').value = payslip.half_days;
    document.getElementById('edit_total_allowances').value = payslip.total_allowances;
    document.getElementById('edit_total_deductions').value = payslip.total_deductions;
    
    new bootstrap.Modal(document.getElementById('editPayslipModal')).show();
}
</script>

<!-- Edit Payslip Modal -->
<div class="modal fade" id="editPayslipModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="payslip_id" id="edit_payslip_id">
            <input type="hidden" name="run_id" value="<?php echo $view_run_id; ?>"> 
            <div class="modal-header">
                <h5 class="modal-title">Edit Payslip Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-3">
                    <div class="col-md-6">
                         <label class="form-label">Total Leave Days</label>
                         <input type="number" name="total_leave" id="edit_total_leave" class="form-control">
                    </div>
                    <div class="col-md-6">
                         <label class="form-label">Effective Absent Days</label>
                         <input type="number" step="0.5" name="total_absent" id="edit_total_absent" class="form-control">
                         <small class="text-muted">Includes penalty & unapproved</small>
                    </div>
                    <div class="col-md-6">
                         <label class="form-label">Total Late (Days)</label>
                         <input type="number" name="total_late" id="edit_total_late" class="form-control">
                    </div>
                    <div class="col-md-6">
                         <label class="form-label">Half Days</label>
                         <input type="number" name="half_days" id="edit_half_days" class="form-control">
                    </div>
                </div>
                <hr>
                <div class="mb-3">
                    <label class="form-label">Total Allowances (Override)</label>
                    <input type="number" step="0.01" name="total_allowances" id="edit_total_allowances" class="form-control">
                </div>
                <div class="mb-3">
                    <label class="form-label">Total Deductions (Override)</label>
                    <input type="number" step="0.01" name="total_deductions" id="edit_total_deductions" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="update_payslip" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>
