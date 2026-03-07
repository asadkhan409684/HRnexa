<?php
header('Content-Type: application/json');
require_once('../../app/Config/database.php');
session_start();

// Security Check
if (!isset($_SESSION['user_id']) || ($_SESSION['role_id'] ?? $_SESSION['user_role'] ?? 0) != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_user') {
    $user_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT id, username, email, role_id, status FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($user = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'user' => $user]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found.']);
    }
    $stmt->close();
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_user' || $action === 'edit_user') {
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $role_id = intval($_POST['role_id']);
        $status = $_POST['status'];
        $password = $_POST['password'] ?? '';

        // Validation
        if (empty($username) || empty($email) || empty($role_id)) {
            echo json_encode(['success' => false, 'message' => 'Required fields are missing.']);
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
            exit();
        }

        if ($action === 'add_user') {
            if (empty($password)) {
                echo json_encode(['success' => false, 'message' => 'Password is required for new users.']);
                exit();
            }

            // Check if email or username already exists
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
            $checkStmt->bind_param("ss", $email, $username);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Email or Username already exists.']);
                exit();
            }
            $checkStmt->close();

            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (username, email, password_hash, role_id, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssis", $username, $email, $password_hash, $role_id, $status);
            
            if ($stmt->execute()) {
                $new_id = $stmt->insert_id;
                
                // If Role is Team Leader (4), also insert into employees table
                if ($role_id == 4) {
                    $employee_code = 'TL' . date('Y') . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
                    
                    // Split username into First and Last Name
                    $parts = explode(' ', $username, 2);
                    $first_name = $parts[0];
                    $last_name = $parts[1] ?? 'User'; // Default last name if not present
                    
                    $hire_date = date('Y-m-d');
                    
                    // Use NULL for department/designation to avoid FK issues
                    $dept_id = null;
                    $desig_id = null;

                    // Prepare statement with 'employment_status' column
                    // Note: 'employment_status' enum allows: 'active','probation','terminated','resigned'
                    $empStmt = $conn->prepare("INSERT INTO employees (
                        employee_code, first_name, last_name, email, password_hash, 
                        department_id, designation_id, team_leader_id, hire_date, employment_status
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, NULL, ?, 'active')");
                    
                    if ($empStmt) {
                        // Bind parameters. Types: s=string, i=integer
                        // For NULL values in integer columns, pass `null` variable.
                        $empStmt->bind_param("sssssiis", 
                            $employee_code, $first_name, $last_name, $email, $password_hash, 
                            $dept_id, $desig_id, $hire_date
                        );
                        
                        if (!$empStmt->execute()) {
                            // Log specific database error
                            error_log("Failed to create employee record. DB Error: " . $empStmt->error);
                        }
                        $empStmt->close();
                    } else {
                         error_log("Failed to prepare employee statement: " . $conn->error);
                    }
                }

                // Log activity
                $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values) VALUES (?, 'Created User', 'users', ?, ?)");
                $logDetails = json_encode(['username' => $username, 'email' => $email]);
                $logStmt->bind_param("iis", $_SESSION['user_id'], $new_id, $logDetails);
                $logStmt->execute();
                echo json_encode(['success' => true, 'message' => 'User created successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
            }
            $stmt->close();

        } else if ($action === 'edit_user') {
            $user_id = intval($_POST['user_id']);
            
            // Check for duplicates excluding current user
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE (email = ? OR username = ?) AND id != ?");
            $checkStmt->bind_param("ssi", $email, $username, $user_id);
            $checkStmt->execute();
            if ($checkStmt->get_result()->num_rows > 0) {
                echo json_encode(['success' => false, 'message' => 'Email or Username already taken by another user.']);
                exit();
            }
            $checkStmt->close();

            if (!empty($password)) {
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password_hash = ?, role_id = ?, status = ? WHERE id = ?");
                $stmt->bind_param("sssssi", $username, $email, $password_hash, $role_id, $status, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role_id = ?, status = ? WHERE id = ?");
                $stmt->bind_param("ssisi", $username, $email, $role_id, $status, $user_id);
            }

            if ($stmt->execute()) {
                // Log activity
                $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values) VALUES (?, 'Updated User', 'users', ?, ?)");
                $logDetails = json_encode(['username' => $username, 'email' => $email]);
                $logStmt->bind_param("iis", $_SESSION['user_id'], $user_id, $logDetails);
                $logStmt->execute();
                echo json_encode(['success' => true, 'message' => 'User updated successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
            }
            $stmt->close();
        }

    } else if ($action === 'delete_user') {
        $user_id = intval($_POST['id']);
        
        // Don't allow self-deletion
        if ($user_id == $_SESSION['user_id']) {
            echo json_encode(['success' => false, 'message' => 'You cannot delete your own account.']);
            exit();
        }

        // 1. Delete active sessions first (to prevent FK error)
        $sessStmt = $conn->prepare("DELETE FROM user_sessions WHERE user_id = ?");
        $sessStmt->bind_param("i", $user_id);
        $sessStmt->execute();
        $sessStmt->close();

        // 2. Delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        
        if ($stmt->execute()) {
            // Log activity
            $logStmt = $conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id) VALUES (?, 'Deleted User', 'users', ?)");
            $logStmt->bind_param("ii", $_SESSION['user_id'], $user_id);
            $logStmt->execute();
            echo json_encode(['success' => true, 'message' => 'User deleted successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $conn->error]);
        }
        $stmt->close();
    }
}
 else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
