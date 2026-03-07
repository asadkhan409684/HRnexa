<?php
// Fetch system settings from database
$settings_res = $conn->query("SELECT * FROM system_settings ORDER BY category, `key` ASC");
$settings = [];
while ($row = $settings_res->fetch_assoc()) {
    $settings[$row['category']][] = $row;
}
?>

<div class="section-card">
    <div class="section-title">
        <i class="fas fa-cogs"></i> System Settings
    </div>
    
    <div id="settingsAlert" class="alert d-none"></div>

    <form id="systemSettingsForm">
        <div class="row">
            <?php foreach ($settings as $category => $categorySettings): ?>
                <div class="col-12 mb-4">
                    <h6 class="text-primary border-bottom pb-2 mb-3 text-uppercase" style="font-size: 0.85rem; letter-spacing: 1px; font-weight: 700;">
                        <?php echo htmlspecialchars($category ?? ''); ?> Settings
                    </h6>
                    <div class="row g-3">
                        <?php foreach ($categorySettings as $setting): ?>
                            <div class="col-md-6">
                                <label for="setting_<?php echo $setting['id']; ?>" class="form-label" style="font-size: 0.9rem; font-weight: 600;">
                                    <?php echo ucwords(str_replace('_', ' ', $setting['key'])); ?>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="setting_<?php echo $setting['id']; ?>" 
                                       name="settings[<?php echo $setting['id']; ?>]" 
                                       value="<?php echo htmlspecialchars($setting['value'] ?? ''); ?>"
                                       placeholder="<?php echo htmlspecialchars($setting['description'] ?? ''); ?>">
                                <div class="form-text" style="font-size: 0.75rem;"><?php echo htmlspecialchars($setting['description'] ?? ''); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <hr class="my-4">

        <div class="d-flex justify-content-end gap-2">
            <button type="reset" class="btn btn-light">Reset Changes</button>
            <button type="submit" class="btn btn-primary" id="saveSettingsBtn">
                <i class="fas fa-save me-1"></i> Save All Settings
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('systemSettingsForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('saveSettingsBtn');
    const alertBox = document.getElementById('settingsAlert');
    const originalText = btn.innerHTML;
    
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...';
    
    const formData = new FormData(this);
    formData.append('action', 'update_settings');

    fetch('process_settings.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        
        alertBox.classList.remove('d-none', 'alert-success', 'alert-danger');
        alertBox.classList.add(data.success ? 'alert-success' : 'alert-danger');
        alertBox.textContent = data.message;
        
        if (data.success) {
            alertBox.classList.remove('d-none');
            setTimeout(() => {
                alertBox.classList.add('d-none');
            }, 3000);
        }
    })
    .catch(error => {
        btn.disabled = false;
        btn.innerHTML = originalText;
        alertBox.classList.remove('d-none', 'alert-success');
        alertBox.classList.add('alert-danger');
        alertBox.textContent = 'An error occurred while saving settings.';
        console.error('Error:', error);
    });
});
</script>
