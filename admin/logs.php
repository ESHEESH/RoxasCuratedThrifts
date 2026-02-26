<?php
/**
 * Admin Activity Logs Page
 * 
 * View system activity logs for audit purposes.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$adminId = getCurrentAdminId();
$pageTitle = 'Activity Logs';

// Filters
$actionFilter = sanitizeInput($_GET['action'] ?? '');
$entityFilter = sanitizeInput($_GET['entity'] ?? '');
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 50;
$offset = ($page - 1) * $perPage;

// Build query
$whereConditions = ['1=1'];
$params = [];

if ($actionFilter) {
    $whereConditions[] = 'al.action LIKE ?';
    $params[] = '%' . $actionFilter . '%';
}

if ($entityFilter) {
    $whereConditions[] = 'al.entity_type = ?';
    $params[] = $entityFilter;
}

if ($dateFrom) {
    $whereConditions[] = 'DATE(al.created_at) >= ?';
    $params[] = $dateFrom;
}

if ($dateTo) {
    $whereConditions[] = 'DATE(al.created_at) <= ?';
    $params[] = $dateTo;
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM activity_logs al WHERE $whereClause";
$totalLogs = fetchOne($countSql, $params)['total'];
$totalPages = ceil($totalLogs / $perPage);

// Get logs
$sql = "SELECT al.*, 
        a.username as admin_username,
        u.username as user_username
        FROM activity_logs al
        LEFT JOIN admins a ON al.admin_id = a.admin_id
        LEFT JOIN users u ON al.user_id = u.user_id
        WHERE $whereClause
        ORDER BY al.created_at DESC
        LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$logs = fetchAll($sql, $params);

// Get unique actions and entities for filters
$actions = fetchAll("SELECT DISTINCT action FROM activity_logs ORDER BY action LIMIT 50");
$entities = fetchAll("SELECT DISTINCT entity_type FROM activity_logs ORDER BY entity_type");

// Action icons
$actionIcons = [
    'login' => '🔑',
    'logout' => '🚪',
    'register' => '✍️',
    'create' => '➕',
    'update' => '✏️',
    'delete' => '🗑️',
    'ban' => '🚫',
    'unban' => '✅',
    'activate' => '✅',
    'deactivate' => '⏸️',
    'order' => '📦',
    'product' => '👕',
    'user' => '👤',
    'admin' => '👑',
    'payment' => '💳',
    'shipping' => '🚚'
];

function getActionIcon($action) {
    global $actionIcons;
    foreach ($actionIcons as $key => $icon) {
        if (strpos($action, $key) !== false) {
            return $icon;
        }
    }
    return '📝';
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
        
        .log-entry {
            display: flex;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            align-items: flex-start;
        }
        
        .log-entry:last-child {
            border-bottom: none;
        }
        
        .log-icon {
            width: 40px;
            height: 40px;
            background: #f5f5f5;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            flex-shrink: 0;
        }
        
        .log-content {
            flex: 1;
        }
        
        .log-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.25rem;
        }
        
        .log-action {
            font-weight: 600;
            color: #1a1a1a;
        }
        
        .log-time {
            font-size: 0.75rem;
            color: #999;
        }
        
        .log-details {
            font-size: 0.875rem;
            color: #666;
        }
        
        .log-meta {
            display: flex;
            gap: 1rem;
            margin-top: 0.5rem;
            font-size: 0.75rem;
            color: #999;
        }
        
        .log-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .entity-badge {
            display: inline-block;
            padding: 0.125rem 0.5rem;
            background: #e3f2fd;
            color: #1976d2;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .data-changes {
            margin-top: 0.5rem;
            padding: 0.5rem;
            background: #f8f8f8;
            border-radius: 6px;
            font-size: 0.75rem;
            font-family: monospace;
        }
        
        .data-changes .change {
            margin-bottom: 0.25rem;
        }
        
        .data-changes .old {
            color: #dc3545;
            text-decoration: line-through;
        }
        
        .data-changes .new {
            color: #28a745;
        }
        
        .empty-logs {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .empty-logs .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
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
                <span style="color: #666;"><?php echo number_format($totalLogs); ?> entries</span>
            </div>
            
            <!-- Filters -->
            <form method="GET" class="filters-bar">
                <select name="action">
                    <option value="">All Actions</option>
                    <?php foreach ($actions as $a): ?>
                        <option value="<?php echo $a['action']; ?>" <?php echo $actionFilter === $a['action'] ? 'selected' : ''; ?>>
                            <?php echo ucfirst(str_replace('_', ' ', $a['action'])); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <select name="entity">
                    <option value="">All Entities</option>
                    <?php foreach ($entities as $e): ?>
                        <option value="<?php echo $e['entity_type']; ?>" <?php echo $entityFilter === $e['entity_type'] ? 'selected' : ''; ?>>
                            <?php echo ucfirst($e['entity_type']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="date" name="date_from" placeholder="From" value="<?php echo $dateFrom; ?>">
                <input type="date" name="date_to" placeholder="To" value="<?php echo $dateTo; ?>">
                
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                <a href="logs.php" class="btn btn-outline btn-sm">Clear</a>
            </form>
            
            <!-- Logs List -->
            <div class="data-card">
                <?php if (empty($logs)): ?>
                    <div class="empty-logs">
                        <div class="icon">📋</div>
                        <h3>No activity logs found</h3>
                        <p>Activity will appear here when users and admins perform actions.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($logs as $log): 
                        $oldValues = $log['old_values'] ? json_decode($log['old_values'], true) : null;
                        $newValues = $log['new_values'] ? json_decode($log['new_values'], true) : null;
                    ?>
                        <div class="log-entry">
                            <div class="log-icon"><?php echo getActionIcon($log['action']); ?></div>
                            <div class="log-content">
                                <div class="log-header">
                                    <span class="log-action"><?php echo ucfirst(str_replace('_', ' ', $log['action'])); ?></span>
                                    <span class="log-time"><?php echo date('M d, Y H:i:s', strtotime($log['created_at'])); ?></span>
                                </div>
                                <div class="log-details">
                                    <span class="entity-badge"><?php echo ucfirst($log['entity_type']); ?></span>
                                    <?php if ($log['entity_id']): ?>
                                        #<?php echo $log['entity_id']; ?>
                                    <?php endif; ?>
                                </div>
                                <div class="log-meta">
                                    <?php if ($log['admin_username']): ?>
                                        <span>👤 Admin: <?php echo cleanOutput($log['admin_username']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($log['user_username']): ?>
                                        <span>👤 User: <?php echo cleanOutput($log['user_username']); ?></span>
                                    <?php endif; ?>
                                    <span>🌐 IP: <?php echo $log['ip_address']; ?></span>
                                </div>
                                
                                <?php if ($oldValues || $newValues): ?>
                                    <div class="data-changes">
                                        <?php if ($oldValues): ?>
                                            <div class="change">
                                                <span class="old">Old: <?php echo json_encode($oldValues); ?></span>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($newValues): ?>
                                            <div class="change">
                                                <span class="new">New: <?php echo json_encode($newValues); ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&action=<?php echo urlencode($actionFilter); ?>&entity=<?php echo urlencode($entityFilter); ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" class="page-link">← Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <?php if ($i == $page): ?>
                                <span class="page-number current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&action=<?php echo urlencode($actionFilter); ?>&entity=<?php echo urlencode($entityFilter); ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" class="page-number"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&action=<?php echo urlencode($actionFilter); ?>&entity=<?php echo urlencode($entityFilter); ?>&date_from=<?php echo $dateFrom; ?>&date_to=<?php echo $dateTo; ?>" class="page-link">Next →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>
