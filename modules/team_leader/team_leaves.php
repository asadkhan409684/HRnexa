            <!-- Leave Summary Section -->
            <div id="leave-summary-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Leave Summary</h1>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-select">
                            <option selected>Current Year (<?php echo date('Y'); ?>)</option>
                        </select>
                    </div>
                </div>
                <div class="section-card">
                    <div class="section-title"><i class="fas fa-calendar-check"></i> Team Leave Summary</div>
                    <div class="data-table">
                        <table class="table table-striped">
                            <thead>
                                <tr><th>Employee Name</th><th>Entitled</th><th>Used</th><th>Pending</th><th>Balance</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($team_leave_summary_result && $team_leave_summary_result->num_rows > 0): 
                                    while($lv = $team_leave_summary_result->fetch_assoc()): 
                                        $balance = $lv['total_entitled'] - $lv['used_days'];
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($lv['first_name'] . ' ' . $lv['last_name']); ?></td>
                                    <td><?php echo $lv['total_entitled']; ?></td>
                                    <td><span class="badge bg-success"><?php echo $lv['used_days']; ?></span></td>
                                    <td><span class="badge bg-warning"><?php echo $lv['pending_days']; ?></span></td>
                                    <td><span class="badge bg-primary"><?php echo $balance; ?></span></td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="5" class="text-center text-muted">No leave records found for this year.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
