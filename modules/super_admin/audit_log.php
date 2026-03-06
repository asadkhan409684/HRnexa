<?php
// Fetch all audit logs with user details
$audit_query = "SELECT a.*, u.username, u.email 
                FROM audit_logs a 
                LEFT JOIN users u ON a.user_id = u.id 
                ORDER BY a.timestamp DESC 
                LIMIT 100";
$audit_result = $conn->query($audit_query);
?>

<div class="section-card border-0 shadow-sm">
    <div class="section-title d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <div class="p-2 bg-info-subtle rounded text-info">
                <i class="fas fa-history fs-4"></i>
            </div>
            <h5 class="mb-0 fw-bold">System Audit Trail</h5>
        </div>
        <div class="text-muted small">Showing last 100 activities</div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle border-top">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Module/Table</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($audit_result && $audit_result->num_rows > 0): ?>
                    <?php while($log = $audit_result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-600 text-dark" style="font-size: 0.85rem;">
                                    <?php echo date('M d, Y', strtotime($log['timestamp'])); ?>
                                </div>
                                <div class="text-muted smaller"><?php echo date('h:i:s A', strtotime($log['timestamp'])); ?></div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar" style="width: 28px; height: 28px; font-size: 12px; background: #6c757d;">
                                        <?php echo strtoupper(substr($log['username'] ?? 'S', 0, 1)); ?>
                                    </div>
                                    <span class="small fw-bold"><?php echo htmlspecialchars($log['username'] ?? 'System/Deleted'); ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill small">
                                    <?php echo htmlspecialchars($log['action'] ?? ''); ?>
                                </span>
                            </td>
                            <td>
                                <code class="small text-secondary"><?php echo htmlspecialchars($log['table_name'] ?? 'N/A'); ?></code>
                            </td>
                            <td>
                                <div class="small text-muted" style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title='<?php echo htmlspecialchars($log['new_values'] ?? $log['old_values'] ?? ''); ?>'>
                                    <?php 
                                    $val = $log['new_values'] ?? $log['old_values'] ?? 'N/A';
                                    $json = json_decode($val, true);
                                    if (json_last_error() === JSON_ERROR_NONE) {
                                        echo isset($json['message']) ? htmlspecialchars($json['message'] ?? '') : htmlspecialchars(substr($val ?? '', 0, 50)) . '...';
                                    } else {
                                        echo htmlspecialchars(substr($val ?? '', 0, 50));
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="5" class="text-center py-4">No audit logs found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>