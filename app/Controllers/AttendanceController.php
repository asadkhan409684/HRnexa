<?php
/**
 * AttendanceController - Handles attendance logic
 */

require_once __DIR__ . '/../Models/Attendance.php';

class AttendanceController {
    private $db;
    private $model;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new Attendance($db);
    }

    public function processPunch() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
            exit();
        }

        $user_email = $_SESSION['user_email'] ?? '';
        $action = $_POST['action'] ?? '';

        $res = $this->handlePunch($user_email, $action);
        
        header('Content-Type: application/json');
        echo json_encode($res);
        exit();
    }

    /**
     * Handle punch logic and return result array
     */
    public function handlePunch($user_email, $action) {
        $emp = $this->model->getEmployeeByEmail($user_email);
        if (!$emp) {
            return ['status' => 'error', 'message' => 'Employee record not found.'];
        }

        $employee_id = $emp['id'];
        $today = date('Y-m-d');

        if ($action === 'in') {
            return $this->processPunchIn($employee_id, $today);
        } elseif ($action === 'out') {
            return $this->processPunchOut($employee_id, $today);
        } else {
            return ['status' => 'error', 'message' => 'Invalid action.'];
        }
    }

    private function processPunchIn($employee_id, $today) {
        $att = $this->model->getTodayAttendance($employee_id, $today);
        if ($att) {
            return ['status' => 'error', 'message' => 'You have already punched in today.'];
        }

        if ($this->model->punchIn($employee_id, $today)) {
            return ['status' => 'success', 'message' => 'Punched in successfully at ' . date('h:i A')];
        }
        return ['status' => 'error', 'message' => 'Failed to punch in.'];
    }

    private function processPunchOut($employee_id, $today) {
        $att = $this->model->getTodayAttendance($employee_id, $today);
        if (!$att || ($att['punch_out'] && $att['punch_out'] !== '0000-00-00 00:00:00')) {
            return ['status' => 'error', 'message' => 'No active punch-in found for today or you have already punched out.'];
        }

        $punch_in_time = strtotime($att['punch_in']);
        $current_time = time();
        $seconds_stayed = $current_time - $punch_in_time;
        $hours_stayed = $seconds_stayed / 3600;

        if ($hours_stayed < 9) {
            $remaining_seconds = (9 * 3600) - $seconds_stayed;
            $h = floor($remaining_seconds / 3600);
            $m = floor(($remaining_seconds % 3600) / 60);
            $msg = "You must stay for at least 9 hours. Remaining: " . ($h > 0 ? "{$h}h " : "") . "{$m}m.";
            return ['status' => 'error', 'message' => $msg];
        }

        $total_hours = round($hours_stayed, 2);
        if ($this->model->punchOut($att['id'], $total_hours)) {
            return ['status' => 'success', 'message' => 'Punched out successfully at ' . date('h:i A') . '. Total hours: ' . $total_hours];
        }
        return ['status' => 'error', 'message' => 'Failed to punch out.'];
    }
}