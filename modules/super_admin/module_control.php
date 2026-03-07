<?php
// Fetch all modules
$modules_res = $conn->query("SELECT * FROM modules ORDER BY display_name ASC");
?>

<div class="section-card border-0 shadow-none bg-transparent position-relative">
    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-5 pb-3">
        <div class="d-flex align-items-center gap-3 mb-3 mb-md-0">
            <div class="icon-box p-3 rounded-circle shadow-lg text-white animate__animated animate__fadeInLeft">
                <i class="fas fa-cube fs-3"></i>
            </div>
            <div class="animate__animated animate__fadeInLeft animate__delay-1s">
                <h3 class="mb-1 fw-800 text-gradient">Module Control</h3>
                <p class="text-secondary mb-0 fw-medium">Manage system capabilities & features</p>
            </div>
        </div>
        <div class="animate__animated animate__fadeInRight animate__delay-1s">
            <span class="badge bg-white text-dark shadow-sm py-2 px-3 rounded-pill border fw-semibold">
                <i class="fas fa-bolt text-warning me-1"></i> Instant Updates
            </span>
        </div>
    </div>

    <div id="moduleAlert" class="alert d-none rounded-4 shadow-lg border-0 mb-5 overflow-hidden"></div>

    <!-- Modules Grid -->
    <div class="row g-4">
        <?php if ($modules_res && $modules_res->num_rows > 0): ?>
            <?php 
            $delay = 0;
            while($module = $modules_res->fetch_assoc()): 
                $isActive = $module['status'] == 'active';
                $delay += 0.1;
            ?>
                <div class="col-xl-3 col-lg-4 col-md-6 animate__animated animate__fadeInUp" style="animation-delay: <?php echo $delay; ?>s;">
                    <div class="card module-card h-100 border-0 shadow-sm <?php echo $isActive ? 'active-module' : 'inactive-module'; ?>">
                        <div class="card-body p-4 d-flex flex-column position-relative z-1">
                            <div class="d-flex justify-content-between align-items-start mb-4">
                                <div class="module-icon-wrapper shadow-sm">
                                    <i class="fas <?php echo $module['icon']; ?> fa-fw"></i>
                                </div>
                                <div class="form-check form-switch custom-switch">
                                    <input class="form-check-input module-toggle" 
                                           type="checkbox" 
                                           role="switch" 
                                           id="module_<?php echo $module['id']; ?>" 
                                           data-module-id="<?php echo $module['id']; ?>"
                                           data-module-name="<?php echo htmlspecialchars($module['display_name'] ?? ''); ?>"
                                           <?php echo $isActive ? 'checked' : ''; ?>>
                                </div>
                            </div>
                            
                            <h5 class="fw-bold text-dark mb-2 letter-spacing-tight"><?php echo htmlspecialchars($module['display_name'] ?? ''); ?></h5>
                            <p class="text-muted small mb-4 flex-grow-1 opacity-75" style="line-height: 1.6; min-height: 48px;">
                                <?php echo htmlspecialchars($module['description'] ?? ''); ?>
                            </p>
                            
                            <div class="pt-3 border-top border-light-subtle d-flex justify-content-between align-items-center mt-auto">
                                <span class="status-indicator <?php echo $isActive ? 'text-success' : 'text-danger'; ?> fw-bold small" id="status_text_<?php echo $module['id']; ?>">
                                    <span class="status-dot me-2"></span>
                                    <span><?php echo $isActive ? 'Active' : 'Disabled'; ?></span>
                                </span>
                                <span class="badge bg-light text-secondary border rounded-pill px-2 py-1" style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.5px;">System</span>
                            </div>
                        </div>
                        <!-- Glass Background & Glow -->
                        <div class="glass-overlay"></div>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="empty-state text-center py-5 rounded-4 bg-white shadow-sm border border-dashed">
                    <div class="mb-3 text-muted opacity-25">
                        <i class="fas fa-cubes fa-4x"></i>
                    </div>
                    <h4 class="fw-bold text-dark">No Modules Available</h4>
                    <p class="text-muted">Currently, there are no modules registered in the system.</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Modern & Premium Styling */
:root {
    --card-bg: #ffffff;
    --card-hover-transform: translateY(-8px);
    --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
    --icon-gradient: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
    --success-color: #10b981;
    --danger-color: #ef4444;
}

.text-gradient {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.icon-box {
    background: var(--primary-gradient);
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.fw-800 { font-weight: 800; }
.letter-spacing-tight { letter-spacing: -0.5px; }

/* Module Card */
.module-card {
    background: var(--card-bg);
    border-radius: 20px;
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    position: relative;
    overflow: hidden;
}

.module-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--primary-gradient);
    opacity: 0;
    transition: opacity 0.3s ease;
}

.module-card:hover {
    transform: var(--card-hover-transform);
    box-shadow: 0 20px 40px -5px rgba(0, 0, 0, 0.1), 0 10px 20px -5px rgba(0, 0, 0, 0.04) !important;
}

.module-card:hover::before {
    opacity: 1;
}

/* Active State Styling */
.module-card.active-module .module-icon-wrapper {
    background: var(--icon-gradient);
    color: white;
    transform: scale(1.1);
}

.module-card.inactive-module {
    background: #f9fafb;
}

.module-card.inactive-module .module-icon-wrapper {
    background: #e5e7eb;
    color: #9ca3af;
    box-shadow: none !important;
}

.module-card.inactive-module h5,
.module-card.inactive-module p {
    filter: grayscale(1);
    opacity: 0.6;
}

/* Icon Wrapper */
.module-icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    transition: all 0.4s ease;
}

/* Status Dot */
.status-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: currentColor;
    position: relative;
}

.text-success .status-dot::after {
    content: '';
    position: absolute;
    top: -4px;
    left: -4px;
    right: -4px;
    bottom: -4px;
    border-radius: 50%;
    background-color: currentColor;
    opacity: 0.2;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { transform: scale(1); opacity: 0.2; }
    50% { transform: scale(1.5); opacity: 0; }
    100% { transform: scale(1); opacity: 0; }
}

/* Custom Switch */
.custom-switch .form-check-input {
    width: 3.2em;
    height: 1.7em;
    cursor: pointer;
    background-color: #e5e7eb;
    border: 2px solid #e5e7eb;
    transition: background-color 0.3s ease, border-color 0.3s ease;
}

.custom-switch .form-check-input:checked {
    background-color: #10b981;
    border-color: #10b981;
}

.custom-switch .form-check-input:focus {
    box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
}

/* Animate.css for subtle entry (if not included, simple fallback) */
@media (prefers-reduced-motion: no-preference) {
    .animate__fadeInUp {
        animation-name: fadeInUp;
        animation-duration: 0.6s;
        animation-fill-mode: both;
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translate3d(0, 30px, 0);
        }
        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }
}
</style>

<script>
document.querySelectorAll('.module-toggle').forEach(toggle => {
    toggle.addEventListener('change', function() {
        const moduleId = this.dataset.moduleId;
        const moduleName = this.dataset.moduleName;
        const isChecked = this.checked;
        const status = isChecked ? 'active' : 'inactive';
        const card = this.closest('.module-card');
        const statusText = document.getElementById('status_text_' + moduleId);
        const alertBox = document.getElementById('moduleAlert');
        
        // Optimistic UI Update with Animation
        if (isChecked) {
            card.classList.remove('inactive-module');
            card.classList.add('active-module');
            statusText.className = 'status-indicator text-success fw-bold small';
            statusText.innerHTML = '<span class="status-dot me-2"></span><span>Active</span>';
        } else {
            card.classList.remove('active-module');
            card.classList.add('inactive-module');
            statusText.className = 'status-indicator text-danger fw-bold small';
            statusText.innerHTML = '<span class="status-dot me-2"></span><span>Disabled</span>';
        }

        this.disabled = true;
        const originalOpacity = card.style.opacity;
        card.style.opacity = '0.7';

        const formData = new FormData();
        formData.append('action', 'update_module_status');
        formData.append('module_id', moduleId);
        formData.append('status', status);

        fetch('process_modules.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            this.disabled = false;
            card.style.opacity = originalOpacity;
            
            if (data.success) {
                // Show floating success toast style alert
                alertBox.className = 'alert alert-dismissible fade show border-0 shadow-lg d-flex align-items-center justify-content-between text-white';
                alertBox.style.background = isChecked ? 'linear-gradient(to right, #10b981, #059669)' : 'linear-gradient(to right, #ef4444, #b91c1c)';
                alertBox.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-white bg-opacity-25 p-2 me-3">
                            <i class="fas ${isChecked ? 'fa-check' : 'fa-ban'} text-white"></i>
                        </div>
                        <div>
                            <h6 class="mb-0 fw-bold">${moduleName} ${isChecked ? 'Enabled' : 'Disabled'}</h6>
                            <small class="opacity-75">System capabilities updated successfully.</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                `;
                alertBox.classList.remove('d-none');
                
                // Auto dismiss
                setTimeout(() => {
                    alertBox.classList.add('d-none');
                }, 3000);
            } else {
                // Revert UI on failure
                this.checked = !isChecked; // Toggle back
                
                // Revert visual classes
                if (!isChecked) { // Re-enable visuals
                    card.classList.remove('inactive-module'); card.classList.add('active-module');
                    statusText.className = 'status-indicator text-success fw-bold small'; statusText.innerHTML = '<span class="status-dot me-2"></span><span>Active</span>';
                } else { // Re-disable visuals
                    card.classList.remove('active-module'); card.classList.add('inactive-module');
                    statusText.className = 'status-indicator text-danger fw-bold small'; statusText.innerHTML = '<span class="status-dot me-2"></span><span>Disabled</span>';
                }
                
                alertBox.className = 'alert alert-danger shadow-lg border-0 text-white';
                alertBox.innerHTML = `<i class="fas fa-exclamation-triangle me-2"></i> ${data.message}`;
                alertBox.classList.remove('d-none');
            }
        })
        .catch(error => {
            this.disabled = false;
            this.checked = !isChecked;
            card.style.opacity = originalOpacity;
            console.error('Error:', error);
            alertBox.className = 'alert alert-danger shadow-lg border-0 text-white';
            alertBox.innerHTML = '<i class="fas fa-server me-2"></i> A system error occurred. Please try again.';
            alertBox.classList.remove('d-none');
        });
    });
});
</script>
