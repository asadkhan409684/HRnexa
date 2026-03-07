<?php
// Fetch users with their roles
$users_query = "SELECT u.*, r.name as role_name 
                FROM users u 
                LEFT JOIN roles r ON u.role_id = r.id 
                ORDER BY u.created_at DESC";
$users_result = $conn->query($users_query);
?>

<div class="section-card border-0 shadow-sm">
    <div class="section-title d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <div class="p-2 bg-primary-subtle rounded text-primary">
                <i class="fas fa-users-cog fs-4"></i>
            </div>
            <h5 class="mb-0 fw-bold">User Access Control</h5>
        </div>
        <button class="btn btn-primary shadow-sm px-4 rounded-pill fw-bold" onclick="showAddUserModal()">
            <i class="fas fa-plus-circle me-1"></i> Add New User
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle border-top">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Identity</th>
                    <th>Email Address</th>
                    <th>System Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th class="pe-4 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($users_result && $users_result->num_rows > 0): ?>
                    <?php while($user_row = $users_result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar shadow-sm" style="width: 38px; height: 38px; font-size: 16px; background: linear-gradient(135deg, #6a11cb 0%, #2575fc 100%);">
                                        <?php echo strtoupper(substr($user_row['username'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($user_row['username'] ?? ''); ?></div>
                                        <div class="text-muted small">ID: #<?php echo str_pad($user_row['id'], 4, '0', STR_PAD_LEFT); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="text-muted"><i class="far fa-envelope me-1 small"></i> <?php echo htmlspecialchars($user_row['email'] ?? ''); ?></span>
                            </td>
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info-subtle px-3 py-2 rounded-pill">
                                    <i class="fas fa-user-shield me-1 small"></i> <?php echo htmlspecialchars($user_row['role_name'] ?? 'N/A'); ?>
                                </span>
                            </td>
                            <td>
                                <?php 
                                $status_class = 'bg-success-subtle text-success border-success-subtle';
                                $status_icon = 'fa-check-circle';
                                if ($user_row['status'] == 'inactive') {
                                    $status_class = 'bg-warning-subtle text-warning border-warning-subtle';
                                    $status_icon = 'fa-clock';
                                }
                                if ($user_row['status'] == 'locked') {
                                    $status_class = 'bg-danger-subtle text-danger border-danger-subtle';
                                    $status_icon = 'fa-user-lock';
                                }
                                ?>
                                <span class="badge <?php echo $status_class; ?> border px-3 py-2 rounded-pill">
                                    <i class="fas <?php echo $status_icon; ?> me-1 small"></i> <?php echo ucfirst($user_row['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="text-muted small fw-600"><?php echo date('M d, Y', strtotime($user_row['created_at'])); ?></div>
                                <div class="text-muted smaller" style="font-size: 0.75rem;"><?php echo date('h:i A', strtotime($user_row['created_at'])); ?></div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group shadow-sm rounded">
                                    <button class="btn btn-white btn-sm px-3 border" title="Edit User" onclick="showEditUserModal(<?php echo $user_row['id']; ?>)">
                                        <i class="fas fa-pencil-alt text-primary"></i>
                                    </button>
                                    <button class="btn btn-white btn-sm px-3 border" title="Delete User" onclick="deleteUser(<?php echo $user_row['id']; ?>)">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-5 text-center">
                            <img src="https://illustrations.popsy.co/gray/not-found.svg" alt="No data" style="width: 150px;" class="mb-3 opacity-50">
                            <p class="text-muted fw-semibold">No system users found in the database.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
.bg-primary-subtle { background-color: #cfe2ff; }
.bg-success-subtle { background-color: #d1e7dd; }
.bg-info-subtle { background-color: #cff4fc; }
.bg-warning-subtle { background-color: #fff3cd; }
.bg-danger-subtle { background-color: #f8d7da; }
.text-info { color: #0dcaf0 !important; }
.smaller { font-size: 0.75rem; }
.btn-white { background: white; }
.btn-white:hover { background: #f8f9fa; }
</style>
