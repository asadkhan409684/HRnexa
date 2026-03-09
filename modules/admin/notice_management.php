<?php
// modules/admin/notice_management.php
require_once('../../app/Config/database.php');

$notice_query = "SELECT * FROM notices ORDER BY created_at DESC";
$notice_result = $conn->query($notice_query);
?>

<div class="row mb-4 align-items-center">
    <div class="col">
        <h4 class="fw-bold mb-0">Notice Board Management</h4>
        <p class="text-muted small mb-0">Manage company notices and announcements</p>
    </div>
    <div class="col-auto">
        <button class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#noticeModal">
            <i class="fas fa-plus me-2"></i> Add New Notice
        </button>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Notice Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Date Posted</th>
                    <th class="text-end pe-4">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($notice_result && $notice_result->num_rows > 0): ?>
                    <?php while($notice = $notice_result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark"><?php echo htmlspecialchars($notice['title']); ?></div>
                                <div class="text-muted small"><?php echo substr(htmlspecialchars($notice['content']), 0, 50) . '...'; ?></div>
                            </td>
                            <td>
                                <?php 
                                $badge_class = 'bg-info';
                                if($notice['category'] == 'important') $badge_class = 'bg-danger';
                                if($notice['category'] == 'holiday') $badge_class = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?php echo $badge_class; ?> rounded-pill px-3">
                                    <?php echo ucfirst($notice['category']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-opacity-10 <?php echo $notice['status'] == 'active' ? 'bg-success text-success' : 'bg-secondary text-secondary'; ?> rounded-pill px-3">
                                    <?php echo ucfirst($notice['status']); ?>
                                </span>
                            </td>
                            <td class="text-muted small"><?php echo date('M d, Y', strtotime($notice['created_at'])); ?></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary rounded-circle me-1" data-bs-toggle="modal" data-bs-target="#noticeModal" data-notice-id="<?php echo $notice['id']; ?>" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger rounded-circle" onclick="deleteNotice(<?php echo $notice['id']; ?>)" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No notices found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
