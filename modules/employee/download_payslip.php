<?php
session_start();
include_once '../../app/Config/database.php';

// Authentication Check
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true || $_SESSION['user_role'] != 5) {
    header("Location: " . BASE_URL . "views/auth/login.php");
    exit();
}

// Get Payslip ID
$payslip_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$user_email = $_SESSION['user_email'];

// Fetch Payslip Data securely ensuring it belongs to the logged-in user
$query = "SELECT p.*, pr.month, pr.year, e.first_name, e.last_name, e.employee_code, d.name as dept_name, des.name as desig_name 
          FROM payslips p 
          JOIN payroll_runs pr ON p.payroll_run_id = pr.id 
          JOIN employees e ON p.employee_id = e.id 
          LEFT JOIN departments d ON e.department_id = d.id 
          LEFT JOIN designations des ON e.designation_id = des.id
          WHERE p.id = ? AND e.email = ? AND p.status = 'approved'";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $payslip_id, $user_email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Payslip not found or access denied.");
}

$payslip = $result->fetch_assoc();
$breakdown = json_decode($payslip['breakdown'], true);
$monthName = date('F', mktime(0, 0, 0, $payslip['month'], 10));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip - <?php echo $monthName . ' ' . $payslip['year']; ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f5f5f5; padding: 20px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .payslip-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            border-radius: 5px;
        }
        .header { border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px; }
        .company-name { font-size: 24px; font-weight: bold; color: #1e5490; }
        .payslip-title { font-size: 20px; text-transform: uppercase; color: #555; margin-top: 10px; }
        .emp-details-table td { padding: 5px 10px; }
        .emp-label { font-weight: 600; color: #555; width: 150px; }
        .salary-table th { background-color: #f8f9fa; }
        .total-row { font-weight: bold; background-color: #f8f9fa; }
        .net-pay-section {
            background: #1e5490;
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .net-pay-label { font-size: 16px; font-weight: 500; }
        .net-pay-amount { font-size: 24px; font-weight: 700; }
        
        @media print {
            body { background: white; padding: 0; }
            .payslip-container { box-shadow: none; padding: 0; width: 100%; max-width: 100%; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="payslip-container">
        <!-- Header -->
        <div class="row header align-items-center">
            <div class="col-6">
                <div class="company-name">HRnexa</div>
                <div class="text-muted">Human Resource Management</div>
            </div>
            <div class="col-6 text-end">
                <div class="payslip-title">Payslip</div>
                <div><?php echo $monthName . ' ' . $payslip['year']; ?></div>
            </div>
        </div>

        <!-- Employee Details -->
        <div class="row mb-4">
            <div class="col-md-12">
                <table class="table table-borderless emp-details-table mb-0">
                    <tr>
                        <td class="emp-label">Employee Name:</td>
                        <td><?php echo $payslip['first_name'] . ' ' . $payslip['last_name']; ?></td>
                        <td class="emp-label">Employee ID:</td>
                        <td><?php echo $payslip['employee_code']; ?></td>
                    </tr>
                    <tr>
                        <td class="emp-label">Department:</td>
                        <td><?php echo $payslip['dept_name']; ?></td>
                        <td class="emp-label">Designation:</td>
                        <td><?php echo $payslip['desig_name']; ?></td>
                    </tr>
                    <tr>
                        <td class="emp-label">Date of Joining:</td>
                        <td>-</td> <!-- Needs to be fetched if required, skipping for now -->
                        <td class="emp-label">Generated On:</td>
                        <td><?php echo date('d M Y', strtotime($payslip['generated_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Attendance Summary -->
        <div class="mb-4">
            <h6 class="border-bottom pb-2 mb-3">Attendance Summary</h6>
            <div class="row text-center" style="font-size: 0.9rem;">
                <div class="col">
                    <div class="text-muted mb-1">Total Days</div>
                    <strong>30</strong>
                </div>
                <div class="col">
                    <div class="text-muted mb-1">Present</div>
                    <strong>30</strong> <!-- Placeholder, logic needed if exact attendance days per month varies -->
                </div>
                <div class="col">
                    <div class="text-muted mb-1">Effective Absent</div>
                    <strong class="text-danger"><?php echo $breakdown['attendance_summary']['effective_absent_total'] ?? 0; ?></strong>
                </div>
                <div class="col">
                    <div class="text-muted mb-1">Late Days</div>
                    <strong><?php echo $breakdown['attendance_summary']['total_late'] ?? 0; ?></strong>
                </div>
            </div>
        </div>

        <!-- Salary Details -->
        <div class="row">
            <div class="col-6">
                <h6 class="border-bottom pb-2 mb-3">Earnings</h6>
                <table class="table table-sm salary-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Basic Salary</td>
                            <td class="text-end"><?php echo number_format($breakdown['summary']['basic'], 2); ?></td>
                        </tr>
                        <?php if (isset($breakdown['allowances'])): ?>
                            <?php foreach ($breakdown['allowances'] as $allowance): ?>
                            <tr>
                                <td><?php echo $allowance['name']; ?></td>
                                <td class="text-end"><?php echo number_format($allowance['amount'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        
                        <tr class="total-row">
                            <td>Gross Earnings</td>
                            <td class="text-end">
                                <?php 
                                $gross = $breakdown['summary']['basic'] + ($breakdown['summary']['total_allowances'] ?? 0);
                                echo number_format($gross, 2); 
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <div class="col-6">
                <h6 class="border-bottom pb-2 mb-3">Deductions</h6>
                <table class="table table-sm salary-table">
                    <thead>
                        <tr>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                         <?php if (isset($breakdown['deductions'])): ?>
                            <?php foreach ($breakdown['deductions'] as $deduction): ?>
                            <tr>
                                <td><?php echo $deduction['name']; ?></td>
                                <td class="text-end"><?php echo number_format($deduction['amount'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                        <!-- If we have tax or other fixed deductions not in array, they would go here -->
                        
                        <tr class="total-row">
                            <td>Total Deductions</td>
                            <td class="text-end"><?php echo number_format($breakdown['summary']['total_deductions'] ?? 0, 2); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Net Pay -->
        <div class="net-pay-section">
            <div class="net-pay-label">NET PAYABLE AMOUNT</div>
            <div class="net-pay-amount"><?php echo number_format($payslip['net_salary'], 2); ?></div>
        </div>
        <div class="text-end mt-2 text-muted fst-italic" style="font-size: 0.85rem;">
            (Amount in currency)
        </div>
        
        <div class="mt-5 pt-4 text-center text-muted no-print">
            <button onclick="window.print()" class="btn btn-primary btn-lg me-2"><i class="fas fa-print"></i> Print Payslip</button>
            <button onclick="window.close()" class="btn btn-secondary btn-lg">Close</button>
        </div>
    </div>

</body>
</html>
