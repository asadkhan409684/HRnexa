<?php
// Include database connection (which now starts the session globally)
require_once __DIR__ . '/../../app/Config/database.php';

global $conn;

$error = '';
$success = '';
$reset_link = '';

if (isset($_POST['forgot_password'])) {
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Security error. Please try again.";
    } else {
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        
        if (empty($email)) {
            $error = "Please enter your email address.";
        } else {
            // Check in Users table
            $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND status = 'active'");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $found = false;
            if ($result->num_rows > 0) {
                $found = true;
            } else {
                // Check in Employees table
                $stmt_emp = $conn->prepare("SELECT id FROM employees WHERE email = ? AND is_active = 1");
                $stmt_emp->bind_param("s", $email);
                $stmt_emp->execute();
                $res_emp = $stmt_emp->get_result();
                if ($res_emp->num_rows > 0) {
                    $found = true;
                }
            }
            
            if ($found) {
                // Generate token
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                // Delete old tokens for this email
                $delStmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                $delStmt->bind_param("s", $email);
                $delStmt->execute();
                
                // Insert new token
                $insStmt = $conn->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
                $insStmt->bind_param("sss", $email, $token, $expires);
                
                if ($insStmt->execute()) {
                    // Audit Log
                    $logStmt = $conn->prepare("INSERT INTO audit_logs (action, new_values) VALUES ('Forgot Password Request', ?)");
                    $logVal = json_encode(['email' => $email, 'ip' => $_SERVER['REMOTE_ADDR']]);
                    $logStmt->bind_param("s", $logVal);
                    $logStmt->execute();

                    // Redirect directly to reset-password.php
                    header("Location: " . BASE_URL . "views/auth/reset-password.php?token=" . $token . "&email=" . urlencode($email));
                    exit();
                } else {
                    $error = "Something went wrong. Please try again later.";
                }
            } else {
                $error = "If that email is registered, you will receive a reset link.";
                $success = "If that email is registered, you will receive a reset link shortly.";
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
    <title>Forgot Password - HRnexa</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .logo-container {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(180deg, #1a5490 0%, #1a252f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            color: white;
            box-shadow: 0 10px 30px rgba(27, 63, 92, 0.4);
            position: relative;
            animation: rotate 10s linear infinite;
        }

        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .company-name {
            text-align: center;
            color: #4a5568;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #1a5490 0%, #1a252f 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .login-container h1 {
            text-align: center;
            color: #2d3748;
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .login-subtitle {
            text-align: center;
            color: #718096;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            color: #2d3748;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            background-color: #f7fafc;
        }

        .form-input:focus {
            outline: none;
            border-color: #1a5490;
            background-color: white;
            box-shadow: 0 0 0 4px rgba(26, 84, 144, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1a5490 0%, #1a252f 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 10px 25px rgba(27, 63, 92, 0.3);
            margin-bottom: 20px;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(27, 63, 92, 0.4);
        }

        .error-message {
            background-color: #fff5f5;
            color: #c53030;
            padding: 12px 15px;
            border-radius: 8px;
            border-left: 4px solid #c53030;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .success-message {
            background-color: #f0fff4;
            color: #2f855a;
            padding: 12px 15px;
            border-radius: 8px;
            border-left: 4px solid #2f855a;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .back-link {
            text-align: center;
            margin-top: 10px;
        }

        .back-link a {
            color: #1a5490;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        .reset-link-box {
            margin-top: 15px;
            padding: 10px;
            background: #edf2f7;
            border-radius: 5px;
            word-break: break-all;
            font-size: 12px;
            border: 1px dashed #cbd5e0;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <div class="logo">
                <i class="fas fa-key"></i>
            </div>
            <h1 class="company-name">HRnexa</h1>
            <h1>Forgot Password</h1>
            <p class="login-subtitle">Enter your email to reset password</p>
        </div>

        <?php if(!empty($error)): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if(!empty($success)): ?>
            <div class="success-message">
                <?php echo htmlspecialchars($success); ?>
                <?php if(!empty($reset_link)): ?>
                    <div class="reset-link-box">
                        <a href="<?php echo $reset_link; ?>"><?php echo $reset_link; ?></a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="forgot_password" value="1">

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-input" placeholder="Enter your registered email" required>
            </div>

            <button type="submit" class="login-btn">Reset Password</button>
        </form>

        <div class="back-link">
            <a href="<?php echo BASE_URL; ?>views/auth/login.php"><i class="fas fa-arrow-left"></i> Back to Login</a>
        </div>
    </div>
</body>
</html>
