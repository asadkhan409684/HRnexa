<div id="job-requisition-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Job Requisition Review</h1>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-file-contract"></i>
            Requisition List
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Proposed By</th>
                        <th>Department</th>
                        <th>Positions</th>
                        <th>Requisition Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($requisitions_result && $requisitions_result->num_rows > 0): ?>
                        <?php while($req = $requisitions_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($req['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($req['requester_name'] ?? 'HR/Admin'); ?></td>
                            <td><?php echo htmlspecialchars($req['department_name']); ?></td>
                            <td><?php echo $req['positions']; ?></td>
                            <td><?php echo date('d M Y', strtotime($req['created_at'])); ?></td>
                            <td>
                                <?php 
                                    $r_badge = 'bg-secondary';
                                    if ($req['status'] == 'open') $r_badge = 'bg-info';
                                    if ($req['status'] == 'closed') $r_badge = 'bg-success';
                                    if ($req['status'] == 'cancelled') $r_badge = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $r_badge; ?>"><?php echo ucfirst($req['status']); ?></span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#viewReqModal<?php echo $req['id']; ?>"><i class="fas fa-eye"></i> Details</button>
                                    <form method="POST" action="" class="d-inline">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="requisition_id" value="<?php echo $req['id']; ?>">
                                        <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                            <option value="">Update Status</option>
                                            <option value="draft" <?php echo ($req['status'] == 'draft') ? 'selected' : ''; ?>>Draft</option>
                                            <option value="open" <?php echo ($req['status'] == 'open') ? 'selected' : ''; ?>>Open</option>
                                            <option value="closed" <?php echo ($req['status'] == 'closed') ? 'selected' : ''; ?>>Closed</option>
                                            <option value="cancelled" <?php echo ($req['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                        <input type="hidden" name="update_requisition_status" value="1">
                                    </form>
                                </div>

                                <!-- Details Modal -->
                                <div class="modal fade" id="viewReqModal<?php echo $req['id']; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content text-dark">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Requisition Details: <?php echo htmlspecialchars($req['title']); ?></h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="row g-3">
                                                    <div class="col-md-6"><strong>Proposed By:</strong> <?php echo htmlspecialchars($req['requester_name'] ?? 'HR/Admin'); ?></div>
                                                    <div class="col-md-6"><strong>Department:</strong> <?php echo htmlspecialchars($req['department_name']); ?></div>
                                                    <div class="col-md-4"><strong>Positions:</strong> <?php echo $req['positions']; ?></div>
                                                    <div class="col-md-4"><strong>Experience:</strong> <?php echo htmlspecialchars($req['experience'] ?? 'N/A'); ?></div>
                                                    <div class="col-md-4"><strong>Salary Range:</strong> <?php echo htmlspecialchars($req['salary_range'] ?? 'N/A'); ?></div>
                                                    <div class="col-md-6"><strong>Joining Date:</strong> <?php echo !empty($req['expected_joining_date']) ? date('d M Y', strtotime($req['expected_joining_date'])) : 'Not Specified'; ?></div>
                                                    <div class="col-md-6"><strong>Created At:</strong> <?php echo date('d M Y h:i A', strtotime($req['created_at'])); ?></div>
                                                    <div class="col-12"><hr></div>
                                                    <div class="col-12"><strong>Required Skills:</strong><br><?php echo nl2br(htmlspecialchars($req['requirements'])); ?></div>
                                                    <div class="col-12"><strong>Job Description:</strong><br><?php echo nl2br(htmlspecialchars($req['description'])); ?></div>
                                                    <div class="col-12"><strong>Reason for Hiring:</strong><br><?php echo nl2br(htmlspecialchars($req['hiring_reason'] ?? 'N/A')); ?></div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No requisitions found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Requisition Modal (For HR) -->
<div class="modal fade" id="addRequisitionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content text-dark" method="POST" action="dashboard.php#job-requisition">
            <div class="modal-header">
                <h5 class="modal-title">Create Job Requisition</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-start">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Job Title</label>
                        <input type="text" name="title" class="form-control" required placeholder="e.g., Senior PHP Developer">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Number of Positions</label>
                        <input type="number" name="positions" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-select" required>
                            <option value="">Select Department</option>
                            <?php 
                                $depts_res = $conn->query("SELECT id, name FROM departments WHERE is_active = 1 ORDER BY name ASC");
                                while($d = $depts_res->fetch_assoc()) {
                                    echo "<option value='{$d['id']}'>".htmlspecialchars($d['name'])."</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Expected Joining Date</label>
                        <input type="date" name="expected_joining_date" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Experience</label>
                        <input type="text" name="experience" class="form-control" placeholder="e.g., 2-3 years">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Salary Range</label>
                        <input type="text" name="salary_range" class="form-control" placeholder="e.g., 40k - 60k">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Required Skills</label>
                        <textarea name="requirements" class="form-control" rows="2" placeholder="e.g., PHP, MySQL, Laravel" required></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Job Description</label>
                        <textarea name="description" class="form-control" rows="3" placeholder="Core responsibilities..."></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Reason for Hiring</label>
                        <textarea name="hiring_reason" class="form-control" rows="2" placeholder="Why do we need this position?"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" name="add_requisition" class="btn btn-primary">Submit Requisition</button>
            </div>
        </form>
    </div>
</div>
