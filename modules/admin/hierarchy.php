            <!-- Reporting Hierarchy Section -->
            <div id="reporting-hierarchy-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <div>
                        <h1>Reporting Hierarchy</h1>
                        <p style="color: #999; margin-top: 5px;">Manage organization structure and reporting lines</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addReportingModal">
                        <i class="fas fa-plus"></i> Add Reporting Link
                    </button>
                </div>

                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-sitemap"></i>
                        Reporting Structure
                    </div>
                    <div class="data-table">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Designation</th>
                                    <th>Reports To</th>
                                    <th>Status</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($hierarchy_result && $hierarchy_result->num_rows > 0): ?>
                                    <?php while($h = $hierarchy_result->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div style="font-weight: 600;"><?php echo htmlspecialchars(($h['emp_fname'] ?? '') . ' ' . ($h['emp_lname'] ?? '')); ?></div>
                                            <div style="font-size: 12px; color: #999;"><?php echo htmlspecialchars($h['emp_code'] ?? ''); ?></div>
                                        </td>
                                        <td><span class="badge bg-light text-dark"><?php echo htmlspecialchars($h['emp_desig'] ?? 'N/A'); ?></span></td>
                                        <td>
                                            <?php if ($h['manager_id']): ?>
                                                <div style="font-weight: 500;"><?php echo htmlspecialchars(($h['mgr_fname'] ?? '') . ' ' . ($h['mgr_lname'] ?? '')); ?></div>
                                                <div style="font-size: 11px; color: #999;"><?php echo htmlspecialchars($h['mgr_code'] ?? ''); ?></div>
                                            <?php else: ?>
                                                <span class="text-muted">No Manager</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-success">Active</span></td>
                                        <td class="text-end">
                                            <button class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                             <a href="dashboard.php?remove_reporting=<?php echo $h['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-outline-danger" title="Remove" onclick="return confirm('Are you sure you want to remove this reporting relationship?')">
                                                 <i class="fas fa-trash"></i>
                                             </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div style="color: #ccc; font-size: 40px; margin-bottom: 10px;"><i class="fas fa-sitemap"></i></div>
                                            <p class="text-muted">No reporting lines established yet.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div><!-- End of #reporting-hierarchy-section -->

            <!-- Add Reporting Modal -->
            <div class="modal fade" id="addReportingModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Reporting Link</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form action="dashboard.php" method="POST">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Select Employee</label>
                                    <select class="form-select" name="employee_id" required>
                                        <option value="">-- Select Employee --</option>
                                        <?php foreach ($employees_list as $emp): ?>
                                            <option value="<?php echo $emp['id']; ?>">
                                                <?php echo htmlspecialchars(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '') . ' (' . ($emp['employee_code'] ?? '') . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">The employee who will be reporting.</div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Select Manager / Team Leader</label>
                                    <select class="form-select" name="manager_id" required>
                                        <option value="">-- Select Manager --</option>
                                        <?php foreach ($employees_list as $emp): ?>
                                            <option value="<?php echo $emp['id']; ?>">
                                                <?php echo htmlspecialchars(($emp['first_name'] ?? '') . ' ' . ($emp['last_name'] ?? '') . ' (' . ($emp['employee_code'] ?? '') . ')'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <div class="form-text">The person this employee will report to.</div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="assign_reporting" class="btn btn-primary">Establish Link</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
