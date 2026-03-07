<?php
header('Content-Type: application/json');
require_once('../../app/Config/database.php');
session_start();

// Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0) != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_module_status') {
    $module_id = intval($_POST['module_id']);
    $status = $_POST['status'];

    if (!in_array($status, ['active', 'inactive'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value.']);
        exit();
    }

    // Get module name for logging
    $getStmt = $conn->prepare("SELECT display_name FROM modules WHERE id = ?");
    $getStmt->bind_param("i", $module_id);
    $getStmt->execute();
    $module_name = $getStmt->get_result()->fetch_assoc()['display_name'] ?? 'Unknown Module';
    $getStmt->close();

    // Update status
    $stmt = $conn->prepare("UPDATE modules SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $module_id);

    if ($stmt->execute()) {
        // Log the activity
        $logAction = ($status == 'active' ? 'Enabled' : 'Disabled') . " Module";
        $tableName = "modules";
        $details = json_encode(['message' => "$logAction: $module_name", 'module_id' => $module_id, 'status' => $status]);
        
        $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values) VALUES (?, ?, ?, ?, ?)");
        $admin_id = $_SESSION['user_id'];
        $logStmt->bind_param("isiss", $admin_id, $logAction, $tableName, $module_id, $details);
        $logStmt->execute();
        $logStmt->close();

        echo json_encode(['success' => true, 'message' => 'Module status updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update module status: ' . $conn->error]);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
