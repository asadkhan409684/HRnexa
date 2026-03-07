<?php
// Fetch all allowances
$allowances_res = $conn->query("SELECT * FROM allowances ORDER BY name ASC");
?>

<!-- Allowance Management Section -->
<div id="allowance-management-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Allowance Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAllowanceModal">
            <i class="fas fa-plus"></i> Add Allowance
        </button>
    </div>
    
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-money-bill-wave"></i>
            System Allowances
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Amount Type</th>
                        <th>Default Amount</th>
                        <th>Designation Level</th>
                        <th>Ref. Basic</th>
                        <th>Taxable</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($allowances_res && $allowances_res->num_rows > 0): ?>
                        <?php while($alw = $allowances_res->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($alw['name'] ?? ''); ?></strong></td>
                            <td><?php echo ucfirst($alw['type']); ?></td>
                            <td><?php echo ucfirst($alw['amount_type']); ?></td>
                            <td><?php echo number_format($alw['default_amount'], 2); ?></td>
                            <td>
                                <span class="badge bg-info"><?php echo htmlspecialchars($alw['designation_level'] ?? 'All'); ?></span>
                            </td>
                            <td><?php echo number_format($alw['basic_salary'], 0); ?></td>
                            <td><?php echo $alw['is_taxable'] ? 'Yes' : 'No'; ?></td>
                            <td>
                                <span class="badge <?php echo $alw['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $alw['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary edit-allowance-btn" 
                                        data-id="<?php echo $alw['id']; ?>" 
                                        data-name="<?php echo htmlspecialchars($alw['name'] ?? ''); ?>" 
                                        data-type="<?php echo $alw['type']; ?>" 
                                        data-amount_type="<?php echo $alw['amount_type']; ?>" 
                                        data-amount="<?php echo $alw['default_amount']; ?>" 
                                        data-level="<?php echo $alw['designation_level']; ?>" 
                                        data-basic_salary="<?php echo $alw['basic_salary']; ?>" 
                                        data-taxable="<?php echo $alw['is_taxable']; ?>" 
                                        data-active="<?php echo $alw['is_active']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-allowance-btn" data-id="<?php echo $alw['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center">No allowances found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Allowance Modal -->
<div class="modal fade" id="addAllowanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="dashboard.php" method="POST" id="addAllowanceForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Allowance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Allowance Name</label>
                        <input type="text" class="form-control" name="alw_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="alw_type">
                            <option value="monthly">Monthly</option>
                            <option value="variable">Variable</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Amount Type</label>
                            <select class="form-select alw_amount_type_select" name="alw_amount_type">
                                <option value="fixed">Fixed</option>
                                <option value="percentage" selected>Percentage</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">Default Amount</label>
                            <input type="number" step="0.01" class="form-control alw_final_amount" name="alw_amount" required>
                        </div>
                    </div>

                    <!-- Percentage Calculation Group -->
                    <div class="bg-light p-3 rounded mb-3 alw-calc-group">
                        <div class="row g-2">
                            <div class="col-md-7">
                                <label class="form-label small">Reference Basic Salary</label>
                                <input type="number" step="0.01" class="form-control form-control-sm alw_ref_basic" name="alw_basic_salary" placeholder="Reference Basic">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small">Percentage (%)</label>
                                <input type="number" step="0.01" class="form-control form-control-sm alw_percentage" placeholder="e.g. 10">
                            </div>
                        </div>
                        <div class="form-text x-small mt-2 text-primary">Default Amount will be auto-calculated if Amount Type is Percentage.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Designation Level</label>
                        <select class="form-select" name="alw_level">
                            <option value="All">All Levels</option>
                            <option value="Junior">Junior</option>
                            <option value="Mid-Level">Mid-Level</option>
                            <option value="Senior">Senior</option>
                            <option value="Lead">Lead</option>
                            <option value="Executive">Executive</option>
                        </select>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="alw_taxable" id="alw_taxable" checked>
                        <label class="form-check-label" for="alw_taxable">Is Taxable?</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="alw_active" id="alw_active" checked>
                        <label class="form-check-label" for="alw_active">Is Active?</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_allowance" class="btn btn-primary">Save Allowance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Allowance Modal -->
<div class="modal fade" id="editAllowanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="dashboard.php" method="POST" id="editAllowanceForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Allowance</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="alw_id" id="edit_alw_id">
                    <div class="mb-3">
                        <label class="form-label">Allowance Name</label>
                        <input type="text" class="form-control" name="alw_name" id="edit_alw_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select class="form-select" name="alw_type" id="edit_alw_type">
                            <option value="monthly">Monthly</option>
                            <option value="variable">Variable</option>
                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Amount Type</label>
                            <select class="form-select alw_amount_type_select" name="alw_amount_type" id="edit_alw_amount_type">
                                <option value="fixed">Fixed</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>
                        <div class="col">
                            <label class="form-label">Default Amount</label>
                            <input type="number" step="0.01" class="form-control alw_final_amount" name="alw_amount" id="edit_alw_amount" required>
                        </div>
                    </div>

                    <!-- Percentage Calculation Group -->
                    <div class="bg-light p-3 rounded mb-3 alw-calc-group">
                        <div class="row g-2">
                            <div class="col-md-7">
                                <label class="form-label small">Reference Basic Salary</label>
                                <input type="number" step="0.01" class="form-control form-control-sm alw_ref_basic" name="alw_basic_salary" id="edit_alw_basic_salary" placeholder="Reference Basic">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small">Percentage (%)</label>
                                <input type="number" step="0.01" class="form-control form-control-sm alw_percentage" id="edit_alw_percentage" placeholder="e.g. 10">
                            </div>
                        </div>
                        <div class="form-text x-small mt-2 text-primary">Calculation is real-time for Percentage type.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Designation Level</label>
                        <select class="form-select" name="alw_level" id="edit_alw_level">
                            <option value="All">All Levels</option>
                            <option value="Junior">Junior</option>
                            <option value="Mid-Level">Mid-Level</option>
                            <option value="Senior">Senior</option>
                            <option value="Lead">Lead</option>
                            <option value="Executive">Executive</option>
                        </select>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="alw_taxable" id="edit_alw_taxable">
                        <label class="form-check-label" for="edit_alw_taxable">Is Taxable?</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="alw_active" id="edit_alw_active">
                        <label class="form-check-label" for="edit_alw_active">Is Active?</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_allowance" class="btn btn-primary">Update Allowance</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Populate Edit Allowance Modal
    document.querySelectorAll('.edit-allowance-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = this.dataset;
            document.getElementById('edit_alw_id').value = data.id;
            document.getElementById('edit_alw_name').value = data.name;
            document.getElementById('edit_alw_type').value = data.type;
            document.getElementById('edit_alw_amount_type').value = data.amount_type;
            document.getElementById('edit_alw_amount').value = data.amount;
            document.getElementById('edit_alw_level').value = data.level;
            document.getElementById('edit_alw_basic_salary').value = data.basic_salary;
            document.getElementById('edit_alw_taxable').checked = data.taxable == 1;
            document.getElementById('edit_alw_active').checked = data.active == 1;
            
            // Reverse calculate percentage if basic > 0
            if (data.amount_type === 'percentage' && parseFloat(data.basic_salary) > 0) {
                const percent = (parseFloat(data.amount) / parseFloat(data.basic_salary)) * 100;
                document.getElementById('edit_alw_percentage').value = percent.toFixed(2);
            } else {
                document.getElementById('edit_alw_percentage').value = '';
            }
            
            new bootstrap.Modal(document.getElementById('editAllowanceModal')).show();
        });
    });

    // Auto-calculation logic for both Modals
    const setupAutoCalc = (formId) => {
        const form = document.getElementById(formId);
        const refBasic = form.querySelector('.alw_ref_basic');
        const percentage = form.querySelector('.alw_percentage');
        const finalAmount = form.querySelector('.alw_final_amount');
        const amountType = form.querySelector('.alw_amount_type_select');

        const calculate = () => {
            if (amountType.value === 'percentage') {
                const basicVal = parseFloat(refBasic.value) || 0;
                const percentVal = parseFloat(percentage.value) || 0;
                if (basicVal > 0 && percentVal > 0) {
                    finalAmount.value = ((basicVal * percentVal) / 100).toFixed(2);
                }
            }
        };

        refBasic.addEventListener('input', calculate);
        percentage.addEventListener('input', calculate);
        amountType.addEventListener('change', () => {
             const group = form.querySelector('.alw-calc-group');
             if (amountType.value === 'fixed') {
                 group.style.opacity = '0.5';
                 group.style.pointerEvents = 'none';
             } else {
                 group.style.opacity = '1';
                 group.style.pointerEvents = 'auto';
                 calculate();
             }
        });
    };

    setupAutoCalc('addAllowanceForm');
    setupAutoCalc('editAllowanceForm');

    // Delete Allowance Confirmation
    document.querySelectorAll('.delete-allowance-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const alwId = this.dataset.id;
            if (confirm('Are you sure you want to delete this allowance? It may affect existing payroll records.')) {
                window.location.href = 'dashboard.php?delete_allowance=' + alwId + '&csrf_token=<?php echo $_SESSION['csrf_token']; ?>';
            }
        });
    });
});
</script>
