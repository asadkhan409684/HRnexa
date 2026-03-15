<?php
session_start();
if (!isset($_SESSION['user_logged_in'])) { header("Location: " . BASE_URL . "login"); exit(); }
?>
<div id="shift-schedule-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Shift Schedule</h1>
    </div>
    <div class="section-card">
        <div class="text-center py-5">
            <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
            <p class="text-muted">Shift scheduling module is coming soon.</p>
        </div>
    </div>
</div>