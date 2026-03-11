<div id="employment-status-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Employment Status</h1>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-briefcase"></i>
            Employee Status Management
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Current Status</th>
                        <th>Probation Start</th>
                        <th>Probation End</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($status_emp_result && $status_emp_result->num_rows > 0): ?>
                        <?php while($s_emp = $status_emp_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($s_emp['first_name'] . ' ' . $s_emp['last_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($s_emp['employee_code']); ?></small>
                            </td>
                            <td>
                                <?php 
                                    $badge_class = 'bg-info';
                                    if ($s_emp['employment_status'] == 'active') $badge_class = 'bg-success';
                                    if ($s_emp['employment_status'] == 'terminated') $badge_class = 'bg-danger';
                                    if ($s_emp['employment_status'] == 'resigned') $badge_class = 'bg-secondary';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($s_emp['employment_status']); ?></span>
                            </td>
                            <td><?php echo $s_emp['probation_start_date'] ?? 'N/A'; ?></td>
                            <td><?php echo $s_emp['probation_end_date'] ?? 'N/A'; ?></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick='viewStatusUpdate(<?php echo json_encode($s_emp); ?>)'>
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No employees found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Employment Status Update Modal -->
<div class="modal fade" id="statusUpdateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="dashboard.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Update Employment Status</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="employee_id" id="status_emp_id">
                    <p>Updating status for: <strong id="status_emp_name"></strong></p>
                    
                    <div class="mb-3">
                        <label class="form-label">Employment Status</label>
                        <select name="employment_status" id="update_emp_status" class="form-select" required>
                            <option value="active">Active</option>
                            <option value="probation">Probation</option>
                            <option value="resigned">Resigned</option>
                            <option value="terminated">Terminated</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Probation Start Date</label>
                        <input type="date" name="probation_start_date" id="update_prob_start" class="form-control">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Probation End Date</label>
                        <input type="date" name="probation_end_date" id="update_prob_end" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_employment_status" class="btn btn-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
