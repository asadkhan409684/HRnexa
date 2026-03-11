<div id="leave-reports-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Leave Reports</h1>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-filter"></i> Filter Reports
        </div>
        <form action="dashboard.php" method="GET" class="row g-3 mb-4" id="reportFilterForm">
            <div class="col-md-3">
                <label class="form-label">Year</label>
                <select name="report_year" class="form-select">
                    <?php 
                    $current_y = date('Y');
                    for ($y = $current_y; $y >= $current_y - 2; $y--) {
                        $selected = ($y == $filter_year) ? 'selected' : '';
                        echo "<option value='$y' $selected>$y</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Month</label>
                <select name="report_month" class="form-select">
                    <option value="">All Months</option>
                    <?php 
                    for ($m = 1; $m <= 12; $m++) {
                        $selected = ($m == $filter_month) ? 'selected' : '';
                        $month_name = date('F', mktime(0, 0, 0, $m, 10));
                        echo "<option value='$m' $selected>$month_name</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search Employee</label>
                <input type="text" name="report_search" class="form-control" placeholder="Name or ID" value="<?php echo htmlspecialchars($filter_search); ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
            </div>
        </form>
        
        <div class="d-flex justify-content-end mb-3">
                <form action="dashboard.php" method="POST" target="_blank" id="exportForm">
                <input type="hidden" name="report_year" value="<?php echo htmlspecialchars($filter_year); ?>">
                <input type="hidden" name="report_month" value="<?php echo htmlspecialchars($filter_month); ?>">
                <input type="hidden" name="report_search" value="<?php echo htmlspecialchars($filter_search); ?>">
                
                <div class="btn-group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download"></i> Export Data
                    </button>
                    <ul class="dropdown-menu">
                        <li><button type="submit" name="export_report" value="csv" class="dropdown-item" onclick="setFormat('csv')"><i class="fas fa-file-csv"></i> CSV</button></li>
                        <li><button type="submit" name="export_report" value="excel" class="dropdown-item" onclick="setFormat('excel')"><i class="fas fa-file-excel"></i> Excel</button></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><button type="button" class="dropdown-item" onclick="downloadPDF()"><i class="fas fa-file-pdf"></i> PDF</button></li>
                    </ul>
                    <input type="hidden" name="export_format" id="exportFormatInput" value="csv">
                </div>
            </form>
        </div>

        <div class="section-title">
            <i class="fas fa-chart-bar"></i>
            Leave History & Status
        </div>
        <div class="data-table" id="reportTableContainer">
            <table class="table table-striped" id="reportTable">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Applied Dates</th>
                        <th>Days</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($report_result && $report_result->num_rows > 0): ?>
                        <?php while($rep = $report_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($rep['first_name'] . ' ' . $rep['last_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($rep['employee_code']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($rep['leave_type']); ?></td>
                            <td>
                                <?php echo date('d M Y', strtotime($rep['start_date'])) . ' - ' . date('d M Y', strtotime($rep['end_date'])); ?>
                            </td>
                            <td><?php echo $rep['total_days']; ?></td>
                            <td>
                                <?php 
                                    $r_badge = 'bg-info';
                                    if ($rep['status'] == 'approved') $r_badge = 'bg-success';
                                    if ($rep['status'] == 'rejected') $r_badge = 'bg-danger';
                                    if ($rep['status'] == 'referred') $r_badge = 'bg-primary';
                                    if ($rep['status'] == 'pending') $r_badge = 'bg-warning';
                                ?>
                                <span class="badge <?php echo $r_badge; ?>"><?php echo ucfirst($rep['status']); ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No records found for selected criteria.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
