<div id="expenses-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>Expense Management</h1>
    </div>
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-receipt"></i>
            Expense Claims & Reimbursements
        </div>
        <div class="data-table">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Amount</th>
                        <th>Category</th>
                        <th>Claim Date</th>
                        <th>Description</th>
                        <th>Receipt</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($expenses_result && $expenses_result->num_rows > 0): ?>
                        <?php while($exp = $expenses_result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($exp['first_name'] . ' ' . $exp['last_name']); ?></strong><br>
                                <small class="text-muted"><?php echo htmlspecialchars($exp['employee_code']); ?></small>
                            </td>
                            <td><?php echo number_format($exp['amount'], 2); ?></td>
                            <td><?php echo htmlspecialchars($exp['category_name']); ?></td>
                            <td><?php echo date('d M Y', strtotime($exp['claim_date'])); ?></td>
                            <td><small><?php echo htmlspecialchars($exp['description'] ?? ''); ?></small></td>
                            <td>
                                <?php if ($exp['receipt_path']): ?>
                                    <a href="<?php echo htmlspecialchars($exp['receipt_path']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file-invoice"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">No Receipt</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $exp_badge = 'bg-info';
                                    if ($exp['status'] == 'approved' || $exp['status'] == 'paid') $exp_badge = 'bg-success';
                                    if ($exp['status'] == 'submitted') $exp_badge = 'bg-warning';
                                    if ($exp['status'] == 'rejected') $exp_badge = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $exp_badge; ?>"><?php echo ucfirst($exp['status']); ?></span>
                            </td>
                            <td>
                                <?php if ($exp['status'] == 'submitted'): ?>
                                    <div class="d-flex gap-1">
                                        <form method="POST" onsubmit="return confirm('Approve this expense?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="claim_id" value="<?php echo $exp['id']; ?>">
                                            <button type="submit" name="approve_expense" class="btn btn-sm btn-success" title="Approve">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        </form>
                                        <form method="POST" onsubmit="return confirm('Reject this expense?')">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <input type="hidden" name="claim_id" value="<?php echo $exp['id']; ?>">
                                            <button type="submit" name="reject_expense" class="btn btn-sm btn-danger" title="Reject">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="8" class="text-center">No expense claims found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
