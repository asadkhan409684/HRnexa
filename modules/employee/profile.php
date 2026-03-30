<?php
// Fetch Emergency Contact Information
$emergency_query = "SELECT * FROM employee_emergency_contacts WHERE employee_id = $employee_id LIMIT 1";
$emergency_result = $conn->query($emergency_query);
$emergency_data = ($emergency_result && $emergency_result->num_rows > 0) ? $emergency_result->fetch_assoc() : null;
?>
<!-- Profile Section -->
<div id="profile-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>My Profile</h1>
    </div>
    <div class="section-card">
        <div class="section-title d-flex justify-content-between align-items-center">
            <div><i class="fas fa-user"></i> Personal Information</div>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editEmergencyModal">
                <i class="fas fa-edit"></i> Edit Information
            </button>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Full Name</strong></label>
                <p><?php echo $emp_data ? htmlspecialchars($emp_data['first_name'] . ' ' . $emp_data['last_name']) : htmlspecialchars($user_name); ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Employee ID</strong></label>
                <p><?php echo $emp_data ? htmlspecialchars($emp_data['employee_code']) : 'N/A'; ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Email</strong></label>
                <p><?php echo htmlspecialchars($user_email); ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Phone</strong></label>
                <p><?php echo $emp_data ? htmlspecialchars($emp_data['phone'] ?? 'Not set') : 'N/A'; ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Date of Birth</strong></label>
                <p><?php echo ($emp_data && $emp_data['date_of_birth']) ? date('d-M-Y', strtotime($emp_data['date_of_birth'])) : 'Not set'; ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Gender</strong></label>
                <p><?php echo $emp_data ? ucfirst(htmlspecialchars($emp_data['gender'] ?? 'Not set')) : 'N/A'; ?></p>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label"><strong>Address</strong></label>
                <p><?php echo $emp_data ? nl2br(htmlspecialchars($emp_data['address'] ?? 'Not set')) : 'N/A'; ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Department</strong></label>
                <p><?php echo $emp_data ? htmlspecialchars($emp_data['dept_name'] ?? 'Not Assigned') : 'N/A'; ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Designation</strong></label>
                <p><?php echo $emp_data ? htmlspecialchars($emp_data['desig_name'] ?? 'Not Assigned') : 'N/A'; ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Joining Date</strong></label>
                <p><?php echo $emp_data ? date('d-M-Y', strtotime($emp_data['hire_date'])) : 'N/A'; ?></p>
            </div>
                <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Employment Status</strong></label>
                <p><span class="badge bg-<?php echo ($emp_data && $emp_data['employment_status'] == 'active') ? 'success' : 'warning'; ?>">
                    <?php echo $emp_data ? ucfirst(htmlspecialchars($emp_data['employment_status'])) : 'N/A'; ?>
                </span></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Team Leader</strong></label>
                <p><?php echo $emp_data && !empty($emp_data['manager_name']) ? htmlspecialchars($emp_data['manager_name']) : 'N/A / Self'; ?></p>
            </div>
        </div>

        <div class="section-title mt-4">
            <i class="fas fa-phone-alt"></i>
            Emergency Contact Information
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Contact Name</strong></label>
                <p><?php echo $emergency_data ? htmlspecialchars($emergency_data['name']) : 'Not set'; ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Relationship</strong></label>
                <p><?php echo $emergency_data ? htmlspecialchars($emergency_data['relationship']) : 'Not set'; ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Phone</strong></label>
                <p><?php echo $emergency_data ? htmlspecialchars($emergency_data['phone']) : 'Not set'; ?></p>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label"><strong>Email</strong></label>
                <p><?php echo $emergency_data && $emergency_data['email'] ? htmlspecialchars($emergency_data['email']) : 'Not set'; ?></p>
            </div>
            <div class="col-md-12">
                <label class="form-label"><strong>Address</strong></label>
                <p><?php echo $emergency_data && $emergency_data['address'] ? nl2br(htmlspecialchars($emergency_data['address'])) : 'Not set'; ?></p>
            </div>
        </div>
    </div>
</div>

<!-- Emergency Contact Modal -->
<div class="modal fade" id="editEmergencyModal" tabindex="-1" aria-labelledby="editEmergencyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="editEmergencyModalLabel">Emergency Contact Information</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="emergencyContactForm">
                <div class="modal-body">
                    <input type="hidden" name="action" value="save_emergency_contact">
                    <input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Contact Name</label>
                        <input type="text" class="form-control" name="contact_name" value="<?php echo $emergency_data ? htmlspecialchars($emergency_data['name']) : ''; ?>" placeholder="Full Name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Relationship</label>
                        <input type="text" class="form-control" name="relationship" value="<?php echo $emergency_data ? htmlspecialchars($emergency_data['relationship']) : ''; ?>" placeholder="e.g. Spouse, Father, Mother" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Phone Number</label>
                        <input type="text" class="form-control" name="contact_phone" value="<?php echo $emergency_data ? htmlspecialchars($emergency_data['phone']) : ''; ?>" placeholder="Contact number" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email (Optional)</label>
                        <input type="email" class="form-control" name="contact_email" value="<?php echo $emergency_data ? htmlspecialchars($emergency_data['email']) : ''; ?>" placeholder="Email address">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Address (Optional)</label>
                        <textarea class="form-control" name="contact_address" rows="3" placeholder="Residential address"><?php echo $emergency_data ? htmlspecialchars($emergency_data['address']) : ''; ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
