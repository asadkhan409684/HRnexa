<div id="attendance-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Attendance Management</h1>
    </div>
    
    <div class="section-card">
            <div class="section-title">
            <i class="fas fa-filter"></i> Filter Attendance
        </div>
        <form action="dashboard.php" method="GET" class="row g-3 mb-4">
            <input type="hidden" name="section" value="attendance">
            <div class="col-md-3">
                <label class="form-label">Year</label>
                <select name="att_year" class="form-select">
                    <?php 
                    $current_y = date('Y');
                    for ($y = $current_y; $y >= $current_y - 2; $y--) {
                        $selected = ($y == $att_filter_year) ? 'selected' : '';
                        echo "<option value='$y' $selected>$y</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Month</label>
                <select name="att_month" class="form-select">
                    <option value="">All Months</option>
                    <?php 
                    for ($m = 1; $m <= 12; $m++) {
                        $selected = ($m == $att_filter_month) ? 'selected' : '';
                        $month_name = date('F', mktime(0, 0, 0, $m, 10));
                        echo "<option value='$m' $selected>$month_name</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search Employee (ID/Name)</label>
                <input type="text" name="att_search" class="form-control" placeholder="Employee ID or Name" value="<?php echo htmlspecialchars($att_filter_search); ?>">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
            </div>
        </form>

        <div class="d-flex justify-content-end mb-3">
            <form action="dashboard.php" method="POST" target="_blank" id="attExportForm">
                <input type="hidden" name="att_year" value="<?php echo htmlspecialchars($att_filter_year); ?>">
                <input type="hidden" name="att_month" value="<?php echo htmlspecialchars($att_filter_month); ?>">
                <input type="hidden" name="att_search" value="<?php echo htmlspecialchars($att_filter_search); ?>">
                
                <div class="btn-group">
                    <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-download"></i> Export Data
                    </button>
                    <ul class="dropdown-menu">
                        <li><button type="submit" name="export_attendance" value="csv" class="dropdown-item" onclick="setAttFormat('csv')"><i class="fas fa-file-csv"></i> CSV</button></li>
                        <li><button type="submit" name="export_attendance" value="excel" class="dropdown-item" onclick="setAttFormat('excel')"><i class="fas fa-file-excel"></i> Excel</button></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><button type="button" class="dropdown-item" onclick="downloadAttPDF()"><i class="fas fa-file-pdf"></i> PDF</button></li>
                    </ul>
                    <input type="hidden" name="export_format" id="attExportFormatInput" value="csv">
                </div>
            </form>
        </div>

        <div class="section-title">
            <i class="fas fa-clock"></i>
            Attendance Logs
        </div>
        <div class="data-table" id="attTableContainer">
            <table class="table table-striped" id="attTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Punch In</th>
                        <th>Punch Out</th>
                        <th>Total Hours</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($att_result && $att_result->num_rows > 0): ?>
                        <?php while($att = $att_result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo date('d M Y', strtotime($att['date'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($att['first_name'] . ' ' . $att['last_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($att['employee_code']); ?></small>
                            </td>
                            <td><?php echo $att['punch_in'] ? date('h:i A', strtotime($att['punch_in'])) : '-'; ?></td>
                            <td><?php echo $att['punch_out'] ? date('h:i A', strtotime($att['punch_out'])) : '-'; ?></td>
                            <td><?php echo isset($att['total_hours']) ? $att['total_hours'] . 'h' : '-'; ?></td>
                            <td>
                                <?php 
                                    $badge_class = 'bg-secondary';
                                    if ($att['status'] == 'present') $badge_class = 'bg-success';
                                    if ($att['status'] == 'absent') $badge_class = 'bg-danger';
                                    if ($att['status'] == 'late') $badge_class = 'bg-warning';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($att['status']); ?></span>
                                <?php if (isset($att['correction_status']) && $att['correction_status'] == 'approved'): ?>
                                    <span class="badge bg-primary" title="Approved Correction"><i class="fas fa-check-circle"></i> Approved</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No attendance records found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
