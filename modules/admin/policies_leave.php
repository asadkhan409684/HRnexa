            <!-- Leave Policy Section -->
            <div id="leave-policy-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Leave Policy Configuration</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPolicyModal"><i class="fas fa-plus"></i> Add Policy</button>
                </div>
                <div class="section-card">
                    <div class="section-title">
                        <i class="fas fa-calendar-check"></i>
                        Leave Policies & Entitlements
                    </div>
                    <div class="data-table">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Policy Title</th>
                                    <th>Status</th>
                                    <th>Created At</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $leave_policies = $conn->query("SELECT * FROM system_policies WHERE category = 'Leave' ORDER BY title ASC");
                                if ($leave_policies && $leave_policies->num_rows > 0):
                                    while($p = $leave_policies->fetch_assoc()):
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($p['title'] ?? ''); ?></strong></td>
                                    <td><span class="badge <?php echo $p['is_active'] ? 'bg-success' : 'bg-secondary'; ?>"><?php echo $p['is_active'] ? 'Active' : 'Inactive'; ?></span></td>
                                    <td><?php echo date('M j, Y', strtotime($p['created_at'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-primary view-policy-btn" 
                                                data-title="<?php echo htmlspecialchars($p['title'] ?? ''); ?>" 
                                                data-description="<?php echo htmlspecialchars($p['description'] ?? ''); ?>">
                                            <i class="fas fa-eye"></i> View
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; else: ?>
                                    <tr><td colspan="4" class="text-center">No leave policies found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
