<?php
header('Content-Type: application/json');
require_once('../../app/Config/database.php');
session_start();

// Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0) != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

$action = $_REQUEST['action'] ?? '';

if ($action === 'get_permissions') {
    $role_id = intval($_GET['role_id']);
    
    $stmt = $conn->prepare("SELECT permission_id FROM role_permissions WHERE role_id = ?");
    $stmt->bind_param("i", $role_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $permissions = [];
    while ($row = $result->fetch_assoc()) {
        $permissions[] = intval($row['permission_id']);
    }
    
    echo json_encode(['success' => true, 'permissions' => $permissions]);
    exit();
}

if ($action === 'save_permissions') {
    $role_id = intval($_POST['role_id']);
    $permissions = $_POST['permissions'] ?? []; // Array of permission IDs

    $conn->begin_transaction();

    try {
        // 1. Clear existing pivot entries
        $stmt = $conn->prepare("DELETE FROM role_permissions WHERE role_id = ?");
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        $stmt->close();

        // 2. Insert new ones
        if (!empty($permissions)) {
            $stmt = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($permissions as $perm_id) {
                $perm_id = intval($perm_id);
                $stmt->bind_param("ii", $role_id, $perm_id);
                $stmt->execute();
            }
            $stmt->close();
        }

        // 3. Sync Roles table JSON column for compatibility
        // Fetch permission strings like 'module.action'
        $perm_strings = [];
        if (!empty($permissions)) {
            $ids = implode(',', array_map('intval', $permissions));
            $res = $conn->query("SELECT module, action FROM permissions WHERE id IN ($ids)");
            while ($row = $res->fetch_assoc()) {
                $perm_strings[] = $row['module'] . '.' . $row['action'];
            }
        }
        
        $json_permissions = json_encode($perm_strings);
        $stmt = $conn->prepare("UPDATE roles SET permissions = ? WHERE id = ?");
        $stmt->bind_param("si", $json_permissions, $role_id);
        $stmt->execute();
        $stmt->close();

        // 4. Audit Log
        $logAction = "Updated Role Permissions";
        $role_res = $conn->query("SELECT name FROM roles WHERE id = $role_id");
        $role_name = $role_res->fetch_assoc()['name'] ?? 'Unknown Role';
        $logDetails = json_encode(['role' => $role_name, 'permissions' => $perm_strings]);
        
        $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values) VALUES (?, ?, ?, ?, ?)");
        $admin_id = $_SESSION['user_id'];
        $log_table = "roles";
        $logStmt->bind_param("isiss", $admin_id, $logAction, $log_table, $role_id, $logDetails);
        $logStmt->execute();
        $logStmt->close();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Permissions saved successfully!']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to save: ' . $e->getMessage()]);
    }
    exit();
}

if ($action === 'add_role') {
    $name = trim($_POST['role_name']);
    $description = trim($_POST['role_description']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $permissions = $_POST['permissions'] ?? [];

    if (empty($name)) {
        echo json_encode(['success' => false, 'message' => 'Role name is required.']);
        exit();
    }

    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("INSERT INTO roles (name, description, is_active) VALUES (?, ?, ?)");
        $stmt->bind_param("ssi", $name, $description, $is_active);

        if (!$stmt->execute()) {
            throw new Exception("Role insertion failed: " . $stmt->error);
        }

        $role_id = $stmt->insert_id;
        $stmt->close();

        $perm_strings = [];
        if (!empty($permissions)) {
            // 1. Insert into pivot table
            $p_stmt = $conn->prepare("INSERT INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
            foreach ($permissions as $perm_id) {
                $perm_id = intval($perm_id);
                $p_stmt->bind_param("ii", $role_id, $perm_id);
                $p_stmt->execute();
            }
            $p_stmt->close();

            // 2. Fetch permission identifier strings for JSON
            $ids = implode(',', array_map('intval', $permissions));
            $res = $conn->query("SELECT module, action FROM permissions WHERE id IN ($ids)");
            while ($row = $res->fetch_assoc()) {
                $perm_strings[] = $row['module'] . '.' . $row['action'];
            }
            
            // 3. Update JSON column
            $json_permissions = json_encode($perm_strings);
            $u_stmt = $conn->prepare("UPDATE roles SET permissions = ? WHERE id = ?");
            $u_stmt->bind_param("si", $json_permissions, $role_id);
            $u_stmt->execute();
            $u_stmt->close();
        }
        
        // Audit Log
        $logAction = "Created New Role";
        $logDetails = json_encode(['role' => $name, 'description' => $description, 'permissions' => $perm_strings]);
        $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values) VALUES (?, ?, ?, ?, ?)");
        $admin_id = $_SESSION['user_id'];
        $log_table = 'roles';
        $logStmt->bind_param("isiss", $admin_id, $logAction, $log_table, $role_id, $logDetails);
        $logStmt->execute();
        $logStmt->close();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'New role created successfully!']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to create role: ' . $e->getMessage()]);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>
