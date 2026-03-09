<div id="add-employee-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Add New Employee</h1>
    </div>
    <div class="row">
        <div class="col-lg-8">
            <div class="section-card">
                <div class="section-title">
                    <i class="fas fa-user-plus"></i>
                    Employee Information
                </div>
                <form action ="#" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label">First Name</label>
                            <input type="text" class="form-control" name="first_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label">Last Name</label>
                            <input type="text" class="form-control" name="last_name" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" name="email" required>
                        </div>
                        <div class="col-md-6">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" name="phone">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">Confirm Password</label>
                            <input type="password" class="form-control" name="confirm_password" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" name = "date_of_birth">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Gender</label>
                            <select class="form-select" name="gender">
                                <option value= "">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Department</label>
                            <select class="form-select" name="department_id" required>
                                <option value="">Select Department</option>
                                <?php while($dept = $dept_result->fetch_assoc()): ?>
                                <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Designation</label>
                            <select class="form-select" name="designation_id" required>
                                <option value="">Select Designation</option>
                                <?php while($desig = $desig_result->fetch_assoc()): ?>
                                <option value="<?php echo $desig['id']; ?>"><?php echo htmlspecialchars($desig['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Team Leader (Optional)</label>
                            <select class="form-select" name="team_leader_id">
                                <option value="">Select Team Leader</option>
                                <?php 
                                if ($tl_result && $tl_result->num_rows > 0):
                                    while($tl = $tl_result->fetch_assoc()): 
                                ?>
                                <option value="<?php echo $tl['id']; ?>">
                                    <?php echo htmlspecialchars($tl['first_name'] . ' ' . $tl['last_name'] . ' (' . $tl['employee_code'] . ')'); ?>
                                </option>
                                <?php 
                                    endwhile; 
                                endif;
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Joining Date</label>
                            <input type="date" class="form-control" name="hire_date">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Employment Type</label>
                            <select class="form-select" name="employment_type">
                                <option value="">Select Type</option>
                                <option value="Full-time">Full-time</option>
                                <option value="Part-time">Part-time</option>
                                <option value="Contract">Contract</option>
                                <option value="Intern">Intern</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="save_employee" class="btn btn-primary"><i class="fas fa-save"></i> Save Employee</button>
                </form>
            </div>
        </div>
    </div>
</div>
