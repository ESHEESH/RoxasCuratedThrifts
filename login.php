<?php
/**
 * Login Page
 * 
 * User authentication with security features:
 * - Rate limiting (5 attempts per 15 minutes)
 * - CSRF protection
 * - Password verification with bcrypt
 * - Session security
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
$email = '';

// Process login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $email = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);
        $ipAddress = getClientIp();
        
        // Validate inputs
        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!validateEmail($email)) {
            $errors[] = "Please enter a valid email address.";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required.";
        }
        
        // Check rate limiting if no validation errors
        if (empty($errors)) {
            $rateCheck = checkLoginAttempts($email, $ipAddress);
            if (!$rateCheck['allowed']) {
                $errors[] = $rateCheck['message'];
            }
        }
        
        // Attempt login if no errors
        if (empty($errors)) {
            // Check if user exists
            $sql = "SELECT user_id, username, email, password_hash, is_active, is_banned, ban_reason 
                    FROM users 
                    WHERE email = ?";
            $user = fetchOne($sql, [$email]);
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Check if account is active
                if (!$user['is_active']) {
                    $errors[] = "Your account has been deactivated. Please contact support.";
                    recordLoginAttempt($email, $ipAddress, false);
                } elseif ($user['is_banned']) {
                    $errors[] = "Your account has been banned. Reason: " . ($user['ban_reason'] ?: 'Violation of terms.');
                    recordLoginAttempt($email, $ipAddress, false);
                } else {
                    // Successful login - create session
                    $_SESSION['user_id'] = $user['user_id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['is_logged_in'] = true;
                    
                    // Update last login
                    $sql = "UPDATE users SET last_login = NOW() WHERE user_id = ?";
                    executeQuery($sql, [$user['user_id']]);
                    
                    // Record successful login
                    recordLoginAttempt($email, $ipAddress, true);
                    
                    // Set remember me cookie if requested
                    if ($remember) {
                        $token = bin2hex(random_bytes(32));
                        $expires = time() + (30 * 24 * 60 * 60); // 30 days
                        setcookie('remember_token', $token, [
                            'expires' => $expires,
                            'path' => '/',
                            'secure' => true,
                            'httponly' => true,
                            'samesite' => 'Strict'
                        ]);
                        
                        // Store token hash in database
                        $sql = "UPDATE users SET remember_token = ? WHERE user_id = ?";
                        executeQuery($sql, [password_hash($token, PASSWORD_DEFAULT), $user['user_id']]);
                    }
                    
                    // Redirect to intended page or home
                    $redirect = $_GET['redirect'] ?? SITE_URL . '/index.php';
                    header("Location: $redirect");
                    exit();
                }
            } else {
                // Invalid credentials
                $errors[] = "Invalid email or password.";
                recordLoginAttempt($email, $ipAddress, false);
            }
        }
    }
}

// Get flash message
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Login to <?php echo SITE_NAME; ?>">
    <title>Login - <?php echo SITE_NAME; ?></title>
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
                <p>Curated thrifted fashion</p>
            </div>
            
            <!-- Flash Messages -->
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo cleanOutput($flash['message']); ?>
                </div>
            <?php endif; ?>
            
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
            
            <!-- Login Form -->
            <form method="POST" action="login.php<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" class="auth-form" novalidate>
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
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="password-input-wrapper">
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Enter your password"
                            required
                            autocomplete="current-password"
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
                </div>
                
                <div class="form-group form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" id="remember">
                        <span class="checkmark"></span>
                        Remember me
                    </label>
                    <a href="forgot-password.php" class="forgot-link">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    Sign In
                </button>
            </form>
            
            <div class="auth-footer">
                <p>Don't have an account? <a href="register.php">Create one</a></p>
            </div>
            
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
