            <!-- Attendance Correction Approval Section -->
            <div id="attendance-approval-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Attendance Correction Approval</h1>
                </div>
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-tasks"></i>
                        Pending Attendance Corrections
                    </div>
                    <div class="data-table">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Date</th>
                                    <th>Reason</th>
                                    <th>Original Time</th>
                                    <th>Requested Time</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($pending_corrections_result && $pending_corrections_result->num_rows > 0): ?>
                                    <?php while($cor = $pending_corrections_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($cor['first_name'] . ' ' . $cor['last_name']); ?></td>
                                        <td><?php echo date('d-M-Y', strtotime($cor['date'])); ?></td>
                                        <td><?php echo htmlspecialchars($cor['reason']); ?></td>
                                        <td><?php echo ($cor['original_punch_in'] ? date('h:i A', strtotime($cor['original_punch_in'])) : '—') . ' / ' . ($cor['original_punch_out'] ? date('h:i A', strtotime($cor['original_punch_out'])) : '—'); ?></td>
                                        <td><?php echo date('h:i A', strtotime($cor['corrected_punch_in'])) . ' / ' . date('h:i A', strtotime($cor['corrected_punch_out'])); ?></td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <button type="button" onclick="handleTeamAction('correction', <?php echo $cor['id']; ?>, 'referred', this)" class="btn btn-sm btn-info"><i class="fas fa-paper-plane"></i> Refer</button>
                                                <button type="button" onclick="handleTeamAction('correction', <?php echo $cor['id']; ?>, 'rejected', this)" class="btn btn-sm btn-danger"><i class="fas fa-times"></i> Reject</button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" class="text-center text-muted">No pending corrections found for your team.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
