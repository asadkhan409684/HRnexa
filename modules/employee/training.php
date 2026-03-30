<?php
// Fetch Training
$training_query = "SELECT et.*, tp.name as program_name, tp.duration 
                  FROM employee_trainings et 
                  JOIN training_programs tp ON et.training_program_id = tp.id 
                  WHERE et.employee_id = $employee_id 
                  ORDER BY et.enrollment_date DESC";
$training_result = $conn->query($training_query);
?>
<!-- Training Section -->
<div id="training-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Training & Development</h1>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-graduation-cap"></i>
            My Training Programs
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Program Name</th>
                        <th>Category</th>
                        <th>Duration</th>
                        <th>Status</th>
                        <th>Certificate</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($training_result && $training_result->num_rows > 0): ?>
                        <?php while($trn = $training_result->fetch_assoc()): 
                            $trn_status_bg = 'bg-info';
                            if ($trn['status'] == 'completed') $trn_status_bg = 'bg-success';
                            elseif ($trn['status'] == 'enrolled') $trn_status_bg = 'bg-warning';
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($trn['program_name']); ?></td>
                                <td>General</td> <!-- Category not in training_programs table based on simple view -->
                                <td><?php echo $trn['duration'] ?? 'N/A'; ?> days</td>
                                <td><span class="badge <?php echo $trn_status_bg; ?>"><?php echo ucfirst($trn['status']); ?></span></td>
                                <td>
                                    <?php if($trn['certificate_path']): ?>
                                        <a href="../<?php echo $trn['certificate_path']; ?>" class="btn btn-sm btn-outline-primary" target="_blank"><i class="fas fa-file-pdf"></i></a>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center text-muted">No training programs enrolled.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
