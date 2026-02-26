<?php
/**
 * Admin Settings Page
 * 
 * System settings including dark mode toggle.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$adminId = getCurrentAdminId();
$pageTitle = 'Settings';

$success = false;
$errors = [];

// Handle settings update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request.";
    } else {
        $darkMode = isset($_POST['dark_mode']) ? 1 : 0;
        
        // Store preference in session
        $_SESSION['admin_dark_mode'] = $darkMode;
        
        // Update admin preference in database (add column if needed)
        try {
            executeQuery("UPDATE admins SET updated_at = NOW() WHERE admin_id = ?", [$adminId]);
            $success = true;
            setFlashMessage('success', 'Settings saved successfully!');
        } catch (Exception $e) {
            $errors[] = "Failed to save settings.";
        }
    }
}

// Get current settings
$darkMode = $_SESSION['admin_dark_mode'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo $darkMode ? 'dark' : 'light'; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* Dark Mode Styles */
        [data-theme="dark"] {
            --bg-primary: #1a1a1a;
            --bg-secondary: #2d2d2d;
            --bg-card: #252525;
            --text-primary: #ffffff;
            --text-secondary: #b0b0b0;
            --border-color: #404040;
            --sidebar-bg: #1a1a1a;
            --header-bg: #252525;
        }
        
        [data-theme="dark"] body {
            background: var(--bg-primary);
            color: var(--text-primary);
        }
        
        [data-theme="dark"] .admin-sidebar {
            background: var(--sidebar-bg);
            border-right: 1px solid var(--border-color);
        }
        
        [data-theme="dark"] .admin-header {
            background: var(--header-bg);
            border-bottom: 1px solid var(--border-color);
        }
        
        [data-theme="dark"] .data-card,
        [data-theme="dark"] .form-card,
        [data-theme="dark"] .stat-card,
        [data-theme="dark"] .chart-container {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
        }
        
        [data-theme="dark"] .data-table th {
            background: var(--bg-secondary);
        }
        
        [data-theme="dark"] .data-table td {
            border-bottom-color: var(--border-color);
        }
        
        [data-theme="dark"] input,
        [data-theme="dark"] select,
        [data-theme="dark"] textarea {
            background: var(--bg-secondary);
            border-color: var(--border-color);
            color: var(--text-primary);
        }
        
        [data-theme="dark"] .page-title,
        [data-theme="dark"] h1,
        [data-theme="dark"] h2,
        [data-theme="dark"] h3 {
            color: var(--text-primary);
        }
        
        [data-theme="dark"] .breadcrumb,
        [data-theme="dark"] .stat-label,
        [data-theme="dark"] .form-hint {
            color: var(--text-secondary);
        }
        
        /* Toggle Switch */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }
        
        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }
        
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .toggle-slider {
            background-color: #1a1a1a;
        }
        
        input:checked + .toggle-slider:before {
            transform: translateX(26px);
        }
        
        /* Settings Card */
        .settings-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            max-width: 600px;
            margin-bottom: 1.5rem;
        }
        
        [data-theme="dark"] .settings-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
        }
        
        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        [data-theme="dark"] .setting-item {
            border-bottom-color: var(--border-color);
        }
        
        .setting-item:last-child {
            border-bottom: none;
        }
        
        .setting-info h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .setting-info p {
            font-size: 0.875rem;
            color: #666;
        }
        
        [data-theme="dark"] .setting-info p {
            color: var(--text-secondary);
        }
        
        .theme-preview {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .theme-option {
            flex: 1;
            padding: 1rem;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .theme-option:hover {
            border-color: #999;
        }
        
        .theme-option.active {
            border-color: #1a1a1a;
            background: #f5f5f5;
        }
        
        .theme-option .icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .theme-option .label {
            font-size: 0.875rem;
            font-weight: 500;
        }
    </style>
</head>
<body class="admin-page">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <?php include __DIR__ . '/includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <h1><?php echo $pageTitle; ?></h1>
            </div>
            
            <?php $flash = getFlashMessage(); ?>
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>" style="margin-bottom: 1.5rem;">
                    <?php echo cleanOutput($flash['message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo cleanOutput($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <!-- Appearance Settings -->
            <div class="settings-card">
                <h3 style="margin-bottom: 1.5rem;">🎨 Appearance</h3>
                
                <form method="POST" action="settings.php">
                    <?php echo csrfField(); ?>
                    
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Dark Mode</h4>
                            <p>Switch between light and dark theme for the admin panel</p>
                        </div>
                        <label class="toggle-switch">
                            <input type="checkbox" name="dark_mode" value="1" <?php echo $darkMode ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <span class="toggle-slider"></span>
                        </label>
                    </div>
                    
                    <div class="theme-preview">
                        <div class="theme-option <?php echo !$darkMode ? 'active' : ''; ?>" onclick="setTheme('light')">
                            <div class="icon">☀️</div>
                            <div class="label">Light</div>
                        </div>
                        <div class="theme-option <?php echo $darkMode ? 'active' : ''; ?>" onclick="setTheme('dark')">
                            <div class="icon">🌙</div>
                            <div class="label">Dark</div>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- System Info -->
            <div class="settings-card">
                <h3 style="margin-bottom: 1.5rem;">ℹ️ System Information</h3>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>PHP Version</h4>
                        <p><?php echo phpversion(); ?></p>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Database</h4>
                        <p>MySQL via PDO</p>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Server Time</h4>
                        <p><?php echo date('Y-m-d H:i:s T'); ?></p>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Session Lifetime</h4>
                        <p>30 minutes</p>
                    </div>
                </div>
            </div>
            
            <!-- Admin Info -->
            <div class="settings-card">
                <h3 style="margin-bottom: 1.5rem;">👤 Your Account</h3>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Username</h4>
                        <p><?php echo cleanOutput($_SESSION['admin_username'] ?? 'N/A'); ?></p>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Email</h4>
                        <p><?php echo cleanOutput($_SESSION['admin_email'] ?? 'N/A'); ?></p>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Role</h4>
                        <p><?php echo ucfirst($_SESSION['admin_role'] ?? 'Admin'); ?></p>
                    </div>
                </div>
                
                <div class="setting-item">
                    <div class="setting-info">
                        <h4>Last Login</h4>
                        <p><?php echo $_SESSION['admin_last_login'] ?? 'Just now'; ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="../assets/js/admin.js"></script>
    <script>
        function setTheme(theme) {
            const checkbox = document.querySelector('input[name="dark_mode"]');
            checkbox.checked = (theme === 'dark');
            checkbox.form.submit();
        }
        
        // Apply dark mode immediately on toggle
        document.querySelector('input[name="dark_mode"]')?.addEventListener('change', function() {
            document.documentElement.setAttribute('data-theme', this.checked ? 'dark' : 'light');
        });
    </script>
</body>
</html>
