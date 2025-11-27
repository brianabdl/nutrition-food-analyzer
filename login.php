<?php
/**
 * Login Page
 * User authentication with NIM and Password
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

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nim = trim($_POST['nim'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($nim) || empty($password)) {
        $error = 'Please enter both NIM and password';
    } else {
        $userModel = new User();
        $user = $userModel->authenticate($nim, $password);
        
        if ($user) {
            // Set session
            setUserSession($user['id'], $user['nim'], $user['name']);
            
            // Create session tracking
            $userModel->createSession($user['id'], $user['nim'], $user['name']);
            
            // Redirect to requested page or home
            $redirect = $_GET['redirect'] ?? 'index.php';
            header('Location: ' . $redirect);
            exit;
        } else {
            $error = 'Invalid NIM or password';
        }
    }
}

// Page configuration
$page_title = 'Login - Food Nutrition Analyzer';
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
                <i class="fas fa-apple-alt"></i>
                <h1>Food Nutrition Analyzer</h1>
                <p>Login to access the system</p>
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
            </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" class="login-form">
                <div class="form-group">
                    <label for="nim">
                        <i class="fas fa-id-card"></i>
                        NIM (Nomor Induk Mahasiswa)
                    </label>
                    <input 
                        type="text" 
                        id="nim" 
                        name="nim" 
                        placeholder="Enter your NIM" 
                        value="<?php echo htmlspecialchars($_POST['nim'] ?? ''); ?>"
                        required
                        autocomplete="username"
                        autofocus>
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
                        placeholder="Enter your password" 
                        required
                        autocomplete="current-password">
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
            </form>
            
            <div class="login-footer">
                <div class="demo-accounts">
                    <p><strong>Demo Accounts:</strong></p>
                    <ul>
                        <li><code>NIM: 123456789</code> | <code>Pass: password123</code></li>
                        <li><code>NIM: 987654321</code> | <code>Pass: password123</code></li>
                        <li><code>NIM: 111222333</code> | <code>Pass: admin123</code></li>
                    </ul>
                </div>
                
                <div class="register-link">
                    <p>Don't have an account? <a href="register.php">Register here</a></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
