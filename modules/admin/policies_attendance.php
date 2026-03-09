            <!-- Attendance Policy Section -->
            <div id="attendance-policy-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Attendance Policy Configuration</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPolicyModal"><i class="fas fa-plus"></i> Add Policy</button>
                </div>
                <div class="row">
                    <div class="col-lg-6">
                        <div class="section-card">
                            <div class="section-title">
                                <i class="fas fa-clock"></i>
                                Working Hours
                            </div>
                            <?php 
                            $shift_start = $conn->query("SELECT value FROM system_settings WHERE `key` = 'office_start_time'")->fetch_assoc()['value'] ?? '09:00';
                            $shift_end = $conn->query("SELECT value FROM system_settings WHERE `key` = 'office_end_time'")->fetch_assoc()['value'] ?? '18:00';
                            ?>
                            <form action="dashboard.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <div class="mb-3">
                                    <label class="form-label">Office Start Time</label>
                                    <input type="time" class="form-control" name="shift_start" value="<?php echo $shift_start; ?>">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Office End Time</label>
                                    <input type="time" class="form-control" name="shift_end" value="<?php echo $shift_end; ?>">
                                </div>
                                <button type="submit" name="update_attendance_settings" class="btn btn-primary">Save Changes</button>
                            </form>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="section-card">
                            <div class="section-title">
                                <i class="fas fa-clipboard-list"></i>
                                Attendance Policies
                            </div>
                            <div class="data-table">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Policy</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $att_policies = $conn->query("SELECT * FROM system_policies WHERE category = 'Attendance' ORDER BY title ASC");
                                        if ($att_policies && $att_policies->num_rows > 0):
                                            while($p = $att_policies->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($p['title'] ?? ''); ?></td>
                                            <td><span class="badge <?php echo $p['is_active'] ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $p['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary view-policy-btn" 
                                                        data-title="<?php echo htmlspecialchars($p['title'] ?? ''); ?>" 
                                                        data-description="<?php echo htmlspecialchars($p['description'] ?? ''); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <?php endwhile; else: ?>
                                            <tr><td colspan="3" class="text-center">No attendance policies.</td></tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
