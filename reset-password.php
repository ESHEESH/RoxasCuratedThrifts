<?php
/**
 * Reset Password Page
 * 
 * Allows users to set a new password using a valid reset token.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: " . SITE_URL . '/index.php');
    exit();
}

$errors = [];
$success = false;
$validToken = false;
$userId = null;

// Get token from URL
$token = $_GET['token'] ?? '';

// Validate token
if (empty($token)) {
    $errors[] = "Invalid reset link. Please request a new one.";
} else {
    // Check if token exists and is not expired
    $sql = "SELECT user_id FROM users 
            WHERE reset_token = ? 
            AND reset_token_expires > NOW() 
            AND is_active = TRUE 
            AND is_banned = FALSE";
    $user = fetchOne($sql, [$token]);
    
    if ($user) {
        $validToken = true;
        $userId = $user['user_id'];
    } else {
        $errors[] = "This reset link has expired or is invalid. Please request a new one.";
    }
}

// Process password reset
if ($validToken && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate password
        if (empty($password)) {
            $errors[] = "Password is required.";
        } else {
            $passwordValidation = validatePassword($password);
            if (!$passwordValidation['valid']) {
                $errors = array_merge($errors, $passwordValidation['errors']);
            }
        }
        
        // Confirm password
        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match.";
        }
        
        // Update password if no errors
        if (empty($errors)) {
            try {
                // Hash new password
                $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                
                // Update user password and clear reset token
                $sql = "UPDATE users 
                        SET password_hash = ?, 
                            reset_token = NULL, 
                            reset_token_expires = NULL,
                            updated_at = NOW() 
                        WHERE user_id = ?";
                executeQuery($sql, [$passwordHash, $userId]);
                
                // Log the password change
                logActivity('password_reset_completed', 'user', $userId);
                
                $success = true;
                
            } catch (Exception $e) {
                error_log("Password reset error: " . $e->getMessage());
                $errors[] = "An error occurred. Please try again later.";
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
    <meta name="description" content="Reset your <?php echo SITE_NAME; ?> password">
    <title>Reset Password - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <a href="index.php">
                    <h1><?php echo SITE_NAME; ?></h1>
                </a>
                <p>Create New Password</p>
            </div>
            
            <?php if ($success): ?>
                <!-- Success Message -->
                <div class="success-message">
                    <div class="success-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <h2>Password Updated!</h2>
                    <p>Your password has been successfully reset. You can now sign in with your new password.</p>
                    
                    <a href="login.php" class="btn btn-primary btn-block" style="margin-top: 20px;">
                        Sign In
                    </a>
                </div>
            <?php elseif (!$validToken): ?>
                <!-- Invalid Token Message -->
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo cleanOutput($error); ?></p>
                    <?php endforeach; ?>
                </div>
                
                <div class="auth-footer">
                    <a href="forgot-password.php" class="btn btn-primary btn-block">
                        Request New Reset Link
                    </a>
                </div>
            <?php else: ?>
                <!-- Error Messages -->
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error">
                        <ul>
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo cleanOutput($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <p class="auth-description">
                    Create a new password for your account.
                </p>
                
                <!-- Reset Password Form -->
                <form method="POST" action="reset-password.php?token=<?php echo urlencode($token); ?>" class="auth-form" id="resetForm" novalidate>
                    <?php echo csrfField(); ?>
                    
                    <div class="form-group">
                        <label for="password">New Password</label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                placeholder="Enter new password"
                                required
                                autocomplete="new-password"
                                minlength="8"
                            >
                            <button type="button" class="toggle-password" data-target="password" aria-label="Show password">
                                <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-off-icon hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                        
                        <!-- Password Strength Indicator -->
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <span class="strength-text" id="strengthText">Enter a password</span>
                        </div>
                        
                        <ul class="password-requirements" id="passwordRequirements">
                            <li data-requirement="length"><span class="check">✓</span> At least 8 characters</li>
                            <li data-requirement="uppercase"><span class="check">✓</span> One uppercase letter</li>
                            <li data-requirement="lowercase"><span class="check">✓</span> One lowercase letter</li>
                            <li data-requirement="number"><span class="check">✓</span> One number</li>
                            <li data-requirement="special"><span class="check">✓</span> One special character</li>
                        </ul>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="password-input-wrapper">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                placeholder="Confirm your password"
                                required
                                autocomplete="new-password"
                            >
                            <button type="button" class="toggle-password" data-target="confirm_password" aria-label="Show password">
                                <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                                <svg class="eye-off-icon hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block" id="submitBtn">
                        Reset Password
                    </button>
                </form>
            <?php endif; ?>
            
            <div class="auth-back">
                <a href="index.php" class="back-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"></path>
                    </svg>
                    Back to home
                </a>
            </div>
        </div>
    </div>
    
    <script src="assets/js/auth.js"></script>
</body>
</html>
