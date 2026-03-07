<div class="section-card">
    <div class="section-title">
        <i class="fas fa-history"></i> Recent Activities
    </div>
    <div class="activity-table table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Action</th>
                    <th>Details</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($recent_activities) > 0): ?>
                    <?php foreach ($recent_activities as $activity): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="user-avatar" style="width: 30px; height: 30px; font-size: 12px;">
                                        <?php echo strtoupper(substr($activity['username'] ?? '', 0, 2)); ?>
                                    </div>
                                    <span><?php echo htmlspecialchars($activity['username'] ?? ''); ?></span>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $action_lower = strtolower($activity['action']);
                                $bg_class = 'bg-info'; // Default
                                
                                if (str_contains($action_lower, 'add') || str_contains($action_lower, 'create') || str_contains($action_lower, 'login')) {
                                    $bg_class = 'bg-success';
                                } elseif (str_contains($action_lower, 'delete') || str_contains($action_lower, 'remove') || str_contains($action_lower, 'lock')) {
                                    $bg_class = 'bg-danger';
                                } elseif (str_contains($action_lower, 'update') || str_contains($action_lower, 'edit') || str_contains($action_lower, 'change')) {
                                    $bg_class = 'bg-primary';
                                } elseif (str_contains($action_lower, 'logout') || str_contains($action_lower, 'restore')) {
                                    $bg_class = 'bg-secondary';
                                }
                                ?>
                                <span class="badge-custom <?php echo $bg_class; ?> text-white"><?php echo htmlspecialchars($activity['action'] ?? ''); ?></span>
                            </td>
                            <td>
                                <?php 
                                $val = $activity['new_values'] ?? $activity['details'] ?? 'N/A';
                                $json = json_decode($val, true);
                                if (json_last_error() === JSON_ERROR_NONE && isset($json['message'])) {
                                    echo htmlspecialchars($json['message'] ?? '');
                                } elseif (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
                                    echo htmlspecialchars(json_encode($json));
                                } else {
                                    echo htmlspecialchars($val ?? '');
                                }
                                ?>
                            </td>
                            <td class="text-muted"><?php echo time_ago($activity['timestamp']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center text-muted">No recent activities found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
