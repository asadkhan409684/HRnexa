<!-- Payslips Section -->
<div id="payslips-section" class="section-content" style="display: none;">
    <div class="page-header">
        <h1>My Payslips</h1>
    </div>
    
    <div class="section-card">
        <div class="section-title">
            <i class="fas fa-file-invoice-dollar"></i>
            Payslip History
        </div>
        
        <div class="data-table">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Month / Year</th>
                        <th>Basic Salary</th>
                        <th>Net Salary</th>
                        <th>Generated On</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($payslips_result && $payslips_result->num_rows > 0):
                        // Reset pointer in case it was used elsewhere
                        $payslips_result->data_seek(0);
                        while($row = $payslips_result->fetch_assoc()): 
                            $monthName = date('F', mktime(0, 0, 0, $row['month'], 10));
                    ?>
                    <tr>
                        <td>
                            <strong><?php echo $monthName . ' ' . $row['year']; ?></strong>
                        </td>
                        <td><?php echo number_format($row['basic_salary'], 2); ?></td>
                        <td>
                            <strong class="text-success"><?php echo number_format($row['net_salary'], 2); ?></strong>
                        </td>
                        <td><?php echo date('d M Y', strtotime($row['generated_at'])); ?></td>
                        <td>
                            <a href="download_payslip.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary" target="_blank">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </td>
                    </tr>
                    <?php 
                        endwhile; 
                    else:
                    ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="fas fa-file-invoice mb-2" style="font-size: 24px;"></i>
                            <p class="mb-0">No payslips available yet.</p>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
