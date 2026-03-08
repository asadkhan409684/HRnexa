            <!-- Designations Section -->
            <div id="designations-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Designation Management</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDesignationModal">
                        <i class="fas fa-plus"></i> Add Designation
                    </button>
                </div>
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-briefcase"></i>
                        Designation List
                    </div>
                    <div class="data-table">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Designation</th>
                                    <th>Department</th>
                                    <th>Employees</th>
                                    <th>Level</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($desigs_result && $desigs_result->num_rows > 0): ?>
                                    <?php while($desig = $desigs_result->fetch_assoc()): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($desig['name'] ?? ''); ?></strong></td>
                                        <td><?php echo htmlspecialchars($desig['department_name'] ?? ''); ?></td>
                                        <td><?php echo $desig['emp_count']; ?></td>
                                        <td>
                                            <?php 
                                                $level = $desig['level'] ?? 'Junior';
                                                $level_class = 'bg-info';
                                                if (stripos($level, 'Executive') !== false) $level_class = 'bg-danger';
                                                elseif (stripos($level, 'Senior') !== false) $level_class = 'bg-warning';
                                            ?>
                                            <span class="badge <?php echo $level_class; ?>"><?php echo htmlspecialchars($level ?? ''); ?></span>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-desig-btn" 
                                                    data-id="<?php echo $desig['id']; ?>" 
                                                    data-name="<?php echo htmlspecialchars($desig['name'] ?? ''); ?>" 
                                                    data-desc="<?php echo htmlspecialchars($desig['description'] ?? ''); ?>" 
                                                    data-level="<?php echo $desig['level'] ?? ''; ?>" 
                                                    data-dept="<?php echo $desig['department_id'] ?? ''; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger delete-desig-btn" data-id="<?php echo $desig['id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center">No designations found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

        </div><!-- End of #designations-section -->

        <!-- Add Designation Modal -->
        <div class="modal fade" id="addDesignationModal" tabindex="-1" aria-labelledby="addDesignationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="dashboard.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addDesignationModalLabel">Add New Designation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="desig_name" class="form-label">Designation Name</label>
                                <input type="text" class="form-control" id="desig_name" name="desig_name" required placeholder="e.g. Senior Developer">
                            </div>
                            <div class="mb-3">
                                <label for="desig_description" class="form-label">Description</label>
                                <textarea class="form-control" id="desig_description" name="desig_description" rows="2" placeholder="Responsibilities and role summary"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="desig_level" class="form-label">Level</label>
                                <select class="form-select" id="desig_level" name="desig_level">
                                    <option value="Junior">Junior</option>
                                    <option value="Mid-Level">Mid-Level</option>
                                    <option value="Senior">Senior</option>
                                    <option value="Lead">Lead</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="desig_dept" class="form-label">Department</label>
                                <select class="form-select" id="desig_dept" name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php 
                                    // Reset the pointer for $depts_result as it might have been used elsewhere
                                    if ($depts_result && $depts_result->num_rows > 0):
                                        $depts_result->data_seek(0);
                                        while($dept = $depts_result->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name'] ?? ''); ?></option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="add_designation" class="btn btn-primary">Save Designation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Designation Modal -->
        <div class="modal fade" id="editDesignationModal" tabindex="-1" aria-labelledby="editDesignationModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form action="dashboard.php" method="POST">
                        <div class="modal-header">
                            <h5 class="modal-title" id="editDesignationModalLabel">Edit Designation</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="desig_id" id="edit_desig_id">
                            <div class="mb-3">
                                <label for="edit_desig_name" class="form-label">Designation Name</label>
                                <input type="text" class="form-control" id="edit_desig_name" name="desig_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="edit_desig_description" class="form-label">Description</label>
                                <textarea class="form-control" id="edit_desig_description" name="desig_description" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="edit_desig_level" class="form-label">Level</label>
                                <select class="form-select" id="edit_desig_level" name="desig_level">
                                    <option value="Junior">Junior</option>
                                    <option value="Mid-Level">Mid-Level</option>
                                    <option value="Senior">Senior</option>
                                    <option value="Lead">Lead</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="edit_desig_dept" class="form-label">Department</label>
                                <select class="form-select" id="edit_desig_dept" name="department_id" required>
                                    <option value="">Select Department</option>
                                    <?php 
                                    if ($depts_result && $depts_result->num_rows > 0):
                                        $depts_result->data_seek(0);
                                        while($dept = $depts_result->fetch_assoc()): 
                                    ?>
                                        <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name'] ?? ''); ?></option>
                                    <?php endwhile; endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="edit_designation" class="btn btn-primary">Update Designation</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Populate Edit Designation Modal
            document.querySelectorAll('.edit-desig-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const data = this.dataset;
                    document.getElementById('edit_desig_id').value = data.id;
                    document.getElementById('edit_desig_name').value = data.name;
                    document.getElementById('edit_desig_description').value = data.desc;
                    document.getElementById('edit_desig_level').value = data.level;
                    document.getElementById('edit_desig_dept').value = data.dept;
                    
                    new bootstrap.Modal(document.getElementById('editDesignationModal')).show();
                });
            });

            // Delete Designation Confirmation
            document.querySelectorAll('.delete-desig-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const desigId = this.dataset.id;
                    if (confirm('Are you sure you want to delete this designation?')) {
                        window.location.href = 'dashboard.php?delete_desig=' + desigId + '&csrf_token=<?php echo $_SESSION['csrf_token']; ?>';
                    }
                });
            });
        });
        </script>
