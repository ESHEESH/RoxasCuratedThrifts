<?php
/**
 * Admin Registration Page
 * 
 * Allows super admins to create new admin accounts.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

// Only super admins can create new admins
if ($_SESSION['admin_role'] !== 'super_admin') {
    setFlashMessage('error', 'Only super admins can create new admin accounts.');
    header("Location: index.php");
    exit();
}

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $username = sanitizeInput($_POST['username'] ?? '');
        $email = sanitizeEmail($_POST['email'] ?? '');
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $role = in_array($_POST['role'] ?? '', ['admin', 'moderator']) ? $_POST['role'] : 'admin';
        
        // Validation
        if (empty($username)) {
            $errors[] = "Username is required.";
        } elseif (!validateUsername($username)) {
            $errors[] = "Username must be 3-20 characters, start with a letter, and contain only letters, numbers, underscores, or hyphens.";
        }
        
        if (empty($email)) {
            $errors[] = "Email is required.";
        } elseif (!validateEmail($email)) {
            $errors[] = "Please enter a valid email address.";
        }
        
        if (empty($fullName)) {
            $errors[] = "Full name is required.";
        }
        
        if (empty($password)) {
            $errors[] = "Password is required.";
        } else {
            $passwordValidation = validatePassword($password);
            if (!$passwordValidation['valid']) {
                $errors = array_merge($errors, $passwordValidation['errors']);
            }
        }
        
        if ($password !== $confirmPassword) {
            $errors[] = "Passwords do not match.";
        }
        
        // Check for duplicates
        if (empty($errors)) {
            $existing = fetchOne("SELECT admin_id FROM admins WHERE username = ? OR email = ?", [$username, $email]);
            if ($existing) {
                $errors[] = "Username or email already exists.";
            }
        }
        
        // Create admin
        if (empty($errors)) {
            try {
                $passwordHash = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
                
                $sql = "INSERT INTO admins (username, email, password_hash, full_name, role) VALUES (?, ?, ?, ?, ?)";
                executeQuery($sql, [$username, $email, $passwordHash, $fullName, $role]);
                
                // Log activity
                logActivity('admin_created', 'admin', getLastInsertId());
                
                $success = true;
                setFlashMessage('success', 'Admin account created successfully!');
                
            } catch (Exception $e) {
                error_log("Admin creation error: " . $e->getMessage());
                $errors[] = "An error occurred. Please try again.";
            }
        }
    }
}

$pageTitle = 'Create Admin';
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
        .form-card {
            max-width: 600px;
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        
        .password-requirements {
            margin-top: 0.5rem;
            padding: 0.75rem;
            background: #f8f8f8;
            border-radius: 8px;
            font-size: 0.75rem;
        }
        
        .password-requirements li {
            margin-bottom: 0.25rem;
            color: #666;
        }
        
        .password-requirements li.met {
            color: #22c55e;
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
            
            <div class="form-card">
                <p style="color: #666; margin-bottom: 1.5rem;">Create a new admin or moderator account. They will be able to access the admin panel.</p>
                
                <?php if ($success): ?>
                    <div class="alert alert-success" style="margin-bottom: 1rem;">
                        Admin account created successfully!
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error" style="margin-bottom: 1rem;">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo cleanOutput($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="register.php">
                    <?php echo csrfField(); ?>
                    
                    <div class="form-row" style="margin-bottom: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="username">Username *</label>
                            <input type="text" id="username" name="username" required 
                                   value="<?php echo cleanOutput($_POST['username'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="email">Email *</label>
                            <input type="email" id="email" name="email" required
                                   value="<?php echo cleanOutput($_POST['email'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required
                               value="<?php echo cleanOutput($_POST['full_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="role">Role *</label>
                        <select id="role" name="role" required>
                            <option value="admin" <?php echo ($_POST['role'] ?? '') === 'admin' ? 'selected' : ''; ?>>Admin (Full Access)</option>
                            <option value="moderator" <?php echo ($_POST['role'] ?? '') === 'moderator' ? 'selected' : ''; ?>>Moderator (Limited Access)</option>
                        </select>
                    </div>
                    
                    <div class="form-row" style="margin-bottom: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="password">Password *</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="confirm_password">Confirm Password *</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>
                    </div>
                    
                    <ul class="password-requirements" id="passwordRequirements">
                        <li data-requirement="length">✓ At least 8 characters</li>
                        <li data-requirement="uppercase">✓ One uppercase letter</li>
                        <li data-requirement="lowercase">✓ One lowercase letter</li>
                        <li data-requirement="number">✓ One number</li>
                        <li data-requirement="special">✓ One special character</li>
                    </ul>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                        <button type="submit" class="btn btn-primary">Create Admin</button>
                        <a href="index.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <script src="../assets/js/admin.js"></script>
    <script>
        // Password strength indicator
        const passwordInput = document.getElementById('password');
        const requirements = document.getElementById('passwordRequirements');
        
        passwordInput?.addEventListener('input', function() {
            const password = this.value;
            const checks = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password),
                special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
            };
            
            requirements.querySelectorAll('li').forEach(li => {
                const req = li.dataset.requirement;
                if (checks[req]) {
                    li.classList.add('met');
                } else {
                    li.classList.remove('met');
                }
            });
        });
    </script>
</body>
</html>
