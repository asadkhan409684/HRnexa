            <!-- Payroll Section -->
            <div id="payslips-section" class="section-content" style="display: none;">
                <div class="page-header"><h1>My Payslips</h1></div>
                <div class="section-card">
                    <div class="section-title"><i class="fas fa-receipt"></i> Salary Slips</div>
                    <div class="data-table">
                        <table class="table table-striped">
                            <thead><tr><th>Month/Year</th><th>Basic</th><th>Gross</th><th>Net</th><th>Action</th></tr></thead>
                            <tbody>
                                <?php if ($payslips_result && $payslips_result->num_rows > 0): while($ps = $payslips_result->fetch_assoc()): ?>
                                <tr><td><?php echo date("F Y", mktime(0, 0, 0, $ps['month'], 10, $ps['year'])); ?></td><td>$<?php echo number_format($ps['basic_salary'], 2); ?></td><td>$<?php echo number_format($ps['gross_salary'], 2); ?></td><td>$<?php echo number_format($ps['net_salary'], 2); ?></td><td><button class="btn btn-sm btn-primary"><i class="fas fa-download"></i></button></td></tr>
                                <?php endwhile; else: echo "<tr><td colspan='5' class='text-center text-muted'>No payslips found.</td></tr>"; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
