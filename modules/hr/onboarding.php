<div id="onboarding-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Onboarding Process</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newOnboardingModal"><i class="fas fa-plus"></i> New Onboarding</button>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-sign-in-alt"></i>
            Active Onboarding Cases
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Joining Date</th>
                        <th>Asset Status</th>
                        <th>Orientation</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($onboarding_result && $onboarding_result->num_rows > 0): ?>
                        <?php while($on = $onboarding_result->fetch_assoc()): 
                            $tasks = json_decode($on['checklist_tasks'], true);
                            $total_tasks = count($tasks);
                            $done_tasks = 0;
                            foreach($tasks as $t) if($t['status'] == 'completed') $done_tasks++;
                            $percent = $total_tasks > 0 ? round(($done_tasks / $total_tasks) * 100) : 0;
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($on['employee_name']); ?></strong></td>
                            <td><?php echo date('d M Y', strtotime($on['hire_date'])); ?></td>
                            <td>
                                <?php 
                                    $a_badge = 'bg-secondary';
                                    if ($on['asset_assignment_status'] == 'partially_assigned') $a_badge = 'bg-warning';
                                    if ($on['asset_assignment_status'] == 'completed') $a_badge = 'bg-success';
                                ?>
                                <span class="badge <?php echo $a_badge; ?>"><?php echo str_replace('_', ' ', ucfirst($on['asset_assignment_status'])); ?></span>
                            </td>
                            <td><?php echo $on['orientation_date'] ? date('d M Y', strtotime($on['orientation_date'])) : 'TBD'; ?></td>
                            <td>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar bg-success" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                                <small><?php echo $percent; ?>% Complete</small>
                            </td>
                            <td>
                                <?php 
                                    $s_badge = 'bg-info';
                                    if ($on['status'] == 'completed') $s_badge = 'bg-success';
                                    if ($on['status'] == 'pending') $s_badge = 'bg-secondary';
                                ?>
                                <span class="badge <?php echo $s_badge; ?>"><?php echo ucfirst($on['status']); ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick='openUpdateOnboardingModal(<?php echo json_encode($on); ?>)'>Update</button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No active onboarding cases.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Onboarding Modal -->
<div class="modal fade" id="newOnboardingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Initialize Onboarding</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php 
                            $all_emps = $conn->query("SELECT id, first_name, last_name FROM employees WHERE id NOT IN (SELECT employee_id FROM onboarding WHERE status != 'completed') ORDER BY first_name ASC");
                            while($e = $all_emps->fetch_assoc()): ?>
                                <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['first_name'] . ' ' . $e['last_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Orientation Date</label>
                        <input type="date" name="orientation_date" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_onboarding" class="btn btn-primary">Initialize</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Onboarding Modal -->
<div class="modal fade" id="updateOnboardingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Update Onboarding Progress</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="onboarding_id" id="up_on_id">
                    <div class="mb-3">
                        <label class="form-label">Employee: <span id="up_on_name" class="fw-bold"></span></label>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Asset Assignment Status</label>
                        <select name="asset_assignment_status" id="up_on_asset" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="partially_assigned">Partially Assigned</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Process Status</label>
                        <select name="status" id="up_on_status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_onboarding" class="btn btn-primary">Update Change</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openUpdateOnboardingModal(on) {
    document.getElementById('up_on_id').value = on.id;
    document.getElementById('up_on_name').textContent = on.employee_name;
    document.getElementById('up_on_asset').value = on.asset_assignment_status;
    document.getElementById('up_on_status').value = on.status;
    new bootstrap.Modal(document.getElementById('updateOnboardingModal')).show();
}
</script>
