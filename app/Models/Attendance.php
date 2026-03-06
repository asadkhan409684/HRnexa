<?php
/**
 * Attendance Model - Handles attendance-related data operations
 */

class Attendance {
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

    public function getTodayAttendance($employee_id, $date) {
        $query = "SELECT * FROM attendance WHERE employee_id = ? AND date = ? ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $employee_id, $date);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function punchIn($employee_id, $date) {
        $query = "INSERT INTO attendance (employee_id, date, punch_in, status) VALUES (?, ?, NOW(), 'present')";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("is", $employee_id, $date);
        return $stmt->execute();
    }

    public function punchOut($id, $total_hours) {
        $punch_out_time = date('Y-m-d H:i:s');
        // Explicitly set punch_in = punch_in to prevent ON UPDATE CURRENT_TIMESTAMP from resetting it
        $query = "UPDATE attendance SET punch_out = ?, total_hours = ?, punch_in = punch_in WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sdi", $punch_out_time, $total_hours, $id);
        return $stmt->execute();
    }
}