            <!-- Weekly Off Section -->
            <div id="weekly-off-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Weekly Off Configuration</h1>
                </div>
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-calendar-times"></i>
                        Weekly Off Days
                    </div>
                    <?php 
                    $weekly_off_json = $conn->query("SELECT value FROM system_settings WHERE `key` = 'weekly_off_days'")->fetch_assoc()['value'] ?? '[]';
                    $off_days = json_decode($weekly_off_json, true) ?: [];
                    $all_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    ?>
                    <form action="dashboard.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="row">
                            <?php foreach($all_days as $day): ?>
                            <div class="col-lg-3 col-md-4 mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="off_days[]" value="<?php echo $day; ?>" id="<?php echo strtolower($day); ?>" <?php echo in_array($day, $off_days) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="<?php echo strtolower($day); ?>">
                                        <strong><?php echo $day; ?></strong>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" name="update_weekly_off" class="btn btn-primary mt-3">Save Configuration</button>
                    </form>
                </div>
            </div>
