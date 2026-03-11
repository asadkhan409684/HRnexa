<div id="leave-applications-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Leave Applications</h1>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-calendar-check"></i>
            Pending Leave Approvals
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Leave Type</th>
                        <th>Dates</th>
                        <th>Days</th>
                        <th>Reason</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($leave_apps_result && $leave_apps_result->num_rows > 0): ?>
                        <?php while($leave = $leave_apps_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($leave['first_name'] . ' ' . $leave['last_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($leave['employee_code']); ?></small>
                                <?php if($leave['role_id'] == 4): ?>
                                    <span class="badge bg-primary" style="font-size: 0.7em;">Team Leader</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($leave['leave_type']); ?></td>
                            <td>
                                <?php echo date('d M', strtotime($leave['start_date'])) . ' - ' . date('d M', strtotime($leave['end_date'])); ?>
                                <br><small class="text-muted"><?php echo date('Y', strtotime($leave['start_date'])); ?></small>
                            </td>
                            <td><?php 
                                // Calculate days if not in DB, roughly
                                $start = new DateTime($leave['start_date']);
                                $end = new DateTime($leave['end_date']);
                                $days = $end->diff($start)->days + 1; 
                                echo $days;
                            ?></td>
                            <td><?php echo htmlspecialchars($leave['reason']); ?></td>
                            <td>
                                <?php 
                                    $s_badge = 'bg-info';
                                    if ($leave['status'] == 'referred') $s_badge = 'bg-primary';
                                    if ($leave['status'] == 'pending') $s_badge = 'bg-warning';
                                    if ($leave['status'] == 'approved') $s_badge = 'bg-success';
                                    if ($leave['status'] == 'rejected') $s_badge = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $s_badge; ?>"><?php echo ucfirst($leave['status']); ?></span>
                            </td>
                            <td>
                                <?php if ($leave['status'] == 'pending' || $leave['status'] == 'referred'): ?>
                                <form method="POST" action="" class="d-flex gap-2">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="leave_id" value="<?php echo $leave['id']; ?>">
                                    <button type="submit" name="approve_leave" class="btn btn-sm btn-success" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="submit" name="reject_leave" class="btn btn-sm btn-danger" title="Reject">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                    <span class="text-muted small"><i class="fas fa-circle-check"></i> Processed</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No pending leave applications.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
