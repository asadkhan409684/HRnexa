<?php
header('Content-Type: application/json');
require_once('../../app/Config/database.php');
session_start();

// Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0) != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_settings') {
    if (!isset($_POST['settings']) || !is_array($_POST['settings'])) {
        echo json_encode(['success' => false, 'message' => 'No settings data received.']);
        exit();
    }

    $success_count = 0;
    $error_count = 0;
    
    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("UPDATE system_settings SET value = ? WHERE id = ?");
        
        foreach ($_POST['settings'] as $id => $value) {
            $id = intval($id);
            $value = trim($value);
            
            $stmt->bind_param("si", $value, $id);
            if ($stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
            }
        }
        
        $stmt->close();

        // Log the activity
        $logAction = "Updated System Settings";
        $tableName = "system_settings";
        $details = json_encode(['message' => "Updated " . $success_count . " settings."]);
        
        $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, new_values) VALUES (?, ?, ?, ?)");
        $admin_id = $_SESSION['user_id'];
        $logStmt->bind_param("isss", $admin_id, $logAction, $tableName, $details);
        $logStmt->execute();
        $logStmt->close();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'System settings updated successfully!']);
        
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to update settings: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
