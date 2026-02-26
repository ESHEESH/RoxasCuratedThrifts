<?php
/**
 * Admin Orders Page
 * 
 * View and manage all orders with filters.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$adminId = getCurrentAdminId();
$pageTitle = 'Orders';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    $orderId = (int)($_POST['order_id'] ?? 0);
    
    switch ($action) {
        case 'update_status':
            $newStatus = $_POST['status'] ?? '';
            $validStatuses = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];
            
            if (!in_array($newStatus, $validStatuses)) {
                echo json_encode(['success' => false, 'message' => 'Invalid status']);
                exit();
            }
            
            $updates = ['status = ?'];
            $params = [$newStatus];
            
            if ($newStatus === 'shipped') {
                $updates[] = 'shipped_at = NOW()';
            } elseif ($newStatus === 'delivered') {
                $updates[] = 'delivered_at = NOW()';
            }
            
            $updates[] = 'updated_at = NOW()';
            $params[] = $orderId;
            
            $sql = "UPDATE orders SET " . implode(', ', $updates) . " WHERE order_id = ?";
            executeQuery($sql, $params);
            
            logActivity('order_status_updated', 'order', $orderId, null, ['status' => $newStatus]);
            
            echo json_encode(['success' => true, 'message' => 'Status updated']);
            exit();
            
        case 'add_tracking':
            $trackingNumber = sanitizeInput($_POST['tracking_number'] ?? '');
            if (empty($trackingNumber)) {
                echo json_encode(['success' => false, 'message' => 'Tracking number required']);
                exit();
            }
            
            executeQuery("UPDATE orders SET tracking_number = ?, status = 'shipped', shipped_at = NOW() WHERE order_id = ?", 
                [$trackingNumber, $orderId]);
            
            logActivity('tracking_added', 'order', $orderId);
            
            echo json_encode(['success' => true, 'message' => 'Tracking number added']);
            exit();
    }
}

// Filters
$statusFilter = $_GET['status'] ?? '';
$search = sanitizeInput($_GET['search'] ?? '');
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$whereConditions = ['1=1'];
$params = [];

if ($statusFilter) {
    $whereConditions[] = 'o.status = ?';
    $params[] = $statusFilter;
}

if ($search) {
    $whereConditions[] = '(o.order_number LIKE ? OR u.username LIKE ? OR u.email LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($dateFrom) {
    $whereConditions[] = 'DATE(o.created_at) >= ?';
    $params[] = $dateFrom;
}

if ($dateTo) {
    $whereConditions[] = 'DATE(o.created_at) <= ?';
    $params[] = $dateTo;
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM orders o LEFT JOIN users u ON o.user_id = u.user_id WHERE $whereClause";
$totalOrders = fetchOne($countSql, $params)['total'];
$totalPages = ceil($totalOrders / $perPage);

// Get orders
$sql = "SELECT o.*, u.username, u.email, u.full_name as user_full_name,
        (SELECT COUNT(*) FROM order_items WHERE order_id = o.order_id) as item_count
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.user_id
        WHERE $whereClause
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$orders = fetchAll($sql, $params);

// Get pending orders count for badge
$pendingCount = fetchOne("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'")['count'];

// Status colors
$statusColors = [
    'pending' => ['bg' => '#fff3cd', 'text' => '#856404'],
    'confirmed' => ['bg' => '#d1ecf1', 'text' => '#0c5460'],
    'processing' => ['bg' => '#d4edda', 'text' => '#155724'],
    'shipped' => ['bg' => '#cce5ff', 'text' => '#004085'],
    'delivered' => ['bg' => '#d4edda', 'text' => '#155724'],
    'cancelled' => ['bg' => '#f8d7da', 'text' => '#721c24'],
    'refunded' => ['bg' => '#e2e3e5', 'text' => '#383d41']
];
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
        .filters-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filters-bar input,
        .filters-bar select {
            padding: 0.625rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.875rem;
        }
        
        .filters-bar input[type="date"] {
            width: 150px;
        }
        
        .status-filters {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            margin-bottom: 1.5rem;
        }
        
        .status-filter {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            background: #f5f5f5;
            color: #666;
            transition: all 0.2s;
        }
        
        .status-filter:hover,
        .status-filter.active {
            background: #1a1a1a;
            color: white;
        }
        
        .status-filter .badge {
            background: #dc3545;
            color: white;
            font-size: 0.75rem;
            padding: 0.125rem 0.5rem;
            border-radius: 10px;
            margin-left: 0.5rem;
        }
        
        .order-row {
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .order-row:hover {
            background: #f8f8f8;
        }
        
        .order-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal {
            background: white;
            border-radius: 16px;
            width: 90%;
            max-width: 600px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .modal-header h3 {
            font-size: 1.25rem;
            font-weight: 600;
        }
        
        .modal-close {
            font-size: 1.5rem;
            color: #666;
            background: none;
            border: none;
            cursor: pointer;
        }
        
        .modal-body {
            padding: 1.5rem;
        }
        
        .order-detail-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .order-detail-row:last-child {
            border-bottom: none;
        }
        
        .order-detail-row .label {
            color: #666;
        }
        
        .modal-footer {
            display: flex;
            gap: 1rem;
            padding: 1.5rem;
            border-top: 1px solid #e0e0e0;
            justify-content: flex-end;
        }
        
        .quick-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .quick-actions select {
            padding: 0.5rem;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.875rem;
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
                <span style="color: #666;"><?php echo number_format($totalOrders); ?> orders</span>
            </div>
            
            <!-- Status Filters -->
            <div class="status-filters">
                <a href="orders.php" class="status-filter <?php echo !$statusFilter ? 'active' : ''; ?>">All Orders</a>
                <a href="orders.php?status=pending" class="status-filter <?php echo $statusFilter === 'pending' ? 'active' : ''; ?>">
                    Pending
                    <?php if ($pendingCount > 0): ?>
                        <span class="badge"><?php echo $pendingCount; ?></span>
                    <?php endif; ?>
                </a>
                <a href="orders.php?status=confirmed" class="status-filter <?php echo $statusFilter === 'confirmed' ? 'active' : ''; ?>">Confirmed</a>
                <a href="orders.php?status=processing" class="status-filter <?php echo $statusFilter === 'processing' ? 'active' : ''; ?>">Processing</a>
                <a href="orders.php?status=shipped" class="status-filter <?php echo $statusFilter === 'shipped' ? 'active' : ''; ?>">Shipped</a>
                <a href="orders.php?status=delivered" class="status-filter <?php echo $statusFilter === 'delivered' ? 'active' : ''; ?>">Delivered</a>
                <a href="orders.php?status=cancelled" class="status-filter <?php echo $statusFilter === 'cancelled' ? 'active' : ''; ?>">Cancelled</a>
            </div>
            
            <!-- Filters Bar -->
            <form method="GET" class="filters-bar">
                <input type="text" name="search" placeholder="Search orders..." value="<?php echo cleanOutput($search); ?>">
                <input type="date" name="date_from" placeholder="From" value="<?php echo $dateFrom; ?>">
                <input type="date" name="date_to" placeholder="To" value="<?php echo $dateTo; ?>">
                <?php if ($statusFilter): ?>
                    <input type="hidden" name="status" value="<?php echo $statusFilter; ?>">
                <?php endif; ?>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="orders.php" class="btn btn-outline btn-sm">Clear</a>
            </form>
            
            <!-- Orders Table -->
            <div class="data-card">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Customer</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): 
                                $statusColor = $statusColors[$order['status']] ?? ['bg' => '#f5f5f5', 'text' => '#666'];
                            ?>
                                <tr class="order-row" onclick="showOrderDetail(<?php echo $order['order_id']; ?>)">
                                    <td><strong><?php echo $order['order_number']; ?></strong></td>
                                    <td>
                                        <?php echo cleanOutput($order['username'] ?: $order['user_full_name'] ?: 'Guest'); ?>
                                        <br><small style="color: #666;"><?php echo cleanOutput($order['email']); ?></small>
                                    </td>
                                    <td><?php echo $order['item_count']; ?> items</td>
                                    <td><strong><?php echo formatPrice($order['total_amount']); ?></strong></td>
                                    <td>
                                        <span class="order-status-badge" style="background: <?php echo $statusColor['bg']; ?>; color: <?php echo $statusColor['text']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M d, Y H:i', strtotime($order['created_at'])); ?></td>
                                    <td>
                                        <div class="quick-actions" onclick="event.stopPropagation()">
                                            <select onchange="updateStatus(<?php echo $order['order_id']; ?>, this.value)">
                                                <option value="">Update Status</option>
                                                <option value="pending">Pending</option>
                                                <option value="confirmed">Confirmed</option>
                                                <option value="processing">Processing</option>
                                                <option value="shipped">Shipped</option>
                                                <option value="delivered">Delivered</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (empty($orders)): ?>
                    <p style="text-align: center; padding: 3rem; color: #666;">No orders found</p>
                <?php endif; ?>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" class="page-link">← Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="page-number current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" class="page-number"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&status=<?php echo $statusFilter; ?>&search=<?php echo urlencode($search); ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" class="page-link">Next →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Order Detail Modal -->
    <div class="modal-overlay" id="orderModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Order Details</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content loaded via JS -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
    <script>
        // Store order data for modal
        const orders = <?php echo json_encode($orders); ?>;
        
        function showOrderDetail(orderId) {
            const order = orders.find(o => o.order_id == orderId);
            if (!order) return;
            
            const modalBody = document.getElementById('modalBody');
            modalBody.innerHTML = `
                <div class="order-detail-row">
                    <span class="label">Order Number</span>
                    <span class="value">${order.order_number}</span>
                </div>
                <div class="order-detail-row">
                    <span class="label">Customer</span>
                    <span class="value">${order.username || order.user_full_name || 'Guest'}</span>
                </div>
                <div class="order-detail-row">
                    <span class="label">Email</span>
                    <span class="value">${order.email || 'N/A'}</span>
                </div>
                <div class="order-detail-row">
                    <span class="label">Shipping Address</span>
                    <span class="value">${order.address}, ${order.city}, ${order.country}</span>
                </div>
                <div class="order-detail-row">
                    <span class="label">Phone</span>
                    <span class="value">${order.phone_number}</span>
                </div>
                <div class="order-detail-row">
                    <span class="label">Payment Method</span>
                    <span class="value">${order.payment_method.replace('_', ' ').toUpperCase()}</span>
                </div>
                <div class="order-detail-row">
                    <span class="label">Subtotal</span>
                    <span class="value">₱${parseFloat(order.subtotal).toLocaleString()}</span>
                </div>
                <div class="order-detail-row">
                    <span class="label">Shipping</span>
                    <span class="value">₱${parseFloat(order.shipping_fee).toLocaleString()}</span>
                </div>
                <div class="order-detail-row">
                    <span class="label"><strong>Total</strong></span>
                    <span class="value"><strong>₱${parseFloat(order.total_amount).toLocaleString()}</strong></span>
                </div>
                ${order.tracking_number ? `
                <div class="order-detail-row">
                    <span class="label">Tracking Number</span>
                    <span class="value">${order.tracking_number}</span>
                </div>
                ` : ''}
            `;
            
            document.getElementById('orderModal').classList.add('active');
        }
        
        function closeModal() {
            document.getElementById('orderModal').classList.remove('active');
        }
        
        function updateStatus(orderId, status) {
            if (!status) return;
            
            fetch('orders.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update_status&order_id=${orderId}&status=${status}&csrf_token=<?php echo generateCsrfToken(); ?>`
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            });
        }
        
        // Close modal on overlay click
        document.getElementById('orderModal').addEventListener('click', function(e) {
            if (e.target === this) closeModal();
        });
    </script>
</body>
</html>
