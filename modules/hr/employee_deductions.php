<?php
// Fetch all employees for the selection dropdown
$employees_res = $conn->query("SELECT id, first_name, last_name, employee_code FROM employees WHERE is_active = 1 ORDER BY first_name ASC");
$employees_list = [];
if ($employees_res) {
    while ($row = $employees_res->fetch_assoc()) {
        $employees_list[] = $row;
    }
}

// Fetch all deduction rules for selection
$deduction_rules_res = $conn->query("SELECT id, name, deduction_code, amount_type, default_amount, calculation_basis FROM deductions WHERE is_active = 1 ORDER BY name ASC");
$deduction_rules = [];
if ($deduction_rules_res) {
    while ($row = $deduction_rules_res->fetch_assoc()) {
        $deduction_rules[] = $row;
    }
}

// Fetch current employee assignments
$assignments_query = "SELECT ed.*, e.first_name, e.last_name, e.employee_code, d.name as deduction_name, d.deduction_code, d.amount_type as rule_amount_type 
                    FROM employee_deductions ed
                    JOIN employees e ON ed.employee_id = e.id
                    JOIN deductions d ON ed.deduction_id = d.id
                    ORDER BY ed.id DESC";
$assignments_res = $conn->query($assignments_query);
?>

<div class="row mb-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-0">Employee Deduction Management</h4>
            <p class="text-muted small">Assign loans, advances, or specific deductions to employees</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#assignDeductionModal">
            <i class="fas fa-plus-circle me-2"></i>Assign New Deduction
        </button>
    </div>
</div>

<div class="section-card">
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Employee</th>
                    <th>Deduction Name</th>
                    <th>Amount / Rate</th>
                    <th>Effective From</th>
                    <th>Effective To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($assignments_res && $assignments_res->num_rows > 0): ?>
                    <?php while($asg = $assignments_res->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($asg['first_name'] . ' ' . $asg['last_name']); ?></strong><br>
                            <small class="text-muted"><?php echo htmlspecialchars($asg['employee_code']); ?></small>
                        </td>
                        <td>
                            <strong><?php echo htmlspecialchars($asg['deduction_name']); ?></strong><br>
                            <small class="text-primary"><?php echo htmlspecialchars($asg['deduction_code'] ?? '-'); ?></small>
                        </td>
                        <td>
                            <span class="fw-bold"><?php echo number_format($asg['amount'], 2); ?></span>
                            <small class="text-muted"><?php echo ($asg['rule_amount_type'] === 'percentage') ? '%' : '(Fixed)'; ?></small>
                        </td>
                        <td><?php echo date('d M, Y', strtotime($asg['effective_from'])); ?></td>
                        <td><?php echo $asg['effective_to'] ? date('d M, Y', strtotime($asg['effective_to'])) : '<span class="text-muted">No End Date</span>'; ?></td>
                        <td>
                            <?php if ($asg['is_active']): ?>
                                <span class="badge bg-success">Active</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary edit-asg-btn" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editAssignmentModal"
                                        data-id="<?php echo $asg['id']; ?>"
                                        data-emp-id="<?php echo $asg['employee_id']; ?>"
                                        data-ded-id="<?php echo $asg['deduction_id']; ?>"
                                        data-amount="<?php echo $asg['amount']; ?>"
                                        data-from="<?php echo $asg['effective_from']; ?>"
                                        data-to="<?php echo $asg['effective_to']; ?>"
                                        data-active="<?php echo $asg['is_active']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <a href="?delete_assignment=<?php echo $asg['id']; ?>" 
                                   class="btn btn-outline-danger" 
                                   onclick="return confirm('Are you sure you want to remove this assignment?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4">No employee deductions assigned yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Assign Deduction Modal -->
<div class="modal fade" id="assignDeductionModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Assign Deduction to Employee</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Employee</label>
                    <select name="employee_id" class="form-select" required>
                        <option value="">Select Employee...</option>
                        <?php foreach($employees_list as $emp): ?>
                            <option value="<?php echo $emp['id']; ?>">
                                <?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name'] . ' (' . $emp['employee_code'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deduction Type</label>
                    <select name="deduction_id" id="assign_ded_id" class="form-select" required>
                        <option value="">Select Deduction Rule...</option>
                        <?php foreach($deduction_rules as $rule): ?>
                            <option value="<?php echo $rule['id']; ?>" 
                                    data-amount="<?php echo $rule['default_amount']; ?>"
                                    data-type="<?php echo $rule['amount_type']; ?>">
                                <?php echo htmlspecialchars($rule['name'] . ' (' . $rule['deduction_code'] . ')'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label">Deduction Amount / Percentage</label>
                    <div class="input-group">
                        <input type="number" step="0.01" name="amount" id="assign_amount" class="form-control" required>
                        <span class="input-group-text" id="assign_amount_addon">Amt</span>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Effective From</label>
                        <input type="date" name="effective_from" class="form-control" value="<?php echo date('Y-m-01'); ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Effective To (Optional)</label>
                        <input type="date" name="effective_to" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="assign_deduction" class="btn btn-primary">Assign Deduction</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Assignment Modal -->
<div class="modal fade" id="editAssignmentModal" tabindex="-1">
    <div class="modal-dialog">
        <form class="modal-content" method="POST">
            <input type="hidden" name="assignment_id" id="edit_asg_id">
            <div class="modal-header">
                <h5 class="modal-title">Edit Employee Deduction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Deduction Amount / Percentage</label>
                    <input type="number" step="0.01" name="amount" id="edit_asg_amount" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Effective From</label>
                        <input type="date" name="effective_from" id="edit_asg_from" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Effective To (Optional)</label>
                        <input type="date" name="effective_to" id="edit_asg_to" class="form-control">
                    </div>
                </div>
                <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" name="active" id="edit_asg_active" value="1">
                    <label class="form-check-label">Active Status</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="edit_assignment" class="btn btn-primary">Update Assignment</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle deduction rule selection to auto-fill default amount
    const assignDedSelect = document.getElementById('assign_ded_id');
    const assignAmountInput = document.getElementById('assign_amount');
    const assignAddon = document.getElementById('assign_amount_addon');

    assignDedSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            const amount = selectedOption.getAttribute('data-amount');
            const type = selectedOption.getAttribute('data-type');
            assignAmountInput.value = amount;
            assignAddon.textContent = (type === 'percentage') ? '%' : 'Amt';
        }
    });

    // Populate Edit Modal
    const editBtns = document.querySelectorAll('.edit-asg-btn');
    editBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('edit_asg_id').value = this.dataset.id;
            document.getElementById('edit_asg_amount').value = this.dataset.amount;
            document.getElementById('edit_asg_from').value = this.dataset.from;
            document.getElementById('edit_asg_to').value = this.dataset.to || '';
            document.getElementById('edit_asg_active').checked = (this.dataset.active == 1);
        });
    });
});
</script>
