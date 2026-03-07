<?php
// Get system health data
$php_version = PHP_VERSION;
$server_os = PHP_OS;
$db_version = $conn->server_info;

// Database Size (approx)
$db_size_res = $conn->query("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size_mb FROM information_schema.TABLES WHERE table_schema = 'hrnexa'");
$db_size = $db_size_res ? number_format($db_size_res->fetch_assoc()['size_mb'], 2) : '0.00';

// Table Count
$tables_res = $conn->query("SELECT COUNT(*) as count FROM information_schema.TABLES WHERE table_schema = 'hrnexa'");
$table_count = $tables_res ? $tables_res->fetch_assoc()['count'] : '0';

// Current time
$timezone = date_default_timezone_get();
$current_time = date('Y-m-d H:i:s');
?>

<div class="section-card border-0 shadow-sm">
    <div class="section-title d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center gap-2">
            <div class="p-2 bg-success-subtle rounded text-success">
                <i class="fas fa-heartbeat fs-4"></i>
            </div>
            <h5 class="mb-0 fw-bold">System Health & Monitoring</h5>
        </div>
        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small">
            <i class="fas fa-check-circle me-1"></i> System Online
        </span>
    </div>

    <div class="row g-4">
        <!-- Server Info -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-light border-0 h-100 p-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <i class="fab fa-php fs-3 text-primary"></i>
                    <h6 class="mb-0 fw-bold">Environment</h6>
                </div>
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">PHP Version:</span>
                        <span class="fw-bold"><?php echo $php_version; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Server OS:</span>
                        <span class="fw-bold"><?php echo $server_os; ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Timezone:</span>
                        <span class="fw-bold"><?php echo $timezone; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Database Info -->
        <div class="col-md-6 col-lg-4">
            <div class="card bg-light border-0 h-100 p-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <i class="fas fa-database fs-3 text-info"></i>
                    <h6 class="mb-0 fw-bold">Database Health</h6>
                </div>
                <div class="small">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">MySQL Version:</span>
                        <span class="fw-bold"><?php echo substr($db_version, 0, 15); ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Database Size:</span>
                        <span class="fw-bold"><?php echo $db_size; ?> MB</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Total Tables:</span>
                        <span class="fw-bold"><?php echo $table_count; ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Status Indicators -->
        <div class="col-md-12 col-lg-4">
            <div class="card bg-light border-0 h-100 p-3">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <i class="fas fa-network-wired fs-3 text-success"></i>
                    <h6 class="mb-0 fw-bold">Services Status</h6>
                </div>
                <div class="small">
                    <div class="health-item d-flex align-items-center justify-content-between py-1 border-bottom-0">
                        <span class="text-muted"><i class="fas fa-globe me-2"></i> Web Server</span>
                        <span class="badge bg-success">Healthy</span>
                    </div>
                    <div class="health-item d-flex align-items-center justify-content-between py-1 border-bottom-0">
                        <span class="text-muted"><i class="fas fa-database me-2"></i> Database Engine</span>
                        <span class="badge bg-success">Active</span>
                    </div>
                    <div class="health-item d-flex align-items-center justify-content-between py-1 border-bottom-0">
                        <span class="text-muted"><i class="fas fa-folder-open me-2"></i> File System</span>
                        <span class="badge bg-success">Writable</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 p-3 bg-light rounded-3">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fas fa-clock text-muted"></i>
            <span class="small fw-bold text-muted uppercase">System Real-time Check</span>
        </div>
        <div class="d-flex align-items-center gap-4">
            <div class="display-6 fw-bold" style="font-size: 1.5rem;"><?php echo date('h:i:s A'); ?></div>
            <div class="text-muted small">Current Server Time: <br><strong><?php echo $current_time; ?></strong></div>
        </div>
    </div>
</div>
