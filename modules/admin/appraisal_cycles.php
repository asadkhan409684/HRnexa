<?php
// Fetch all appraisal cycles
$cycles_res = $conn->query("SELECT ac.*, u.username as creator_name 
                            FROM appraisal_cycles ac
                            JOIN users u ON ac.created_by = u.id 
                            ORDER BY ac.created_at DESC");

if (!$cycles_res) {
    // Log error or handle gracefully
    error_log("Appraisal Cycles Query Failed: " . $conn->error);
}
?>

<div id="appraisal-cycles-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Appraisal Cycle Management</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCycleModal">
            <i class="fas fa-plus"></i> Create New Cycle
        </button>
    </div>

    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-sync-alt"></i>
            Appraisal Cycles
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Cycle Name</th>
                        <th>Period</th>
                        <th>Created By</th>
                        <th>Status</th>
                        <th>Reviews</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($cycles_res && $cycles_res->num_rows > 0): ?>
                        <?php while($cycle = $cycles_res->fetch_assoc()): ?>
                        <?php 
                            // Count total and completed reviews for this cycle
                            $cid = $cycle['id'];
                            $review_stats = $conn->query("SELECT COUNT(*) as total, SUM(CASE WHEN status='finalized' THEN 1 ELSE 0 END) as completed FROM performance_reviews WHERE appraisal_cycle_id = $cid")->fetch_assoc();
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($cycle['name'] ?? ''); ?></strong></td>
                            <td>
                                <small class="text-muted">
                                    <?php echo date('M d, Y', strtotime($cycle['start_date'])); ?> - <?php echo date('M d, Y', strtotime($cycle['end_date'])); ?>
                                </small>
                            </td>
                            <td><?php echo htmlspecialchars($cycle['creator_name'] ?? ''); ?></td>
                            <td>
                                <?php
                                $badge_class = 'bg-secondary';
                                if($cycle['status'] == 'active') $badge_class = 'bg-success';
                                if($cycle['status'] == 'completed') $badge_class = 'bg-primary';
                                if($cycle['status'] == 'cancelled') $badge_class = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($cycle['status']); ?></span>
                            </td>
                            <td>
                                <?php if($cycle['status'] != 'draft'): ?>
                                    <div class="progress" style="height: 10px; width: 100px;">
                                        <?php 
                                        $percent = ($review_stats['total'] > 0) ? ($review_stats['completed'] / $review_stats['total']) * 100 : 0;
                                        ?>
                                        <div class="progress-bar" role="progressbar" style="width: <?php echo $percent; ?>%;"></div>
                                    </div>
                                    <small><?php echo $review_stats['completed']; ?> / <?php echo $review_stats['total']; ?></small>
                                <?php else: ?>
                                    <span class="text-muted">Not launched</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($cycle['status'] == 'draft'): ?>
                                    <form action="dashboard.php" method="POST" style="display:inline;">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <input type="hidden" name="cycle_id" value="<?php echo $cycle['id']; ?>">
                                        <button type="submit" name="launch_cycle" class="btn btn-sm btn-success" onclick="return confirm('Launch this cycle? This will create review records for all active employees.')">
                                            <i class="fas fa-rocket"></i> Launch
                                        </button>
                                    </form>
                                <?php endif; ?>
                                
                                <button class="btn btn-sm btn-outline-primary edit-cycle-btn" 
                                        data-id="<?php echo $cycle['id']; ?>"
                                        data-name="<?php echo htmlspecialchars($cycle['name'] ?? ''); ?>"
                                        data-start="<?php echo $cycle['start_date']; ?>"
                                        data-end="<?php echo $cycle['end_date']; ?>"
                                        data-status="<?php echo $cycle['status']; ?>">
                                    <i class="fas fa-edit"></i>
                                </button>
                                
                                <?php if($cycle['status'] == 'draft' || $cycle['status'] == 'cancelled'): ?>
                                    <button class="btn btn-sm btn-outline-danger delete-cycle-btn" data-id="<?php echo $cycle['id']; ?>">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center">No appraisal cycles found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Cycle Modal -->
<div class="modal fade" id="addCycleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="dashboard.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Create Appraisal Cycle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Cycle Name</label>
                        <input type="text" class="form-control" name="cycle_name" required placeholder="e.g. Q1 2026 Annual Performance Review">
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" required>
                        </div>
                        <div class="col">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" required>
                        </div>
                    </div>
                    <div class="form-text text-muted">A new cycle is created as 'Draft'. You must 'Launch' it to start the review process.</div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_appraisal_cycle" class="btn btn-primary">Save Cycle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Cycle Modal -->
<div class="modal fade" id="editCycleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="dashboard.php" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Appraisal Cycle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="cycle_id" id="edit_cycle_id">
                    <div class="mb-3">
                        <label class="form-label">Cycle Name</label>
                        <input type="text" class="form-control" name="cycle_name" id="edit_cycle_name" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date" id="edit_cycle_start" required>
                        </div>
                        <div class="col">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date" id="edit_cycle_end" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select class="form-select" name="status" id="edit_cycle_status">
                            <option value="draft">Draft</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="edit_appraisal_cycle" class="btn btn-primary">Update Cycle</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Populate Edit Cycle Modal
    document.querySelectorAll('.edit-cycle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const data = this.dataset;
            document.getElementById('edit_cycle_id').value = data.id;
            document.getElementById('edit_cycle_name').value = data.name;
            document.getElementById('edit_cycle_start').value = data.start;
            document.getElementById('edit_cycle_end').value = data.end;
            document.getElementById('edit_cycle_status').value = data.status;
            
            new bootstrap.Modal(document.getElementById('editCycleModal')).show();
        });
    });

    // Delete Cycle Confirmation
    document.querySelectorAll('.delete-cycle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            if (confirm('Are you sure you want to delete this appraisal cycle?')) {
                window.location.href = 'dashboard.php?delete_appraisal_cycle=' + id + '&csrf_token=<?php echo $_SESSION['csrf_token']; ?>';
            }
        });
    });
});
</script>
