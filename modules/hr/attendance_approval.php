<div id="attendance-approval-section" class="section-content" style="display: none;">
    <div class="page-header">
            <h1>Attendance Approval</h1>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-list-alt"></i>
            Attendance Correction Requests
        </div>
        <?php if (isset($correction_result) && $correction_result->num_rows > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Date</th>
                            <th>Original Time</th>
                            <th>Requested Time</th>
                            <th>Reason</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Reset pointer just in case, though query is fresh
                        $correction_result->data_seek(0);
                        while ($req = $correction_result->fetch_assoc()): 
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($req['first_name'] . ' ' . $req['last_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($req['employee_code']); ?></small>
                            </td>
                            <td><?php echo date('d M Y', strtotime($req['date'])); ?></td>
                            <td>
                                <small class="text-muted">In: <?php echo ($req['original_punch_in'] && $req['original_punch_in'] != '0000-00-00 00:00:00') ? date('h:i A', strtotime($req['original_punch_in'])) : '-'; ?></small><br>
                                <small class="text-muted">Out: <?php echo ($req['original_punch_out'] && $req['original_punch_out'] != '0000-00-00 00:00:00') ? date('h:i A', strtotime($req['original_punch_out'])) : '-'; ?></small>
                            </td>
                            <td>
                                <strong>In: <?php echo date('h:i A', strtotime($req['corrected_punch_in'])); ?></strong><br>
                                <strong>Out: <?php echo date('h:i A', strtotime($req['corrected_punch_out'])); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($req['reason']); ?></td>
                            <td>
                                <?php 
                                    $status = $req['status'];
                                    $badge = 'bg-secondary';
                                    if ($status == 'referred') $badge = 'bg-info';
                                    if ($status == 'pending') $badge = 'bg-warning';
                                    if ($status == 'approved') $badge = 'bg-success';
                                    if ($status == 'rejected') $badge = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $badge; ?>"><?php echo ucfirst($status); ?></span>
                            </td>
                            <td>
                                <?php if ($status == 'pending' || $status == 'referred'): ?>
                                <form action="dashboard.php" method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="request_id" value="<?php echo $req['id']; ?>">
                                    <button type="submit" name="approve_correction" class="btn btn-sm btn-success me-1" title="Approve">
                                        <i class="fas fa-check"></i>
                                    </button>
                                    <button type="submit" name="reject_correction" class="btn btn-sm btn-danger" title="Reject" onclick="return confirm('Are you sure you want to reject this request?');">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                <?php else: ?>
                                    <span class="text-muted"><small>Processed</small></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                No attendance correction requests found.
            </div>
        <?php endif; ?>
    </div>
</div>
