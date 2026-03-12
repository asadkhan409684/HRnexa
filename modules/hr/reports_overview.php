<div id="reports-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>HR Reports & Analytics</h1>
    </div>
    
    <div class="row">
        <!-- Employee Reports -->
        <div class="col-lg-6 mb-3">
            <div class="section-card">
                <div class="section-title">
                    <i class="fas fa-chart-line"></i>
                    Employee Reports
                </div>
                <div class="report-list">
                    <div class="report-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-users text-primary"></i> Total Headcount Report</span>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Export</button>
                                <ul class="dropdown-menu">
                                    <li><form method="POST"><input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"><input type="hidden" name="report_type" value="headcount"><input type="hidden" name="export_format" value="csv"><button type="submit" name="export_generic_report" class="dropdown-item">CSV</button></form></li>
                                    <li><form method="POST"><input type="hidden" name="report_type" value="headcount"><input type="hidden" name="export_format" value="excel"><button type="submit" name="export_generic_report" class="dropdown-item">Excel</button></form></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="report-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-sitemap text-info"></i> Department-wise Report</span>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Export</button>
                                <ul class="dropdown-menu">
                                    <li><form method="POST"><input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"><input type="hidden" name="report_type" value="department"><input type="hidden" name="export_format" value="csv"><button type="submit" name="export_generic_report" class="dropdown-item">CSV</button></form></li>
                                    <li><form method="POST"><input type="hidden" name="report_type" value="department"><input type="hidden" name="export_format" value="excel"><button type="submit" name="export_generic_report" class="dropdown-item">Excel</button></form></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="report-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-walking text-warning"></i> Employee Movement</span>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Export</button>
                                <ul class="dropdown-menu">
                                    <li><form method="POST"><input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"><input type="hidden" name="report_type" value="movement"><input type="hidden" name="export_format" value="csv"><button type="submit" name="export_generic_report" class="dropdown-item">CSV</button></form></li>
                                    <li><form method="POST"><input type="hidden" name="report_type" value="movement"><input type="hidden" name="export_format" value="excel"><button type="submit" name="export_generic_report" class="dropdown-item">Excel</button></form></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operational Reports -->
        <div class="col-lg-6 mb-3">
            <div class="section-card">
                <div class="section-title">
                    <i class="fas fa-chart-bar"></i>
                    Operational Reports
                </div>
                <div class="report-list">
                    <div class="report-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-clock text-success"></i> Attendance Report</span>
                            <form method="POST" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="number" name="att_year" value="<?php echo date('Y'); ?>" class="form-control form-control-sm" style="width: 70px;">
                                <input type="hidden" name="export_format" value="csv">
                                <button type="submit" name="export_attendance" class="btn btn-sm btn-outline-secondary">CSV</button>
                            </form>
                        </div>
                    </div>
                    <div class="report-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-calendar-minus text-danger"></i> Leave Analytics</span>
                            <form method="POST" class="d-flex gap-2 align-items-center">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="number" name="report_year" value="<?php echo date('Y'); ?>" class="form-control form-control-sm" style="width: 70px;">
                                <input type="hidden" name="export_format" value="csv">
                                <button type="submit" name="export_report" class="btn btn-sm btn-outline-secondary">CSV</button>
                            </form>
                        </div>
                    </div>
                    <div class="report-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-money-bill-wave text-primary"></i> Payroll Report</span>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Export</button>
                                <ul class="dropdown-menu">
                                    <li><form method="POST"><input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"><input type="hidden" name="report_type" value="payroll"><input type="hidden" name="export_format" value="csv"><button type="submit" name="export_generic_report" class="dropdown-item">CSV</button></form></li>
                                    <li><form method="POST"><input type="hidden" name="report_type" value="payroll"><input type="hidden" name="export_format" value="excel"><button type="submit" name="export_generic_report" class="dropdown-item">Excel</button></form></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="report-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="fas fa-user-tie text-info"></i> Recruitment Report</span>
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">Export</button>
                                <ul class="dropdown-menu">
                                    <li><form method="POST"><input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>"><input type="hidden" name="report_type" value="recruitment"><input type="hidden" name="export_format" value="csv"><button type="submit" name="export_generic_report" class="dropdown-item">CSV</button></form></li>
                                    <li><form method="POST"><input type="hidden" name="report_type" value="recruitment"><input type="hidden" name="export_format" value="excel"><button type="submit" name="export_generic_report" class="dropdown-item">Excel</button></form></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.report-item {
    padding: 12px 15px;
    border-bottom: 1px solid #f0f0f0;
    transition: background 0.2s;
}
.report-item:last-child {
    border-bottom: none;
}
.report-item:hover {
    background: #f8f9fa;
}
.report-item i {
    width: 20px;
    margin-right: 10px;
}
</style>
