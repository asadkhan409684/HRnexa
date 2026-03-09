            <!-- Company Policies Section -->
            <div id="company-policies-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Company Policies</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPolicyModal"><i class="fas fa-plus"></i> Add Policy</button>
                </div>
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-file-contract"></i>
                        Policy List
                    </div>
                    <div class="data-table">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Policy Name</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $policies_res = $conn->query("SELECT * FROM system_policies WHERE category = 'Company' ORDER BY title ASC");
                                if ($policies_res && $policies_res->num_rows > 0):
                                    while($p = $policies_res->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($p['title'] ?? ''); ?></strong></td>
                                    <td><span class="badge <?php echo $p['is_active'] ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $p['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                                    <td><?php echo date('M j, Y', strtotime($p['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary view-policy-btn" 
                                                data-title="<?php echo htmlspecialchars($p['title'] ?? ''); ?>" 
                                                data-description="<?php echo htmlspecialchars($p['description'] ?? ''); ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="4" class="text-center">No company policies found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- End of #company-policies-section -->

        <!-- Add Policy Modal -->
        <div class="modal fade" id="addPolicyModal" tabindex="-1" aria-labelledby="addPolicyModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="dashboard.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="modal-header">
                            <h5 class="modal-title" id="addPolicyModalLabel">Add New Policy</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Policy Title</label>
                                    <input type="text" class="form-control" name="policy_title" required placeholder="e.g. Work From Home Policy">
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Category</label>
                                    <select class="form-select" name="policy_category" required>
                                        <option value="Company">Company</option>
                                        <option value="General">General</option>
                                        <option value="Compliance">Compliance</option>
                                        <option value="HR">HR</option>
                                        <option value="Leave">Leave</option>
                                        <option value="Attendance">Attendance</option>
                                        <option value="Payroll">Payroll</option>
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Policy Description/Content</label>
                                <textarea class="form-control" name="policy_content" rows="6" required placeholder="Enter the full policy details here..."></textarea>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="is_active" id="policyActive" checked value="1">
                                    <label class="form-check-label" for="policyActive">Mark as Active</label>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="add_policy" class="btn btn-primary">Publish Policy</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
