            <!-- Holiday Calendar Section -->
            <div id="holiday-calendar-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Holiday Calendar</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHolidayModal"><i class="fas fa-plus"></i> Add Holiday</button>
                </div>
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-calendar-alt"></i>
                        Holidays in <?php echo date('Y'); ?>
                    </div>
                    <div class="data-table">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Holiday Name</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $holidays_res = $conn->query("SELECT * FROM system_holidays WHERE YEAR(holiday_date) = YEAR(CURDATE()) ORDER BY holiday_date ASC");
                                if ($holidays_res && $holidays_res->num_rows > 0):
                                    while($h = $holidays_res->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($h['title'] ?? ''); ?></strong></td>
                                    <td><?php echo date('F j, Y', strtotime($h['holiday_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($h['type'] ?? ''); ?></td>
                                    <td>
                                        <a href="dashboard.php?delete_holiday=<?php echo $h['id']; ?>&csrf_token=<?php echo $_SESSION['csrf_token']; ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this holiday?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="4" class="text-center">No holidays found for this year.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div><!-- End of #holiday-calendar-section -->

        <!-- Add Holiday Modal (Generic for all holiday sections) -->
        <div class="modal fade" id="addHolidayModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Holiday / Festival</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="dashboard.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" class="form-control" name="holiday_title" required placeholder="e.g. Eid-ul-Fitr">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" name="holiday_date" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Duration (Days)</label>
                                <input type="number" class="form-control" name="duration" value="1" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Type</label>
                                <select class="form-select" name="holiday_type" required>
                                    <option value="National">Government Holiday</option>
                                    <option value="Festival">Religious Festival</option>
                                    <option value="Company">Company Event</option>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" name="add_holiday" class="btn btn-primary">Save Holiday</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
