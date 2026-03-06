<?php
/**
 * Leave Model - Handles leave-related data operations
 */

class Leave {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getEmployeeByEmail($email) {
        $query = "SELECT id FROM employees WHERE email = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function checkOverlap($employee_id, $start_date, $end_date) {
        $query = "SELECT id FROM leave_applications 
                  WHERE employee_id = ? 
                  AND status IN ('pending', 'approved', 'referred')
                  AND (
                      (? BETWEEN start_date AND end_date) OR 
                      (? BETWEEN start_date AND end_date) OR
                      (start_date BETWEEN ? AND ?)
                  )";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("issss", $employee_id, $start_date, $end_date, $start_date, $end_date);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function getLeaveTypeLimits($leave_type_id) {
        $query = "SELECT name, max_days_per_year FROM leave_types WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $leave_type_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getUsedDays($employee_id, $leave_type_id) {
        $query = "SELECT SUM(total_days) as used_days FROM leave_applications 
                  WHERE employee_id = ? AND leave_type_id = ? 
                  AND status = 'approved' AND YEAR(start_date) = YEAR(CURDATE())";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $employee_id, $leave_type_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row['used_days'] ?? 0;
    }

    public function saveApplication($data) {
        $stmt = $this->conn->prepare("INSERT INTO leave_applications (employee_id, leave_type_id, start_date, end_date, total_days, reason, status) VALUES (?, ?, ?, ?, ?, ?, 'pending')");
        $stmt->bind_param("iissis", 
            $data['employee_id'], 
            $data['leave_type_id'], 
            $data['start_date'], 
            $data['end_date'], 
            $data['total_days'], 
            $data['reason']
        );
        return $stmt->execute();
    }
}