            <!-- Team Members Section -->
            <div id="team-members-section" class="section-content" style="display: none;">
                <div class="page-header">
                    <h1>Team Members</h1>
                </div>
                <div class="section-card">
                    <div class="section-title"><i class="fas fa-user-group"></i> All Team Members</div>
                    <div class="data-table">
                        <table class="table table-striped">
                            <thead>
                                <tr><th>Name</th><th>Employee ID</th><th>Designation</th><th>Email</th><th>Joining Date</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php if ($team_members_result && $team_members_result->num_rows > 0): 
                                    $team_members_result->data_seek(0);
                                    while($member = $team_members_result->fetch_assoc()): 
                                        $s_badge = ($member['employment_status'] == 'active') ? 'bg-success' : 'bg-info';
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($member['employee_code']); ?></td>
                                    <td><?php echo htmlspecialchars($member['desig_name']); ?></td>
                                    <td><?php echo htmlspecialchars($member['email']); ?></td>
                                    <td><?php echo date('d-M-Y', strtotime($member['hire_date'])); ?></td>
                                    <td><span class="badge <?php echo $s_badge; ?>"><?php echo ucfirst($member['employment_status']); ?></span></td>
                                </tr>
                                <?php endwhile; else: ?>
                                <tr><td colspan="6" class="text-center text-muted">No team members found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
