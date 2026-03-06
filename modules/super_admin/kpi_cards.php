<div class="row">
    <!-- Total Employees -->
    <div class="col-md-3">
        <div class="kpi-card primary">
            <div class="kpi-icon">
                <i class="fas fa-users"></i>
            </div>
            <div class="kpi-value"><?php echo number_format($total_employees); ?></div>
            <div class="kpi-label">Total Employees</div>
            <div class="kpi-change positive">
                <i class="fas fa-arrow-up"></i> Active Workforce
            </div>
        </div>
    </div>

    <!-- Active Admins -->
    <div class="col-md-3">
        <div class="kpi-card success">
            <div class="kpi-icon">
                <i class="fas fa-user-shield"></i>
            </div>
            <div class="kpi-value"><?php echo number_format($active_admins); ?></div>
            <div class="kpi-label">Active Admins</div>
            <div class="kpi-change positive">
                <i class="fas fa-check-circle"></i> System Managers
            </div>
        </div>
    </div>

    <!-- Locked Accounts -->
    <div class="col-md-3">
        <div class="kpi-card danger">
            <div class="kpi-icon">
                <i class="fas fa-user-lock"></i>
            </div>
            <div class="kpi-value"><?php echo number_format($locked_accounts); ?></div>
            <div class="kpi-label">Locked Accounts</div>
            <div class="kpi-change negative">
                <i class="fas fa-exclamation-circle"></i> Action Required
            </div>
        </div>
    </div>

    <!-- Pending Approvals -->
    <div class="col-md-3">
        <div class="kpi-card warning">
            <div class="kpi-icon">
                <i class="fas fa-clock"></i>
            </div>
            <div class="kpi-value"><?php echo number_format($pending_approvals); ?></div>
            <div class="kpi-label">Pending Approvals</div>
            <div class="kpi-change warning">
                <i class="fas fa-hourglass-half"></i> Leave Requests
            </div>
        </div>
    </div>
</div>
