<?php
/**
 * Admin Login Page
 * 
 * Separate login for administrators with enhanced security.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in as admin
if (isAdminLoggedIn()) {
    header("Location: index.php");
    exit();
}

$errors = [];

// Process login form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $ipAddress = getClientIp();
        
        // Validate inputs
        if (empty($username)) {
            $errors[] = "Username is required.";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required.";
        }
        
        // Check rate limiting
        if (empty($errors)) {
            $rateCheck = checkLoginAttempts($username, $ipAddress);
            if (!$rateCheck['allowed']) {
                $errors[] = $rateCheck['message'];
            }
        }
        
        // Attempt login
        if (empty($errors)) {
            $sql = "SELECT admin_id, username, email, password_hash, full_name, role, is_active 
                    FROM admins 
                    WHERE username = ? OR email = ?";
            $admin = fetchOne($sql, [$username, $username]);
            
            if ($admin && password_verify($password, $admin['password_hash'])) {
                if (!$admin['is_active']) {
                    $errors[] = "Your account has been deactivated.";
                    recordLoginAttempt($username, $ipAddress, false);
                } else {
                    // Create admin session
                    $_SESSION['admin_id'] = $admin['admin_id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_email'] = $admin['email'];
                    $_SESSION['admin_name'] = $admin['full_name'];
                    $_SESSION['admin_role'] = $admin['role'];
                    $_SESSION['is_admin_logged_in'] = true;
                    
                    // Update last login
                    $sql = "UPDATE admins SET last_login = NOW() WHERE admin_id = ?";
                    executeQuery($sql, [$admin['admin_id']]);
                    
                    // Log activity
                    logActivity('admin_login', 'admin', $admin['admin_id']);
                    recordLoginAttempt($username, $ipAddress, true);
                    
                    header("Location: index.php");
                    exit();
                }
            } else {
                $errors[] = "Invalid username or password.";
                recordLoginAttempt($username, $ipAddress, false);
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
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-login-page">
    <div class="admin-login-container">
        <div class="admin-login-card">
            <div class="admin-login-header">
                <h1><?php echo SITE_NAME; ?></h1>
                <p>Admin Panel</p>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo cleanOutput($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php" class="admin-login-form">
                <?php echo csrfField(); ?>
                
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="toggle-password" data-target="password">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>
            
            <div class="admin-login-footer">
                <a href="../index.php">← Back to Store</a>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/auth.js"></script>
</body>
</html>
