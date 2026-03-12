<div id="training-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Training & Development</h1>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProgramModal">
            <i class="fas fa-plus"></i> New Program
        </button>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-graduation-cap"></i>
            Training Programs
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Program Name</th>
                        <th>Trainer</th>
                        <th>Duration</th>
                        <th>Timeline</th>
                        <th>Participants</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($training_result && $training_result->num_rows > 0): ?>
                        <?php while($prog = $training_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($prog['name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars(substr($prog['description'] ?? '', 0, 50)) . '...'; ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($prog['trainer'] ?? 'N/A'); ?></td>
                            <td><?php echo $prog['duration'] ? $prog['duration'] . ' Days' : 'N/A'; ?></td>
                            <td>
                                <small>
                                    <?php if ($prog['start_date']): ?>
                                        <?php echo date('d M Y', strtotime($prog['start_date'])); ?>
                                        <?php if ($prog['end_date']) echo ' - ' . date('d M Y', strtotime($prog['end_date'])); ?>
                                    <?php else: ?>
                                        Dates TBD
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <div class="participant-stats">
                                    <span class="fw-bold"><?php echo $prog['participant_count']; ?></span> / <?php echo $prog['capacity'] ?? '?'; ?>
                                    <div class="progress mt-1" style="height: 5px; width: 60px;">
                                        <?php 
                                            $perc = ($prog['capacity'] ?? 0) > 0 ? ($prog['participant_count'] / $prog['capacity']) * 100 : 0;
                                            $prog_color = $perc >= 100 ? 'bg-danger' : 'bg-primary';
                                        ?>
                                        <div class="progress-bar <?php echo $prog_color; ?>" role="progressbar" style="width: <?php echo min($perc, 100); ?>%"></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php 
                                    $prog_badge = 'bg-info';
                                    if ($prog['status'] == 'active') $prog_badge = 'bg-success';
                                    if ($prog['status'] == 'completed') $prog_badge = 'bg-secondary';
                                    if ($prog['status'] == 'planned') $prog_badge = 'bg-primary';
                                    if ($prog['status'] == 'cancelled') $prog_badge = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $prog_badge; ?>"><?php echo ucfirst($prog['status']); ?></span>
                            </td>
                            <td>
                                <?php if ($prog['status'] == 'planned' || $prog['status'] == 'active'): ?>
                                    <button class="btn btn-sm btn-outline-primary" onclick='openEnrollModal(<?php echo json_encode($prog); ?>)' title="Enroll Employee">
                                        <i class="fas fa-user-plus"></i> Enroll
                                    </button>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center">No training programs found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Program Modal -->
<div class="modal fade" id="addProgramModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Training Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Program Name</label>
                        <input type="text" class="form-control" name="name" required placeholder="e.g. Advanced AI for HR Managers">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Brief program overview..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Trainer / Speaker</label>
                            <input type="text" class="form-control" name="trainer" placeholder="e.g. Dr. Salman Khan">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Capacity</label>
                            <input type="number" class="form-control" name="capacity" value="20">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Duration (Days)</label>
                            <input type="number" class="form-control" name="duration" placeholder="e.g. 5">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="start_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="end_date">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_program" class="btn btn-primary">Create Program</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Enroll Modal -->
<div class="modal fade" id="enrollModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Enroll Employee</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="program_id" id="enroll_program_id">
                    <div class="mb-3">
                        <label class="form-label">Program</label>
                        <div id="enroll_program_name" class="fw-bold text-primary"></div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select Employee</label>
                        <select class="form-select" name="employee_id" required>
                            <option value="">Choose Employee...</option>
                            <?php 
                            $e_res = $conn->query("SELECT id, first_name, last_name, employee_code FROM employees WHERE employment_status = 'active' ORDER BY first_name ASC");
                            while($e = $e_res->fetch_assoc()) {
                                echo "<option value='{$e['id']}'>{$e['first_name']} {$e['last_name']} ({$e['employee_code']})</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="enroll_employee" class="btn btn-success">Enroll Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openEnrollModal(prog) {
    document.getElementById('enroll_program_id').value = prog.id;
    document.getElementById('enroll_program_name').textContent = prog.name;
    new bootstrap.Modal(document.getElementById('enrollModal')).show();
}
</script>
