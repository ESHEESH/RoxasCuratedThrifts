<?php
/**
 * Admin Dashboard
 * 
 * Main admin panel with:
 * - Sales statistics
 * - Recent orders
 * - Low stock alerts
 * - Quick actions
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$adminId = getCurrentAdminId();

// Get date range for statistics
$period = $_GET['period'] ?? 'today';
$dateRange = match($period) {
    'week' => [date('Y-m-d', strtotime('-7 days')), date('Y-m-d')],
    'month' => [date('Y-m-d', strtotime('-30 days')), date('Y-m-d')],
    'year' => [date('Y-m-d', strtotime('-365 days')), date('Y-m-d')],
    default => [date('Y-m-d'), date('Y-m-d')]
};

// Sales statistics
$sql = "SELECT 
            COUNT(DISTINCT order_id) as total_orders,
            SUM(total_amount) as total_revenue,
            AVG(total_amount) as average_order_value,
            COUNT(DISTINCT user_id) as unique_customers
        FROM orders 
        WHERE DATE(created_at) BETWEEN ? AND ?
        AND status NOT IN ('cancelled', 'refunded')";
$salesStats = fetchOne($sql, $dateRange);

// Today's orders
$sql = "SELECT o.*, u.username, u.email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE DATE(o.created_at) = CURDATE()
        ORDER BY o.created_at DESC
        LIMIT 10";
$todayOrders = fetchAll($sql);

// Low stock products
$sql = "SELECT p.*, c.name as category_name, COALESCE(SUM(pv.stock_quantity), 0) as total_stock
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN product_variants pv ON p.product_id = pv.product_id
        WHERE p.is_active = TRUE
        GROUP BY p.product_id
        HAVING total_stock <= 5
        ORDER BY total_stock ASC
        LIMIT 10";
$lowStockProducts = fetchAll($sql);

// Recent users
$sql = "SELECT user_id, username, email, created_at, is_active, is_banned
        FROM users
        ORDER BY created_at DESC
        LIMIT 10";
$recentUsers = fetchAll($sql);

// Order status counts
$sql = "SELECT status, COUNT(*) as count FROM orders GROUP BY status";
$orderStatusCounts = fetchAll($sql);
$statusCounts = array_column($orderStatusCounts, 'count', 'status');

// Total counts
$totalProducts = fetchOne("SELECT COUNT(*) as count FROM products WHERE is_active = TRUE")['count'];
$totalUsers = fetchOne("SELECT COUNT(*) as count FROM users WHERE is_active = TRUE AND is_banned = FALSE")['count'];
$totalOrders = fetchOne("SELECT COUNT(*) as count FROM orders")['count'];

// Get flash message
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-page">
    <!-- Admin Sidebar -->
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <!-- Main Content -->
    <main class="admin-main">
        <!-- Admin Header -->
        <?php include __DIR__ . '/includes/header.php'; ?>
        
        <div class="admin-content">
            <!-- Flash Messages -->
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>" id="flashMessage">
                    <?php echo cleanOutput($flash['message']); ?>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>
            
            <!-- Page Title -->
            <div class="page-header">
                <h1>Dashboard</h1>
                <div class="period-selector">
                    <a href="?period=today" class="btn btn-sm <?php echo $period === 'today' ? 'btn-primary' : 'btn-outline'; ?>">Today</a>
                    <a href="?period=week" class="btn btn-sm <?php echo $period === 'week' ? 'btn-primary' : 'btn-outline'; ?>">Week</a>
                    <a href="?period=month" class="btn btn-sm <?php echo $period === 'month' ? 'btn-primary' : 'btn-outline'; ?>">Month</a>
                    <a href="?period=year" class="btn btn-sm <?php echo $period === 'year' ? 'btn-primary' : 'btn-outline'; ?>">Year</a>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon orders">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo number_format($salesStats['total_orders'] ?? 0); ?></span>
                        <span class="stat-label">Orders</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon revenue">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo formatPrice($salesStats['total_revenue'] ?? 0); ?></span>
                        <span class="stat-label">Revenue</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon customers">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo number_format($salesStats['unique_customers'] ?? 0); ?></span>
                        <span class="stat-label">Customers</span>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon aov">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo formatPrice($salesStats['average_order_value'] ?? 0); ?></span>
                        <span class="stat-label">Avg. Order</span>
                    </div>
                </div>
            </div>
            
            <!-- Quick Stats Row -->
            <div class="quick-stats">
                <div class="quick-stat">
                    <span class="quick-value"><?php echo number_format($totalProducts); ?></span>
                    <span class="quick-label">Products</span>
                </div>
                <div class="quick-stat">
                    <span class="quick-value"><?php echo number_format($totalUsers); ?></span>
                    <span class="quick-label">Users</span>
                </div>
                <div class="quick-stat">
                    <span class="quick-value"><?php echo number_format($totalOrders); ?></span>
                    <span class="quick-label">Total Orders</span>
                </div>
                <div class="quick-stat">
                    <span class="quick-value"><?php echo $statusCounts['pending'] ?? 0; ?></span>
                    <span class="quick-label">Pending</span>
                </div>
                <div class="quick-stat">
                    <span class="quick-value"><?php echo $statusCounts['shipped'] ?? 0; ?></span>
                    <span class="quick-label">Shipped</span>
                </div>
            </div>
            
            <!-- Dashboard Grid -->
            <div class="dashboard-grid">
                <!-- Recent Orders -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Recent Orders</h2>
                        <a href="orders.php" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($todayOrders)): ?>
                            <p class="no-data">No orders today</p>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Order #</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($todayOrders as $order): ?>
                                        <tr>
                                            <td><a href="order-detail.php?id=<?php echo $order['order_id']; ?>"><?php echo $order['order_number']; ?></a></td>
                                            <td><?php echo cleanOutput($order['username'] ?: 'Guest'); ?></td>
                                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                                            <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                                            <td><?php echo date('H:i', strtotime($order['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Low Stock Alert -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Low Stock Alert</h2>
                        <a href="products.php?stock=low" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($lowStockProducts)): ?>
                            <p class="no-data">All products well stocked</p>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Stock</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($lowStockProducts as $product): ?>
                                        <tr>
                                            <td><?php echo cleanOutput($product['name']); ?></td>
                                            <td><span class="stock-low"><?php echo $product['total_stock']; ?> left</span></td>
                                            <td><a href="product-edit.php?id=<?php echo $product['product_id']; ?>" class="btn btn-sm btn-primary">Restock</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Recent Users -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Recent Users</h2>
                        <a href="users.php" class="btn btn-sm btn-outline">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($recentUsers)): ?>
                            <p class="no-data">No users yet</p>
                        <?php else: ?>
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>User</th>
                                        <th>Status</th>
                                        <th>Joined</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td>
                                                <div class="user-info">
                                                    <span class="user-name"><?php echo cleanOutput($user['username']); ?></span>
                                                    <span class="user-email"><?php echo cleanOutput($user['email']); ?></span>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if ($user['is_banned']): ?>
                                                    <span class="status-badge status-banned">Banned</span>
                                                <?php elseif (!$user['is_active']): ?>
                                                    <span class="status-badge status-inactive">Inactive</span>
                                                <?php else: ?>
                                                    <span class="status-badge status-active">Active</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2>Quick Actions</h2>
                    </div>
                    <div class="card-body">
                        <div class="quick-actions">
                            <a href="product-add.php" class="quick-action">
                                <div class="action-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 5v14M5 12h14"></path>
                                    </svg>
                                </div>
                                <span>Add Product</span>
                            </a>
                            <a href="orders.php?status=pending" class="quick-action">
                                <div class="action-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <polyline points="12 6 12 12 16 14"></polyline>
                                    </svg>
                                </div>
                                <span>Pending Orders</span>
                            </a>
                            <a href="users.php" class="quick-action">
                                <div class="action-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <span>Manage Users</span>
                            </a>
                            <a href="statistics.php" class="quick-action">
                                <div class="action-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="20" x2="18" y2="10"></line>
                                        <line x1="12" y1="20" x2="12" y2="4"></line>
                                        <line x1="6" y1="20" x2="6" y2="14"></line>
                                    </svg>
                                </div>
                                <span>Statistics</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>
