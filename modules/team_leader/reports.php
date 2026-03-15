<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) { header("Location: " . BASE_URL . "login"); exit(); }
?>
<div id="reports-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Reports</h1>
    </div>
    <div class="section-card">
        <div class="text-center py-5">
            <i class="fas fa-chart-bar fa-3x text-muted mb-3"></i>
            <p class="text-muted">Advanced reporting features are coming soon.</p>
        </div>
    </div>
</div>