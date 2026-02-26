<?php
/**
 * User Orders Page
 * 
 * Displays user's order history with status tracking.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/includes/functions.php';

// Require login
requireLogin('orders.php');

$userId = getCurrentUserId();

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 10;
$offset = ($page - 1) * $perPage;

// Get total orders count
$countResult = fetchOne("SELECT COUNT(*) as total FROM orders WHERE user_id = ?", [$userId]);
$totalOrders = $countResult['total'];
$totalPages = ceil($totalOrders / $perPage);

// Get orders
$sql = "SELECT o.*, 
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as item_count
        FROM orders o
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?";
$orders = fetchAll($sql, [$userId, $perPage, $offset]);

// Get user data for sidebar
$user = fetchOne("SELECT * FROM users WHERE user_id = ?", [$userId]);

$pageTitle = 'My Orders';
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
        
        .orders-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }
        
        .order-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            padding: 1.5rem;
            transition: box-shadow 0.2s;
        }
        
        .order-card:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .order-info h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .order-date {
            font-size: 0.875rem;
            color: #666;
        }
        
        .order-status {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-confirmed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        .status-processing {
            background: #d4edda;
            color: #155724;
        }
        
        .status-shipped {
            background: #cce5ff;
            color: #004085;
        }
        
        .status-delivered {
            background: #d4edda;
            color: #155724;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }
        
        .order-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .order-items {
            font-size: 0.875rem;
            color: #666;
        }
        
        .order-total {
            text-align: right;
        }
        
        .order-total .label {
            font-size: 0.875rem;
            color: #666;
        }
        
        .order-total .amount {
            font-size: 1.25rem;
            font-weight: 700;
        }
        
        .empty-orders {
            text-align: center;
            padding: 3rem;
        }
        
        .empty-orders .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .empty-orders h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .empty-orders p {
            color: #666;
            margin-bottom: 1.5rem;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }
        
        .pagination a,
        .pagination span {
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
        }
        
        .pagination a {
            background: #f5f5f5;
            color: #333;
        }
        
        .pagination a:hover {
            background: #e0e0e0;
        }
        
        .pagination .current {
            background: #1a1a1a;
            color: white;
        }
        
        @media (max-width: 768px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }
            
            .order-header {
                flex-direction: column;
                gap: 1rem;
            }
            
            .order-details {
                flex-direction: column;
                align-items: flex-start;
                gap: 1rem;
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
                <span class="current">My Orders</span>
            </nav>
            <h1 class="page-title">My Orders</h1>
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
                    <li><a href="profile.php"><span class="icon">👤</span> Profile</a></li>
                    <li><a href="orders.php" class="active"><span class="icon">📦</span> My Orders</a></li>
                    <li><a href="wishlist.php"><span class="icon">❤️</span> Wishlist</a></li>
                    <li><a href="logout.php"><span class="icon">🚪</span> Logout</a></li>
                </ul>
            </aside>
            
            <!-- Content -->
            <div class="profile-content">
                <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Order History</h2>
                
                <?php if (empty($orders)): ?>
                    <div class="empty-orders">
                        <div class="icon">📦</div>
                        <h3>No orders yet</h3>
                        <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                        <a href="products.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="orders-list">
                        <?php foreach ($orders as $order): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="order-info">
                                        <h3>Order #<?php echo $order['order_number']; ?></h3>
                                        <div class="order-date">Placed on <?php echo date('F j, Y', strtotime($order['created_at'])); ?></div>
                                    </div>
                                    <span class="order-status status-<?php echo $order['status']; ?>">
                                        <?php 
                                        $statusIcons = [
                                            'pending' => '⏳',
                                            'confirmed' => '✅',
                                            'processing' => '🔧',
                                            'shipped' => '🚚',
                                            'delivered' => '📬',
                                            'cancelled' => '❌'
                                        ];
                                        echo ($statusIcons[$order['status']] ?? '📦') . ' ';
                                        echo ucfirst($order['status']);
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="order-details">
                                    <div class="order-items">
                                        <?php echo $order['item_count']; ?> item<?php echo $order['item_count'] > 1 ? 's' : ''; ?>
                                        <?php if ($order['tracking_number']): ?>
                                            <br>Tracking: <?php echo cleanOutput($order['tracking_number']); ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="order-total">
                                        <div class="label">Total</div>
                                        <div class="amount"><?php echo formatPrice($order['total_amount']); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?>">← Previous</a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <?php if ($i == $page): ?>
                                    <span class="current"><?php echo $i; ?></span>
                                <?php else: ?>
                                    <a href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                <?php endif; ?>
                            <?php endfor; ?>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="?page=<?php echo $page + 1; ?>">Next →</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
