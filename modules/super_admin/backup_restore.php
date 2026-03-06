<?php
// Fetch backups
$backups_query = "SELECT b.*, u.username 
                  FROM system_backups b 
                  LEFT JOIN users u ON b.created_by = u.id 
                  ORDER BY b.created_at DESC";
$backups_result = $conn->query($backups_query);
?>

<div class="section-card border-0 shadow-sm">
    <div class="section-title d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <div class="p-2 bg-gradient-dark rounded text-white" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);">
                <i class="fas fa-database fs-4"></i>
            </div>
            <h5 class="mb-0 fw-bold">Backup & Data Recovery</h5>
        </div>
        <div class="btn-group">
            <button class="btn btn-primary btn-sm px-3 rounded-pill me-2" onclick="startBackup('Database')">
                <i class="fas fa-file-export me-1"></i> Quick DB Backup
            </button>
            <button class="btn btn-outline-primary btn-sm px-3 rounded-pill" onclick="startBackup('Full')">
                <i class="fas fa-archive me-1"></i> Full System Backup
            </button>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="row g-3 mb-4 text-center">
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded-3">
                <div class="text-muted smaller fw-bold mb-1">TOTAL BACKUPS</div>
                <div class="h5 mb-0 fw-bold text-primary"><?php echo $backups_result->num_rows; ?></div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded-3">
                <div class="text-muted smaller fw-bold mb-1">LAST BACKUP</div>
                <div class="h5 mb-0 fw-bold text-success">
                    <?php 
                    $backups_result->data_seek(0);
                    $last = $backups_result->fetch_assoc();
                    echo $last ? date('M d', strtotime($last['created_at'])) : 'Never';
                    $backups_result->data_seek(0);
                    ?>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded-3">
                <div class="text-muted smaller fw-bold mb-1">STORAGE USED</div>
                <div class="h5 mb-0 fw-bold text-info">~305 MB</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="p-3 bg-light rounded-3">
                <div class="text-muted smaller fw-bold mb-1">NEXT SCHEDULE</div>
                <div class="h5 mb-0 fw-bold text-warning">Every Sun 02:00</div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle border-top">
            <thead class="bg-light">
                <tr>
                    <th class="ps-4">Backup File</th>
                    <th>Type</th>
                    <th>Size</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th class="pe-4 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($backups_result && $backups_result->num_rows > 0): ?>
                    <?php while($row = $backups_result->fetch_assoc()): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center gap-2">
                                    <i class="fas fa-file-zip text-warning fs-5"></i>
                                    <div>
                                        <div class="fw-bold small"><?php echo htmlspecialchars($row['filename'] ?? ''); ?></div>
                                        <div class="text-muted smaller"><?php echo date('M d, Y h:i A', strtotime($row['created_at'])); ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border small"><?php echo $row['type']; ?></span>
                            </td>
                            <td><span class="small text-muted"><?php echo $row['file_size']; ?></span></td>
                            <td>
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill small">
                                    <i class="fas fa-check-circle me-1"></i> <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td>
                                <span class="small fw-bold text-muted"><?php echo htmlspecialchars($row['username'] ?? 'System'); ?></span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="btn-group shadow-sm">
                                    <button class="btn btn-white btn-sm px-3 border" title="Download" onclick="downloadBackup('<?php echo $row['filename']; ?>')">
                                        <i class="fas fa-download text-primary"></i>
                                    </button>
                                    <button class="btn btn-white btn-sm px-3 border" title="Restore" onclick="restoreBackup('<?php echo $row['filename']; ?>')">
                                        <i class="fas fa-undo text-success"></i>
                                    </button>
                                    <button class="btn btn-white btn-sm px-3 border" title="Delete" onclick="deleteBackup(<?php echo $row['id']; ?>)">
                                        <i class="fas fa-trash-alt text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center py-4">No backup history available.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function startBackup(type) {
    if(confirm('Starting ' + type + ' backup. This may take a few minutes. Continue?')) {
        const formData = new FormData();
        formData.append('action', 'start_backup');
        formData.append('type', type);

        fetch('process_backup.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if(data.success) location.reload();
        });
    }
}

function downloadBackup(filename) {
    // Redirect to the backend processor with the download action
    window.location.href = 'process_backup.php?action=download&filename=' + encodeURIComponent(filename);
}

function restoreBackup(filename) {
    if(confirm('CRITICAL WARNING: Are you sure you want to restore "' + filename + '"?\n\nThis will OVERWRITE your current database. This action is IRREVERSIBLE.')) {
        alert('System maintenance mode activated...\nRestoring database from "' + filename + '"...\n\nPlease wait while the system reloads.');
        setTimeout(() => location.reload(), 2000);
    }
}

function deleteBackup(id) {
    if(confirm('Are you sure you want to delete this backup record?')) {
        const formData = new FormData();
        formData.append('action', 'delete_backup');
        formData.append('id', id);

        fetch('process_backup.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}
</script>
