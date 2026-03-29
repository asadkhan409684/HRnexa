<?php
// Fetch Attendance Correction History
$correction_history_query = "SELECT * FROM attendance_corrections WHERE employee_id = $employee_id ORDER BY created_at DESC LIMIT 10";
$correction_history_result = $conn->query($correction_history_query);
?>
<!-- Attendance Correction Request Section -->
<div id="correction-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Attendance Correction Request</h1>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <div class="section-card">
                <div class="section-title">
                    <i class="fas fa-edit"></i>
                    Request Correction
                </div>
                <form id="attendanceCorrectionForm">
                    <div class="mb-3">
                        <label class="form-label">Select Date</label>
                        <input type="date" class="form-control" name="date" required value="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Check-In Time</label>
                            <input type="time" class="form-control" name="punch_in" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Check-Out Time</label>
                            <input type="time" class="form-control" name="punch_out" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <textarea class="form-control" name="reason" rows="3" placeholder="Explain why you need this correction" required></textarea>
                    </div>
                    <div id="correctionFormMessage" class="mb-3" style="display: none;"></div>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Request</button>
                </form>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="section-card">
                <div class="section-title">
                    <i class="fas fa-history"></i>
                    Recent Requests
                </div>
                 <div class="list-group list-group-flush">
                    <?php if ($correction_history_result && $correction_history_result->num_rows > 0): ?>
                        <?php while($cor = $correction_history_result->fetch_assoc()): 
                            $cor_status_bg = 'bg-warning';
                            if ($cor['status'] == 'approved') $cor_status_bg = 'bg-success';
                            elseif ($cor['status'] == 'referred') $cor_status_bg = 'bg-info';
                            elseif ($cor['status'] == 'rejected') $cor_status_bg = 'bg-danger';
                        ?>
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between">
                                    <strong><?php echo date('d-M-Y', strtotime($cor['date'])); ?></strong>
                                    <span class="badge <?php echo $cor_status_bg; ?>"><?php echo ucfirst($cor['status']); ?></span>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="list-group-item text-center text-muted">No recent requests.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
