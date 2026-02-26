<?php
/**
 * Admin Statistics Page
 * 
 * Shows sales statistics, charts, and analytics.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$adminId = getCurrentAdminId();
$pageTitle = 'Statistics';

// Get date range
$period = $_GET['period'] ?? 'month';
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

// Set default date ranges
if (empty($startDate) || empty($endDate)) {
    switch ($period) {
        case 'today':
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
            break;
        case 'week':
            $startDate = date('Y-m-d', strtotime('-7 days'));
            $endDate = date('Y-m-d');
            break;
        case 'year':
            $startDate = date('Y-m-d', strtotime('-365 days'));
            $endDate = date('Y-m-d');
            break;
        default: // month
            $startDate = date('Y-m-d', strtotime('-30 days'));
            $endDate = date('Y-m-d');
            $period = 'month';
    }
}

// Sales statistics
$salesStats = fetchOne("SELECT 
    COUNT(DISTINCT order_id) as total_orders,
    SUM(total_amount) as total_revenue,
    AVG(total_amount) as avg_order_value,
    SUM(shipping_fee) as total_shipping
FROM orders 
WHERE DATE(created_at) BETWEEN ? AND ?
AND status NOT IN ('cancelled', 'refunded')", [$startDate, $endDate]);

// Daily sales data for chart
$dailySales = fetchAll("SELECT 
    DATE(created_at) as date,
    COUNT(*) as orders,
    SUM(total_amount) as revenue
FROM orders 
WHERE DATE(created_at) BETWEEN ? AND ?
AND status NOT IN ('cancelled', 'refunded')
GROUP BY DATE(created_at)
ORDER BY date", [$startDate, $endDate]);

// Top selling products
$topProducts = fetchAll("SELECT 
    p.product_id,
    p.name,
    p.slug,
    SUM(oi.quantity) as total_sold,
    SUM(oi.total_price) as total_revenue
FROM order_items oi
JOIN products p ON oi.variant_id IN (SELECT variant_id FROM product_variants WHERE product_id = p.product_id)
JOIN orders o ON oi.order_id = o.order_id
WHERE DATE(o.created_at) BETWEEN ? AND ?
AND o.status NOT IN ('cancelled', 'refunded')
GROUP BY p.product_id
ORDER BY total_sold DESC
LIMIT 10", [$startDate, $endDate]);

// Order status breakdown
$statusBreakdown = fetchAll("SELECT 
    status,
    COUNT(*) as count,
    SUM(total_amount) as revenue
FROM orders 
WHERE DATE(created_at) BETWEEN ? AND ?
GROUP BY status", [$startDate, $endDate]);

// Customer statistics
$customerStats = fetchOne("SELECT 
    COUNT(DISTINCT user_id) as unique_customers,
    COUNT(DISTINCT CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY) THEN user_id END) as new_customers
FROM orders 
WHERE DATE(created_at) BETWEEN ? AND ?", [$startDate, $endDate]);

// Payment method breakdown
$paymentMethods = fetchAll("SELECT 
    payment_method,
    COUNT(*) as count,
    SUM(total_amount) as total
FROM orders 
WHERE DATE(created_at) BETWEEN ? AND ?
AND status NOT IN ('cancelled', 'refunded')
GROUP BY payment_method", [$startDate, $endDate]);

// Prepare chart data
$chartLabels = [];
$chartData = [];
foreach ($dailySales as $day) {
    $chartLabels[] = date('M d', strtotime($day['date']));
    $chartData[] = $day['revenue'];
}
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .stats-overview {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card-large {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .stat-card-large .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .stat-card-large .stat-value {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }
        
        .stat-card-large .stat-label {
            color: #666;
            font-size: 0.875rem;
        }
        
        .chart-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .chart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .chart-header h3 {
            font-size: 1.125rem;
            font-weight: 600;
        }
        
        .charts-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .data-table-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .data-table-card h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .status-pills {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .status-pill {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: #f5f5f5;
            border-radius: 20px;
            font-size: 0.875rem;
        }
        
        .status-pill .dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }
        
        .filters-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        
        .filters-bar select,
        .filters-bar input {
            padding: 0.5rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.875rem;
        }
        
        @media (max-width: 1024px) {
            .stats-overview {
                grid-template-columns: repeat(2, 1fr);
            }
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 640px) {
            .stats-overview {
                grid-template-columns: 1fr;
            }
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
            
            <!-- Date Filters -->
            <div class="filters-bar">
                <select onchange="window.location.href='?period=' + this.value">
                    <option value="today" <?php echo $period === 'today' ? 'selected' : ''; ?>>Today</option>
                    <option value="week" <?php echo $period === 'week' ? 'selected' : ''; ?>>Last 7 Days</option>
                    <option value="month" <?php echo $period === 'month' ? 'selected' : ''; ?>>Last 30 Days</option>
                    <option value="year" <?php echo $period === 'year' ? 'selected' : ''; ?>>Last Year</option>
                </select>
                <input type="date" value="<?php echo $startDate; ?>" onchange="updateDateRange(this.value, 'start')">
                <span>to</span>
                <input type="date" value="<?php echo $endDate; ?>" onchange="updateDateRange(this.value, 'end')">
            </div>
            
            <!-- Stats Overview -->
            <div class="stats-overview">
                <div class="stat-card-large">
                    <div class="stat-icon" style="background: #e3f2fd;">💰</div>
                    <div class="stat-value"><?php echo formatPrice($salesStats['total_revenue'] ?? 0); ?></div>
                    <div class="stat-label">Total Revenue</div>
                </div>
                <div class="stat-card-large">
                    <div class="stat-icon" style="background: #e8f5e9;">📦</div>
                    <div class="stat-value"><?php echo number_format($salesStats['total_orders'] ?? 0); ?></div>
                    <div class="stat-label">Total Orders</div>
                </div>
                <div class="stat-card-large">
                    <div class="stat-icon" style="background: #fff3e0;">📊</div>
                    <div class="stat-value"><?php echo formatPrice($salesStats['avg_order_value'] ?? 0); ?></div>
                    <div class="stat-label">Avg Order Value</div>
                </div>
                <div class="stat-card-large">
                    <div class="stat-icon" style="background: #f3e5f5;">👥</div>
                    <div class="stat-value"><?php echo number_format($customerStats['unique_customers'] ?? 0); ?></div>
                    <div class="stat-label">Unique Customers</div>
                </div>
            </div>
            
            <!-- Charts -->
            <div class="charts-grid">
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Revenue Over Time</h3>
                    </div>
                    <canvas id="revenueChart" height="100"></canvas>
                </div>
                
                <div class="chart-container">
                    <div class="chart-header">
                        <h3>Order Status</h3>
                    </div>
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
            
            <!-- Status Pills -->
            <div class="chart-container" style="margin-bottom: 2rem;">
                <h3 style="margin-bottom: 1rem;">Order Status Breakdown</h3>
                <div class="status-pills">
                    <?php 
                    $statusColors = [
                        'pending' => '#ffc107',
                        'confirmed' => '#17a2b8',
                        'processing' => '#6f42c1',
                        'shipped' => '#007bff',
                        'delivered' => '#28a745',
                        'cancelled' => '#dc3545',
                        'refunded' => '#6c757d'
                    ];
                    foreach ($statusBreakdown as $status): 
                        $color = $statusColors[$status['status']] ?? '#666';
                    ?>
                        <div class="status-pill">
                            <span class="dot" style="background: <?php echo $color; ?>"></span>
                            <span><?php echo ucfirst($status['status']); ?>: <?php echo $status['count']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
            
            <!-- Top Products & Payment Methods -->
            <div class="charts-grid">
                <div class="data-table-card">
                    <h3>Top Selling Products</h3>
                    <?php if (empty($topProducts)): ?>
                        <p style="color: #666; text-align: center; padding: 2rem;">No sales data for this period</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Sold</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topProducts as $product): ?>
                                    <tr>
                                        <td><a href="../product-detail.php?slug=<?php echo $product['slug']; ?>" target="_blank"><?php echo cleanOutput($product['name']); ?></a></td>
                                        <td><?php echo $product['total_sold']; ?></td>
                                        <td><?php echo formatPrice($product['total_revenue']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
                
                <div class="data-table-card">
                    <h3>Payment Methods</h3>
                    <?php if (empty($paymentMethods)): ?>
                        <p style="color: #666; text-align: center; padding: 2rem;">No payment data</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Method</th>
                                    <th>Orders</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paymentMethods as $method): ?>
                                    <tr>
                                        <td><?php echo ucfirst(str_replace('_', ' ', $method['payment_method'])); ?></td>
                                        <td><?php echo $method['count']; ?></td>
                                        <td><?php echo formatPrice($method['total']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <script src="../assets/js/admin.js"></script>
    <script>
        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($chartLabels); ?>,
                datasets: [{
                    label: 'Revenue',
                    data: <?php echo json_encode($chartData); ?>,
                    borderColor: '#1a1a1a',
                    backgroundColor: 'rgba(26, 26, 26, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '₱' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
        
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode(array_column($statusBreakdown, 'status')); ?>,
                datasets: [{
                    data: <?php echo json_encode(array_column($statusBreakdown, 'count')); ?>,
                    backgroundColor: [
                        '#ffc107', '#17a2b8', '#6f42c1', '#007bff', '#28a745', '#dc3545', '#6c757d'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
        
        function updateDateRange(value, type) {
            const url = new URL(window.location.href);
            url.searchParams.set(type + '_date', value);
            window.location.href = url.toString();
        }
    </script>
</body>
</html>
