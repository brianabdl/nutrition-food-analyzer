<?php
/**
 * Registration Page
 * New user registration
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/session.php';
require_once MODELS_PATH . '/User.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    if (empty($nim) || empty($name) || empty($password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!preg_match('/^[0-9]+$/', $nim)) {
        $error = 'NIM must contain only numbers';
    } else {
        $userModel = new User();
        $result = $userModel->register($nim, $name, $password);
        
        if ($result['success']) {
            $success = 'Registration successful! You can now login.';
            // Clear form
            $_POST = [];
        } else {
            $error = $result['message'];
        }
    }
}

$page_title = 'Register - Food Nutrition Analyzer';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?></title>
    <link rel="stylesheet" href="<?php echo ROOT_URL; ?>/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <i class="fas fa-user-plus"></i>
                <h1>Create Account</h1>
                <p>Register to access the system</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
                <br><a href="login.php">Click here to login</a>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="register.php" class="login-form">
                <div class="form-group">
                    <label for="nim">
                        <i class="fas fa-id-card"></i>
                        NIM (Nomor Induk Mahasiswa)
                    </label>
                    <input 
                        type="text" 
                        id="nim" 
                        name="nim" 
                        placeholder="Enter your NIM (numbers only)" 
                        value="<?php echo htmlspecialchars($_POST['nim'] ?? ''); ?>"
                        pattern="[0-9]+"
                        required
                        autofocus>
                </div>
                
                <div class="form-group">
                    <label for="name">
                        <i class="fas fa-user"></i>
                        Full Name
                    </label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        placeholder="Enter your full name" 
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                        required>
                </div>
                
                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter password (min. 6 characters)" 
                        minlength="6"
                        required>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i>
                        Confirm Password
                    </label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Confirm your password" 
                        minlength="6"
                        required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-user-plus"></i>
                    Register
                </button>
            </form>
            
            <div class="login-footer">
                <div class="register-link">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
