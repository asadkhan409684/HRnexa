<?php
// Fetch all roles
$roles_query = "SELECT * FROM roles ORDER BY id ASC";
$roles_result = $conn->query($roles_query);

// Fetch all permissions grouped by module
$perms_query = "SELECT * FROM permissions ORDER BY module, action ASC";
$perms_result = $conn->query($perms_query);
$grouped_permissions = [];
while ($p = $perms_result->fetch_assoc()) {
    $grouped_permissions[$p['module']][] = $p;
}
?>

<div class="section-card border-0 shadow-sm">
    <div class="section-title d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <div class="p-2 bg-gradient-primary rounded text-white" style="background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);">
                <i class="fas fa-user-shield fs-4"></i>
            </div>
            <h5 class="mb-0 fw-bold">Roles & Access Control</h5>
        </div>
        <button class="btn btn-primary btn-sm px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#addRoleModal">
            <i class="fas fa-plus me-1"></i> Add New Role
        </button>
    </div>

    <div id="roleAlert" class="alert d-none rounded-3 shadow-sm border-0 mb-4"></div>

    <div class="table-responsive">
        <table class="table table-hover align-middle border-top">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Role Name</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th class="pe-4 text-end">Security Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($roles_result && $roles_result->num_rows > 0): ?>
                    <?php while($role = $roles_result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                        <i class="fas fa-user-tag small"></i>
                                    </div>
                                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($role['name'] ?? ''); ?></span>
                                </div>
                            </td>
                            <td>
                                <div class="small text-muted" style="max-width: 300px;"><?php echo htmlspecialchars($role['description'] ?? ''); ?></div>
                            </td>
                            <td>
                                <?php if($role['is_active']): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill small">
                                        Active
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1 rounded-pill small">
                                        Inactive
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-white btn-sm px-3 border shadow-sm rounded-pill" onclick="managePermissions(<?php echo $role['id']; ?>, '<?php echo addslashes($role['name']); ?>')">
                                    <i class="fas fa-key text-warning me-1"></i> Manage Permissions
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center py-5 text-muted">No roles found in the system.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>



<!-- Add New Role Modal -->
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
            <div class="modal-header bg-primary text-white border-0">
                <h5 class="modal-title fw-bold"><i class="fas fa-user-tag me-2"></i>Create New System Role</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addRoleForm">
                <div class="modal-body p-4">
                    <div id="addRoleAlert" class="alert d-none rounded-3 border-0 small"></div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Role Name</label>
                        <input type="text" name="role_name" class="form-control" placeholder="e.g. Sales Manager" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Description</label>
                        <textarea name="role_description" class="form-control" rows="3" placeholder="Describe the responsibilities..."></textarea>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" name="is_active" id="roleActiveStatus" checked>
                        <label class="form-check-label small" for="roleActiveStatus">Mark as Active</label>
                    </div>

                    <hr>
                    <h6 class="fw-bold mb-3"><i class="fas fa-lock me-2 text-warning"></i>Select Role Permissions</h6>
                    
                    <div class="row g-3 overflow-auto" style="max-height: 400px;">
                        <?php foreach ($grouped_permissions as $module => $perms): ?>
                            <div class="col-md-6">
                                <div class="card border shadow-none rounded-3 h-100">
                                    <div class="card-header bg-light border-bottom-0 py-2 d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold text-dark small text-uppercase">
                                            <i class="fas fa-cube me-1 text-primary"></i> <?php echo ucfirst($module); ?>
                                        </h6>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none smaller" 
                                                onclick="toggleModulePermsAdd(this, '<?php echo $module; ?>')">Select All</button>
                                    </div>
                                    <div class="card-body p-3">
                                        <?php foreach ($perms as $p): ?>
                                            <div class="permission-item p-2 rounded mb-1 transition-all">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input add-perm-checkbox add-module-<?php echo $module; ?>" 
                                                           type="checkbox" 
                                                           name="permissions[]" 
                                                           value="<?php echo $p['id']; ?>" 
                                                           id="add_perm_<?php echo $p['id']; ?>">
                                                    <label class="form-check-label d-block" for="add_perm_<?php echo $p['id']; ?>" style="cursor: pointer;">
                                                        <div class="fw-bold small"><?php echo ucfirst($p['action']); ?></div>
                                                        <div class="text-muted smaller"><?php echo htmlspecialchars($p['description'] ?? ''); ?></div>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 rounded-pill shadow" id="createRoleBtn">Create Role</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manage Permissions Modal -->
<div class="modal fade" id="permissionsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg overflow-hidden" style="border-radius: 20px;">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-lock text-warning"></i>
                    <h5 class="modal-title fw-bold mb-0">System Permissions: <span id="modalRoleName" class="text-warning"></span></h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rolePermissionsForm">
                <input type="hidden" name="role_id" id="modalRoleId">
                <div class="modal-body p-4" style="max-height: 70vh; overflow-y: auto;">
                    <div id="modalAlert" class="alert d-none rounded-3 border-0 shadow-sm mb-4"></div>
                    
                    <div class="alert alert-warning-subtle border-0 mb-4 small d-flex align-items-center gap-2">
                        <i class="fas fa-info-circle text-warning fs-5"></i>
                        <span>Changes made here will affect all users assigned to this role immediately after saving.</span>
                    </div>

                    <div class="row g-4">
                        <?php foreach ($grouped_permissions as $module => $perms): ?>
                            <div class="col-md-6">
                                <div class="card border shadow-none rounded-3 h-100">
                                    <div class="card-header bg-light border-bottom-0 py-2 d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0 fw-bold text-dark small text-uppercase letter-spacing-1">
                                            <i class="fas fa-cube me-1 text-primary"></i> <?php echo ucfirst($module); ?>
                                        </h6>
                                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none smaller" 
                                                onclick="toggleModulePerms(this, '<?php echo $module; ?>')">Select All</button>
                                    </div>
                                    <div class="card-body p-3">
                                        <?php foreach ($perms as $p): ?>
                                            <div class="permission-item p-2 rounded mb-1 transition-all" style="background: transparent;">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input perm-checkbox module-<?php echo $module; ?>" 
                                                           type="checkbox" 
                                                           name="permissions[]" 
                                                           value="<?php echo $p['id']; ?>" 
                                                           id="perm_<?php echo $p['id']; ?>"
                                                           style="cursor: pointer;">
                                                    <label class="form-check-label d-block" for="perm_<?php echo $p['id']; ?>" style="cursor: pointer;">
                                                        <div class="fw-bold small"><?php echo ucfirst($p['action']); ?></div>
                                                        <div class="text-muted smaller"><?php echo htmlspecialchars($p['description'] ?? ''); ?></div>
                                                    </label>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-4">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-dark px-5 rounded-pill shadow" id="saveRoleBtn">
                        <i class="fas fa-save me-2"></i> Update Permissions
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.letter-spacing-1 { letter-spacing: 1px; }
.smaller { font-size: 0.75rem; }
.transition-all { transition: all 0.2s ease; }
.permission-item:hover { background: #f8f9fa !important; }
.form-check-input:checked + .form-check-label .fw-bold { color: var(--bs-primary); }
</style>

<script>
function toggleModulePerms(btn, module) {
    const checkboxes = document.querySelectorAll('.module-' + module);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
    btn.textContent = !allChecked ? 'Deselect All' : 'Select All';
}

function toggleModulePermsAdd(btn, module) {
    const checkboxes = document.querySelectorAll('.add-module-' + module);
    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
    checkboxes.forEach(cb => cb.checked = !allChecked);
    btn.textContent = !allChecked ? 'Deselect All' : 'Select All';
}

function managePermissions(roleId, roleName) {
    document.getElementById('modalRoleId').value = roleId;
    document.getElementById('modalRoleName').textContent = roleName;
    const alertBox = document.getElementById('modalAlert');
    alertBox.classList.add('d-none');
    
    // Clear checkboxes first
    document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
    
    // Reset "Select All" buttons
    document.querySelectorAll('[onclick^="toggleModulePerms"]').forEach(btn => btn.textContent = 'Select All');
    
    // Fetch current permissions for this role
    fetch('process_roles.php?action=get_permissions&role_id=' + roleId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                data.permissions.forEach(permId => {
                    const cb = document.getElementById('perm_' + permId);
                    if (cb) cb.checked = true;
                });
                new bootstrap.Modal(document.getElementById('permissionsModal')).show();
            } else {
                alert('Error loading permissions: ' + data.message);
            }
        });
}

document.getElementById('rolePermissionsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('saveRoleBtn');
    const alertBox = document.getElementById('modalAlert');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Updating...';
    
    const formData = new FormData(this);
    formData.append('action', 'save_permissions');

    fetch('process_roles.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        if (data.success) {
            alertBox.classList.remove('d-none', 'alert-danger');
            alertBox.classList.add('alert-success');
            alertBox.innerHTML = '<i class="fas fa-check-circle me-1"></i> Permissions updated successfully!';
            setTimeout(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('permissionsModal'));
                if(modal) modal.hide();
            }, 1000);
        } else {
            alertBox.classList.remove('d-none', 'alert-success');
            alertBox.classList.add('alert-danger');
            alertBox.innerHTML = '<i class="fas fa-exclamation-circle me-1"></i> ' + data.message;
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alertBox.classList.remove('d-none');
        alertBox.classList.add('alert-danger');
        alertBox.textContent = 'Server error occurred. Please try again.';
    });
});

// Handle New Role Submission
document.getElementById('addRoleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('createRoleBtn');
    const alertBox = document.getElementById('addRoleAlert');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creating...';
    
    const formData = new FormData(this);
    formData.append('action', 'add_role');

    fetch('process_roles.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        if (data.success) {
            alertBox.className = 'alert alert-success border-0 small mb-3';
            alertBox.textContent = data.message;
            alertBox.classList.remove('d-none');
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alertBox.className = 'alert alert-danger border-0 small mb-3';
            alertBox.textContent = data.message;
            alertBox.classList.remove('d-none');
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alertBox.className = 'alert alert-danger border-0 small mb-3';
        alertBox.textContent = 'Server error. Please try again.';
        alertBox.classList.remove('d-none');
    });
});
</script>
