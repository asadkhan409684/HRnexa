<?php
// Fetch Goals
$goals_query = "SELECT eg.*, k.name as kpi_name 
               FROM employee_goals eg 
               JOIN kpis k ON eg.kpi_id = k.id 
               WHERE eg.employee_id = $employee_id 
               ORDER BY eg.assigned_at DESC";
$goals_result = $conn->query($goals_query);
?>
<!-- Goals Section -->
<div id="goals-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>My Goals</h1>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-bullseye"></i>
            Performance Goals
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Goal</th>
                        <th>Target</th>
                        <th>Progress</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($goals_result && $goals_result->num_rows > 0): ?>
                        <?php while($goal = $goals_result->fetch_assoc()): 
                            $progress = ($goal['target_value'] > 0) ? ($goal['achievement_value'] / $goal['target_value']) * 100 : 0;
                            $bar_class = ($goal['status'] == 'completed') ? 'bg-success' : 'bg-info';
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($goal['kpi_name']); ?></td>
                                <td><?php echo $goal['target_value']; ?></td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar <?php echo $bar_class; ?>" style="width: <?php echo $progress; ?>%"></div>
                                    </div>
                                </td>
                                <td><span class="badge <?php echo $bar_class; ?>"><?php echo ucfirst(str_replace('_', ' ', $goal['status'])); ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" class="text-center text-muted">No goals assigned yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
