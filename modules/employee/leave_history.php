<!-- Leave Status Section -->
<div id="leave-status-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Leave Status</h1>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-calendar-check"></i>
            Leave Applications
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Leave Type</th>
                        <th>From Date</th>
                        <th>To Date</th>
                        <th>Days</th>
                        <th>Applied On</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($leave_apps_result && $leave_apps_result->num_rows > 0) {
                        while($app = $leave_apps_result->fetch_assoc()) {
                            $status_class = 'bg-warning';
                            if ($app['status'] == 'approved') $status_class = 'bg-success';
                            elseif ($app['status'] == 'referred') $status_class = 'bg-info';
                            elseif ($app['status'] == 'rejected') $status_class = 'bg-danger';
                            elseif ($app['status'] == 'cancelled') $status_class = 'bg-secondary';
                            
                            echo "<tr>";
                            echo "<td>".htmlspecialchars($app['leave_type_name'])."</td>";
                            echo "<td>".date('d-M-Y', strtotime($app['start_date']))."</td>";
                            echo "<td>".date('d-M-Y', strtotime($app['end_date']))."</td>";
                            echo "<td>".$app['total_days']."</td>";
                            echo "<td>".date('d-M-Y', strtotime($app['applied_at']))."</td>";
                            echo "<td><span class='badge $status_class'>".ucfirst($app['status'])."</span></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' class='text-center text-muted'>No leave applications found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
