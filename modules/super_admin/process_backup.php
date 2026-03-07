<?php
header('Content-Type: application/json');
require_once('../../app/Config/database.php');
session_start();

// Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0) != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'download') {
    $filename = $_GET['filename'];
    
    // In a real system, you would check if the file exists on disk
    // $filepath = "../../storage/backups/" . $filename;
    
    // Simulations for local environment: 
    // Since we don't have actual .sql/.zip files on disk, 
    // we send a simple text file with the same name to simulate the process.
    
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    // Output a dummy content to represent the backup
    echo "-- HRnexa System Backup\n";
    echo "-- Date: " . date('Y-m-d H:i:s') . "\n";
    echo "-- Backup File: " . $filename . "\n";
    echo "-- This is a simulated download for the local environment --\n";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_backup') {
        $id = intval($_POST['id']);
        
        // In a real system, you would also delete the file from storage/backups/
        $stmt = $conn->prepare("DELETE FROM system_backups WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            // Log the activity
            $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id) VALUES (?, 'Deleted Backup', 'system_backups', ?)");
            $logStmt->bind_param("ii", $_SESSION['user_id'], $id);
            $logStmt->execute();
            
            echo json_encode(['success' => true, 'message' => 'Backup record deleted successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete record: ' . $conn->error]);
        }
        $stmt->close();
    } 
    else if ($action === 'start_backup') {
        $type = $_POST['type'] ?? 'Database';
        $filename = "backup_" . strtolower($type) . "_" . date('Ymd_His') . ($type === 'Database' ? ".sql" : ".zip");
        $user_id = $_SESSION['user_id'];
        
        // Simulation of backup process
        $size = ($type === 'Database') ? rand(1, 5) . " MB" : rand(100, 200) . " MB";
        
        $stmt = $conn->prepare("INSERT INTO system_backups (filename, file_size, type, status, created_by) VALUES (?, ?, ?, 'Success', ?)");
        $stmt->bind_param("sssi", $filename, $size, $type, $user_id);
        
        if ($stmt->execute()) {
            // Log the activity
            $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values) VALUES (?, 'Created Backup', 'system_backups', ?, ?)");
            $val = json_encode(['filename' => $filename, 'type' => $type]);
            $rec_id = $stmt->insert_id;
            $logStmt->bind_param("iis", $_SESSION['user_id'], $rec_id, $val);
            $logStmt->execute();

            echo json_encode(['success' => true, 'message' => 'New ' . $type . ' backup created successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Backup failed: ' . $conn->error]);
        }
    }
    else {
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
}
?>
