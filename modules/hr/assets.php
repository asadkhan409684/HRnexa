<div id="assets-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Asset Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAssetModal">
            <i class="fas fa-plus"></i> New Asset
        </button>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-cube"></i>
            Asset Inventory & Assignment
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Asset Code</th>
                        <th>Asset Name</th>
                        <th>Category</th>
                        <th>Assigned To</th>
                        <th>Serial No.</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($assets_result && $assets_result->num_rows > 0): ?>
                        <?php while($ast = $assets_result->fetch_assoc()): ?>
                        <tr>
                            <td><code><?php echo htmlspecialchars($ast['asset_code']); ?></code></td>
                            <td>
                                <strong><?php echo htmlspecialchars($ast['name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($ast['brand'] . ' ' . $ast['model']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($ast['category'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if ($ast['assigned_to']): ?>
                                    <strong><?php echo htmlspecialchars($ast['assigned_to']); ?></strong><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($ast['emp_code']); ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Unassigned</span>
                                <?php endif; ?>
                            </td>
                            <td><small><?php echo htmlspecialchars($ast['serial_number'] ?? 'N/A'); ?></small></td>
                            <td>
                                <?php 
                                    $ast_badge = 'bg-info';
                                    if ($ast['status'] == 'available') $ast_badge = 'bg-success';
                                    if ($ast['status'] == 'assigned') $ast_badge = 'bg-primary';
                                    if ($ast['status'] == 'maintenance') $ast_badge = 'bg-warning';
                                    if ($ast['status'] == 'disposed') $ast_badge = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $ast_badge; ?>"><?php echo ucfirst($ast['status']); ?></span>
                            </td>
                            <td>
                                <?php if ($ast['status'] == 'available'): ?>
                                    <button class="btn btn-sm btn-outline-success" onclick='openAssignModal(<?php echo json_encode($ast); ?>)' title="Assign Asset">
                                        <i class="fas fa-user-plus"></i> Assign
                                    </button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No assets found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Asset Modal -->
<div class="modal fade" id="addAssetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Add New Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Asset Code</label>
                            <input type="text" class="form-control" name="asset_code" required placeholder="e.g. AST-001">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Category</label>
                            <select class="form-select" name="category">
                                <option value="Electronics">Electronics</option>
                                <option value="Furniture">Furniture</option>
                                <option value="Vehicles">Vehicles</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Asset Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. Dell Latitude 5420">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Brand</label>
                            <input type="text" class="form-control" name="brand">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Model</label>
                            <input type="text" class="form-control" name="model">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Serial Number</label>
                        <input type="text" class="form-control" name="serial_number">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Purchase Date</label>
                            <input type="date" class="form-control" name="purchase_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Warranty Expiry</label>
                            <input type="date" class="form-control" name="warranty_expiry">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Initial Location</label>
                        <input type="text" class="form-control" name="location" placeholder="e.g. Dhaka Office Shelf A">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_asset" class="btn btn-primary">Save Asset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Assign Asset Modal -->
<div class="modal fade" id="assignAssetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Assign Asset</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="asset_id" id="assign_asset_id">
                    <div class="mb-3">
                        <label class="form-label">Asset</label>
                        <div id="assign_asset_name" class="fw-bold text-primary"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Employee</label>
                        <select class="form-select" name="employee_id" required>
                            <option value="">Choose Employee...</option>
                            <?php 
                            $e_res = $conn->query("SELECT id, first_name, last_name, employee_code FROM employees WHERE employment_status = 'active' ORDER BY first_name ASC");
                            while($e = $e_res->fetch_assoc()) {
                                echo "<option value='{$e['id']}'>{$e['first_name']} {$e['last_name']} ({$e['employee_code']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Assignment Date</label>
                        <input type="date" class="form-control" name="assigned_date" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Condition Notes</label>
                        <textarea class="form-control" name="condition" rows="2" placeholder="e.g. Brand new, minor scratches, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="assign_asset" class="btn btn-primary">Assign Asset</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openAssignModal(ast) {
    document.getElementById('assign_asset_id').value = ast.id;
    document.getElementById('assign_asset_name').textContent = ast.asset_code + ' - ' + ast.name;
    new bootstrap.Modal(document.getElementById('assignAssetModal')).show();
}
</script>
