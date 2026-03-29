<?php
// Fetch Monthly Attendance History
$month_att_query = "SELECT * FROM attendance 
                   WHERE employee_id = $employee_id 
                   AND MONTH(date) = MONTH(CURDATE()) 
                   AND YEAR(date) = YEAR(CURDATE()) 
                   ORDER BY date DESC";
$month_att_result = $conn->query($month_att_query);
?>
<!-- Attendance Section -->
<div id="attendance-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>My Attendance</h1>
    </div>
    <div class="row mb-3">
        <div class="col-md-3">
            <select class="form-select">
                <option>Select Month</option>
                <option>January 2026</option>
                <option>December 2025</option>
            </select>
        </div>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-clock"></i>
            Monthly Attendance
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
