<?php
// modules/super_admin/team_management.php
// This file is included in dashboard.php or called via AJAX

require_once('../../app/Config/database.php');

// Fetch team members
$team_query = "SELECT * FROM team_members ORDER BY sort_order ASC, name ASC";
$team_result = $conn->query($team_query);
?>

<div class="section-card border-0 shadow-sm">
    <div class="section-title d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <div class="p-2 bg-info-subtle rounded text-info">
                <i class="fas fa-users fs-4"></i>
            </div>
            <h5 class="mb-0 fw-bold">Team Member Management</h5>
        </div>
        <button class="btn btn-primary shadow-sm px-4 rounded-pill fw-bold" onclick="showAddTeamModal()">
            <i class="fas fa-plus-circle me-1"></i> Add New Member
        </button>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle border-top">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Member</th>
                    <th>Designation</th>
                    <th>Social Links</th>
                    <th>Status</th>
                    <th>Sort Order</th>
                    <th class="pe-4 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($team_result && $team_result->num_rows > 0): ?>
                    <?php while($member = $team_result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="member-img shadow-sm" style="width: 45px; height: 45px; overflow: hidden; border-radius: 8px;">
                                        <?php if ($member['image_path']): ?>
                                            <img src="../../<?php echo htmlspecialchars($member['image_path']); ?>" alt="" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php else: ?>
                                            <div class="bg-secondary text-white d-flex align-items-center justify-content-center h-100">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo htmlspecialchars($member['name']); ?></div>
                                        <div class="text-muted small"><?php echo htmlspecialchars($member['email'] ?? 'No email'); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill">
                                    <?php echo htmlspecialchars($member['designation']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <?php if ($member['linkedin']): ?>
                                        <a href="<?php echo htmlspecialchars($member['linkedin']); ?>" target="_blank" class="text-primary"><i class="fab fa-linkedin"></i></a>
                                    <?php endif; ?>
                                    <?php if ($member['twitter']): ?>
                                        <a href="<?php echo htmlspecialchars($member['twitter']); ?>" target="_blank" class="text-info"><i class="fab fa-twitter"></i></a>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td>
                                <span class="badge <?php echo $member['status'] == 'active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger'; ?> border px-3 py-2 rounded-pill">
                                    <?php echo ucfirst($member['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="text-muted fw-bold"><?php echo $member['sort_order']; ?></span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group shadow-sm rounded">
                                    <button class="btn btn-white btn-sm px-3 border" title="Edit Member" onclick="editTeamMember(<?php echo $member['id']; ?>)">
                                        <i class="fas fa-pencil-alt text-primary"></i>
                                    </button>
                                    <button class="btn btn-white btn-sm px-3 border" title="Delete Member" onclick="deleteTeamMember(<?php echo $member['id']; ?>)">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="py-5 text-center">
                            <p class="text-muted fw-semibold">No team members found.</p>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal structure for Add/Edit would be added here or handled via JS -->
