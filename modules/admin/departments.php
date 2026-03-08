            <!-- Departments Section -->
            <div id="departments-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Department Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDepartmentModal">
                        <i class="fas fa-plus"></i> Add Department
                    </button>
                </div>
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-project-diagram"></i>
                        Department List
                    </div>
                    <div class="data-table">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Department Name</th>
                                    <th>Head</th>
                                    <th>Employees</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($depts_result && $depts_result->num_rows > 0): ?>
                                    <?php while($dept = $depts_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($dept['name'] ?? ''); ?></strong></td>
                                        <td><?php echo htmlspecialchars($dept['head_name'] ?? 'Not Set'); ?></td>
                                        <td><?php echo $dept['emp_count']; ?></td>
                                        <td>
                                            <span class="badge <?php echo $dept['is_active'] ? 'bg-success' : 'bg-secondary'; ?>">
                                                <?php echo $dept['is_active'] ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-dept-btn" 
                                                    title="Edit"
                                                    data-id="<?php echo $dept['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($dept['name'] ?? ''); ?>"
                                                    data-description="<?php echo htmlspecialchars($dept['description'] ?? ''); ?>"
                                                    data-status="<?php echo $dept['is_active']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-dept-btn" 
                                                    title="Delete"
                                                    data-id="<?php echo $dept['id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($dept['name'] ?? ''); ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center">No departments found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
            </div>

        </div><!-- End of #departments-section -->

        <!-- Add Department Modal -->
        <div class="modal fade" id="addDepartmentModal" tabindex="-1" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="dashboard.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addDepartmentModalLabel">Add New Department</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="dept_name" class="form-label">Department Name</label>
                                <input type="text" class="form-control" id="dept_name" name="dept_name" required placeholder="e.g. Marketing">
                            </div>
                            <div class="mb-3">
                                <label for="dept_description" class="form-label">Description</label>
                                <textarea class="form-control" id="dept_description" name="dept_description" rows="3" placeholder="Enter department description"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="dept_status" class="form-label">Status</label>
                                <select class="form-select" id="dept_status" name="dept_status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="add_department" class="btn btn-primary">Save Department</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Department Modal -->
        <div class="modal fade" id="editDepartmentModal" tabindex="-1" aria-labelledby="editDepartmentModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="dashboard.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="dept_id" id="edit_dept_id">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editDepartmentModalLabel">Edit Department</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="edit_dept_name" class="form-label">Department Name</label>
                                <input type="text" class="form-control" id="edit_dept_name" name="dept_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_dept_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_dept_description" name="dept_description" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="edit_dept_status" class="form-label">Status</label>
                                <select class="form-select" id="edit_dept_status" name="dept_status">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="edit_department" class="btn btn-primary">Update Department</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit Button Click
            const editBtns = document.querySelectorAll('.edit-dept-btn');
            const editModalElement = document.getElementById('editDepartmentModal');
            const editModal = new bootstrap.Modal(editModalElement);
            
            editBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    document.getElementById('edit_dept_id').value = this.getAttribute('data-id');
                    document.getElementById('edit_dept_name').value = this.getAttribute('data-name');
                    document.getElementById('edit_dept_description').value = this.getAttribute('data-description');
                    document.getElementById('edit_dept_status').value = this.getAttribute('data-status');
                    editModal.show();
                });
            });

            // Delete Button Click
            const deleteBtns = document.querySelectorAll('.delete-dept-btn');
            deleteBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    if (confirm(`Are you sure you want to delete the department "${name}"? This action cannot be undone.`)) {
                        window.location.href = `dashboard.php?delete_dept=${id}&csrf_token=<?php echo $_SESSION['csrf_token']; ?>`;
                    }
                });
            });
        });
        </script>
<?php
// Correcting unclosed div from line 2
?>
