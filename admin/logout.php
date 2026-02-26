<?php
/**
 * Admin Logout
 * 
 * Handles admin logout and session destruction.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/../includes/functions.php';

// Log the logout activity
if (isAdminLoggedIn()) {
    logActivity('admin_logout', 'admin', getCurrentAdminId());
}

// Clear admin session data
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_role']);
unset($_SESSION['is_admin_logged_in']);

// Redirect to admin login
header("Location: login.php");
exit();
