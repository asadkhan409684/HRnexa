<?php
// Fetch reviews for the logged-in employee
$emp_id = $_SESSION['employee_id'] ?? 0;
$my_reviews_res = null;
if ($emp_id > 0) {
    $my_reviews_query = "SELECT pr.*, ac.name as cycle_name 
                         FROM performance_reviews pr
                         JOIN appraisal_cycles ac ON pr.appraisal_cycle_id = ac.id
                         WHERE pr.employee_id = $emp_id
                         ORDER BY ac.created_at DESC";
    $my_reviews_res = $conn->query($my_reviews_query);
}
?>

<!-- Appraisal History Section -->
<div id="appraisals-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Appraisal History</h1>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-star"></i>
            Performance Reviews
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Manager Rating</th>
                        <th>Your Rating</th>
                        <th>Overall Score</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($my_reviews_res && $my_reviews_res->num_rows > 0): ?>
                        <?php while($rev = $my_reviews_res->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rev['cycle_name']); ?></td>
                            <td><?php echo ($rev['manager_rating'] ?? 'N/A'); ?></td>
                            <td><?php echo ($rev['self_rating'] ?? 'N/A'); ?></td>
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
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No appraisal history found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
