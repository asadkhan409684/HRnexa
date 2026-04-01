<?php
// Include database connection (which now starts the session globally)
require_once __DIR__ . '/../../app/Config/database.php';

global $conn;

$error = '';
$success = '';
$token = $_GET['token'] ?? ($_POST['token'] ?? '');
$email = $_GET['email'] ?? ($_POST['email'] ?? '');

if (empty($token) || empty($email)) {
    header("Location: " . BASE_URL . "login");
    exit();
}

// Validate token
$stmt = $conn->prepare("SELECT * FROM password_resets WHERE email = ? AND token = ? AND expires_at > NOW()");
$stmt->bind_param("ss", $email, $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Invalid or expired reset token. Please request a new one.");
}

if (isset($_POST['reset_password'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Security error. Please try again.";
    } else {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Start transaction
            $conn->begin_transaction();
            try {
                // Update in Users table
                $updUser = $conn->prepare("UPDATE users SET password_hash = ? WHERE email = ?");
                $updUser->bind_param("ss", $hashed_password, $email);
                $updUser->execute();
                
                // Update in Employees table (in case it's an employee)
                $updEmp = $conn->prepare("UPDATE employees SET password_hash = ? WHERE email = ?");
                $updEmp->bind_param("ss", $hashed_password, $email);
                $updEmp->execute();
                
                // Delete the token
                $delStmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                $delStmt->bind_param("s", $email);
                $delStmt->execute();
                
                // Audit Log
                $logStmt = $conn->prepare("INSERT INTO audit_logs (action, new_values) VALUES ('Password Reset Success', ?)");
                $logVal = json_encode(['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]);
                $logStmt->bind_param("s", $logVal);
                $logStmt->execute();
                
                $conn->commit();
                $success = "Your password has been reset successfully. You can now login.";
            } catch (Exception $e) {
                $conn->rollback();
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - HRnexa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(180deg, #1a5490 0%, #1a252f 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            padding: 50px 40px;
            animation: slideUp 0.6s ease-out;
        }
        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .logo-container { text-align: center; margin-bottom: 30px; }
        .logo {
            width: 80px; height: 80px; margin: 0 auto 20px;
            background: linear-gradient(180deg, #1a5490 0%, #1a252f 100%);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            font-size: 40px; color: white; box-shadow: 0 10px 30px rgba(27, 63, 92, 0.4);
        }
        .company-name {
            text-align: center; color: #4a5568; font-size: 16px; font-weight: 600; margin-bottom: 8px;
            background: linear-gradient(135deg, #1a5490 0%, #1a252f 100%);
            -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;
        }
        .login-container h1 { text-align: center; color: #2d3748; font-size: 28px; margin-bottom: 10px; font-weight: 700; }
        .login-subtitle { text-align: center; color: #718096; font-size: 14px; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; color: #2d3748; font-size: 14px; font-weight: 600; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-input {
            width: 100%; padding: 12px 15px; border: 2px solid #e2e8f0; border-radius: 8px;
            font-size: 14px; transition: all 0.3s ease; background-color: #f7fafc;
        }
        .form-input:focus { outline: none; border-color: #1a5490; background-color: white; box-shadow: 0 0 0 4px rgba(26, 84, 144, 0.1); }
        .login-btn {
            width: 100%; padding: 14px; background: linear-gradient(135deg, #1a5490 0%, #1a252f 100%);
            color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: 600;
            cursor: pointer; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px;
            box-shadow: 0 10px 25px rgba(27, 63, 92, 0.3); margin-bottom: 20px;
        }
        .login-btn:hover { transform: translateY(-3px); box-shadow: 0 15px 35px rgba(27, 63, 92, 0.4); }
        .error-message { background-color: #fff5f5; color: #c53030; padding: 12px 15px; border-radius: 8px; border-left: 4px solid #c53030; margin-bottom: 20px; font-size: 13px; }
        .success-message { background-color: #f0fff4; color: #2f855a; padding: 12px 15px; border-radius: 8px; border-left: 4px solid #2f855a; margin-bottom: 20px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <div class="logo"><i class="fas fa-lock-open"></i></div>
            <h1 class="company-name">HRnexa</h1>
            <h1>Reset Password</h1>
            <p class="login-subtitle">Resetting password for: <strong><?php echo htmlspecialchars($email); ?></strong></p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if(!empty($success)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
                <div style="margin-top: 10px;">
                    <a href="<?php echo BASE_URL; ?>views/auth/login.php" style="color: #2f855a; font-weight: 700; text-decoration: none;">Go to Login <i class="fas fa-arrow-right"></i></a>
                </div>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <input type="hidden" name="reset_password" value="1">

                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-input" placeholder="Min 8 characters" required minlength="6">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-input" placeholder="Confirm new password" required>
                </div>

                <button type="submit" class="login-btn">Update Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
