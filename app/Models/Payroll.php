<?php
class Payroll {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function createPayrollRun($month, $year) {
        $check_sql = "SELECT id FROM payroll_runs WHERE month = ? AND year = ? AND status != 'cancelled'";
        $stmt = $this->conn->prepare($check_sql);
        $stmt->bind_param("ii", $month, $year);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            return false; // Run already exists
        }

        $sql = "INSERT INTO payroll_runs (month, year, status) VALUES (?, ?, 'draft')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $month, $year);
        if ($stmt->execute()) {
            return $this->conn->insert_id;
        }
        return false;
    }

    public function getAllPayrollRuns() {
        $sql = "SELECT * FROM payroll_runs ORDER BY created_at DESC";
        return $this->conn->query($sql);
    }

    public function getPayrollRun($id) {
        $sql = "SELECT * FROM payroll_runs WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function updatePayrollRunStatus($id, $status) {
        $sql = "UPDATE payroll_runs SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function getEmployeesForPayroll($level = null) {
        $sql = "SELECT e.*, d.name as department_name, ds.name as designation_name, ds.level as designation_level
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN designations ds ON e.designation_id = ds.id
                WHERE e.employment_status IN ('active', 'probation') AND e.is_active = 1";
        
        if ($level && $level !== 'All') {
            $sql .= " AND ds.level = '" . $this->conn->real_escape_string($level) . "'";
        }
        
        return $this->conn->query($sql);
    }

    public function getEmployeeSalary($employee_id, $effective_date) {
        $sql = "SELECT * FROM employee_salaries 
                WHERE employee_id = ? AND effective_from <= ? 
                AND (effective_to IS NULL OR effective_to >= ?) 
                ORDER BY effective_from DESC LIMIT 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $employee_id, $effective_date, $effective_date);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getEmployeeAllowances($employee_id, $effective_date) {
        $sql = "SELECT a.id as allowance_id, a.name, a.amount_type, 
                       COALESCE(ea.amount, a.default_amount) as amount
                FROM allowances a
                LEFT JOIN employee_allowances ea ON a.id = ea.allowance_id 
                    AND ea.employee_id = ? 
                    AND ea.effective_from <= ? 
                    AND (ea.effective_to IS NULL OR ea.effective_to >= ?)
                JOIN employees e ON e.id = ?
                LEFT JOIN designations ds ON e.designation_id = ds.id
                WHERE a.is_active = 1 
                AND (ea.id IS NOT NULL OR a.designation_level = ds.level OR a.designation_level = 'All')";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issi", $employee_id, $effective_date, $effective_date, $employee_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getEmployeeDeductions($employee_id, $effective_date) {
        $sql = "SELECT ed.*, d.name, d.amount_type 
                FROM employee_deductions ed
                JOIN deductions d ON ed.deduction_id = d.id
                WHERE ed.employee_id = ? AND ed.effective_from <= ? 
                AND (ed.effective_to IS NULL OR ed.effective_to >= ?) AND d.is_active = 1";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $employee_id, $effective_date, $effective_date);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getAttendanceData($employee_id, $start_date, $end_date) {
        $sql = "SELECT 
                    COUNT(CASE WHEN status = 'present' THEN 1 END) as present_days,
                    COUNT(CASE WHEN status = 'absent' THEN 1 END) as raw_absent_days,
                    COUNT(CASE WHEN status = 'late' THEN 1 END) as total_late,
                    COUNT(CASE WHEN status = 'half_day' THEN 1 END) as half_days,
                    COALESCE(SUM(overtime_hours), 0) as total_overtime,
                    0 as total_leave,
                    0 as total_absent
                FROM attendance 
                WHERE employee_id = ? AND date BETWEEN ? AND ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iss", $employee_id, $start_date, $end_date);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getApprovedLeaveDays($employee_id, $start_date, $end_date) {
        $sql = "SELECT SUM(total_days) as leave_days FROM leave_applications 
                WHERE employee_id = ? AND status = 'approved' 
                AND ((start_date BETWEEN ? AND ?) OR (end_date BETWEEN ? AND ?))";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("issss", $employee_id, $start_date, $end_date, $start_date, $end_date);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['leave_days'] ?? 0;
    }

    public function savePayslip($data) {
        $sql = "INSERT INTO payslips (payroll_run_id, employee_id, basic_salary, total_allowances, total_deductions, gross_salary, net_salary, breakdown, total_leave, total_absent, total_late, half_days, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')
                ON DUPLICATE KEY UPDATE 
                basic_salary = VALUES(basic_salary), total_allowances = VALUES(total_allowances), 
                total_deductions = VALUES(total_deductions), gross_salary = VALUES(gross_salary), 
                net_salary = VALUES(net_salary), breakdown = VALUES(breakdown),
                total_leave = VALUES(total_leave), total_absent = VALUES(total_absent),
                total_late = VALUES(total_late), half_days = VALUES(half_days), status = 'draft'";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iidddddsiiii", 
            $data['payroll_run_id'], $data['employee_id'], $data['basic_salary'], 
            $data['total_allowances'], $data['total_deductions'], $data['gross_salary'], 
            $data['net_salary'], $data['breakdown'], 
            $data['total_leave'], $data['total_absent'], $data['total_late'], $data['half_days']);
        return $stmt->execute();
    }

    public function updatePayslipStatus($id, $status) {
        $sql = "UPDATE payslips SET status = ? WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function updatePayslipData($id, $data) {
        $sql = "UPDATE payslips SET 
                total_leave = ?, total_absent = ?, total_late = ?, half_days = ?,
                total_allowances = ?, total_deductions = ?, net_salary = ?
                WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iiiidddi", 
            $data['total_leave'], $data['total_absent'], $data['total_late'], $data['half_days'],
            $data['total_allowances'], $data['total_deductions'], $data['net_salary'],
            $id);
        return $stmt->execute();
    }

    public function getPayslipsByRun($run_id) {
        $sql = "SELECT p.*, e.first_name, e.last_name, e.employee_code, d.name as dept_name, ds.name as desig_name
                FROM payslips p
                JOIN employees e ON p.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN designations ds ON e.designation_id = ds.id
                WHERE p.payroll_run_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $run_id);
        $stmt->execute();
        return $stmt->get_result();
    }

    public function getEmployeePayslips($employee_id) {
        $sql = "SELECT p.*, pr.month, pr.year 
                FROM payslips p
                JOIN payroll_runs pr ON p.payroll_run_id = pr.id
                WHERE p.employee_id = ? AND pr.status = 'completed'
                ORDER BY pr.year DESC, pr.month DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("i", $employee_id);
        $stmt->execute();
        return $stmt->get_result();
    }
}
?>