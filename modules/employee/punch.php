<?php
// Fetch Today's Attendance for Punch Section
$today_att_query = "SELECT * FROM attendance WHERE employee_id = $employee_id AND date = CURDATE() ORDER BY id DESC LIMIT 1";
$today_att_result = $conn->query($today_att_query);
$today_att = $today_att_result->fetch_assoc();

$punch_in_time = ($today_att && !empty($today_att['punch_in'])) ? date('h:i A', strtotime($today_att['punch_in'])) : 'Not Yet';
$punch_out_time = ($today_att && !empty($today_att['punch_out']) && $today_att['punch_out'] != '0000-00-00 00:00:00') ? date('h:i A', strtotime($today_att['punch_out'])) : 'Not Yet';
$hours_worked = ($today_att && $today_att['total_hours']) ? $today_att['total_hours'] . 'h' : '0h';
?>
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
                    <h2 id="current-time">09:45 AM</h2>
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
                                    // Check if 9 hours have passed
                                    if (time() < $nine_hours_later) {
                                        $punch_out_disabled = true;
                                        $remaining_seconds = $nine_hours_later - time();
                                        $r_hours = floor($remaining_seconds / 3600);
                                        $r_minutes = floor(($remaining_seconds % 3600) / 60);
                                        $punch_out_title = "Available in " . $r_hours . "h " . $r_minutes . "m";
                                    } else {
                                        $punch_out_disabled = false;
                                        $punch_out_title = "Punch Out";
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
