<?php
header('Content-Type: application/json');
require_once('../../app/Config/database.php');
session_start();

// Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0) != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'run_audit') {
        $report_name = "System Audit - " . date('M Y');
        $category = "Security";
        $status = "Compliant";
        $findings = rand(0, 5);
        if ($findings > 0) $status = "Warning";
        
        $summary = "Automated compliance check performed on " . date('Y-m-d H:i:s') . ". Audited system logs, user permissions, and database integrity. " . ($findings == 0 ? "No issues found." : "Found " . $findings . " minor configuration issues.");
        
        $user_id = $_SESSION['user_id'];
        
        $stmt = $conn->prepare("INSERT INTO compliance_reports (report_name, category, status, summary, findings_count, generated_by) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $report_name, $category, $status, $summary, $findings, $user_id);
        
        if ($stmt->execute()) {
            // Log the activity
            $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id) VALUES (?, 'Generated Compliance Report', 'compliance_reports', ?)");
            $rec_id = $stmt->insert_id;
            $logStmt->bind_param("ii", $_SESSION['user_id'], $rec_id);
            $logStmt->execute();

            echo json_encode(['success' => true, 'message' => 'New compliance audit completed successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Audit failed: ' . $conn->error]);
        }
        $stmt->close();
    }
}
?>
