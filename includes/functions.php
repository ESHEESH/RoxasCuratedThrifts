<?php
/**
 * Core Functions and Security Utilities
 * 
 * This file contains all helper functions, validators, and security utilities
 * used throughout the application.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include database configuration
require_once __DIR__ . '/../config/database.php';

// =====================================================
// CONSTANTS
// =====================================================

define('SITE_NAME', 'Thrift Store');
define('SITE_URL', 'http://localhost/thrift-store');  // Change for production
define('ADMIN_EMAIL', 'admin@thriftstore.com');

// Security constants
define('MAX_LOGIN_ATTEMPTS', 5);
define('LOGIN_TIMEOUT_MINUTES', 15);
define('SESSION_LIFETIME', 1800);  // 30 minutes in seconds
define('CSRF_TOKEN_LIFETIME', 3600);  // 1 hour in seconds

// Password requirements
define('MIN_PASSWORD_LENGTH', 8);
define('MAX_PASSWORD_LENGTH', 128);

// File upload constants
define('MAX_FILE_SIZE', 5 * 1024 * 1024);  // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('UPLOAD_PATH', __DIR__ . '/../assets/images/uploads/');

// =====================================================
// INPUT SANITIZATION FUNCTIONS
// =====================================================

/**
 * Sanitize user input to prevent XSS attacks
 * 
 * Removes HTML tags and converts special characters to HTML entities.
 * Use this for all user input before displaying on page.
 * 
 * @param string $input The raw user input
 * @return string Sanitized input
 */
function sanitizeInput(string $input): string {
    $input = trim($input);
    $input = strip_tags($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    return $input;
}

/**
 * Sanitize email address
 * 
 * @param string $email The email to sanitize
 * @return string Sanitized email
 */
function sanitizeEmail(string $email): string {
    $email = trim($email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return strtolower($email);
}

/**
 * Clean output for display
 * 
 * Double-encodes HTML entities for extra security.
 * Use when displaying database content.
 * 
 * @param string $text The text to clean
 * @return string Cleaned text
 */
function cleanOutput(string $text): string {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// =====================================================
// VALIDATION FUNCTIONS
// =====================================================

/**
 * Validate email format
 * 
 * Checks if email has valid format and domain.
 * 
 * @param string $email The email to validate
 * @return bool True if valid, false otherwise
 */
function validateEmail(string $email): bool {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    // Check for valid domain
    $domain = substr(strrchr($email, '@'), 1);
    if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
        return false;
    }
    
    return true;
}

/**
 * Validate username
 * 
 * Rules:
 * - 3-20 characters
 * - Alphanumeric, underscore, hyphen only
 * - Must start with letter
 * 
 * @param string $username The username to validate
 * @return bool True if valid, false otherwise
 */
function validateUsername(string $username): bool {
    // Username must be 3-20 characters, start with letter, alphanumeric/underscore/hyphen only
    return preg_match('/^[a-zA-Z][a-zA-Z0-9_-]{2,19}$/', $username) === 1;
}

/**
 * Validate password strength
 * 
 * Requirements:
 * - Minimum 8 characters
 * - At least one uppercase letter
 * - At least one lowercase letter
 * - At least one number
 * - At least one special character
 * 
 * @param string $password The password to validate
 * @return array ['valid' => bool, 'errors' => array]
 */
function validatePassword(string $password): array {
    $errors = [];
    
    if (strlen($password) < MIN_PASSWORD_LENGTH) {
        $errors[] = "Password must be at least " . MIN_PASSWORD_LENGTH . " characters long.";
    }
    
    if (strlen($password) > MAX_PASSWORD_LENGTH) {
        $errors[] = "Password must not exceed " . MAX_PASSWORD_LENGTH . " characters.";
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "Password must contain at least one uppercase letter.";
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "Password must contain at least one lowercase letter.";
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "Password must contain at least one number.";
    }
    
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",<>.?\\|`~]/', $password)) {
        $errors[] = "Password must contain at least one special character.";
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}

/**
 * Validate phone number
 * 
 * Supports international formats.
 * 
 * @param string $phone The phone number to validate
 * @return bool True if valid, false otherwise
 */
function validatePhoneNumber(string $phone): bool {
    // Remove all non-numeric characters except +
    $phone = preg_replace('/[^0-9+]/', '', $phone);
    
    // Must be between 10-15 digits (including country code)
    return preg_match('/^\+?[0-9]{10,15}$/', $phone) === 1;
}

/**
 * Validate birthdate
 * 
 * User must be at least 13 years old.
 * 
 * @param string $birthdate Date in Y-m-d format
 * @return array ['valid' => bool, 'error' => string|null]
 */
function validateBirthdate(string $birthdate): array {
    $date = DateTime::createFromFormat('Y-m-d', $birthdate);
    
    if (!$date || $date->format('Y-m-d') !== $birthdate) {
        return ['valid' => false, 'error' => 'Invalid date format.'];
    }
    
    $today = new DateTime();
    $age = $today->diff($date)->y;
    
    if ($date > $today) {
        return ['valid' => false, 'error' => 'Birthdate cannot be in the future.'];
    }
    
    if ($age < 13) {
        return ['valid' => false, 'error' => 'You must be at least 13 years old.'];
    }
    
    if ($age > 120) {
        return ['valid' => false, 'error' => 'Please enter a valid birthdate.'];
    }
    
    return ['valid' => true, 'error' => null];
}

/**
 * Validate file upload
 * 
 * @param array $file The $_FILES array element
 * @return array ['valid' => bool, 'error' => string|null, 'path' => string|null]
 */
function validateFileUpload(array $file): array {
    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMsg = match($file['error']) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds server size limit.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            default => 'Upload failed. Please try again.'
        };
        return ['valid' => false, 'error' => $errorMsg, 'path' => null];
    }
    
    // Check file size
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['valid' => false, 'error' => 'File size must be less than 5MB.', 'path' => null];
    }
    
    // Verify MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_IMAGE_TYPES)) {
        return ['valid' => false, 'error' => 'Only JPG, PNG, GIF, and WebP images are allowed.', 'path' => null];
    }
    
    // Check extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, ALLOWED_EXTENSIONS)) {
        return ['valid' => false, 'error' => 'Invalid file extension.', 'path' => null];
    }
    
    // Generate unique filename
    $newFilename = uniqid() . '_' . bin2hex(random_bytes(8)) . '.' . $extension;
    $uploadPath = UPLOAD_PATH . $newFilename;
    
    // Ensure upload directory exists
    if (!is_dir(UPLOAD_PATH)) {
        mkdir(UPLOAD_PATH, 0755, true);
    }
    
    return ['valid' => true, 'error' => null, 'path' => $uploadPath, 'filename' => $newFilename];
}

// =====================================================
// RATE LIMITING FUNCTIONS
// =====================================================

/**
 * Check if user has exceeded login attempts
 * 
 * Implements rate limiting for login attempts.
 * 
 * @param string $identifier Username or email
 * @param string $ipAddress User's IP address
 * @return array ['allowed' => bool, 'remaining' => int, 'message' => string]
 */
function checkLoginAttempts(string $identifier, string $ipAddress): array {
    $db = Database::getConnection();
    
    // Count failed attempts in the last 15 minutes
    $sql = "SELECT COUNT(*) as attempt_count 
            FROM login_attempts 
            WHERE (username_or_email = ? OR ip_address = ?) 
            AND attempted_at > DATE_SUB(NOW(), INTERVAL ? MINUTE)
            AND is_successful = FALSE";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([$identifier, $ipAddress, LOGIN_TIMEOUT_MINUTES]);
    $result = $stmt->fetch();
    
    $attemptCount = $result['attempt_count'];
    $remaining = max(0, MAX_LOGIN_ATTEMPTS - $attemptCount);
    
    if ($attemptCount >= MAX_LOGIN_ATTEMPTS) {
        return [
            'allowed' => false,
            'remaining' => 0,
            'message' => "Too many failed attempts. Please try again in " . LOGIN_TIMEOUT_MINUTES . " minutes."
        ];
    }
    
    return [
        'allowed' => true,
        'remaining' => $remaining,
        'message' => ""
    ];
}

/**
 * Record login attempt
 * 
 * @param string $identifier Username or email
 * @param string $ipAddress User's IP address
 * @param bool $isSuccessful Whether the login was successful
 */
function recordLoginAttempt(string $identifier, string $ipAddress, bool $isSuccessful): void {
    $sql = "INSERT INTO login_attempts (username_or_email, ip_address, is_successful) 
            VALUES (?, ?, ?)";
    executeQuery($sql, [$identifier, $ipAddress, $isSuccessful ? 1 : 0]);
}

/**
 * Clear old login attempts
 * 
 * Should be run periodically via cron job.
 */
function clearOldLoginAttempts(): void {
    $sql = "DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 24 HOUR)";
    executeQuery($sql);
}

// =====================================================
// CSRF PROTECTION FUNCTIONS
// =====================================================

/**
 * Generate CSRF token
 * 
 * Creates a new CSRF token and stores it in session.
 * 
 * @return string The generated token
 */
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token']) || empty($_SESSION['csrf_token_time']) || 
        (time() - $_SESSION['csrf_token_time'] > CSRF_TOKEN_LIFETIME)) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['csrf_token_time'] = time();
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate CSRF token
 * 
 * @param string $token The token to validate
 * @return bool True if valid, false otherwise
 */
function validateCsrfToken(string $token): bool {
    if (empty($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    
    // Check token age
    if (time() - ($_SESSION['csrf_token_time'] ?? 0) > CSRF_TOKEN_LIFETIME) {
        return false;
    }
    
    return hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Get CSRF token input field
 * 
 * Returns HTML for a hidden input field with CSRF token.
 * 
 * @return string HTML input field
 */
function csrfField(): string {
    $token = generateCsrfToken();
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

// =====================================================
// SESSION MANAGEMENT FUNCTIONS
// =====================================================

/**
 * Initialize secure session
 * 
 * Sets secure session parameters.
 */
function initSecureSession(): void {
    // Regenerate session ID periodically to prevent session fixation
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerate session ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }
    
    // Check session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_LIFETIME)) {
        // Session expired, destroy it
        session_unset();
        session_destroy();
        session_start();
    }
    
    $_SESSION['last_activity'] = time();
}

/**
 * Check if user is logged in
 * 
 * @return bool True if logged in, false otherwise
 */
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if admin is logged in
 * 
 * @return bool True if admin logged in, false otherwise
 */
function isAdminLoggedIn(): bool {
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

/**
 * Require user to be logged in
 * 
 * Redirects to login page if not logged in.
 * 
 * @param string $redirect URL to redirect after login
 */
function requireLogin(string $redirect = ''): void {
    if (!isLoggedIn()) {
        $loginUrl = SITE_URL . '/login.php';
        if ($redirect) {
            $loginUrl .= '?redirect=' . urlencode($redirect);
        }
        header("Location: $loginUrl");
        exit();
    }
}

/**
 * Require admin to be logged in
 * 
 * Redirects to admin login if not logged in.
 */
function requireAdminLogin(): void {
    if (!isAdminLoggedIn()) {
        header("Location: " . SITE_URL . '/admin/login.php');
        exit();
    }
}

/**
 * Get current user ID
 * 
 * @return int|null User ID or null if not logged in
 */
function getCurrentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get current admin ID
 * 
 * @return int|null Admin ID or null if not logged in
 */
function getCurrentAdminId(): ?int {
    return $_SESSION['admin_id'] ?? null;
}

// =====================================================
// UTILITY FUNCTIONS
// =====================================================

/**
 * Generate unique order number
 * 
 * @return string Unique order number (e.g., ORD-20240225-XXXXX)
 */
function generateOrderNumber(): string {
    $prefix = 'ORD-' . date('Ymd') . '-';
    $random = strtoupper(bin2hex(random_bytes(3)));
    return $prefix . $random;
}

/**
 * Format price in Philippine Peso
 * 
 * @param float $amount The amount to format
 * @return string Formatted price
 */
function formatPrice(float $amount): string {
    return '₱' . number_format($amount, 2);
}

/**
 * Format date for display
 * 
 * @param string $date Date string
 * @param string $format Output format
 * @return string Formatted date
 */
function formatDate(string $date, string $format = 'F j, Y'): string {
    return date($format, strtotime($date));
}

/**
 * Truncate text to specified length
 * 
 * @param string $text Text to truncate
 * @param int $length Maximum length
 * @param string $suffix Suffix to add if truncated
 * @return string Truncated text
 */
function truncateText(string $text, int $length = 100, string $suffix = '...'): string {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . $suffix;
}

/**
 * Get client IP address
 * 
 * @return string IP address
 */
function getClientIp(): string {
    $headers = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
    foreach ($headers as $header) {
        if (!empty($_SERVER[$header])) {
            $ips = explode(',', $_SERVER[$header]);
            $ip = trim($ips[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

/**
 * Log activity for audit
 * 
 * @param string $action The action performed
 * @param string $entityType Type of entity (user, product, order, etc.)
 * @param int|null $entityId ID of the entity
 * @param array|null $oldValues Old values before change
 * @param array|null $newValues New values after change
 */
function logActivity(string $action, string $entityType, ?int $entityId = null, ?array $oldValues = null, ?array $newValues = null): void {
    $adminId = getCurrentAdminId();
    $userId = getCurrentUserId();
    $ipAddress = getClientIp();
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
    
    $sql = "INSERT INTO activity_logs (admin_id, user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    executeQuery($sql, [
        $adminId,
        $userId,
        $action,
        $entityType,
        $entityId,
        $oldValues ? json_encode($oldValues) : null,
        $newValues ? json_encode($newValues) : null,
        $ipAddress,
        $userAgent
    ]);
}

/**
 * Display flash message
 * 
 * @param string $type success, error, warning, info
 * @param string $message The message to display
 */
function setFlashMessage(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Get and clear flash message
 * 
 * @return array|null Flash message or null
 */
function getFlashMessage(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Redirect with flash message
 * 
 * @param string $url URL to redirect to
 * @param string $type Message type
 * @param string $message Message content
 */
function redirectWithMessage(string $url, string $type, string $message): void {
    setFlashMessage($type, $message);
    header("Location: $url");
    exit();
}

// Initialize secure session
initSecureSession();
