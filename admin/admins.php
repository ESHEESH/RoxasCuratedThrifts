<?php
/**
 * Admin Management Page
 * 
 * Allows super admins to view, create, edit, and manage admin accounts.
 * Only accessible by super_admin role.
 */

require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

// Only super admins can manage admins
if ($_SESSION['admin_role'] !== 'super_admin') {
    setFlashMessage('error', 'Access denied. Only super admins can manage admin accounts.');
    header("Location: index.php");
    exit();
}

$flash = getFlashMessage();

// Handle admin actions (activate, deactivate, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request.');
    } else {
        $adminId = (int)($_POST['admin_id'] ?? 0);
        $action = $_POST['action'];
        
        // Prevent self-modification
        if ($adminId === getCurrentAdminId()) {
            setFlashMessage('error', 'You cannot modify your own account.');
        } else {
            switch ($action) {
                case 'activate':
                    executeQuery("UPDATE admins SET is_active = TRUE WHERE admin_id = ?", [$adminId]);
                    logActivity('admin_activated', 'admin', $adminId);
                    setFlashMessage('success', 'Admin account activated.');
                    break;
                    
                case 'deactivate':
                    executeQuery("UPDATE admins SET is_active = FALSE WHERE admin_id = ?", [$adminId]);
                    logActivity('admin_deactivated', 'admin', $adminId);
                    setFlashMessage('success', 'Admin account deactivated.');
                    break;
                    
                case 'delete':
                    // Get admin info before deletion
                    $adminInfo = fetchOne("SELECT username, email FROM admins WHERE admin_id = ?", [$adminId]);
                    executeQuery("DELETE FROM admins WHERE admin_id = ?", [$adminId]);
                    logActivity('admin_deleted', 'admin', $adminId, $adminInfo, null);
                    setFlashMessage('success', 'Admin account deleted.');
                    break;
            }
        }
    }
    header("Location: admins.php");
    exit();
}

// Get all admins
$sql = "SELECT admin_id, username, email, full_name, role, is_active, created_at, last_login 
        FROM admins 
        ORDER BY 
            CASE role 
                WHEN 'super_admin' THEN 1 
                WHEN 'admin' THEN 2 
                WHEN 'moderator' THEN 3 
            END,
            created_at DESC";
$admins = fetchAll($sql);

$pageTitle = 'Admin Management';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Admin Panel</title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-page">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <?php include __DIR__ . '/includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <div>
                    <h1><?php echo $pageTitle; ?></h1>
                    <p class="page-subtitle">Manage admin and moderator accounts</p>
                </div>
                <a href="register.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px; margin-right: 0.5rem;">
                        <path d="M12 5v14M5 12h14"></path>
                    </svg>
                    Create Admin
                </a>
            </div>
            
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>">
                    <?php echo cleanOutput($flash['message']); ?>
                </div>
            <?php endif; ?>
            
            <!-- Admin Stats -->
            <div class="stats-grid" style="margin-bottom: 2rem;">
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e3f2fd;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#2196F3" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo count($admins); ?></div>
                        <div class="stat-label">Total Admins</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #e8f5e9;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#4caf50" stroke-width="2">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo count(array_filter($admins, fn($a) => $a['is_active'])); ?></div>
                        <div class="stat-label">Active</div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon" style="background: #fff3e0;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="#ff9800" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <div class="stat-value"><?php echo count(array_filter($admins, fn($a) => !$a['is_active'])); ?></div>
                        <div class="stat-label">Inactive</div>
                    </div>
                </div>
            </div>
            
            <!-- Admins Table -->
            <div class="card">
                <div class="card-header">
                    <h2>All Administrators</h2>
                </div>
                
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Admin</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th>Last Login</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($admins as $admin): ?>
                                <tr>
                                    <td>
                                        <div class="user-info">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($admin['full_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="user-name">
                                                    <?php echo cleanOutput($admin['full_name']); ?>
                                                    <?php if ($admin['admin_id'] === getCurrentAdminId()): ?>
                                                        <span class="badge badge-info" style="margin-left: 0.5rem;">You</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="user-email"><?php echo cleanOutput($admin['email']); ?></div>
                                                <div class="user-username">@<?php echo cleanOutput($admin['username']); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <?php
                                        $roleColors = [
                                            'super_admin' => 'badge-danger',
                                            'admin' => 'badge-primary',
                                            'moderator' => 'badge-warning'
                                        ];
                                        $roleLabels = [
                                            'super_admin' => 'Super Admin',
                                            'admin' => 'Admin',
                                            'moderator' => 'Moderator'
                                        ];
                                        ?>
                                        <span class="badge <?php echo $roleColors[$admin['role']]; ?>">
                                            <?php echo $roleLabels[$admin['role']]; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($admin['is_active']): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($admin['last_login']): ?>
                                            <span title="<?php echo formatDate($admin['last_login'], 'F j, Y g:i A'); ?>">
                                                <?php echo formatDate($admin['last_login'], 'M j, Y'); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">Never</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo formatDate($admin['created_at'], 'M j, Y'); ?></td>
                                    <td>
                                        <?php if ($admin['admin_id'] !== getCurrentAdminId()): ?>
                                            <div class="action-buttons">
                                                <?php if ($admin['is_active']): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <?php echo csrfField(); ?>
                                                        <input type="hidden" name="admin_id" value="<?php echo $admin['admin_id']; ?>">
                                                        <input type="hidden" name="action" value="deactivate">
                                                        <button type="submit" class="btn btn-sm btn-outline" 
                                                                onclick="return confirm('Deactivate this admin account?')">
                                                            Deactivate
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <form method="POST" style="display: inline;">
                                                        <?php echo csrfField(); ?>
                                                        <input type="hidden" name="admin_id" value="<?php echo $admin['admin_id']; ?>">
                                                        <input type="hidden" name="action" value="activate">
                                                        <button type="submit" class="btn btn-sm btn-success">
                                                            Activate
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                                
                                                <?php if ($admin['role'] !== 'super_admin'): ?>
                                                    <form method="POST" style="display: inline;">
                                                        <?php echo csrfField(); ?>
                                                        <input type="hidden" name="admin_id" value="<?php echo $admin['admin_id']; ?>">
                                                        <input type="hidden" name="action" value="delete">
                                                        <button type="submit" class="btn btn-sm btn-danger" 
                                                                onclick="return confirm('Delete this admin account? This action cannot be undone.')">
                                                            Delete
                                                        </button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
    
    <script src="<?php echo SITE_URL; ?>/assets/js/admin.js"></script>
</body>
</html>
