<div id="employees-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Employee List</h1>
        <button class="btn btn-primary" onclick="switchSection('add-employee')"><i class="fas fa-plus"></i> Add Employee</button>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-users"></i>
            All Employees
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Employee Name</th>
                        <th>Employee ID</th>
                        <th>Department</th>
                        <th>Designation</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($employees_list_result && $employees_list_result->num_rows > 0): ?>
                        <?php while($emp = $employees_list_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($emp['first_name'] . ' ' . $emp['last_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($emp['employee_code']); ?></td>
                            <td><?php echo htmlspecialchars($emp['dept_name'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($emp['desig_name'] ?? 'N/A'); ?></td>
                            <td>
                                <?php 
                                    $badge_status = 'bg-info';
                                    if ($emp['employment_status'] == 'active') $badge_status = 'bg-success';
                                    if ($emp['employment_status'] == 'terminated') $badge_status = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $badge_status; ?>"><?php echo ucfirst($emp['employment_status']); ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick='viewEmployeeDetails(<?php echo json_encode($emp); ?>)'>
                                    <i class="fas fa-eye"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No employees found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Employee Detail Modal -->
<div class="modal fade" id="employeeDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Employee Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Loaded via JS -->
            </div>
            <div class="modal-footer">
                <div style="margin-right: auto;">
                    <button type="button" class="btn btn-success btn-sm" onclick="openAllowanceModal()" title="Add Allowance">
                        <i class="fas fa-plus-circle"></i> Add Allowance
                    </button>
                    <button type="button" class="btn btn-warning btn-sm" onclick="openDeductionModal()" title="Add Deduction">
                        <i class="fas fa-minus-circle"></i> Add Deduction
                    </button>
                </div>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
<!-- Allowance Management Modal -->
<div class="modal fade" id="allowanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Allowances - <span id="allowance_emp_name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Select Allowance</label>
                        <select id="allowance_select" class="form-select">
                            <option value="">-- Choose Allowance --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount</label>
                        <input type="number" id="allowance_amount" class="form-control" placeholder="Amount" step="0.01">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Effective From</label>
                    <input type="date" id="allowance_from" class="form-control">
                </div>
                <button class="btn btn-success" onclick="addAllowanceToEmployee()">
                    <i class="fas fa-plus"></i> Add Allowance
                </button>
                <hr>
                <h6>Current Allowances:</h6>
                <div id="allowance_list" class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Allowance Name</th>
                                <th>Amount</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="allowance_tbody">
                            <tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Deduction Management Modal -->
<div class="modal fade" id="deductionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Manage Deductions - <span id="deduction_emp_name"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Select Deduction</label>
                        <select id="deduction_select" class="form-select">
                            <option value="">-- Choose Deduction --</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Amount</label>
                        <input type="number" id="deduction_amount" class="form-control" placeholder="Amount" step="0.01">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Effective From</label>
                    <input type="date" id="deduction_from" class="form-control">
                </div>
                <button class="btn btn-danger" onclick="addDeductionToEmployee()">
                    <i class="fas fa-plus"></i> Add Deduction
                </button>
                <hr>
                <h6>Current Deductions:</h6>
                <div id="deduction_list" class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <th>Deduction Name</th>
                                <th>Amount</th>
                                <th>From Date</th>
                                <th>To Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="deduction_tbody">
                            <tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>