<div id="job-posting-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Job Postings</h1>
        <button class="btn btn-primary" onclick="switchSection('job-posting')"><i class="fas fa-plus"></i> New Job Posting</button>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-bullhorn"></i>
            Active Job Postings
            <small class="text-muted ms-2">(Based on Open Requisitions)</small>
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Department</th>
                        <th>Posted Date</th>
                        <th>Applicants</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($postings_result && $postings_result->num_rows > 0): ?>
                        <?php while($post = $postings_result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($post['title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($post['department_name']); ?></td>
                            <td><?php echo date('d M Y', strtotime($post['created_at'])); ?></td>
                            <td>
                                <span class="badge bg-primary"><?php echo $post['applicant_count']; ?> Applicants</span>
                            </td>
                            <td><span class="badge bg-success">Active</span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" class="text-center">No active job postings. Open a requisition to start.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
