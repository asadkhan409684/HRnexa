            <!-- Leave Approval Section -->
            <div id="leave-approval-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Leave Approval</h1>
                </div>
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-check-circle"></i>
                        Pending Leave Requests
                    </div>
                    <div class="data-table">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>From Date</th>
                                    <th>To Date</th>
                                    <th>Days</th>
                                    <th>Reason</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($pending_leaves_result && $pending_leaves_result->num_rows > 0): ?>
                                    <?php 
                                    $pending_leaves_result->data_seek(0);
                                    while($leaf = $pending_leaves_result->fetch_assoc()): 
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($leaf['first_name'] . ' ' . $leaf['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($leaf['leave_type_name']); ?></td>
                                        <td><?php echo date('d-M-Y', strtotime($leaf['start_date'])); ?></td>
                                        <td><?php echo date('d-M-Y', strtotime($leaf['end_date'])); ?></td>
                                        <td><?php echo $leaf['total_days']; ?></td>
                                        <td><?php echo htmlspecialchars($leaf['reason']); ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" onclick="handleTeamAction('leave', <?php echo $leaf['id']; ?>, 'referred', this)" class="btn btn-sm btn-info"><i class="fas fa-paper-plane"></i> Refer</button>
                                                <button type="button" onclick="handleTeamAction('leave', <?php echo $leaf['id']; ?>, 'rejected', this)" class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Reject</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" class="text-center text-muted">No pending leave requests found for your team.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
