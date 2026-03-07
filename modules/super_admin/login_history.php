<?php
// Fetch login/logout history including employees and failed attempts
$login_query = "SELECT a.*, 
                       u.username as user_name, u.email as user_email,
                       e.first_name, e.last_name, e.email as emp_email
                FROM audit_logs a 
                LEFT JOIN users u ON a.user_id = u.id AND a.table_name = 'users'
                LEFT JOIN employees e ON a.user_id = e.id AND a.table_name = 'employees'
                WHERE a.action IN ('Login', 'Logout', 'Failed Login', 'Account Locked') 
                ORDER BY a.timestamp DESC 
                LIMIT 100";
$login_result = $conn->query($login_query);
?>

<div class="section-card border-0 shadow-sm">
    <div class="section-title d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <div class="p-2 bg-warning-subtle rounded text-warning">
                <i class="fas fa-sign-in-alt fs-4"></i>
            </div>
            <h5 class="mb-0 fw-bold">User Login History</h5>
        </div>
        <div class="text-muted small">Access Monitoring</div>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle border-top">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">User</th>
                    <th>Action</th>
                    <th>Date & Time</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($login_result && $login_result->num_rows > 0): ?>
                    <?php while($log = $login_result->fetch_assoc()): ?>
                        <?php 
                        // Determine display name and email
                        $json = $log['new_values'] ? json_decode($log['new_values'], true) : null;
                        if ($log['user_name']) {
                            $display_name = $log['user_name'];
                            $display_email = $log['user_email'];
                        } elseif ($log['first_name']) {
                            $display_name = $log['first_name'] . ' ' . $log['last_name'];
                            $display_email = $log['emp_email'];
                        } else {
                            $display_name = 'Guest/Unknown';
                            $display_email = isset($json['email']) ? $json['email'] : 'N/A';
                        }
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="user-avatar shadow-sm" style="width: 34px; height: 34px; font-size: 14px; background: <?php echo $log['user_id'] ? '#4e73df' : '#e74a3b'; ?>;">
                                        <?php echo strtoupper(substr($display_name, 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold small"><?php echo htmlspecialchars($display_name ?? ''); ?></div>
                                        <div class="text-muted smaller"><?php echo htmlspecialchars($display_email ?? ''); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php 
                                $bg = 'bg-success';
                                if ($log['action'] == 'Logout') $bg = 'bg-secondary';
                                if ($log['action'] == 'Failed Login') $bg = 'bg-danger';
                                if ($log['action'] == 'Account Locked') $bg = 'bg-dark';
                                ?>
                                <span class="badge <?php echo $bg; ?> px-2 py-1 rounded-pill small">
                                    <?php echo htmlspecialchars($log['action'] ?? ''); ?>
                                </span>
                            </td>
                            <td>
                                <div class="small fw-600"><?php echo date('M d, Y', strtotime($log['timestamp'])); ?></div>
                                <div class="text-muted smaller"><?php echo date('h:i:s A', strtotime($log['timestamp'])); ?></div>
                            </td>
                            <td>
                                <div class="small text-muted" style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title='<?php echo htmlspecialchars($log['new_values'] ?? ''); ?>'>
                                    <?php 
                                    if ($json) {
                                        echo (isset($json['ip']) ? 'IP: '.$json['ip'].' ' : '') . (isset($json['reason']) ? '| '.$json['reason'] : '');
                                    } else {
                                        echo 'Standard session';
                                    }
                                    ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="4" class="text-center py-4">No login history recorded yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
