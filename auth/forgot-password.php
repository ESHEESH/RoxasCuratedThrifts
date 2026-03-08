<?php
/**
 * Forgot Password Page
 * 
 * Password recovery functionality with email and phone verification
 */

// Enable error display for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header("Location: " . SITE_URL . '/index.php');
    exit();
}

$errors = [];
$success = false;
$email = '';
$phone = '';

// Process forgot password request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $email = sanitizeEmail($_POST['email'] ?? '');
        $phone = sanitizeInput($_POST['phone'] ?? '');
        $ipAddress = getClientIp();
        
        // Validate email
        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Please enter a valid email address.";
        }
        
        if (empty($errors)) {
            try {
                $db = Database::getConnection();
                
                // Check if user exists with matching email (phone column doesn't exist)
                $sql = "SELECT user_id, username, email FROM users 
                        WHERE email = ? AND is_active = 1 AND is_banned = 0";
                
                $stmt = $db->prepare($sql);
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($user && isset($user['user_id'])) {
                    // Store user ID in session for password reset
                    $_SESSION['reset_user_id'] = $user['user_id'];
                    $_SESSION['reset_verified'] = true;
                    $_SESSION['reset_time'] = time();
                    
                    // Redirect directly to reset password page
                    header("Location: reset-password.php");
                    exit();
                } else {
                    // Show generic error to prevent enumeration
                    $errors[] = "The email does not match our records.";
                }
            } catch (PDOException $e) {
                error_log("Database error in forgot-password: " . $e->getMessage());
                $errors[] = "Database error: " . $e->getMessage();
            } catch (Exception $e) {
                error_log("Forgot password error: " . $e->getMessage());
                $errors[] = "Error: " . $e->getMessage();
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
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/auth.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="auth-page">
    <div class="auth-container">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <a href="<?php echo SITE_URL; ?>/index.php">
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
                        <div class="dev-reset-link" style="background: #f0f0f0; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                            <p style="margin-bottom: 0.5rem;"><strong>Development Mode:</strong></p>
                            <p style="font-size: 0.875rem; color: #666; margin-bottom: 1rem;">Since email is not configured, use this link to reset your password:</p>
                            <a href="<?php echo $_SESSION['reset_link']; ?>" class="btn btn-primary" style="display: inline-block;">
                                Click here to reset password
                            </a>
                            <p style="font-size: 0.75rem; color: #999; margin-top: 0.5rem;">This link will expire in 1 hour.</p>
                        </div>
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
                    Enter your email address to verify your identity and reset your password.
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
                        Verify and Reset Password
                    </button>
                </form>
                
                <div class="auth-footer">
                    <p>Remember your password? <a href="login.php">Sign in</a></p>
                </div>
            <?php endif; ?>
            
            <div class="auth-back">
                <a href="<?php echo SITE_URL; ?>/index.php" class="back-link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M19 12H5M12 19l-7-7 7-7"></path>
                    </svg>
                    Back to home
                </a>
            </div>
        </div>
    </div>
    
    <script src="<?php echo SITE_URL; ?>/assets/js/auth.js"></script>
</body>
</html>
