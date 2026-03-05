<?php
/**
 * AuthController - Handles user authentication
 */

class AuthController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    /**
     * Display the login form
     */
    public function showLoginForm() {
        if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
            $this->redirectByRole($_SESSION['user_role']);
        }
        require_once __DIR__ . '/../../views/auth/login.php';
    }

    /**
     * Helper to redirect based on role
     */
    private function redirectByRole($role_id) {
        $path = '';
        switch ($role_id) {
            case 1: $path = "modules/super_admin/dashboard.php"; break;
            case 2: $path = "modules/admin/dashboard.php"; break;
            case 3: $path = "modules/hr/dashboard.php"; break;
            case 4: $path = "modules/team_leader/dashboard.php"; break;
            case 5: $path = "modules/employee/dashboard.php"; break;
            default: $path = "public/index.php";
        }
        header("Location: " . BASE_URL . $path);
        exit();
    }

    /**
     * Handle user login POST request
     */
    public function login($passed_email = null, $passed_password = null, $passed_remember = null) {
        if ($passed_email === null && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: " . BASE_URL . "login");
            exit();
        }

        // Only do CSRF check if we are NOT being called internally (e.g., from login.php which has its own check)
        if ($passed_email === null) {
            if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
                $_SESSION['error'] = "Invalid session token.";
                header("Location: " . BASE_URL . "login");
                exit();
            }
        }

        $email = $passed_email ?? trim($_POST['email'] ?? '');
        $password = $passed_password ?? ($_POST['password'] ?? '');
        $remember = $passed_remember ?? isset($_POST['remember']);

        $error = '';
        
        // Get user from database (users table)
        $stmt = $this->conn->prepare("SELECT id, username, email, role_id, password_hash, status, login_attempts, account_locked, locked_at FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Check if account is locked
            if ($user['account_locked'] == 1) {
                $lockout_duration = 300; 
                $last_attempt_time = $user['locked_at'] ? strtotime($user['locked_at']) : 0;
                
                if (time() - $last_attempt_time < $lockout_duration) {
                    $remaining = ceil(($lockout_duration - (time() - $last_attempt_time)) / 60);
                    $_SESSION['error'] = "Account locked. Please try again in $remaining minutes.";
                    header("Location: " . BASE_URL . "login");
                    exit();
                } else {
                    $this->unlockAccount('users', $user['id']);
                }
            }

            if (password_verify($password, $user['password_hash'])) {
                $this->setupSession($user, 'user');
                $this->resetAttempts('users', $user['id']);
                $this->logActivity($user['id'], 'Login', 'users', $user['id'], ['ip' => $_SERVER['REMOTE_ADDR'], 'user_agent' => $_SERVER['HTTP_USER_AGENT']]);
                
                if ($remember) {
                    $this->handleRememberMe($user['id'], 'user');
                }
                
                if ($passed_email !== null) {
                    return ['success' => true, 'role_id' => $user['role_id']];
                }
                $this->redirectByRole($user['role_id']);
            } else {
                $new_attempts = $user['login_attempts'] + 1;
                $lock_account = ($new_attempts >= 5) ? 1 : 0;
                $this->updateAttempts('users', $user['id'], $new_attempts, $lock_account);
                
                $error = $lock_account ? "Too many failed attempts. Account locked for 5 minutes." : "Invalid email or password";
                $this->logActivity($user['id'], $lock_account ? 'Account Locked' : 'Failed Login', 'users', $user['id'], ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR'], 'attempts' => $new_attempts]);
                
                if ($passed_email !== null) {
                    return ['success' => false, 'error' => $error];
                }
                $_SESSION['error'] = $error;
                header("Location: " . BASE_URL . "login");
                exit();
            }
        } else {
            // Check Employees table
            $res = $this->loginEmployee($email, $password, $remember);
            if ($res['success']) {
                if ($passed_email !== null) {
                    return $res;
                }
                $this->redirectByRole(5); // Employee role_id is 5
            } else {
                if ($passed_email !== null) {
                    return $res;
                }
                $_SESSION['error'] = $res['error'];
                header("Location: " . BASE_URL . "login");
                exit();
            }
        }
    }

    private function loginEmployee($email, $password, $remember) {
        $stmt = $this->conn->prepare("SELECT id, first_name, last_name, email, password_hash, is_active, login_attempts, account_locked, locked_at FROM employees WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res_emp = $stmt->get_result();

        if ($res_emp->num_rows > 0) {
            $emp = $res_emp->fetch_assoc();
            
            if ($emp['account_locked'] == 1) {
                 $lockout_duration = 300; 
                 $last_attempt_time = $emp['locked_at'] ? strtotime($emp['locked_at']) : 0;
                 if (time() - $last_attempt_time < $lockout_duration) {
                     $remaining = ceil(($lockout_duration - (time() - $last_attempt_time)) / 60);
                     return ['success' => false, 'error' => "Account locked. Please try again in $remaining minutes."];
                 } else {
                     $this->unlockAccount('employees', $emp['id']);
                 }
            }

            if ($emp['is_active'] == 0) {
                return ['success' => false, 'error' => "Your account is inactive."];
            } elseif (password_verify($password, $emp['password_hash'])) {
                $user_data = [
                    'id' => $emp['id'],
                    'username' => $emp['first_name'] . ' ' . $emp['last_name'],
                    'email' => $emp['email'],
                    'role_id' => 5
                ];
                $this->setupSession($user_data, 'employee');
                $this->resetAttempts('employees', $emp['id']);
                $this->logActivity(NULL, 'Login', 'employees', $emp['id'], ['ip' => $_SERVER['REMOTE_ADDR'], 'user_agent' => $_SERVER['HTTP_USER_AGENT']]);
                
                if ($remember) {
                    $this->handleRememberMe($emp['id'], 'employee');
                }
                return ['success' => true, 'role_id' => 5];
            } else {
                $new_attempts = $emp['login_attempts'] + 1;
                $lock_account = ($new_attempts >= 5) ? 1 : 0;
                $this->updateAttempts('employees', $emp['id'], $new_attempts, $lock_account);
                
                $error = $lock_account ? "Too many failed attempts. Account locked for 5 minutes." : "Invalid email or password";
                $this->logActivity(NULL, $lock_account ? 'Account Locked' : 'Failed Login', 'employees', $emp['id'], ['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR'], 'attempts' => $new_attempts]);
                
                return ['success' => false, 'error' => $error];
            }
        }
        
        return ['success' => false, 'error' => "Invalid email or password"];
    }

    /**
     * Handle user logout
     */
    public function logout() {
        $user_id = $_SESSION['user_id'] ?? null;
        $role_id = $_SESSION['user_role'] ?? null;

        if ($user_id) {
            $table_name = ($role_id == 5) ? 'employees' : 'users';
            $log_user_id = ($table_name === 'users') ? $user_id : NULL;
            $this->logActivity($log_user_id, 'Logout', $table_name, $user_id);
            
            if (isset($_COOKIE['remember_token'])) {
                $token = $_COOKIE['remember_token'];
                $stmt = $this->conn->prepare("DELETE FROM user_sessions WHERE user_id = ? AND user_type = ? AND token = ?");
                $user_type = ($role_id == 5) ? 'employee' : 'user';
                $stmt->bind_param("iss", $user_id, $user_type, $token);
                $stmt->execute();
            }
        }

        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        setcookie('remember_token', '', time() - 3600, BASE_FOLDER);
        header("Location: " . BASE_URL . "login");
        exit();
    }

    private function setupSession($user, $type = 'user') {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['username'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role_id'];
        $_SESSION['user_type'] = $type;
    }

    private function handleRememberMe($user_id, $user_type = 'user') {
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + (86400 * 30));
        $sessStmt = $this->conn->prepare("INSERT INTO user_sessions (user_id, user_type, token, expires_at) VALUES (?, ?, ?, ?)");
        $sessStmt->bind_param("isss", $user_id, $user_type, $token, $expires);
        $sessStmt->execute();
        setcookie('remember_token', $token, [
            'expires' => time() + (86400 * 30),
            'path' => BASE_FOLDER,
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
    }

    private function unlockAccount($table, $id) {
        $stmt = $this->conn->prepare("UPDATE $table SET account_locked = 0, login_attempts = 0, locked_at = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    private function resetAttempts($table, $id) {
        $stmt = $this->conn->prepare("UPDATE $table SET login_attempts = 0, account_locked = 0, locked_at = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
    }

    private function updateAttempts($table, $id, $attempts, $lock) {
        $locked_at = ($lock) ? date('Y-m-d H:i:s') : NULL;
        $stmt = $this->conn->prepare("UPDATE $table SET login_attempts = ?, account_locked = ?, locked_at = ? WHERE id = ?");
        $stmt->bind_param("iisi", $attempts, $lock, $locked_at, $id);
        $stmt->execute();
    }

    private function logActivity($user_id, $action, $table, $record_id, $values = []) {
        $logVal = !empty($values) ? json_encode($values) : NULL;
        $stmt = $this->conn->prepare("INSERT INTO audit_logs (user_id, action, table_name, record_id, new_values) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issis", $user_id, $action, $table, $record_id, $logVal);
        $stmt->execute();
    }
}