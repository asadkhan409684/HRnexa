            <!-- Festival Calendar Section -->
            <div id="festival-calendar-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Festival Calendar</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHolidayModal"><i class="fas fa-plus"></i> Add Festival</button>
                </div>
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-snowflake"></i>
                        Festival Holidays
                    </div>
                    <div class="data-table">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Festival Name</th>
                                    <th>Date</th>
                                    <th>Duration</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $festivals_res = $conn->query("SELECT * FROM system_holidays WHERE type = 'Festival' AND YEAR(holiday_date) = YEAR(CURDATE()) ORDER BY holiday_date ASC");
                                if ($festivals_res && $festivals_res->num_rows > 0):
                                    while($f = $festivals_res->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($f['title'] ?? ''); ?></strong></td>
                                    <td><?php echo date('F j, Y', strtotime($f['holiday_date'])); ?></td>
                                    <td><?php echo $f['duration']; ?> day(s)</td>
                                    <td>
                                        <a href="dashboard.php?delete_holiday=<?php echo $f['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this festival?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="4" class="text-center">No festival holidays found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
