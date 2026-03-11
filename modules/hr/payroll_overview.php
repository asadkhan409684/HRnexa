<?php
/**
 * Payroll Testing - Complete System Overview
 */

require_once(__DIR__ . '/../../app/Config/database.php');

echo "\n";
echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║          PAYROLL SYSTEM - DEMO DATA OVERVIEW                  ║\n";
echo "║                    February 2026                               ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

// 1. ATTENDANCE OVERVIEW
echo "📋 ATTENDANCE RECORDS\n";
echo "─────────────────────────────────────────────────────────────\n";

$att_query = "SELECT status, COUNT(*) as cnt FROM attendance WHERE DATE_FORMAT(date, '%Y-%m') = '2026-02' GROUP BY status";
$att_result = $conn->query($att_query);

$total_att = 0;
while ($row = $att_result->fetch_assoc()) {
    $status_icon = ['present' => '✓', 'late' => '⏱', 'absent' => '✗', 'half_day' => '◐'][$row['status']] ?? '•';
    printf("  %s %-12s: %4d records\n", $status_icon, ucfirst($row['status']), $row['cnt']);
    $total_att += $row['cnt'];
}

echo "  ─────────────────────────────────\n";
printf("  Total Attendance Records: %d\n\n", $total_att);

// 2. EMPLOYEE BREAKDOWN
echo "👥 EMPLOYEE BREAKDOWN\n";
echo "─────────────────────────────────────────────────────────────\n";

$emp_query = "SELECT 
    e.id, 
    CONCAT(e.first_name, ' ', e.last_name) as name,
    (SELECT COUNT(*) FROM attendance WHERE employee_id = e.id AND DATE_FORMAT(date, '%Y-%m') = '2026-02') as att_days,
    (SELECT SUM(total_days) FROM leave_applications WHERE employee_id = e.id AND status = 'approved' AND DATE_FORMAT(start_date, '%Y-%m') = '2026-02') as leave_days,
    COALESCE((SELECT basic_salary FROM payslips WHERE employee_id = e.id LIMIT 1), 0) as basic,
    COALESCE((SELECT SUM(amount) FROM employee_allowances WHERE employee_id = e.id AND effective_from <= '2026-02-28' AND (effective_to IS NULL OR effective_to >= '2026-02-01')), 0) as total_allow,
    COALESCE((SELECT SUM(amount) FROM employee_deductions WHERE employee_id = e.id AND effective_from <= '2026-02-28' AND (effective_to IS NULL OR effective_to >= '2026-02-01')), 0) as total_ded
FROM employees e
WHERE e.is_active = 1
LIMIT 6";

$emp_result = $conn->query($emp_query);

printf("%-15s | %4s | %4s | %10s | %8s | %8s\n", "Employee", "Att", "Lvs", "Basic", "Allow", "Ded");
echo "───────────────────────────────────────────────────────────────\n";

while ($emp = $emp_result->fetch_assoc()) {
    printf("%-15s | %4d | %4d | ৳%8.0f | ৳%6.0f | ৳%6.0f\n",
        substr($emp['name'], 0, 15),
        $emp['att_days'] ?? 0,
        $emp['leave_days'] ?? 0,
        $emp['basic'],
        $emp['total_allow'],
        $emp['total_ded']
    );
}

echo "\n";

// 3. LEAVE APPLICATIONS  
echo "📅 LEAVE APPLICATIONS\n";
echo "─────────────────────────────────────────────────────────────\n";

$leave_query = "SELECT 
    e.first_name,
    lt.name as leave_type,
    DATE_FORMAT(la.start_date, '%Y-%m-%d') as start_date,
    DATE_FORMAT(la.end_date, '%Y-%m-%d') as end_date,
    la.total_days,
    la.status
FROM leave_applications la
JOIN employees e ON la.employee_id = e.id
JOIN leave_types lt ON la.leave_type_id = lt.id
WHERE DATE_FORMAT(la.start_date, '%Y-%m') = '2026-02'
ORDER BY la.start_date";

$leave_result = $conn->query($leave_query);

if ($leave_result->num_rows > 0) {
    printf("%-12s | %-15s | %10s | %10s | %4s | %10s\n", "Employee", "Leave Type", "From", "To", "Days", "Status");
    echo "────────────────────────────────────────────────────────────────\n";
    
    while ($leave = $leave_result->fetch_assoc()) {
        $status_badge = $leave['status'] == 'approved' ? '✓' : ($leave['status'] == 'pending' ? '⧗' : '✗');
        printf("%-12s | %-15s | %10s | %10s | %4d | %s %s\n",
            substr($leave['first_name'], 0, 12),
            substr($leave['leave_type'], 0, 15),
            $leave['start_date'],
            $leave['end_date'],
            $leave['total_days'],
            $status_badge,
            ucfirst($leave['status'])
        );
    }
} else {
    echo "No leave applications for February 2026\n";
}

echo "\n";

// 4. PAYROLL CONFIGURATION
echo "💰 PAYROLL CONFIGURATION\n";
echo "─────────────────────────────────────────────────────────────\n";

// Check salary structures
$sal_struct = $conn->query("SELECT COUNT(*) as cnt FROM salary_structures WHERE is_active = 1");
$sal_row = $sal_struct->fetch_assoc();
echo "✓ Salary Structures: " . $sal_row['cnt'] . "\n";

// Check allowances
$allow = $conn->query("SELECT COUNT(*) as cnt FROM allowances WHERE is_active = 1");
$allow_row = $allow->fetch_assoc();
echo "✓ Active Allowances: " . $allow_row['cnt'] . "\n";

// Check deductions
$ded = $conn->query("SELECT COUNT(*) as cnt FROM deductions WHERE is_active = 1");
$ded_row = $ded->fetch_assoc();
echo "✓ Active Deductions: " . $ded_row['cnt'] . "\n";

// Check employee allowances
$emp_allow = $conn->query("SELECT COUNT(*) as cnt FROM employee_allowances");
$emp_allow_row = $emp_allow->fetch_assoc();
echo "✓ Employee Allowance Assignments: " . $emp_allow_row['cnt'] . "\n";

// Check employee deductions
$emp_ded = $conn->query("SELECT COUNT(*) as cnt FROM employee_deductions");
$emp_ded_row = $emp_ded->fetch_assoc();
echo "✓ Employee Deduction Assignments: " . $emp_ded_row['cnt'] . "\n";

// Check leave policies
$leave_pol = $conn->query("SELECT COUNT(*) as cnt FROM leave_policies WHERE year = 2026");
$leave_pol_row = $leave_pol->fetch_assoc();
echo "✓ Leave Policies (2026): " . $leave_pol_row['cnt'] . "\n";

echo "\n";

// 5. PAYROLL RUNS
echo "📊 PAYROLL RUNS\n";
echo "─────────────────────────────────────────────────────────────\n";

$payroll_query = "SELECT CONCAT(month, '/', year) as period, status, COUNT(*) as cnt FROM payroll_runs GROUP BY month, year, status ORDER BY year DESC, month DESC";
$payroll_result = $conn->query($payroll_query);

if ($payroll_result->num_rows > 0) {
    printf("%-10s | %-15s | %4s\n", "Period", "Status", "Cnt");
    echo "─────────────────────────────────────────\n";
    
    while ($payroll = $payroll_result->fetch_assoc()) {
        printf("%-10s | %-15s | %4d\n",
            $payroll['period'],
            ucfirst($payroll['status']),
            $payroll['cnt']
        );
    }
} else {
    echo "No payroll runs created yet\n";
}

echo "\n";

// 6. PAYSLIPS
echo "📃 GENERATED PAYSLIPS\n";
echo "─────────────────────────────────────────────────────────────\n";

$payslip_query = "SELECT COUNT(*) as cnt, DATE_FORMAT(generated_at, '%Y-%m') as period FROM payslips GROUP BY period ORDER BY period DESC LIMIT 1";
$payslip_result = $conn->query($payslip_query);

if ($payslip_result->num_rows > 0) {
    $payslip_row = $payslip_result->fetch_assoc();
    echo "✓ Payslips for " . $payslip_row['period'] . ": " . $payslip_row['cnt'] . " records\n";
    
    $avg_query = "SELECT 
        ROUND(AVG(basic_salary), 2) as avg_basic,
        ROUND(AVG(total_allowances), 2) as avg_allow,
        ROUND(AVG(total_deductions), 2) as avg_ded,
        ROUND(AVG(net_salary), 2) as avg_net
    FROM payslips
    WHERE DATE_FORMAT(generated_at, '%Y-%m') = '" . $payslip_row['period'] . "'";
    
    $avg_result = $conn->query($avg_query);
    $avg_row = $avg_result->fetch_assoc();
    
    echo "  Average Basic Salary: ৳" . $avg_row['avg_basic'] . "\n";
    echo "  Average Allowances: ৳" . $avg_row['avg_allow'] . "\n";
    echo "  Average Deductions: ৳" . $avg_row['avg_ded'] . "\n";
    echo "  Average Net Salary: ৳" . $avg_row['avg_net'] . "\n";
} else {
    echo "No payslips generated yet\n";
}

echo "\n";

// 7. STATUS CHECK
echo "✅ SYSTEM STATUS\n";
echo "─────────────────────────────────────────────────────────────\n";

$checks = [
    'Employees' => $conn->query("SELECT COUNT(*) as cnt FROM employees WHERE is_active = 1")->fetch_assoc()['cnt'],
    'Attendance Records' => $total_att,
    'Leave Applications' => $conn->query("SELECT COUNT(*) as cnt FROM leave_applications")->fetch_assoc()['cnt'],
    'Employee Allowances' => $emp_allow_row['cnt'],
    'Employee Deductions' => $emp_ded_row['cnt'],
    'Leave Policies' => $leave_pol_row['cnt'],
];

foreach ($checks as $name => $value) {
    $status = $value > 0 ? '✓' : '✗';
    printf("%s %-30s: %d\n", $status, $name, $value);
}

echo "\n";

echo "╔════════════════════════════════════════════════════════════════╗\n";
echo "║  Ready for Payroll Testing! Follow PAYROLL_TESTING_GUIDE.md   ║\n";
echo "╚════════════════════════════════════════════════════════════════╝\n\n";

$conn->close();
?>
