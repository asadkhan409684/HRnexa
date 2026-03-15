            <!-- Apply Leave Section -->
            <div id="apply-leave-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Apply Leave</h1>
                </div>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="section-card">
                            <div class="section-title">
                                <i class="fas fa-calendar-plus"></i>
                                Submit Leave Application
                            </div>
                            <form id="leaveApplicationForm">
                                <div class="mb-3">
                                    <label class="form-label">Leave Type</label>
                                    <select class="form-select" name="leave_type_id" required>
                                        <option value="">Select Leave Type</option>
                                        <?php 
                                        foreach($leave_types_data as $lt) {
                                            echo "<option value='".$lt['id']."'>".$lt['name']."</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">From Date</label>
                                        <input type="date" class="form-control" name="from_date" required id="leave_from_date">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">To Date</label>
                                        <input type="date" class="form-control" name="to_date" required id="leave_to_date">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Number of Days</label>
                                    <input type="number" class="form-control" id="total_days_display" placeholder="Auto-calculated" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Reason</label>
                                    <textarea class="form-control" name="reason" rows="3" placeholder="Provide reason for leave" required></textarea>
                                </div>
                                <div id="leaveFormMessage" class="mb-3" style="display: none;"></div>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Application</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="section-card">
                            <div class="section-title">
                                <i class="fas fa-info-circle"></i>
                                Leave Information
                            </div>
                            <div class="list-group list-group-flush">
                                <?php 
                                // Show Core Leaves
                                foreach ($leave_balances['core'] as $balance): 
                                    $remaining = $balance['allocated'] - $balance['used'];
                                    $status_text = ($remaining > 2) ? 'Available' : (($remaining > 0) ? 'Low' : 'Zero');
                                    $badge_class = ($remaining > 2) ? 'bg-success' : (($remaining > 0) ? 'bg-warning' : 'bg-danger');
                                ?>
                                <div class="list-group-item">
                                    <small class="text-muted"><?php echo htmlspecialchars($balance['name']); ?> Available</small>
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo $remaining; ?> days</strong>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>

                                <?php 
                                // Show Special Leaves
                                foreach ($leave_balances['special'] as $balance): 
                                    $remaining = $balance['allocated'] - $balance['used'];
                                    $status_text = ($remaining > 0) ? 'Available' : 'Zero';
                                    $badge_class = ($remaining > 0) ? 'bg-info' : 'bg-danger';
                                ?>
                                <div class="list-group-item">
                                    <small class="text-muted"><?php echo htmlspecialchars($balance['name']); ?> Total</small>
                                    <div class="d-flex justify-content-between">
                                        <strong><?php echo $remaining; ?> days left</strong>
                                        <span class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

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

            <!-- Leave Balance Section -->
            <div id="leave-balance-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Leave Balance</h1>
                </div>
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-chart-pie"></i>
                        Core Leave Balance
                    </div>
                    <div class="row">
                        <?php foreach ($leave_balances['core'] as $balance): 
                            $percent = ($balance['allocated'] > 0) ? ($balance['used'] / $balance['allocated']) * 100 : 0;
                            $bar_class = ($percent > 80) ? 'bg-danger' : (($percent > 50) ? 'bg-warning' : 'bg-success');
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="info-box">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong><?php echo htmlspecialchars($balance['name']); ?></strong>
                                    <span class="badge <?php echo $bar_class; ?>">
                                        <?php echo ($balance['allocated'] - $balance['used']) . '/' . $balance['allocated']; ?>
                                    </span>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar <?php echo $bar_class; ?>" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <div class="col-md-12 mb-4">
                            <div class="info-box" style="border-left-color: var(--info-color);">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong>Total Annual Core Balance</strong>
                                    <span class="badge bg-info"><?php echo $total_balance_core . '/' . $total_allocated_core; ?></span>
                                </div>
                                <?php $total_percent = ($total_allocated_core > 0) ? ($total_used_core / $total_allocated_core) * 100 : 0; ?>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-info" style="width: <?php echo $total_percent; ?>%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="section-title mt-4">
                        <i class="fas fa-star"></i>
                        Special Leave Entitlement
                    </div>
                    <div class="row">
                        <?php foreach ($leave_balances['special'] as $balance): 
                            $percent = ($balance['allocated'] > 0) ? ($balance['used'] / $balance['allocated']) * 100 : 0;
                        ?>
                        <div class="col-md-6 mb-4">
                            <div class="info-box" style="border-left-color: var(--warning-color);">
                                <div class="d-flex justify-content-between mb-2">
                                    <strong><?php echo htmlspecialchars($balance['name']); ?></strong>
                                    <span class="badge bg-info">
                                        <?php echo ($balance['allocated'] - $balance['used']) . '/' . $balance['allocated']; ?>
                                    </span>
                                </div>
                                <div class="progress" style="height: 25px;">
                                    <div class="progress-bar bg-info" style="width: <?php echo $percent; ?>%"></div>
                                </div>
                                <small class="text-muted mt-1 d-block">This leave does not count towards your annual core balance.</small>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
