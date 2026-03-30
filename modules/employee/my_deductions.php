<?php
// Get user information from session
$user_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';

// Fetch employee ID from database
$emp_data_query = "SELECT id FROM employees WHERE email = ?";
$emp_stmt = $conn->prepare($emp_data_query);
$emp_stmt->bind_param("s", $user_email);
$emp_stmt->execute();
$emp_result = $emp_stmt->get_result();
$emp_data = $emp_result->fetch_assoc();
$emp_id = $emp_data['id'] ?? 0;
$emp_stmt->close();

// Fetch current employee deductions for the logged-in employee
$my_assignments_query = "SELECT ed.*, d.name as deduction_name, d.type, d.amount_type
                    FROM employee_deductions ed
                    JOIN deductions d ON ed.deduction_id = d.id
                    WHERE ed.employee_id = ?
                    ORDER BY ed.effective_from DESC";
$stmt = $conn->prepare($my_assignments_query);
$stmt->bind_param("i", $emp_id);
$stmt->execute();
$my_assignments_res = $stmt->get_result();
?>

<div id="my-deductions-section" class="section-content" style="display:none;">
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="mb-0">My Deductions</h4>
            <p class="text-muted small">Overview of active deductions assigned to your profile</p>
        </div>
    </div>

    <div class="section-card">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Deduction Name</th>
                        <th>Type</th>
                        <th>Amount / Rate</th>
                        <th>Effective From</th>
                        <th>Effective To</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($my_assignments_res && $my_assignments_res->num_rows > 0): ?>
                        <?php while($asg = $my_assignments_res->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($asg['deduction_name']); ?></strong></td>
                            <td><span class="text-primary"><?php echo htmlspecialchars($asg['type'] ?? '-'); ?></span></td>
                            <td>
                                <span class="fw-bold"><?php echo number_format($asg['amount'], 2); ?></span>
                                <small class="text-muted"><?php echo ($asg['amount_type'] === 'percentage') ? '%' : '(Fixed)'; ?></small>
                            </td>
                            <td><?php echo date('d M, Y', strtotime($asg['effective_from'])); ?></td>
                            <td><?php echo $asg['effective_to'] ? date('d M, Y', strtotime($asg['effective_to'])) : '<span class="text-muted">Ongoing</span>'; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">You have no active deductions assigned at the moment.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
