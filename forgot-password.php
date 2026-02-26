<?php
/**
 * Forgot Password Page
 * 
 * Password recovery functionality:
 * - Email verification
 * - Token generation with expiration
 * - Rate limiting
 * - CSRF protection
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
$email = '';

// Process forgot password request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $email = sanitizeEmail($_POST['email'] ?? '');
        $ipAddress = getClientIp();
        
        // Validate email
        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!validateEmail($email)) {
            $errors[] = "Please enter a valid email address.";
        }
        
        if (empty($errors)) {
            // Check rate limiting (max 3 requests per hour)
            $sql = "SELECT COUNT(*) as count FROM login_attempts 
                    WHERE username_or_email = ? 
                    AND attempted_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
                    AND is_successful = FALSE";
            $result = fetchOne($sql, [$email]);
            
            if ($result['count'] >= 3) {
                $errors[] = "Too many requests. Please try again in 1 hour.";
            } else {
                // Check if user exists
                $sql = "SELECT user_id, username, email FROM users WHERE email = ? AND is_active = TRUE AND is_banned = FALSE";
                $user = fetchOne($sql, [$email]);
                
                if ($user) {
                    // Generate reset token
                    $resetToken = bin2hex(random_bytes(32));
                    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
                    
                    // Save token to database
                    $sql = "UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE user_id = ?";
                    executeQuery($sql, [$resetToken, $expires, $user['user_id']]);
                    
                    // Log the request
                    logActivity('password_reset_requested', 'user', $user['user_id']);
                    
                    // TODO: Send email with reset link
                    // For development, we'll show the link
                    $resetLink = SITE_URL . '/reset-password.php?token=' . $resetToken;
                    
                    // In production, send email instead
                    // sendPasswordResetEmail($user['email'], $user['username'], $resetLink);
                    
                    $success = true;
                    
                    // Store reset link in session for demo purposes
                    $_SESSION['reset_link'] = $resetLink;
                }
                
                // Always show success message even if email not found (security)
                // This prevents email enumeration attacks
                if (!$success) {
                    // Record attempt for rate limiting
                    recordLoginAttempt($email, $ipAddress, false);
                }
                
                $success = true;
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
    <title>Forgot Password - <?php echo SITE_NAME; ?></title>
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
                <p>Password Recovery</p>
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
                    <h2>Check Your Email</h2>
                    <p>If an account exists with <strong><?php echo cleanOutput($email); ?></strong>, we've sent password reset instructions to that address.</p>
                    <p class="text-muted">The link will expire in 1 hour.</p>
                    
                    <?php if (isset($_SESSION['reset_link'])): ?>
                        <!-- Development Only: Show reset link -->
                        <div class="dev-reset-link">
                            <p><strong>Development Mode:</strong></p>
                            <a href="<?php echo $_SESSION['reset_link']; ?>" class="btn btn-secondary">
                                Click here to reset password
                            </a>
                        </div>
                        <?php unset($_SESSION['reset_link']); ?>
                    <?php endif; ?>
                    
                    <div class="auth-footer">
                        <p>Remember your password? <a href="login.php">Sign in</a></p>
                    </div>
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
                    Enter your email address and we'll send you instructions to reset your password.
                </p>
                
                <!-- Forgot Password Form -->
                <form method="POST" action="forgot-password.php" class="auth-form" novalidate>
                    <?php echo csrfField(); ?>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="<?php echo cleanOutput($email); ?>"
                            placeholder="your@email.com"
                            required
                            autocomplete="email"
                        >
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Send Reset Instructions
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p>Remember your password? <a href="login.php">Sign in</a></p>
                </div>
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
