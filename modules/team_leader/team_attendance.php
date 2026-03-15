            <!-- Attendance Summary Section -->
            <div id="attendance-summary-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Attendance Summary</h1>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-select">
                            <option selected>Current Month (<?php echo date('F Y'); ?>)</option>
                        </select>
                    </div>
                </div>
                <div class="section-card">
                    <div class="section-title"><i class="fas fa-clock"></i> Monthly Attendance Report</div>
                    <div class="data-table">
                        <table class="table table-striped">
                            <thead>
                                <tr><th>Employee Name</th><th>Present</th><th>Absent</th><th>Late</th><th>Attendance %</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($team_attendance_summary_result && $team_attendance_summary_result->num_rows > 0): 
                                    while($att = $team_attendance_summary_result->fetch_assoc()): 
                                        $total = $att['present_days'] + $att['absent_days'];
                                        $percent = ($total > 0) ? round(($att['present_days'] / $total) * 100, 1) : 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($att['first_name'] . ' ' . $att['last_name']); ?></td>
                                    <td><span class="badge bg-success"><?php echo $att['present_days']; ?></span></td>
                                    <td><span class="badge bg-danger"><?php echo $att['absent_days']; ?></span></td>
                                    <td><span class="badge bg-warning"><?php echo $att['late_days']; ?></span></td>
                                    <td><strong><?php echo $percent; ?>%</strong></td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="5" class="text-center text-muted">No attendance records found for this month.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
