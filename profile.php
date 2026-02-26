<?php
/**
 * User Profile Page
 * 
 * Allows users to view and edit their profile information.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/includes/functions.php';

// Require login
requireLogin('profile.php');

$userId = getCurrentUserId();
$errors = [];
$success = false;

// Fetch user data
$user = fetchOne("SELECT * FROM users WHERE user_id = ?", [$userId]);

if (!$user) {
    header("Location: logout.php");
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $fullName = sanitizeInput($_POST['full_name'] ?? '');
        $phoneNumber = sanitizeInput($_POST['phone_number'] ?? '');
        $country = sanitizeInput($_POST['country'] ?? '');
        $city = sanitizeInput($_POST['city'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $postalCode = sanitizeInput($_POST['postal_code'] ?? '');
        
        // Validate phone number if provided
        if ($phoneNumber && !validatePhoneNumber($phoneNumber)) {
            $errors[] = "Please enter a valid phone number.";
        }
        
        if (empty($errors)) {
            try {
                $sql = "UPDATE users SET 
                        full_name = ?, 
                        phone_number = ?, 
                        country = ?, 
                        city = ?, 
                        address = ?, 
                        postal_code = ?,
                        updated_at = NOW()
                        WHERE user_id = ?";
                
                executeQuery($sql, [
                    $fullName,
                    $phoneNumber,
                    $country,
                    $city,
                    $address,
                    $postalCode,
                    $userId
                ]);
                
                // Refresh user data
                $user = fetchOne("SELECT * FROM users WHERE user_id = ?", [$userId]);
                $success = true;
                
                setFlashMessage('success', 'Profile updated successfully!');
                
            } catch (Exception $e) {
                error_log("Profile update error: " . $e->getMessage());
                $errors[] = "An error occurred. Please try again.";
            }
        }
    }
}

// Get order statistics
$orderStats = fetchOne("SELECT 
    COUNT(*) as total_orders,
    SUM(CASE WHEN status = 'delivered' THEN 1 ELSE 0 END) as delivered_orders,
    SUM(total_amount) as total_spent
    FROM orders 
    WHERE user_id = ? AND status NOT IN ('cancelled', 'refunded')", [$userId]);

$pageTitle = 'My Profile';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .profile-sidebar {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .profile-avatar-large {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin: 0 auto 1rem;
        }
        
        .profile-name {
            text-align: center;
            font-weight: 600;
            font-size: 1.125rem;
        }
        
        .profile-email {
            text-align: center;
            color: #666;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }
        
        .profile-menu {
            list-style: none;
        }
        
        .profile-menu li {
            margin-bottom: 0.25rem;
        }
        
        .profile-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: #333;
            transition: all 0.2s;
        }
        
        .profile-menu a:hover,
        .profile-menu a.active {
            background: #f5f5f5;
            color: #1a1a1a;
        }
        
        .profile-menu .icon {
            font-size: 1.25rem;
        }
        
        .profile-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-box {
            background: #f8f8f8;
            border-radius: 8px;
            padding: 1rem;
            text-align: center;
        }
        
        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1a1a1a;
        }
        
        .stat-label {
            font-size: 0.875rem;
            color: #666;
        }
        
        @media (max-width: 768px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="container" style="padding-top: 90px; padding-bottom: 3rem;">
        <!-- Page Header -->
        <div class="page-header" style="background: none; padding: 0; margin-bottom: 0;">
            <nav class="breadcrumb">
                <a href="index.php">Home</a>
                <span class="separator">/</span>
                <span class="current">My Profile</span>
            </nav>
            <h1 class="page-title">My Profile</h1>
        </div>
        
        <div class="profile-layout">
            <!-- Sidebar -->
            <aside class="profile-sidebar">
                <div class="profile-avatar-large">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <div class="profile-name"><?php echo cleanOutput($user['username']); ?></div>
                <div class="profile-email"><?php echo cleanOutput($user['email']); ?></div>
                
                <ul class="profile-menu">
                    <li><a href="profile.php" class="active"><span class="icon">👤</span> Profile</a></li>
                    <li><a href="orders.php"><span class="icon">📦</span> My Orders</a></li>
                    <li><a href="wishlist.php"><span class="icon">❤️</span> Wishlist</a></li>
                    <li><a href="logout.php"><span class="icon">🚪</span> Logout</a></li>
                </ul>
            </aside>
            
            <!-- Content -->
            <div class="profile-content">
                <!-- Stats -->
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $orderStats['total_orders'] ?? 0; ?></div>
                        <div class="stat-label">Total Orders</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo $orderStats['delivered_orders'] ?? 0; ?></div>
                        <div class="stat-label">Delivered</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value"><?php echo formatPrice($orderStats['total_spent'] ?? 0); ?></div>
                        <div class="stat-label">Total Spent</div>
                    </div>
                </div>
                
                <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Edit Profile</h2>
                
                <?php if ($success): ?>
                    <div class="alert alert-success" style="margin-bottom: 1rem;">
                        Profile updated successfully!
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error" style="margin-bottom: 1rem;">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo cleanOutput($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="profile.php">
                    <?php echo csrfField(); ?>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Username</label>
                            <input type="text" value="<?php echo cleanOutput($user['username']); ?>" disabled style="background: #f5f5f5;">
                            <small class="form-hint">Username cannot be changed</small>
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Email</label>
                            <input type="email" value="<?php echo cleanOutput($user['email']); ?>" disabled style="background: #f5f5f5;">
                            <small class="form-hint">Email cannot be changed</small>
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?php echo cleanOutput($user['full_name'] ?? ''); ?>" placeholder="Enter your full name">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="phone_number">Phone Number</label>
                            <input type="tel" id="phone_number" name="phone_number" value="<?php echo cleanOutput($user['phone_number'] ?? ''); ?>" placeholder="+63 912 345 6789">
                        </div>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin-bottom: 1rem;">
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="country">Country</label>
                            <input type="text" id="country" name="country" value="<?php echo cleanOutput($user['country'] ?? ''); ?>" placeholder="Your country">
                        </div>
                        
                        <div class="form-group" style="margin-bottom: 0;">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" value="<?php echo cleanOutput($user['city'] ?? ''); ?>" placeholder="Your city">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1rem;">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3" placeholder="Your complete address"><?php echo cleanOutput($user['address'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 1.5rem;">
                        <label for="postal_code">Postal Code</label>
                        <input type="text" id="postal_code" name="postal_code" value="<?php echo cleanOutput($user['postal_code'] ?? ''); ?>" placeholder="Postal code">
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
