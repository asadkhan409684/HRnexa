<?php
/**
 * Document Model - Handles document-related data operations
 */

class Document {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getEmployeeCode($emp_id) {
        $query = "SELECT employee_code FROM employees WHERE id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $emp_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res['employee_code'] ?? 'UNK';
    }

    public function saveDocument($emp_id, $doc_type, $file_path) {
        $query = "INSERT INTO employee_documents (employee_id, document_type, file_path, verification_status) VALUES (?, ?, ?, 'pending')";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iss", $emp_id, $doc_type, $file_path);
        return $stmt->execute();
    }
}
