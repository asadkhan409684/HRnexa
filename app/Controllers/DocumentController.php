<?php
/**
 * DocumentController - Handles document upload and management
 */

require_once __DIR__ . '/../Models/Document.php';

class DocumentController {
    private $db;
    private $model;

    public function __construct($db) {
        $this->db = $db;
        $this->model = new Document($db);
    }

    public function uploadDocument($emp_id, $doc_type, $file) {
        if ($file['error'] !== 0) {
            return ['status' => 'error', 'message' => 'File upload error: ' . $file['error']];
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            return ['status' => 'error', 'message' => 'Only PDF files are allowed.'];
        }

        $system_upload_dir = UPLOAD_DIR . 'employee_document/pending/';
        if (!is_dir($system_upload_dir)) {
            mkdir($system_upload_dir, 0777, true);
        }

        $emp_code = $this->model->getEmployeeCode($emp_id);
        $new_filename = $emp_code . '-' . date('YmdHis') . '.pdf';
        $target_path = $system_upload_dir . $new_filename;
        $db_path = '../Upload/employee_document/pending/' . $new_filename;

        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            if ($this->model->saveDocument($emp_id, $doc_type, $db_path)) {
                return ['status' => 'success', 'message' => 'Document uploaded successfully! Waiting for HR approval.'];
            } else {
                return ['status' => 'error', 'message' => 'Database error.'];
            }
        } else {
            return ['status' => 'error', 'message' => 'Failed to move uploaded file.'];
        }
    }
}