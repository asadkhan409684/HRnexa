<?php
require_once __DIR__ . '/../Models/Payroll.php';
require_once __DIR__ . '/../Helpers/tax_helper.php';

class PayrollController {
    private $payrollModel;
    private $db;

    public function __construct($db) {
        $this->db = $db;
        $this->payrollModel = new Payroll($db);
    }

    public function initiateRun($month, $year) {
        return $this->payrollModel->createPayrollRun($month, $year);
    }

    public function processRun($run_id, $level = null) {
        $run = $this->payrollModel->getPayrollRun($run_id);
        if (!$run || $run['status'] === 'completed') {
            return false;
        }

        $this->payrollModel->updatePayrollRunStatus($run_id, 'processing');

        $month = $run['month'];
        $year = $run['year'];
        $start_date = "$year-" . str_pad($month, 2, '0', STR_PAD_LEFT) . "-01";
        $end_date = date("Y-m-t", strtotime($start_date));
        $days_in_month = date("t", strtotime($start_date));

        $employees = $this->payrollModel->getEmployeesForPayroll($level);
        
        while ($emp = $employees->fetch_assoc()) {
            $emp_id = $emp['id'];
            
            // 1. Fetch Salary Structure
            $salary = $this->payrollModel->getEmployeeSalary($emp_id, $end_date);
            if (!$salary) continue; // Skip if no salary defined

            $basic_salary = $salary['basic_salary'];
            $daily_rate = $basic_salary / $days_in_month;

            // 2. Fetch Attendance & Leave
            $attendance = $this->payrollModel->getAttendanceData($emp_id, $start_date, $end_date);
            $approved_leave_days = $this->payrollModel->getApprovedLeaveDays($emp_id, $start_date, $end_date);

            $total_leave_days = floatval($attendance['total_leave'] ?? 0);
            $total_late_days = intval($attendance['total_late'] ?? 0);
            $total_absent_days = floatval($attendance['total_absent'] ?? 0);
            $half_days = intval($attendance['half_days'] ?? 0);
            $ot_hours = floatval($attendance['total_overtime'] ?? 0);

            // Rule: 3 lates = 1 absent
            $late_absent_penalty = floor($total_late_days / 3);
            
            // Rule: Unapproved leave = absent
            $unapproved_leave_absent = max(0, $total_leave_days - $approved_leave_days);
            
            // Total effective absent days for deduction
            $effective_absent_days = $total_absent_days + $late_absent_penalty + $unapproved_leave_absent;

            // 3. Calculate Deductions for Absence/Half-day
            $absence_deduction = $effective_absent_days * $daily_rate;
            $half_day_deduction = $half_days * (0.5 * $daily_rate);
            $total_attendance_deduction = $absence_deduction + $half_day_deduction;

            // 4. Calculate Allowances
            $total_allowances = 0;
            $allowance_list = [];
            $allowances_res = $this->payrollModel->getEmployeeAllowances($emp_id, $end_date);
            while ($alw = $allowances_res->fetch_assoc()) {
                $amt = ($alw['amount_type'] === 'percentage') ? ($basic_salary * ($alw['amount'] / 100)) : $alw['amount'];
                $total_allowances += $amt;
                $allowance_list[] = ['name' => $alw['name'], 'amount' => round($amt, 2)];
            }

            // 5. Calculate OT (Simplified: 1.5x hourly rate)
            $hourly_rate = ($daily_rate / 8); 
            $ot_pay = $ot_hours * ($hourly_rate * 1.5);
            if ($ot_pay > 0) {
                $total_allowances += $ot_pay;
                $allowance_list[] = ['name' => 'Overtime Pay', 'amount' => round($ot_pay, 2)];
            }

            // 6. Calculate Fixed Deductions
            $total_deductions = $total_attendance_deduction;
            $deduction_list = [
                ['name' => 'Attendance Deductions', 'amount' => round($total_attendance_deduction, 2)]
            ];
            
            $deductions_res = $this->payrollModel->getEmployeeDeductions($emp_id, $end_date);
            while ($ded = $deductions_res->fetch_assoc()) {
                $amt = ($ded['amount_type'] === 'percentage') ? ($basic_salary * ($ded['amount'] / 100)) : $ded['amount'];
                $total_deductions += $amt;
                $deduction_list[] = ['name' => $ded['name'], 'amount' => round($amt, 2)];
            }

            // 7. Gross Salary
            $gross_salary = $basic_salary + $total_allowances - $total_attendance_deduction;

            // 8. Tax Calculation
            $tax_amount = calculateTax($gross_salary);
            $total_deductions += $tax_amount;
            $deduction_list[] = ['name' => 'Income Tax', 'amount' => round($tax_amount, 2)];

            // 9. Net Salary
            $net_salary = $gross_salary - ($total_deductions - $total_attendance_deduction);

            // 10. Prepare Breakdown
            $breakdown = json_encode([
                'attendance_summary' => [
                    'total_leave' => $total_leave_days,
                    'approved_leave' => $approved_leave_days,
                    'total_late' => $total_late_days,
                    'late_penalty_absent' => $late_absent_penalty,
                    'total_absent_marked' => $total_absent_days,
                    'unapproved_leave_absent' => $unapproved_leave_absent,
                    'effective_absent_total' => $effective_absent_days,
                    'half_days' => $half_days,
                    'ot_hours' => $ot_hours
                ],
                'allowances' => $allowance_list,
                'deductions' => $deduction_list,
                'summary' => [
                    'basic' => round($basic_salary, 2),
                    'total_allowances' => round($total_allowances, 2),
                    'total_deductions' => round($total_deductions, 2),
                    'tax' => round($tax_amount, 2),
                    'gross' => round($gross_salary, 2),
                    'net' => round($net_salary, 2)
                ]
            ]);

            // 11. Save Payslip
            $payslip_data = [
                'payroll_run_id' => $run_id,
                'employee_id' => $emp_id,
                'basic_salary' => $basic_salary,
                'total_allowances' => $total_allowances,
                'total_deductions' => $total_deductions,
                'gross_salary' => $gross_salary,
                'net_salary' => $net_salary,
                'breakdown' => $breakdown,
                'total_leave' => $total_leave_days,
                'total_absent' => $effective_absent_days, // Use effective absent days
                'total_late' => $total_late_days,
                'half_days' => $half_days
            ];
            $this->payrollModel->savePayslip($payslip_data);
        }

        $this->payrollModel->updatePayrollRunStatus($run_id, 'draft'); 
        return true;
    }

    public function finalizeRun($run_id) {
        // HR keeps it in draft or moves to pending_approval? 
        // Based on req: "Generate icon button... approval... admin dashboard"
        // So finalizeRun here effectively means "Submit for Approval"
        if ($this->payrollModel->updatePayrollRunStatus($run_id, 'pending_approval')) {
             $sql = "UPDATE payslips SET status = 'pending_approval' WHERE payroll_run_id = ?";
             $stmt = $this->db->prepare($sql);
             $stmt->bind_param("i", $run_id);
             return $stmt->execute();
        }
        return false;
    }

    public function approveRun($run_id) {
        // Admin approves, status becomes 'approved' (visible to employee)
        // 'completed' might be a legacy status, let's stick to 'approved' for clarity based on new column
        // But payroll_runs table uses 'completed', let's map 'approved' to 'completed' for runs, and 'approved' for payslips
        if ($this->payrollModel->updatePayrollRunStatus($run_id, 'completed')) {
             $sql = "UPDATE payslips SET status = 'approved' WHERE payroll_run_id = ?";
             $stmt = $this->db->prepare($sql);
             $stmt->bind_param("i", $run_id);
             return $stmt->execute();
        }
        return false;
    }
}
?>