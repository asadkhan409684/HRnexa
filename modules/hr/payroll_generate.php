<?php
// Note: $payrollModel and $conn are assumed to be available from dashboard.php

$runs = $payrollModel->getAllPayrollRuns();
$active_run_id = isset($_GET['run_id']) ? intval($_GET['run_id']) : null;
// Fallback compatibility
if (!$active_run_id && isset($_GET['view'])) {
    $active_run_id = intval($_GET['view']);
}

$payslips = null;
$current_run = null;

if ($active_run_id) {
    $current_run = $payrollModel->getPayrollRun($active_run_id);
    $payslips = $payrollModel->getPayslipsByRun($active_run_id);
}
?>

<div id="payroll-section" class="section-content" style="display: none;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Payroll Management</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRunModal">
            <i class="fas fa-plus me-2"></i>New Payroll Run
        </button>
    </div>

    <div class="row">
        <div class="col-md-<?php echo $active_run_id ? '4' : '12'; ?>">
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
                            <?php if ($runs && $runs->num_rows > 0): ?>
                                <?php while ($run = $runs->fetch_assoc()): ?>
                                    <tr class="<?php echo ($active_run_id == $run['id']) ? 'table-primary' : ''; ?>">
                                        <td><?php echo date('F', mktime(0, 0, 0, $run['month'], 10)) . " " . $run['year']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo match($run['status']) {
                                                    'draft' => 'secondary',
                                                    'processing' => 'warning text-dark',
                                                    'pending_approval' => 'info',
                                                    'approved', 'completed' => 'success',
                                                    'rejected' => 'danger',
                                                    default => 'secondary'
                                                };
                                            ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', $run['status'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="dashboard.php?run_id=<?php echo $run['id']; ?>#payroll" class="btn btn-sm btn-info text-white">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="3" class="text-center text-muted">No runs found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($active_run_id && $current_run): ?>
        <div class="col-md-8"> 
            <div class="section-card">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5>Review: <?php echo date('F', mktime(0, 0, 0, $current_run['month'], 10)) . " " . $current_run['year']; ?></h5>
                    <div class="d-flex gap-2">
                        <?php if ($current_run['status'] === 'draft' || $current_run['status'] === 'processing'): ?>
                            <form method="POST" class="d-flex gap-2">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="run_id" value="<?php echo $active_run_id; ?>">
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
                    <table class="table table-sm table-hover align-middle" style="font-size: 0.85rem;">
                        <thead class="table-light">
                            <tr>
                                <th>Employee</th>
                                <th class="text-center">Leave</th>
                                <th class="text-center">Absent</th>
                                <th class="text-center">Late</th>
                                <th class="text-center">Half</th>
                                <th class="text-end">Allowances</th>
                                <th class="text-end">Deductions</th>
                                <th class="text-end">Net Salary</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($payslips && $payslips->num_rows > 0): ?>
                                <?php while ($ps = $payslips->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo $ps['first_name'] . ' ' . $ps['last_name']; ?></strong><br>
                                            <small class="text-muted"><?php echo $ps['employee_code']; ?></small>
                                        </td>
                                        <td class="text-center"><?php echo $ps['total_leave']; ?></td>
                                        <td class="text-center"><?php echo $ps['total_absent']; ?></td>
                                        <td class="text-center"><?php echo $ps['total_late']; ?></td>
                                        <td class="text-center"><?php echo $ps['half_days']; ?></td>
                                        <td class="text-end"><?php echo number_format($ps['total_allowances'], 2); ?></td>
                                        <td class="text-end"><?php echo number_format($ps['total_deductions'], 2); ?></td>
                                        <td class="text-end fw-bold text-success"><?php echo number_format($ps['net_salary'], 2); ?></td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-primary" type="button" onclick='showPayslipDetails(<?php echo $ps['breakdown']; ?>, <?php echo json_encode($ps, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)' title="View Details">
                                                    <i class="fas fa-eye me-1"></i> Details
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="9" class="text-center py-4 text-muted">No payslips generated. Click "Process/Refresh" to generate.</td></tr>
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

<!-- Edit Payslip Modal -->
<div class="modal fade" id="editPayslipModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="payslip_id" id="edit_payslip_id">
            <input type="hidden" name="run_id" value="<?php echo $active_run_id; ?>"> 
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

<script>
function showPayslipDetails(data, payslip) {
    if (!data) return;
    
    let editButton = '';
    const status = payslip ? payslip.status : 'N/A';
    let manualBadge = data.manual_adjustment ? '<span class="badge bg-info ms-2"><i class="fas fa-edit me-1"></i> Manually Adjusted</span>' : '';
    
    // Check if it's draft, processing, or pending_approval
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
                    <tr><td>Marked Absent</td><td>${data.attendance_summary ? data.attendance_summary.total_absent_marked : 0} days</td></tr>
                    <tr><td>Late Days</td><td>${data.attendance_summary ? data.attendance_summary.total_late : 0} days</td></tr>
                    <tr><td>Leave Taken</td><td>${data.attendance_summary ? data.attendance_summary.total_leave : 0} days</td></tr>
                    <tr class="table-danger fw-bold"><td>Effective Absent Total</td><td>${data.attendance_summary ? data.attendance_summary.effective_absent_total : 0} days</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6>Total Summary</h6>
                <table class="table table-sm table-bordered">
                    <tr><td>Basic Salary</td><td>${data.summary ? data.summary.basic.toFixed(2) : 0}</td></tr>
                    <tr><td>Total Allowances</td><td>${data.summary ? data.summary.total_allowances.toFixed(2) : 0}</td></tr>
                    <tr><td>Total Deductions</td><td>${data.summary ? data.summary.total_deductions.toFixed(2) : 0}</td></tr>
                    <tr class="table-success"><th>Net Payable</th><th>${data.summary ? data.summary.net.toFixed(2) : 0}</th></tr>
                </table>
            </div>
        </div>
        <hr>
        <div class="row">
            <div class="col-md-6">
                <h6>Allowances</h6>
                <ul class="list-group">
                    ${data.allowances ? data.allowances.map(a => `<li class="list-group-item d-flex justify-content-between"><span>${a.name}</span><strong>${a.amount.toFixed(2)}</strong></li>`).join('') : '<li class="list-group-item text-muted">No allowances</li>'}
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Deductions</h6>
                <ul class="list-group">
                    ${data.deductions ? data.deductions.map(d => `<li class="list-group-item d-flex justify-content-between"><span>${d.name}</span><strong>${d.amount.toFixed(2)}</strong></li>`).join('') : '<li class="list-group-item text-muted">No deductions</li>'}
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
    document.getElementById('edit_total_absent').value = payslip.total_absent;
    document.getElementById('edit_total_late').value = payslip.total_late;
    document.getElementById('edit_half_days').value = payslip.half_days;
    document.getElementById('edit_total_allowances').value = payslip.total_allowances;
    document.getElementById('edit_total_deductions').value = payslip.total_deductions;
    
    new bootstrap.Modal(document.getElementById('editPayslipModal')).show();
}
</script>
