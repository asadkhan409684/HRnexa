            <!-- Personal Attendance View -->
            <div id="personal-attendance-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>My Attendance</h1>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <select class="form-select">
                            <option>Select Month</option>
                            <option selected><?php echo date('F Y'); ?></option>
                            <option><?php echo date('F Y', strtotime('-1 month')); ?></option>
                        </select>
                    </div>
                </div>
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-clock"></i>
                        Monthly Attendance History
                    </div>
                    <div class="data-table">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Check-In</th>
                                    <th>Check-Out</th>
                                    <th>Hours</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($month_att_result && $month_att_result->num_rows > 0): ?>
                                    <?php while($att = $month_att_result->fetch_assoc()): 
                                        $status_class = 'bg-success';
                                        if ($att['status'] == 'late') $status_class = 'bg-warning';
                                        elseif ($att['status'] == 'absent') $status_class = 'bg-danger';
                                        elseif ($att['status'] == 'half_day') $status_class = 'bg-info';
                                        
                                        $p_in = !empty($att['punch_in']) ? date('h:i A', strtotime($att['punch_in'])) : '—';
                                        $p_out = (!empty($att['punch_out']) && $att['punch_out'] != '0000-00-00 00:00:00') ? date('h:i A', strtotime($att['punch_out'])) : '—';
                                    ?>
                                        <tr>
                                            <td><?php echo date('d-M-Y', strtotime($att['date'])); ?></td>
                                            <td><?php echo $p_in; ?></td>
                                            <td><?php echo $p_out; ?></td>
                                            <td><?php echo $att['total_hours'] ? $att['total_hours'] . 'h' : '—'; ?></td>
                                            <td><span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($att['status']); ?></span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" class="text-center text-muted">No attendance records found for this month.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Punch In/Out Section -->
            <div id="punch-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Punch In / Out</h1>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="section-card">
                            <div class="section-title">
                                <i class="fas fa-hand-paper"></i>
                                Daily Time Recording
                            </div>
                            <div class="text-center mb-4">
                                <h2 id="current-time">--:-- --</h2>
                                <p style="color: #999;">Current Time</p>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6">
                                    <button class="btn btn-success w-100" onclick="handlePunch('in')" id="btn-punch-in" <?php echo ($today_att) ? 'disabled' : ''; ?>><i class="fas fa-sign-in-alt"></i> Punch In</button>
                                </div>
                                <div class="col-6">
                                    <?php 
                                        $punch_out_disabled = true;
                                        $punch_out_title = "Please punch in first";
                                        
                                        if ($today_att) {
                                            $punch_in_ts = strtotime($today_att['punch_in']);
                                            $nine_hours_later = $punch_in_ts + (9 * 3600);
                                            $currently_pushed_out = (!empty($today_att['punch_out']) && $today_att['punch_out'] != '0000-00-00 00:00:00');
                                            
                                            if ($currently_pushed_out) {
                                                $punch_out_disabled = true;
                                                $punch_out_title = "Already punched out for today";
                                            } else {
                                                // TL can punch out anytime, keeping it flexible or mirroring employee 9h rule
                                                // Let's mirror employee 9h rule for consistency as requested
                                                if (time() >= $nine_hours_later) {
                                                    $punch_out_disabled = false;
                                                    $punch_out_title = "Punch Out";
                                                } else {
                                                    $punch_out_disabled = true;
                                                    $punch_out_title = "You can punch out after " . date('h:i A', $nine_hours_later);
                                                }
                                            }
                                        }
                                    ?>
                                    <button class="btn btn-danger w-100" onclick="handlePunch('out')" id="btn-punch-out" <?php echo ($punch_out_disabled) ? 'disabled' : ''; ?> title="<?php echo $punch_out_title; ?>"><i class="fas fa-sign-out-alt"></i> Punch Out</button>
                                </div>
                            </div>
                            <div id="punch-message" class="text-center mt-2 small" style="display: none;"></div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="section-card">
                            <div class="section-title">
                                <i class="fas fa-hourglass-end"></i>
                                Today's Summary
                            </div>
                             <table class="table table-sm mb-0">
                                <tr>
                                    <td style="border: none;"><strong>Check-In Time:</strong></td>
                                    <td style="border: none;" class="text-end" id="display-punch-in"><?php echo $punch_in_time; ?></td>
                                </tr>
                                <tr>
                                    <td style="border: none;"><strong>Check-Out Time:</strong></td>
                                    <td style="border: none;" class="text-end" id="display-punch-out"><?php echo $punch_out_time; ?></td>
                                </tr>
                                <tr>
                                    <td style="border: none;"><strong>Hours Worked:</strong></td>
                                    <td style="border: none;" class="text-end" id="display-hours"><?php echo $hours_worked; ?></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

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
