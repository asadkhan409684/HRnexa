<div id="offers-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Offer Letters</h1>
        <button class="btn btn-primary"><i class="fas fa-plus"></i> Generate Offer</button>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-envelope-open-text"></i>
            Generated Offer Letters
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Candidate</th>
                        <th>Position</th>
                        <th>Generated Date</th>
                        <th>Salary</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($offers_result && $offers_result->num_rows > 0): ?>
                        <?php while($off = $offers_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($off['candidate_name']); ?></strong></td>
                            <td><?php echo htmlspecialchars($off['job_title']); ?></td>
                            <td><?php echo date('d M Y', strtotime($off['generated_at'])); ?></td>
                            <td><?php echo number_format($off['salary_offered'], 2); ?></td>
                            <td>
                                <?php 
                                    $o_badge = 'bg-info';
                                    if ($off['status'] == 'accepted') $o_badge = 'bg-success';
                                    if ($off['status'] == 'rejected') $o_badge = 'bg-danger';
                                    if ($off['status'] == 'draft') $o_badge = 'bg-secondary';
                                ?>
                                <span class="badge <?php echo $o_badge; ?>"><?php echo ucfirst($off['status']); ?></span>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No offer letters generated.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
