<?php
// Fetch all performance reviews
$reviews_query = "SELECT pr.*, CONCAT(e.first_name, ' ', e.last_name) as emp_name, ac.name as cycle_name 
                  FROM performance_reviews pr
                  JOIN employees e ON pr.employee_id = e.id
                  JOIN appraisal_cycles ac ON pr.appraisal_cycle_id = ac.id
                  ORDER BY ac.created_at DESC, e.first_name ASC, e.last_name ASC";
$reviews_res = $conn->query($reviews_query);
?>

<div id="performance-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Performance Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addAppraisalModal"><i class="fas fa-plus"></i> New Appraisal</button>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-star"></i>
            KPI & Appraisals
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Appraisal Period</th>
                        <th>Rating (Self/Mgr)</th>
                        <th>Final Rating</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($reviews_res && $reviews_res->num_rows > 0): ?>
                        <?php while($rev = $reviews_res->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($rev['emp_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($rev['cycle_name']); ?></td>
                            <td>
                                <?php echo ($rev['self_rating'] ?? 'N/A'); ?> / <?php echo ($rev['manager_rating'] ?? 'N/A'); ?>
                            </td>
                            <td><strong><?php echo ($rev['final_rating'] ?? 'N/A'); ?></strong></td>
                            <td>
                                <?php
                                $b_class = 'bg-secondary';
                                if($rev['status'] == 'self_completed') $b_class = 'bg-info';
                                if($rev['status'] == 'manager_completed') $b_class = 'bg-warning';
                                if($rev['status'] == 'finalized') $b_class = 'bg-success';
                                ?>
                                <span class="badge <?php echo $b_class; ?>"><?php echo str_replace('_', ' ', ucfirst($rev['status'])); ?></span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" 
                                        onclick="openUpdateModal(<?php echo htmlspecialchars(json_encode($rev)); ?>)" 
                                        title="View/Update"><i class="fas fa-edit"></i></button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No performance reviews found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Appraisal Modal -->
<div class="modal fade" id="addAppraisalModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Initiate New Appraisal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Employee</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="">Select Employee</option>
                            <?php 
                            $status_emp_result->data_seek(0);
                            while($e = $status_emp_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $e['id']; ?>"><?php echo htmlspecialchars($e['first_name'].' '.$e['last_name']); ?> (<?php echo $e['employee_code']; ?>)</option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Appraisal Cycle</label>
                        <select name="cycle_id" class="form-select" required>
                            <option value="">Select Cycle</option>
                            <?php 
                            $cycles_result->data_seek(0);
                            while($c = $cycles_result->fetch_assoc()): 
                            ?>
                                <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['name']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_appraisal" class="btn btn-primary">Initiate</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Appraisal Modal -->
<div class="modal fade" id="updateAppraisalModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="review_id" id="upd_review_id">
                <div class="modal-header">
                    <h5 class="modal-title">Update Performance Appraisal</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label d-block text-muted small">Employee</label>
                            <div id="upd_emp_name" class="fw-bold"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label d-block text-muted small">Cycle</label>
                            <div id="upd_cycle_name" class="fw-bold"></div>
                        </div>
                    </div>
                    <hr>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Self Rating</label>
                            <input type="text" id="upd_self_rating" class="form-control" readonly>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Manager Rating (1-5)</label>
                            <input type="number" name="manager_rating" id="upd_manager_rating" class="form-control" step="0.1" min="1" max="5" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Final Rating (1-5)</label>
                            <input type="number" name="final_rating" id="upd_final_rating" class="form-control" step="0.1" min="1" max="5" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Comments</label>
                        <textarea name="comments" id="upd_comments" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" id="upd_status" class="form-select">
                            <option value="pending">Pending</option>
                            <option value="manager_completed">Manager Completed</option>
                            <option value="finalized">Finalized</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_appraisal" class="btn btn-success">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openUpdateModal(rev) {
    document.getElementById('upd_review_id').value = rev.id;
    document.getElementById('upd_emp_name').innerText = rev.emp_name;
    document.getElementById('upd_cycle_name').innerText = rev.cycle_name;
    document.getElementById('upd_self_rating').value = rev.self_rating || 'N/A';
    document.getElementById('upd_manager_rating').value = rev.manager_rating || '';
    document.getElementById('upd_final_rating').value = rev.final_rating || '';
    document.getElementById('upd_comments').value = rev.comments || '';
    document.getElementById('upd_status').value = rev.status;
    
    var modal = new bootstrap.Modal(document.getElementById('updateAppraisalModal'));
    modal.show();
}
</script>
