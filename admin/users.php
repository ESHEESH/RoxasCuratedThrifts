<?php
/**
 * User Management Page
 * 
 * Admin interface for managing users:
 * - View all users
 * - Edit user info
 * - Ban/unban users
 * - Delete users
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$adminId = getCurrentAdminId();
$pageTitle = 'User Management';

// Handle AJAX actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'];
    $userId = (int)($_POST['user_id'] ?? 0);
    
    // Prevent self-modification
    if ($userId === getCurrentUserId() && in_array($action, ['ban', 'delete'])) {
        echo json_encode(['success' => false, 'message' => 'You cannot modify your own account from here.']);
        exit();
    }
    
    switch ($action) {
        case 'toggle_status':
            /**
             * Toggle user active status
             * Disables or enables user account
             */
            $user = fetchOne("SELECT is_active FROM users WHERE user_id = ?", [$userId]);
            if ($user) {
                $newStatus = $user['is_active'] ? 0 : 1;
                executeQuery("UPDATE users SET is_active = ? WHERE user_id = ?", [$newStatus, $userId]);
                
                // Log activity
                logActivity($newStatus ? 'user_activated' : 'user_deactivated', 'user', $userId);
                
                echo json_encode(['success' => true, 'message' => $newStatus ? 'User activated' : 'User deactivated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found']);
            }
            exit();
            
        case 'toggle_ban':
            /**
             * Toggle user ban status
             * Bans or unbans user with optional reason
             */
            $user = fetchOne("SELECT is_banned FROM users WHERE user_id = ?", [$userId]);
            if ($user) {
                $newStatus = $user['is_banned'] ? 0 : 1;
                $banReason = $newStatus ? ($_POST['ban_reason'] ?? 'Violation of terms') : null;
                executeQuery("UPDATE users SET is_banned = ?, ban_reason = ? WHERE user_id = ?", [$newStatus, $banReason, $userId]);
                
                // Log activity
                logActivity($newStatus ? 'user_banned' : 'user_unbanned', 'user', $userId, null, ['ban_reason' => $banReason]);
                
                echo json_encode(['success' => true, 'message' => $newStatus ? 'User banned' : 'User unbanned']);
            } else {
                echo json_encode(['success' => false, 'message' => 'User not found']);
            }
            exit();
            
        case 'delete':
            /**
             * Delete user account permanently
             * Note: Consider soft delete for data integrity
             */
            // Check if user has orders
            $hasOrders = fetchOne("SELECT COUNT(*) as count FROM orders WHERE user_id = ?", [$userId]);
            if ($hasOrders['count'] > 0) {
                echo json_encode(['success' => false, 'message' => 'Cannot delete user with existing orders. Deactivate instead.']);
                exit();
            }
            
            executeQuery("DELETE FROM users WHERE user_id = ?", [$userId]);
            
            // Log activity
            logActivity('user_deleted', 'user', $userId);
            
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
            exit();
            
        case 'update':
            /**
             * Update user information
             */
            $username = sanitizeInput($_POST['username'] ?? '');
            $email = sanitizeEmail($_POST['email'] ?? '');
            $fullName = sanitizeInput($_POST['full_name'] ?? '');
            $phoneNumber = sanitizeInput($_POST['phone_number'] ?? '');
            
            // Validate
            if (empty($username) || empty($email)) {
                echo json_encode(['success' => false, 'message' => 'Username and email are required']);
                exit();
            }
            
            // Check for duplicates
            $existing = fetchOne("SELECT user_id FROM users WHERE (username = ? OR email = ?) AND user_id != ?", [$username, $email, $userId]);
            if ($existing) {
                echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
                exit();
            }
            
            // Get old values for logging
            $oldUser = fetchOne("SELECT * FROM users WHERE user_id = ?", [$userId]);
            
            executeQuery("UPDATE users SET username = ?, email = ?, full_name = ?, phone_number = ? WHERE user_id = ?", 
                [$username, $email, $fullName, $phoneNumber, $userId]);
            
            // Log activity
            logActivity('user_updated', 'user', $userId, $oldUser, ['username' => $username, 'email' => $email, 'full_name' => $fullName]);
            
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
            exit();
    }
}

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Filters
$search = sanitizeInput($_GET['search'] ?? '');
$statusFilter = $_GET['status'] ?? '';

// Build query
$whereConditions = ['1=1'];
$params = [];

if ($search) {
    $whereConditions[] = '(username LIKE ? OR email LIKE ? OR full_name LIKE ?)';
    $searchTerm = '%' . $search . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($statusFilter === 'active') {
    $whereConditions[] = 'is_active = TRUE AND is_banned = FALSE';
} elseif ($statusFilter === 'inactive') {
    $whereConditions[] = 'is_active = FALSE';
} elseif ($statusFilter === 'banned') {
    $whereConditions[] = 'is_banned = TRUE';
}

$whereClause = implode(' AND ', $whereConditions);

// Get total count
$countSql = "SELECT COUNT(*) as total FROM users WHERE $whereClause";
$totalUsers = fetchOne($countSql, $params)['total'];
$totalPages = ceil($totalUsers / $perPage);

// Get users
$sql = "SELECT u.*, 
        (SELECT COUNT(*) FROM orders WHERE user_id = u.user_id) as order_count,
        (SELECT SUM(total_amount) FROM orders WHERE user_id = u.user_id AND status NOT IN ('cancelled', 'refunded')) as total_spent
        FROM users u
        WHERE $whereClause
        ORDER BY u.created_at DESC
        LIMIT ? OFFSET ?";
$params[] = $perPage;
$params[] = $offset;
$users = fetchAll($sql, $params);

$flash = getFlashMessage();
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
</head>
<body class="admin-page">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <?php include __DIR__ . '/includes/header.php'; ?>
        
        <div class="admin-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>" id="flashMessage">
                    <?php echo cleanOutput($flash['message']); ?>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>
            
            <!-- Page Header -->
            <div class="page-header">
                <h1><?php echo $pageTitle; ?></h1>
                <div class="header-actions">
                    <span class="results-count"><?php echo number_format($totalUsers); ?> users</span>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="filters-bar">
                <form method="GET" class="search-form">
                    <div class="search-input">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="M21 21l-4.35-4.35"></path>
                        </svg>
                        <input type="text" name="search" placeholder="Search users..." value="<?php echo cleanOutput($search); ?>">
                    </div>
                    <select name="status" onchange="this.form.submit()">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="banned" <?php echo $statusFilter === 'banned' ? 'selected' : ''; ?>>Banned</option>
                    </select>
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <?php if ($search || $statusFilter): ?>
                        <a href="users.php" class="btn btn-outline">Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <!-- Users Table -->
            <div class="data-card">
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Contact</th>
                                <th>Joined</th>
                                <th>Orders</th>
                                <th>Spent</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr data-user-id="<?php echo $user['user_id']; ?>">
                                    <td>
                                        <div class="user-cell">
                                            <div class="user-avatar">
                                                <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                                            </div>
                                            <div class="user-info">
                                                <span class="user-name"><?php echo cleanOutput($user['username']); ?></span>
                                                <span class="user-id">ID: <?php echo $user['user_id']; ?></span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-cell">
                                            <span><?php echo cleanOutput($user['email']); ?></span>
                                            <?php if ($user['phone_number']): ?>
                                                <span><?php echo cleanOutput($user['phone_number']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                    <td><?php echo number_format($user['order_count']); ?></td>
                                    <td><?php echo formatPrice($user['total_spent'] ?? 0); ?></td>
                                    <td>
                                        <?php if ($user['is_banned']): ?>
                                            <span class="status-badge status-banned">Banned</span>
                                        <?php elseif (!$user['is_active']): ?>
                                            <span class="status-badge status-inactive">Inactive</span>
                                        <?php else: ?>
                                            <span class="status-badge status-active">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="btn btn-sm btn-outline" onclick="editUser(<?php echo $user['user_id']; ?>)">
                                                Edit
                                            </button>
                                            <?php if ($user['is_banned']): ?>
                                                <button class="btn btn-sm btn-success" onclick="toggleBan(<?php echo $user['user_id']; ?>, false)">
                                                    Unban
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-warning" onclick="showBanModal(<?php echo $user['user_id']; ?>)">
                                                    Ban
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['user_id']; ?>)">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $statusFilter; ?>" class="page-link">Previous</a>
                        <?php endif; ?>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <?php if ($i === $page): ?>
                                <span class="page-number current"><?php echo $i; ?></span>
                            <?php else: ?>
                                <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $statusFilter; ?>" class="page-number"><?php echo $i; ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo $statusFilter; ?>" class="page-link">Next</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <!-- Ban Modal -->
    <div class="modal" id="banModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Ban User</h3>
                <button class="modal-close" onclick="closeModal('banModal')">&times;</button>
            </div>
            <div class="modal-body">
                <p>Please provide a reason for banning this user:</p>
                <textarea id="banReason" rows="3" placeholder="Ban reason..."></textarea>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('banModal')">Cancel</button>
                <button class="btn btn-danger" id="confirmBanBtn">Ban User</button>
            </div>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div class="modal" id="editModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Edit User</h3>
                <button class="modal-close" onclick="closeModal('editModal')">&times;</button>
            </div>
            <div class="modal-body">
                <form id="editUserForm">
                    <input type="hidden" id="editUserId">
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" id="editUsername" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" id="editEmail" required>
                    </div>
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" id="editFullName">
                    </div>
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" id="editPhone">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-outline" onclick="closeModal('editModal')">Cancel</button>
                <button class="btn btn-primary" onclick="saveUser()">Save Changes</button>
            </div>
        </div>
    </div>
    
    <script>
        /**
         * User Management JavaScript
         * Handles all user-related actions via AJAX
         */
        
        let currentUserId = null;
        
        /**
         * Show ban modal
         * @param {number} userId - User ID to ban
         */
        function showBanModal(userId) {
            currentUserId = userId;
            document.getElementById('banModal').classList.add('active');
        }
        
        /**
         * Close modal
         * @param {string} modalId - Modal element ID
         */
        function closeModal(modalId) {
            document.getElementById(modalId).classList.remove('active');
        }
        
        /**
         * Toggle user ban status
         * @param {number} userId - User ID
         * @param {boolean} ban - True to ban, false to unban
         */
        function toggleBan(userId, ban) {
            const reason = ban ? document.getElementById('banReason').value : '';
            
            fetch('users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=toggle_ban&user_id=${userId}&ban_reason=${encodeURIComponent(reason)}&csrf_token=<?php echo generateCsrfToken(); ?>`
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            });
        }
        
        /**
         * Delete user
         * @param {number} userId - User ID to delete
         */
        function deleteUser(userId) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) return;
            
            fetch('users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=delete&user_id=${userId}&csrf_token=<?php echo generateCsrfToken(); ?>`
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            });
        }
        
        /**
         * Load user data for editing
         * @param {number} userId - User ID to edit
         */
        function editUser(userId) {
            // Fetch user data from row
            const row = document.querySelector(`tr[data-user-id="${userId}"]`);
            const username = row.querySelector('.user-name').textContent;
            const email = row.querySelector('.contact-cell span').textContent;
            
            document.getElementById('editUserId').value = userId;
            document.getElementById('editUsername').value = username;
            document.getElementById('editEmail').value = email;
            
            document.getElementById('editModal').classList.add('active');
        }
        
        /**
         * Save user changes
         */
        function saveUser() {
            const userId = document.getElementById('editUserId').value;
            const username = document.getElementById('editUsername').value;
            const email = document.getElementById('editEmail').value;
            const fullName = document.getElementById('editFullName').value;
            const phone = document.getElementById('editPhone').value;
            
            fetch('users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=update&user_id=${userId}&username=${encodeURIComponent(username)}&email=${encodeURIComponent(email)}&full_name=${encodeURIComponent(fullName)}&phone_number=${encodeURIComponent(phone)}&csrf_token=<?php echo generateCsrfToken(); ?>`
            })
            .then(r => r.json())
            .then(data => {
                alert(data.message);
                if (data.success) location.reload();
            });
        }
        
        // Confirm ban button
        document.getElementById('confirmBanBtn')?.addEventListener('click', () => {
            if (currentUserId) {
                toggleBan(currentUserId, true);
                closeModal('banModal');
            }
        });
    </script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
