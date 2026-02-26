<?php
/**
 * User Logout
 * 
 * Handles user logout and session destruction.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/includes/functions.php';

// Log the logout activity
if (isLoggedIn()) {
    logActivity('user_logout', 'user', getCurrentUserId());
}

// Clear session data
$_SESSION = [];

// Destroy session cookie
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

// Destroy session
session_destroy();

// Clear remember me cookie
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
}

// Redirect to home page
header("Location: index.php");
exit();
