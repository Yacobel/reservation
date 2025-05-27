<?php
session_start();

// Include database connection
require_once '../config/db.php';

// Check for registration success message
$success_message = isset($_SESSION['registration_success']) ? $_SESSION['registration_success'] : '';
if(isset($_SESSION['registration_success'])) {
    unset($_SESSION['registration_success']);
}

// Check for logged out message
if(isset($_GET['logged_out']) && $_GET['logged_out'] == 1) {
    $success_message = 'You have been successfully logged out.';
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $remember = isset($_POST['remember']) ? true : false;

    // Basic validation
    $errors = [];
    
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    // If no validation errors, attempt to authenticate
    if (empty($errors)) {
        try {
            // Get user from database
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && password_verify($password, $user['password'])) {
                // Authentication successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                
                // Set remember me cookie if requested
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + 30 * 24 * 60 * 60, '/');
                    
                    // Store token in database (you would need a remember_tokens table)
                    // For now, we'll just set the session
                }
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: ../admin/dashboard.php");
                } else {
                    header("Location: ../booking.php");
                }
                exit();
            } else {
                $errors[] = "Invalid email or password";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
    
    // If there are errors, store them in session to display
    if (!empty($errors)) {
        $_SESSION['login_error'] = implode('<br>', $errors);
    }
}

// Check if there's an error message
$error_message = isset($_SESSION['login_error']) ? $_SESSION['login_error'] : '';
// Clear any error message
if(isset($_SESSION['login_error'])) {
    unset($_SESSION['login_error']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Hilton Tanger City Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/auth.css">
    <link rel="icon" type="image/x-icon" href="https://www.hilton.com/favicon.ico">
    <meta name="description" content="Sign in to your Hilton Tanger City Center account to book your stay and access exclusive member benefits.">
</head>

<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <a href="../index.php" class="logo-link">
                    <img src="https://www.hilton.com/modules/assets/svgs/logos/HH.svg" alt="Hilton Logo" style="height: 48px;">
                </a>
                <h2>Welcome Back</h2>
                <p>Sign in to book your stay at Hilton Tanger City Center</p>
            </div>

            <form method="POST" class="auth-form" id="loginForm">
                <?php if(!empty($success_message)): ?>
                <div class="success-message" style="margin-bottom: 15px; padding: 10px; background-color: rgba(40, 167, 69, 0.1); border-radius: 5px; color: var(--success-green); font-size: 14px;">
                    <?php echo $success_message; ?>
                </div>
                <?php endif; ?>
                
                <?php if(!empty($error_message)): ?>
                <div class="error-message" style="margin-bottom: 15px; padding: 10px; background-color: rgba(220, 53, 69, 0.1); border-radius: 5px; color: var(--error-red); font-size: 14px;">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email address" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Enter your password" required>
                </div>

                <div class="form-group">
                    <label class="checkbox-container" style="margin-top: -10px;">
                        <input type="checkbox" name="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Sign In</span>
                        <span class="spinner" style="display: none;"></span>
                    </button>
                </div>

                <div class="auth-links">
                    <a href="forgot-password.php">Forgot Password?</a>
                    <span class="divider">•</span>
                    <a href="register.php">Create Account</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>