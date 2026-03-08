<?php
/**
 * Admin Login Page
 * 
 * Separate login for administrators with enhanced security.
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
        
        // TOGGLE CODE: Rate Limiting 
        // START: Rate Limiting
        // if (empty($errors)) {
        //     $rateCheck = checkLoginAttempts($username, $ipAddress);
        //     if (!$rateCheck['allowed']) {
        //         $errors[] = $rateCheck['message'];
        //     }
        // }
        // END: Rate Limiting
        
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
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
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
                
                <!-- ============================================
                     PASSWORD FIELD WITH TOGGLE EYE ICON
                     To disable the eye icon toggle:
                     1. Remove the <div class="password-input-wrapper"> wrapper
                     2. Remove the <button class="toggle-password"> element
                     3. Keep only: <input type="password" id="password" name="password" required>
                     ============================================ -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <!-- START: Password Toggle Feature -->
                    <div class="password-input-wrapper" style="position: relative; display: flex; align-items: center;">
                        <input type="password" id="password" name="password" required style="padding-right: 3rem !important;">
                        <button type="button" class="toggle-password" data-target="password" aria-label="Show password" 
                                style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; padding: 0.5rem; display: flex; align-items: center; justify-content: center; color: #666; z-index: 100;">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; display: block;">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                            <svg class="eye-off-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; display: none;">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                <line x1="1" y1="1" x2="23" y2="23"></line>
                            </svg>
                        </button>
                    </div>
                    <!-- END: Password Toggle Feature -->
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Sign In</button>
            </form>
            
            <div class="admin-login-footer">
                <a href="<?php echo SITE_URL; ?>/index.php">← Back to Store</a>
            </div>
        </div>
    </div>
    
    <script src="<?php echo SITE_URL; ?>/assets/js/auth.js"></script>
</body>
</html>
