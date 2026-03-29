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
