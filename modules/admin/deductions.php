<?php
// Fetch all deduction rules
$deductions_res = $conn->query("SELECT * FROM deductions ORDER BY name ASC");
?>

<!-- Deduction Management Section -->
<div id="deduction-rules-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Deduction Rules</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeductionModal">
            <i class="fas fa-plus"></i> Add Deduction
        </button>
    </div>
    
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-minus-circle"></i>
            System Deductions
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Basis</th>
                        <th>Amount Type</th>
                        <th>Limit</th>
                        <th>Level</th>
                        <th>Mandatory</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($deductions_res && $deductions_res->num_rows > 0): ?>
                        <?php while($ded = $deductions_res->fetch_assoc()): ?>
                        <tr>
                            <td><code class="text-primary"><?php echo htmlspecialchars($ded['deduction_code'] ?? '-'); ?></code></td>
                            <td><strong><?php echo htmlspecialchars($ded['name'] ?? ''); ?></strong></td>
                            <td><?php echo ucfirst($ded['type']); ?></td>
                            <td><?php echo ucfirst($ded['calculation_basis'] ?? 'Basic'); ?></td>
                            <td><?php echo ucfirst($ded['amount_type']); ?></td>
                            <td><?php echo $ded['max_limit'] ? number_format($ded['max_limit'], 2) : 'No Limit'; ?></td>
                            <td><span class="badge bg-info"><?php echo htmlspecialchars($ded['designation_level'] ?? 'All'); ?></span></td>
                            <td>
                                <span class="badge <?php echo $ded['is_mandatory'] ? 'bg-warning text-dark' : 'bg-light text-muted'; ?>">
                                    <?php echo $ded['is_mandatory'] ? 'Mandatory' : 'Optional'; ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?php echo $ded['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                    <?php echo $ded['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary edit-deduction-btn" 
                                        data-id="<?php echo $ded['id']; ?>" 
                                        data-code="<?php echo htmlspecialchars($ded['deduction_code'] ?? ''); ?>"
                                        data-name="<?php echo htmlspecialchars($ded['name'] ?? ''); ?>" 
                                        data-type="<?php echo $ded['type']; ?>" 
                                        data-basis="<?php echo $ded['calculation_basis'] ?? 'Basic'; ?>"
                                        data-amount_type="<?php echo $ded['amount_type']; ?>" 
                                        data-amount="<?php echo $ded['default_amount']; ?>" 
                                        data-max_limit="<?php echo $ded['max_limit'] ?? ''; ?>"
                                        data-level="<?php echo $ded['designation_level'] ?? 'All'; ?>"
                                        data-mandatory="<?php echo $ded['is_mandatory']; ?>" 
                                        data-active="<?php echo $ded['is_active']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger delete-deduction-btn" data-id="<?php echo $ded['id']; ?>">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="10" class="text-center">No deductions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Deduction Modal -->
<div class="modal fade" id="addDeductionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="dashboard.php" method="POST" id="addDeductionForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Deduction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Deduction Name</label>
                            <input type="text" class="form-control" name="ded_name" id="add_ded_name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Deduction Code</label>
                            <input type="text" class="form-control bg-light" name="ded_code" id="add_ded_code" readonly required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="ded_type">
                                <option value="monthly">Monthly</option>
                                <option value="variable">Variable</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Calculation Basis</label>
                            <select class="form-select" name="ded_basis">
                                <option value="Basic">Basic Salary</option>
                                <option value="Gross">Gross Salary</option>
                                <option value="Net">Net Salary</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Amount Type</label>
                            <select class="form-select ded_amount_type_select" name="ded_amount_type">
                                <option value="fixed">Fixed</option>
                                <option value="percentage" selected>Percentage</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default Amount / %</label>
                            <input type="number" step="0.01" class="form-control ded_final_amount" name="ded_amount" required>
                        </div>
                    </div>

                    <!-- Percentage Calculation Group (Helper) -->
                    <div class="bg-light p-3 rounded mb-3 ded-calc-group">
                        <div class="row g-2">
                            <div class="col-md-7">
                                <label class="form-label small">Reference Basis Salary Amount (Helper)</label>
                                <input type="number" step="0.01" class="form-control form-control-sm ded_ref_basic" placeholder="e.g. 50000">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small">Percentage (%)</label>
                                <input type="number" step="0.01" class="form-control form-control-sm ded_percentage" placeholder="e.g. 10">
                            </div>
                        </div>
                        <div class="form-text x-small mt-2 text-primary">Helper to calculate Default Amount if Amount Type is Percentage.</div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Max Limit (Optional)</label>
                            <input type="number" step="0.01" class="form-control" name="ded_max_limit" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Designation Level</label>
                            <select class="form-select" name="ded_level">
                                <option value="All">All Levels</option>
                                <option value="Junior">Junior</option>
                                <option value="Mid-Level">Mid-Level</option>
                                <option value="Senior">Senior</option>
                                <option value="Lead">Lead</option>
                                <option value="Executive">Executive</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="ded_mandatory" id="ded_mandatory" checked>
                        <label class="form-check-label" for="ded_mandatory">Is Mandatory?</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="ded_active" id="ded_active" checked>
                        <label class="form-check-label" for="ded_active">Is Active?</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_deduction" class="btn btn-primary">Save Deduction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Deduction Modal -->
<div class="modal fade" id="editDeductionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="dashboard.php" method="POST" id="editDeductionForm">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Deduction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="ded_id" id="edit_ded_id">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label class="form-label">Deduction Name</label>
                            <input type="text" class="form-control" name="ded_name" id="edit_ded_name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Deduction Code</label>
                            <input type="text" class="form-control bg-light" name="ded_code" id="edit_ded_code" readonly required>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Type</label>
                            <select class="form-select" name="ded_type" id="edit_ded_type">
                                <option value="monthly">Monthly</option>
                                <option value="variable">Variable</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Calculation Basis</label>
                            <select class="form-select" name="ded_basis" id="edit_ded_basis">
                                <option value="Basic">Basic Salary</option>
                                <option value="Gross">Gross Salary</option>
                                <option value="Net">Net Salary</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Amount Type</label>
                            <select class="form-select ded_amount_type_select" name="ded_amount_type" id="edit_ded_amount_type">
                                <option value="fixed">Fixed</option>
                                <option value="percentage">Percentage</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Default Amount / %</label>
                            <input type="number" step="0.01" class="form-control ded_final_amount" name="ded_amount" id="edit_ded_amount" required>
                        </div>
                    </div>

                    <!-- Percentage Calculation Group (Helper) -->
                    <div class="bg-light p-3 rounded mb-3 ded-calc-group">
                        <div class="row g-2">
                            <div class="col-md-7">
                                <label class="form-label small">Reference Basis Salary (Helper)</label>
                                <input type="number" step="0.01" class="form-control form-control-sm ded_ref_basic" placeholder="e.g. 50000">
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small">Percentage (%)</label>
                                <input type="number" step="0.01" class="form-control form-control-sm ded_percentage" placeholder="e.g. 10">
                            </div>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Max Limit (Optional)</label>
                            <input type="number" step="0.01" class="form-control" name="ded_max_limit" id="edit_ded_max_limit" placeholder="0.00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Designation Level</label>
                            <select class="form-select" name="ded_level" id="edit_ded_level">
                                <option value="All">All Levels</option>
                                <option value="Junior">Junior</option>
                                <option value="Mid-Level">Mid-Level</option>
                                <option value="Senior">Senior</option>
                                <option value="Lead">Lead</option>
                                <option value="Executive">Executive</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" name="ded_mandatory" id="edit_ded_mandatory">
                        <label class="form-check-label" for="edit_ded_mandatory">Is Mandatory?</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="ded_active" id="edit_ded_active">
                        <label class="form-check-label" for="edit_ded_active">Is Active?</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_deduction" class="btn btn-primary">Update Deduction</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Helper function to generate Deduction Code
    const generateDeductionCode = (name) => {
        if (!name) return '';
        const prefix = 'DED-';
        const shortName = name.replace(/[^a-zA-Z]/g, '').substring(0, 3).toUpperCase();
        const rand = Math.floor(1000 + Math.random() * 9000);
        return prefix + shortName + rand;
    };

    // Auto-generate code when name changes (only for Add modal)
    document.getElementById('add_ded_name').addEventListener('input', function() {
        const codeInput = document.getElementById('add_ded_code');
        if (this.value) {
            codeInput.value = generateDeductionCode(this.value);
        } else {
            codeInput.value = '';
        }
    });

    // Populate Edit Deduction Modal
    document.querySelectorAll('.edit-deduction-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = this.dataset;
            document.getElementById('edit_ded_id').value = data.id;
            document.getElementById('edit_ded_code').value = data.code;
            document.getElementById('edit_ded_name').value = data.name;
            document.getElementById('edit_ded_type').value = data.type;
            document.getElementById('edit_ded_basis').value = data.basis;
            document.getElementById('edit_ded_amount_type').value = data.amount_type;
            document.getElementById('edit_ded_amount').value = data.amount;
            document.getElementById('edit_ded_max_limit').value = data.max_limit;
            document.getElementById('edit_ded_level').value = data.level;
            document.getElementById('edit_ded_mandatory').checked = data.mandatory == 1;
            document.getElementById('edit_ded_active').checked = data.active == 1;
            
            new bootstrap.Modal(document.getElementById('editDeductionModal')).show();
        });
    });

    // Auto-calculation logic for both Modals
    const setupDedAutoCalc = (formId) => {
        const form = document.getElementById(formId);
        if (!form) return;
        
        const refBasic = form.querySelector('.ded_ref_basic');
        const percentage = form.querySelector('.ded_percentage');
        const finalAmount = form.querySelector('.ded_final_amount');
        const amountType = form.querySelector('.ded_amount_type_select');

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
             const group = form.querySelector('.ded-calc-group');
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

    setupDedAutoCalc('addDeductionForm');
    setupDedAutoCalc('editDeductionForm');

    // Delete Deduction Confirmation
    document.querySelectorAll('.delete-deduction-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const dedId = this.dataset.id;
            if (confirm('Are you sure you want to delete this deduction rule?')) {
                window.location.href = 'dashboard.php?delete_deduction=' + dedId + '&csrf_token=<?php echo $_SESSION['csrf_token']; ?>';
            }
        });
    });
});
</script>
