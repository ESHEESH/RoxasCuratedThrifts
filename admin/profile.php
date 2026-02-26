<?php
/**
 * Admin Profile Page
 * 
 * Allows admins to view and update their profile information.
 * 
 */

require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$adminId = getCurrentAdminId();
$pageTitle = 'My Profile';

// Get admin details
$admin = fetchOne("SELECT * FROM admins WHERE admin_id = ?", [$adminId]);

if (!$admin) {
    header("Location: login.php");
    exit();
}

// Update page title with username
$pageTitle = $admin['username'] . "'s Profile";

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    if (!validateCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid security token');
    } else {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        
        // Validate current password
        if (!password_verify($currentPassword, $admin['password'])) {
            setFlashMessage('error', 'Current password is incorrect');
        } elseif (strlen($newPassword) < 8) {
            setFlashMessage('error', 'New password must be at least 8 characters');
        } elseif ($newPassword !== $confirmPassword) {
            setFlashMessage('error', 'New passwords do not match');
        } else {
            // Update password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            executeQuery("UPDATE admins SET password = ?, updated_at = NOW() WHERE admin_id = ?", 
                [$hashedPassword, $adminId]);
            
            logActivity('password_changed', 'admin', $adminId);
            setFlashMessage('success', 'Password changed successfully');
        }
    }
    
    header("Location: profile.php");
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    if (!validateCsrfToken($_POST['csrf_token'])) {
        setFlashMessage('error', 'Invalid security token');
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeInput($_POST['email'] ?? '');
        
        // Validate
        if (empty($username) || empty($email)) {
            setFlashMessage('error', 'Username and email are required');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            setFlashMessage('error', 'Invalid email format');
        } else {
            // Check if username/email already exists (excluding current admin)
            $existing = fetchOne("SELECT admin_id FROM admins WHERE (username = ? OR email = ?) AND admin_id != ?", 
                [$username, $email, $adminId]);
            
            if ($existing) {
                setFlashMessage('error', 'Username or email already in use');
            } else {
                executeQuery("UPDATE admins SET username = ?, email = ?, updated_at = NOW() WHERE admin_id = ?", 
                    [$username, $email, $adminId]);
                
                logActivity('profile_updated', 'admin', $adminId);
                setFlashMessage('success', 'Profile updated successfully');
                
                // Refresh admin data
                $admin = fetchOne("SELECT * FROM admins WHERE admin_id = ?", [$adminId]);
            }
        }
    }
    
    header("Location: profile.php");
    exit();
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .profile-header {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }
        
        .profile-avatar {
            width: 80px;
            height: 80px;
            background: #1a1a1a;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            font-weight: 600;
            flex-shrink: 0;
        }
        
        .profile-info h2 {
            margin: 0 0 0.5rem 0;
            font-size: 1.5rem;
        }
        
        .profile-meta {
            display: flex;
            gap: 1.5rem;
            color: #666;
            font-size: 0.875rem;
        }
        
        .profile-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .role-badge.super-admin {
            background: #f3e5f5;
            color: #7b1fa2;
        }
        
        .profile-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .profile-section h3 {
            margin: 0 0 1.5rem 0;
            font-size: 1.125rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .profile-section h3 svg {
            width: 20px;
            height: 20px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .form-grid.single {
            grid-template-columns: 1fr;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #666;
            font-size: 0.875rem;
        }
        
        .info-value {
            font-weight: 500;
        }
        
        @media (max-width: 640px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>
<body class="admin-page">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <?php include __DIR__ . '/includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="profile-container">
                <!-- Flash Messages -->
                <?php if ($flash): ?>
                    <div class="alert alert-<?php echo $flash['type']; ?>" id="flashMessage">
                        <?php echo cleanOutput($flash['message']); ?>
                        <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                <?php endif; ?>
                
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php echo strtoupper(substr($admin['username'], 0, 2)); ?>
                    </div>
                    <div class="profile-info">
                        <h2><?php echo cleanOutput($admin['username']); ?></h2>
                        <div class="profile-meta">
                            <span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                    <polyline points="22,6 12,13 2,6"></polyline>
                                </svg>
                                <?php echo cleanOutput($admin['email']); ?>
                            </span>
                            <span>
                                <span class="role-badge <?php echo $admin['role'] === 'super_admin' ? 'super-admin' : ''; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?>
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Account Information -->
                <div class="profile-section">
                    <h3>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        Account Information
                    </h3>
                    <div class="info-row">
                        <span class="info-label">Admin ID</span>
                        <span class="info-value">#<?php echo $admin['admin_id']; ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Role</span>
                        <span class="info-value"><?php echo ucfirst(str_replace('_', ' ', $admin['role'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <span class="status-badge status-<?php echo $admin['is_active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $admin['is_active'] ? 'Active' : 'Inactive'; ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Member Since</span>
                        <span class="info-value"><?php echo date('F d, Y', strtotime($admin['created_at'])); ?></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Last Updated</span>
                        <span class="info-value"><?php echo date('F d, Y H:i', strtotime($admin['updated_at'])); ?></span>
                    </div>
                </div>
                
                <!-- Update Profile -->
                <div class="profile-section">
                    <h3>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                        </svg>
                        Update Profile
                    </h3>
                    <form method="POST" class="form">
                        <?php echo csrfField(); ?>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="username">Username</label>
                                <input type="text" id="username" name="username" 
                                       value="<?php echo cleanOutput($admin['username']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" 
                                       value="<?php echo cleanOutput($admin['email']); ?>" required>
                            </div>
                        </div>
                        <button type="submit" name="update_profile" class="btn btn-primary">
                            Update Profile
                        </button>
                    </form>
                </div>
                
                <!-- Change Password -->
                <div class="profile-section">
                    <h3>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        Change Password
                    </h3>
                    <form method="POST" class="form">
                        <?php echo csrfField(); ?>
                        <div class="form-grid single">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" 
                                       minlength="8" required>
                                <small>Minimum 8 characters</small>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" 
                                       minlength="8" required>
                            </div>
                        </div>
                        <button type="submit" name="change_password" class="btn btn-primary">
                            Change Password
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </main>
    
    <script src="../assets/js/admin.js"></script>
    <script>
        // Auto-hide flash messages
        setTimeout(() => {
            const flash = document.getElementById('flashMessage');
            if (flash) flash.remove();
        }, 5000);
    </script>
</body>
</html>
