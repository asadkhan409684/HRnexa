<?php
// Include database connection (which now starts the session globally)
require_once __DIR__ . '/../../app/Config/database.php';
require_once __DIR__ . '/../../app/Controllers/AuthController.php';

global $conn;

$auth = new AuthController($conn);

// Check if user already logged in
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    redirectToDashboard($_SESSION['user_role']);
    exit();
}

if (!isset($_SESSION['user_logged_in']) && isset($_COOKIE['remember_token'])) {
    $token = $_COOKIE['remember_token'];
    $stmt = $conn->prepare("SELECT user_id, user_type FROM user_sessions WHERE token = ? AND expires_at > NOW()");
    
    if ($stmt) {
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $session_data = $result->fetch_assoc();
            
            // Determine table based on user_type
            $user_table = ($session_data['user_type'] === 'employee') ? 'employees' : 'users';
            $user_stmt = $conn->prepare("SELECT id, " . ($session_data['user_type'] === 'employee' ? "first_name, last_name" : "username") . ", email, " . ($session_data['user_type'] === 'employee' ? "5 as role_id" : "role_id") . " FROM $user_table WHERE id = ? AND " . ($session_data['user_type'] === 'employee' ? "is_active = 1" : "status = 'active'"));
            
            if ($user_stmt) {
                $user_stmt->bind_param("i", $session_data['user_id']);
                $user_stmt->execute();
                $user_result = $user_stmt->get_result();

                if ($user_result && $user_result->num_rows > 0) {
                    $user = $user_result->fetch_assoc();
                    
                    $_SESSION['user_logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = ($session_data['user_type'] === 'employee') ? $user['first_name'] . ' ' . $user['last_name'] : $user['username'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_role'] = $user['role_id'];
                    $_SESSION['user_type'] = $session_data['user_type'];
                    
                    redirectToDashboard($user['role_id']);
                    exit();
                }
            }
        }
        // If we reach here, the token was invalid or schema is wrong, clear the cookie
        setcookie('remember_token', '', time() - 3600, BASE_FOLDER);
    }
}

// Function to redirect based on role
function redirectToDashboard($role_id) {
    $role_redirects = [
        1 => BASE_URL . 'modules/super_admin/dashboard.php',
        2 => BASE_URL . 'modules/admin/dashboard.php',
        3 => BASE_URL . 'modules/hr/dashboard.php',
        4 => BASE_URL . 'modules/team_leader/dashboard.php',
        5 => BASE_URL . 'modules/employee/dashboard.php'
    ];
    
    $redirect_path = isset($role_redirects[$role_id]) ? $role_redirects[$role_id] : BASE_URL . 'index.php';
    header("Location: " . $redirect_path);
}

$error = '';

// Handle login form submission
if(isset($_POST['login'])){
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $error = "Security error. Please try again.";
    } else {
        $email = trim(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL));
        $password = $_POST['password'];
        $remember = isset($_POST['remember']) && $_POST['remember'] == '1';
        
        if(empty($email) || empty($password)) {
            $error = "Please enter valid email and password";
        } else {
            $result = $auth->login($email, $password, $remember);
            
            if ($result['success']) {
                unset($_SESSION['csrf_token']);
                redirectToDashboard($result['role_id']);
                exit();
            } else {
                $error = $result['error'];
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
    <title>Login - HRnexa</title>
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
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
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
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .logo:hover {
            animation: none;
        }

        .logo i {
            animation: none;
        }

        .login-container h1 {
            text-align: center;
            color: #2d3748;
            font-size: 28px;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .company-name {
            text-align: center;
            color: #4a5568;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
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

        .form-input-wrapper {
            position: relative;
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
            border-color: #ff8c42;
            background-color: white;
            box-shadow: 0 0 0 4px rgba(255, 140, 66, 0.1);
        }

        .form-input::placeholder {
            color: #a0aec0;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #718096;
            font-size: 16px;
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: #ff8c42;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            margin-top: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            user-select: none;
        }

        .remember-me input[type="checkbox"] {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #ff8c42;
        }

        .remember-me label {
            color: #4a5568;
            font-size: 13px;
            cursor: pointer;
            margin: 0;
        }

        .forgot-password {
            color: #ff8c42;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .forgot-password:hover {
            color: #ff6b9d;
            text-decoration: underline;
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
            box-shadow: 0 10px 25px rgba(255, 107, 157, 0.3);
            margin-bottom: 20px;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(255, 107, 157, 0.4);
        }

        .login-btn:active {
            transform: translateY(-1px);
        }

        .login-btn:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .error-message {
            display: none;
            background-color: #fff5f5;
            color: #c53030;
            padding: 12px 15px;
            border-radius: 8px;
            border-left: 4px solid #c53030;
            margin-bottom: 20px;
            font-size: 13px;
            animation: slideDown 0.3s ease-out;
        }

        .error-message.show {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 35px 25px;
            }

            .login-container h1 {
                font-size: 24px;
            }

            .form-options {
                flex-direction: column;
                align-items: flex-start;
            }

            .forgot-password {
                margin-left: auto;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Logo -->
        <div class="logo-container">
            <div class="logo">
                <i class="fas fa-shield-alt"></i>
            </div>
            <h1 class="company-name">HRnexa</h1>
            <h1>Login</h1>
            <p class="login-subtitle">HRnexa Management System</p>
        </div>

        <!-- Error Message -->
        <?php if(!empty($error)): ?>
            <div class="error-message show" id="errorMessage"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Login Form -->
        <form id="loginForm" method="POST" action="">
            <!-- CSRF Token -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
            <input type="hidden" name="login" value="1">

            <!-- Email Input -->
            <div class="form-group">
                <label class="form-label" for="email">
                    <i class="fas fa-envelope"></i> Email Address
                </label>
                <input 
                    type="email" 
                    id="email" 
                    name="email"
                    class="form-input" 
                    placeholder="Enter your email"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    required
                >
            </div>

            <!-- Password Input -->
            <div class="form-group">
                <label class="form-label" for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <div class="form-input-wrapper">
                    <input 
                        type="password" 
                        id="password" 
                        name="password"
                        class="form-input" 
                        placeholder="Enter your password"
                        required
                    >
                    <span class="password-toggle" onclick="togglePassword()" title="Show/Hide password">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <!-- Form Options -->
            <div class="form-options">
                <div class="remember-me">
                    <input 
                        type="checkbox" 
                        id="rememberMe"
                        name="remember"
                        value="1"
                    >
                    <label for="rememberMe">Remember me</label>
                </div>
                <a href="<?php echo BASE_URL; ?>views/auth/forgot-password.php" class="forgot-password">Forgot password?</a>
            </div>

            <!-- Login Button -->
            <button type="submit" class="login-btn" id="loginBtn">
                <span id="buttonText">Login</span>
            </button>
        </form>
    </div>

    <script>
        // Toggle Password Visibility
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.querySelector('.password-toggle i');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
