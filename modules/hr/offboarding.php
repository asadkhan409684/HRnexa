<div id="offboarding-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Offboarding Process</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newOffboardingModal"><i class="fas fa-plus"></i> New Offboarding</button>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-sign-out-alt"></i>
            Active Offboarding Cases
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>LWD</th>
                        <th>Resignation</th>
                        <th>Reason</th>
                        <th>Settlement</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($offboarding_result && $offboarding_result->num_rows > 0): ?>
                        <?php while($ob = $offboarding_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($ob['employee_name']); ?></strong></td>
                            <td><?php echo date('d M Y', strtotime($ob['last_working_day'])); ?></td>
                            <td><?php echo date('d M Y', strtotime($ob['resignation_date'])); ?></td>
                            <td><?php echo htmlspecialchars($ob['exit_reason']); ?></td>
                            <td>
                                <?php 
                                    $set_badge = 'bg-secondary';
                                    if ($ob['settlement_status'] == 'processed') $set_badge = 'bg-info';
                                    if ($ob['settlement_status'] == 'paid') $set_badge = 'bg-success';
                                ?>
                                <span class="badge <?php echo $set_badge; ?>"><?php echo ucfirst($ob['settlement_status']); ?></span>
                            </td>
                            <td>
                                <?php 
                                    $st_badge = 'bg-warning';
                                    if ($ob['status'] == 'completed') $st_badge = 'bg-success';
                                ?>
                                <span class="badge <?php echo $st_badge; ?>"><?php echo str_replace('_', ' ', ucfirst($ob['status'])); ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No active offboarding cases.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- New Offboarding Modal -->
<div class="modal fade" id="newOffboardingModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Initialize Offboarding</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php 
                            $act_emps = $conn->query("SELECT id, first_name, last_name FROM employees WHERE employment_status IN ('active', 'probation') ORDER BY first_name ASC");
                            while($e = $act_emps->fetch_assoc()): ?>
                                <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['first_name'] . ' ' . $e['last_name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Resignation Date</label>
                        <input type="date" name="resignation_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Last Working Day</label>
                        <input type="date" name="last_working_day" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Exit Reason</label>
                        <textarea name="exit_reason" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_offboarding" class="btn btn-primary">Initialize</button>
                </div>
            </form>
        </div>
    </div>
</div>
