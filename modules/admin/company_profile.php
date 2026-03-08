            <!-- Company Profile Section -->
            <div id="company-profile-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Company Profile</h1>
                </div>
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-building"></i>
                        Basic Information
                    </div>
                    <form action="dashboard.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Company Name</label>
                                <input type="text" class="form-control" name="company_name" value="<?php echo htmlspecialchars($company_settings['company_name'] ?? ''); ?>" placeholder="Enter company name">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Registration Number</label>
                                <input type="text" class="form-control" name="company_reg_no" value="<?php echo htmlspecialchars($company_settings['company_reg_no'] ?? ''); ?>" placeholder="Enter registration number">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="company_email" value="<?php echo htmlspecialchars($company_settings['company_email'] ?? ''); ?>" placeholder="Enter email">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" name="company_phone" value="<?php echo htmlspecialchars($company_settings['company_phone'] ?? ''); ?>" placeholder="Enter phone number">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="company_address" rows="3" placeholder="Enter company address"><?php echo htmlspecialchars($company_settings['company_address'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="update_company_profile" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>
            </div>
