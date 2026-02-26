<?php
/**
 * Admin Activity Logs Page
 * 
 * View system activity logs for audit purposes.
 * 
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

// Action icons - SVG icons mapping
$actionIcons = [
    'login' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path><polyline points="10 17 15 12 10 7"></polyline><line x1="15" y1="12" x2="3" y2="12"></line></svg>',
    'logout' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>',
    'register' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>',
    'create' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>',
    'update' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>',
    'delete' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>',
    'ban' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>',
    'unban' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>',
    'activate' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>',
    'deactivate' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="6" y="4" width="4" height="16"></rect><rect x="14" y="4" width="4" height="16"></rect></svg>',
    'order' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>',
    'product' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>',
    'user' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>',
    'admin' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5"></path><path d="M2 12l10 5 10-5"></path></svg>',
    'payment' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>',
    'shipping' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>'
];

function getActionIcon($action) {
    global $actionIcons;
    foreach ($actionIcons as $key => $icon) {
        if (strpos($action, $key) !== false) {
            return $icon;
        }
    }
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>';
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
            flex-shrink: 0;
        }
        
        .log-icon svg {
            width: 20px;
            height: 20px;
            color: #666;
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
                        <div class="icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" style="width: 64px; height: 64px; color: #ccc;">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                <polyline points="14 2 14 8 20 8"></polyline>
                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                <polyline points="10 9 9 9 8 9"></polyline>
                            </svg>
                        </div>
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
                                        <span>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 12px; height: 12px;">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                            Admin: <?php echo cleanOutput($log['admin_username']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($log['user_username']): ?>
                                        <span>
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 12px; height: 12px;">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                            User: <?php echo cleanOutput($log['user_username']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span>
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 12px; height: 12px;">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <line x1="2" y1="12" x2="22" y2="12"></line>
                                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                        </svg>
                                        IP: <?php echo $log['ip_address']; ?>
                                    </span>
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
