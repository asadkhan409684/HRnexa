<!-- Leave Balance Section -->
<div id="leave-balance-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Leave Balance</h1>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-chart-pie"></i>
            Core Leave Balance
        </div>
        <div class="row">
            <?php foreach ($leave_balances['core'] as $balance): 
                $percent = ($balance['allocated'] > 0) ? ($balance['used'] / $balance['allocated']) * 100 : 0;
                $bar_class = ($percent > 80) ? 'bg-danger' : (($percent > 50) ? 'bg-warning' : 'bg-success');
            ?>
            <div class="col-md-6 mb-4">
                <div class="info-box">
                    <div class="d-flex justify-content-between mb-2">
                        <strong><?php echo htmlspecialchars($balance['name']); ?></strong>
                        <span class="badge <?php echo $bar_class; ?>">
                            <?php echo ($balance['allocated'] - $balance['used']) . '/' . $balance['allocated']; ?>
                        </span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar <?php echo $bar_class; ?>" style="width: <?php echo $percent; ?>%"></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <div class="col-md-12 mb-4">
                <div class="info-box" style="border-left-color: var(--info-color);">
                    <div class="d-flex justify-content-between mb-2">
                        <strong>Total Annual Core Balance</strong>
                        <span class="badge bg-info"><?php echo $total_balance_core . '/' . $total_allocated_core; ?></span>
                    </div>
                    <?php $total_percent = ($total_allocated_core > 0) ? ($total_used_core / $total_allocated_core) * 100 : 0; ?>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-info" style="width: <?php echo $total_percent; ?>%"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-title mt-4">
            <i class="fas fa-star"></i>
            Special Leave Entitlement
        </div>
        <div class="row">
            <?php foreach ($leave_balances['special'] as $balance): 
                $percent = ($balance['allocated'] > 0) ? ($balance['used'] / $balance['allocated']) * 100 : 0;
                // For special leaves, maybe we don't need a percentage bar if it's "total usage"
            ?>
            <div class="col-md-6 mb-4">
                <div class="info-box" style="border-left-color: var(--warning-color);">
                    <div class="d-flex justify-content-between mb-2">
                        <strong><?php echo htmlspecialchars($balance['name']); ?></strong>
                        <span class="badge bg-info">
                            <?php echo ($balance['allocated'] - $balance['used']) . '/' . $balance['allocated']; ?>
                        </span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar bg-info" style="width: <?php echo $percent; ?>%"></div>
                    </div>
                    <small class="text-muted mt-1 d-block">This leave does not count towards your annual core balance.</small>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
