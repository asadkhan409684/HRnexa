<?php
// This file will be included in the Team Leader dashboard
// It assumes $tl_id is defined in the parent dashboard.php

// Fetch Job Requisitions for this Team Leader
$my_requisitions_query = "SELECT jr.*, d.name as department_name 
                         FROM job_requisitions jr
                         JOIN departments d ON jr.department_id = d.id
                         WHERE jr.created_by = $tl_id
                         ORDER BY jr.created_at DESC";
$my_requisitions_result = $conn->query($my_requisitions_query);

// Handle Job Requisition Submission (Logic moved to dashboard.php for consistency, 
// but we define the form here)
?>
<div id="job-requisition-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Job Requisition</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRequisitionModal"><i class="fas fa-plus"></i> New Requisition</button>
    </div>
    
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-file-contract"></i>
            My Job Requisitions
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Department</th>
                        <th>Positions</th>
                        <th>Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($my_requisitions_result && $my_requisitions_result->num_rows > 0): ?>
                        <?php while($req = $my_requisitions_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($req['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($req['department_name']); ?></td>
                            <td><?php echo $req['positions']; ?></td>
                            <td><?php echo date('d M Y', strtotime($req['created_at'])); ?></td>
                            <td>
                                <?php 
                                    $r_badge = 'bg-secondary';
                                    if ($req['status'] == 'open') $r_badge = 'bg-info';
                                    if ($req['status'] == 'closed') $r_badge = 'bg-success';
                                    if ($req['status'] == 'cancelled') $r_badge = 'bg-danger';
                                    if ($req['status'] == 'draft') $r_badge = 'bg-warning';
                                ?>
                                <span class="badge <?php echo $r_badge; ?>"><?php echo ucfirst($req['status']); ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">You haven't submitted any job requisitions yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Requisition Modal -->
<div class="modal fade" id="addRequisitionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form class="modal-content" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="modal-header">
                <h5 class="modal-title">New Job Requisition</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
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
                            <?php 
                                $depts_res = $conn->query("SELECT id, name FROM departments ORDER BY name ASC");
                                while($d = $depts_res->fetch_assoc()) {
                                    $selected = ($d['id'] == $emp_data['department_id']) ? 'selected' : '';
                                    echo "<option value='{$d['id']}' $selected>".htmlspecialchars($d['name'])."</option>";
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
