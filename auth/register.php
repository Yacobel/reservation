<?php
session_start();

// Include database connection
require_once '../config/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstName = trim($_POST['firstName']);
    $lastName = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    
    // Basic validation
    $errors = [];
    
    if (empty($firstName)) {
        $errors[] = "First name is required";
    }
    
    if (empty($lastName)) {
        $errors[] = "Last name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Please enter a valid email address";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }
    
    // Check if email already exists
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $errors[] = "Email address already exists. Please use a different email or login.";
        }
    } catch (PDOException $e) {
        $errors[] = "Database error: " . $e->getMessage();
    }
    
    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Hash the password for security
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            
            // Full name from first and last name
            $name = $firstName . ' ' . $lastName;
            
            // Insert user into database
            $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role, created_at) VALUES (?, ?, ?, 'user', NOW())");
            $result = $stmt->execute([$name, $email, $hashedPassword]);
            
            if ($result) {
                // Registration successful
                $_SESSION['registration_success'] = 'Your account has been created successfully. Please sign in.';
                header("Location: login.php");
                exit();
            } else {
                $errors[] = "Failed to create account. Please try again later.";
            }
        } catch (PDOException $e) {
            $errors[] = "Database error: " . $e->getMessage();
        }
    }
    
    // If there are errors, store them in session to display
    if (!empty($errors)) {
        $_SESSION['register_error'] = implode('<br>', $errors);
    }
}

// Check if there's an error message
$error_message = isset($_SESSION['register_error']) ? $_SESSION['register_error'] : '';
// Clear any error message
if(isset($_SESSION['register_error'])) {
    unset($_SESSION['register_error']);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Hilton Tanger City Center</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/auth.css">
    <link rel="icon" type="image/x-icon" href="https://www.hilton.com/favicon.ico">
</head>

<body>
    <div class="auth-container">
        <div class="auth-box">
            <div class="auth-header">
                <a href="../index.php" class="logo-link">
                    <img src="https://www.hilton.com/modules/assets/svgs/logos/HH.svg" alt="Hilton Logo" style="height: 48px;">
                </a>
                <h2>Create Account</h2>
                <p>Join Hilton Honors and unlock exclusive benefits</p>
            </div>

            <form method="POST" class="auth-form" id="registerForm">
                <?php if(!empty($error_message)): ?>
                <div class="error-message" style="margin-bottom: 15px; padding: 10px; background-color: rgba(220, 53, 69, 0.1); border-radius: 5px; color: var(--error-red); font-size: 14px;">
                    <?php echo $error_message; ?>
                </div>
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="firstName">First Name</label>
                        <input type="text" id="firstName" name="firstName" class="form-input" placeholder="Enter your first name" required>
                    </div>

                    <div class="form-group">
                        <label for="lastName">Last Name</label>
                        <input type="text" id="lastName" name="lastName" class="form-input" placeholder="Enter your last name" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-input" placeholder="Enter your email address" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input" placeholder="Create a secure password (min. 6 characters)" required>
                </div>

                <div class="form-group terms">
                    <label class="checkbox-container">
                        <input type="checkbox" name="terms" required>
                        <span class="checkmark"></span>
                        I agree to the <a href="#">Terms & Conditions</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>

                <div class="form-group" style="margin-top: 30px;">
                    <button type="submit" class="btn btn-primary">
                        <span class="btn-text">Create Account</span>
                        <span class="spinner" style="display: none;"></span>
                    </button>
                </div>

                <div class="auth-links">
                    <p>Already have an account? <a href="login.php">Sign In</a></p>
                </div>
            </form>
        </div>
    </div>
    <script src="../js/auth.js"></script>
</body>

</html>