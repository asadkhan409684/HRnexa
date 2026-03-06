<?php
/**
 * Employee Model - Handles employee-related data operations
 */

class Employee {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getEmployeeById($id) {
        $query = "SELECT * FROM employees WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getEmergencyContact($emp_id) {
        $query = "SELECT id FROM employee_emergency_contacts WHERE employee_id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $emp_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function saveEmergencyContact($data) {
        $existing = $this->getEmergencyContact($data['employee_id']);
        
        if ($existing) {
            $query = "UPDATE employee_emergency_contacts SET name = ?, relationship = ?, phone = ?, email = ?, address = ? WHERE employee_id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("sssssi", $data['name'], $data['relationship'], $data['phone'], $data['email'], $data['address'], $data['employee_id']);
        } else {
            $query = "INSERT INTO employee_emergency_contacts (employee_id, name, relationship, phone, email, address) VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("isssss", $data['employee_id'], $data['name'], $data['relationship'], $data['phone'], $data['email'], $data['address']);
        }
        
        return $stmt->execute();
    }

    public function updateEmergencyContact($id, $contact_name, $contact_phone, $relationship) {
        $query = "UPDATE employees SET emergency_contact_name = ?, emergency_contact_phone = ?, emergency_contact_relationship = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("sssi", $contact_name, $contact_phone, $relationship, $id);
        return $stmt->execute();
    }
}