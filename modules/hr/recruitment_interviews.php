<div id="interviews-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Interview Management</h1>
        <button class="btn btn-primary"><i class="fas fa-plus"></i> Schedule Interview</button>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-video"></i>
            Scheduled Interviews
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Candidate</th>
                        <th>Position</th>
                        <th>Date & Time</th>
                        <th>Interviewer</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($interviews_result && $interviews_result->num_rows > 0): ?>
                        <?php while($int = $interviews_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($int['candidate_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($int['job_title']); ?></td>
                            <td><?php echo date('d M Y, h:i A', strtotime($int['scheduled_at'])); ?></td>
                            <td><?php echo htmlspecialchars($int['interviewer_name'] ?? 'Not Assigned'); ?></td>
                            <td>
                                <?php 
                                    $i_badge = 'bg-info';
                                    if ($int['status'] == 'completed') $i_badge = 'bg-success';
                                    if ($int['status'] == 'cancelled') $i_badge = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $i_badge; ?>"><?php echo ucfirst($int['status']); ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No interviews scheduled.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
