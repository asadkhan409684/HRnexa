<?php
/**
 * LeaveController - Handles leave application logic
 */

require_once __DIR__ . '/../Models/Leave.php';

class LeaveController {
    private $db;
    private $model;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new Leave($db);
    }

    public function submitLeave() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
            exit();
        }

        $user_email = $_SESSION['user_email'] ?? '';
        $post_data = $_POST;

        $res = $this->submitApplication($user_email, $post_data);

        header('Content-Type: application/json');
        echo json_encode($res);
        exit();
    }

    /**
     * Logic-only method for submitting leave application
     */
    public function submitApplication($user_email, $post_data) {
        // Get employee_id from email
        $emp = $this->model->getEmployeeByEmail($user_email);
        if (!$emp) {
            return ['status' => 'error', 'message' => 'Employee record not found.'];
        }

        $employee_id = $emp['id'];
        $leave_type_id = $post_data['leave_type_id'] ?? null;
        $start_date = $post_data['from_date'] ?? null;
        $end_date = $post_data['to_date'] ?? null;
        $reason = $post_data['reason'] ?? '';

        if (!$leave_type_id || !$start_date || !$end_date) {
            return ['status' => 'error', 'message' => 'Missing required fields.'];
        }

        // Calculate total days
        $start = new DateTime($start_date);
        $end = new DateTime($end_date);
        $interval = $start->diff($end);
        $total_days = $interval->days + 1;

        if ($total_days < 1) {
            return ['status' => 'error', 'message' => 'Invalid date range.'];
        } 
        
        if ($this->model->checkOverlap($employee_id, $start_date, $end_date)) {
            return ['status' => 'error', 'message' => 'You already have an application for these dates.'];
        }

        // Balance Validation
        $lt_data = $this->model->getLeaveTypeLimits($leave_type_id);
        if (!$lt_data) {
            return ['status' => 'error', 'message' => 'Invalid leave type.'];
        }

        $max_days = $lt_data['max_days_per_year'];
        $used_days = $this->model->getUsedDays($employee_id, $leave_type_id);
        $remaining_balance = $max_days - $used_days;

        if ($total_days > $remaining_balance) {
            return ['status' => 'error', 'message' => "Insufficient balance for {$lt_data['name']}. Remaining: $remaining_balance, Requested: $total_days"];
        }

        $save_data = [
            'employee_id' => $employee_id,
            'leave_type_id' => $leave_type_id,
            'start_date' => $start_date,
            'end_date' => $end_date,
            'total_days' => $total_days,
            'reason' => $reason
        ];

        if ($this->model->saveApplication($save_data)) {
            return ['status' => 'success', 'message' => 'Leave application submitted successfully.'];
        } else {
            return ['status' => 'error', 'message' => 'Failed to submit application.'];
        }
    }
}