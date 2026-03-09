            <!-- HR Policies Section -->
            <div id="hr-policies-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>HR Policy Configuration</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPolicyModal"><i class="fas fa-plus"></i> Add Policy</button>
                </div>
                <div class="row">
                    <?php 
                    $hr_policies = $conn->query("SELECT * FROM system_policies WHERE category = 'HR' AND is_active = 1 ORDER BY title ASC");
                    if ($hr_policies && $hr_policies->num_rows > 0):
                        while($p = $hr_policies->fetch_assoc()):
                    ?>
                    <div class="col-lg-6 mb-3">
                        <div class="section-card">
                            <div class="section-title">
                                <i class="fas fa-file-alt"></i>
                                <?php echo htmlspecialchars($p['title'] ?? ''); ?>
                            </div>
                            <p style="color: #666; margin-bottom: 15px;"><?php echo htmlspecialchars(substr($p['description'] ?? '', 0, 100)) . '...'; ?></p>
                            <button class="btn btn-sm btn-outline-primary view-policy-btn" 
                                    data-title="<?php echo htmlspecialchars($p['title'] ?? ''); ?>" 
                                    data-description="<?php echo htmlspecialchars($p['description'] ?? ''); ?>">
                                <i class="fas fa-eye"></i> View
                            </button>
                        </div>
                    </div>
                    <?php endwhile; else: ?>
                        <div class="col-12"><p class="text-center text-muted">No HR policies found.</p></div>
                    <?php endif; ?>
                </div>
            </div>
