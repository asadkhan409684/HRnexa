<?php
/**
 * EmployeeController - Handles employee profile and data management
 */

require_once __DIR__ . '/../Models/Employee.php';

class EmployeeController {
    private $db;
    private $model;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new Employee($db);
    }

    /**
     * Show employee dashboard
     */
    public function dashboard() {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 5) {
            header("Location: " . BASE_URL . "login");
            exit();
        }
        $employee_id = $_SESSION['user_id'];
        $data = $this->model->getEmployeeById($employee_id);
        require_once __DIR__ . '/../../modules/employee/dashboard.php';
    }

    /**
     * Show employee profile
     */
    public function profile() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "login");
            exit();
        }
        $employee_id = $_SESSION['user_id'];
        $data = $this->model->getEmployeeById($employee_id);
        require_once __DIR__ . '/../../modules/employee/profile.php';
    }

    public function getProfile($id) {
        $data = $this->model->getEmployeeById($id);
        if (!$data) {
            return ['status' => 'error', 'message' => 'Employee not found.'];
        }
        return ['status' => 'success', 'data' => $data];
    }

    public function saveEmergencyContact() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return ['status' => 'error', 'message' => 'Invalid request method.'];
        }

        $emp_id = $_SESSION['user_id'] ?? 0;
        $data = [
            'employee_id' => $emp_id,
            'name' => $_POST['contact_name'] ?? '',
            'relationship' => $_POST['relationship'] ?? '',
            'phone' => $_POST['contact_phone'] ?? '',
            'email' => $_POST['contact_email'] ?? '',
            'address' => $_POST['contact_address'] ?? ''
        ];

        if (empty($data['name']) || empty($data['phone'])) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Contact name and phone are required.']);
            exit();
        }

        if ($this->model->saveEmergencyContact($data)) {
            $res = ['status' => 'success', 'message' => 'Emergency contact updated successfully!'];
        } else {
            $res = ['status' => 'error', 'message' => 'Failed to update emergency contact.'];
        }

        header('Content-Type: application/json');
        echo json_encode($res);
        exit();
    }

    public function updateEmergencyContact($id, $post_data) {
        $name = $post_data['emergency_contact_name'] ?? '';
        $phone = $post_data['emergency_contact_phone'] ?? '';
        $relation = $post_data['emergency_contact_relationship'] ?? '';

        if (empty($name) || empty($phone)) {
            return ['status' => 'error', 'message' => 'Contact name and phone are required.'];
        }

        if ($this->model->updateEmergencyContact($id, $name, $phone, $relation)) {
            return ['status' => 'success', 'message' => 'Emergency contact updated successfully.'];
        }
        return ['status' => 'error', 'message' => 'Failed to update emergency contact.'];
    }
}