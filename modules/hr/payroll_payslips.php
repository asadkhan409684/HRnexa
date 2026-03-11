<?php
// Note: $payrollModel and $conn are assumed to be available from dashboard.php

$completed_runs = $conn->query("SELECT * FROM payroll_runs WHERE status = 'completed' ORDER BY year DESC, month DESC");
$view_run_id = isset($_GET['view_run_id']) ? intval($_GET['view_run_id']) : null;
$view_run = null;
$payslips = null;

if ($view_run_id) {
    $view_run = $payrollModel->getPayrollRun($view_run_id);
    $payslips = $payrollModel->getPayslipsByRun($view_run_id);
}
?>

<div id="payslips-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Payslip Management</h1>
    </div>

    <div class="row">
        <div class="col-md-5">
            <div class="section-card">
                <div class="section-title">
                    <i class="fas fa-history"></i> Completed Payroll Runs
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Month/Year</th>
                                <th>Date Finalized</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($run = $completed_runs->fetch_assoc()): ?>
                                <tr class="<?php echo ($view_run_id == $run['id']) ? 'table-primary' : ''; ?>">
                                    <td><?php echo date('F', mktime(0, 0, 0, $run['month'], 10)) . " " . $run['year']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($run['created_at'])); ?></td>
                                    <td>
                                        <a href="dashboard.php?view_run_id=<?php echo $run['id']; ?>#payslips" class="btn btn-sm btn-info text-white">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                            <?php if ($completed_runs->num_rows === 0): ?>
                                <tr><td colspan="3" class="text-center py-4 text-muted">No completed payroll runs found.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <?php if ($view_run): ?>
        <div class="col-md-7">
            <div class="section-card">
                <div class="section-title">
                    <i class="fas fa-receipt"></i> 
                    Payslips: <?php echo date('F', mktime(0, 0, 0, $view_run['month'], 10)) . " " . $view_run['year']; ?>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Net Salary</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($payslips): ?>
                                <?php while ($ps = $payslips->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <?php echo $ps['first_name'] . ' ' . $ps['last_name']; ?><br>
                                            <small class="text-muted"><?php echo $ps['employee_code']; ?></small>
                                        </td>
                                        <td class="fw-bold"><?php echo number_format($ps['net_salary'], 2); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" onclick='showPayslipDetails(<?php echo $ps['breakdown']; ?>, <?php echo json_encode($ps, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                                <i class="fas fa-external-link-alt"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
// The viewPayrollDetails function is already defined in payroll_generate.php 
// and since they are both included in the same dashboard.php, it's accessible.
</script>
