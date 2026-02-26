<?php
/**
 * Admin Products Management
 * 
 * Product listing and management page for admins.
 * Features:
 * - View all products with filtering
 * - Search products
 * - Filter by category, stock status
 * - Quick actions (edit, delete, toggle status)
 * - Pagination
 * 
 */

require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$adminId = getCurrentAdminId();

// Handle product deletion (soft delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_product'])) {
    $productId = (int)$_POST['product_id'];
    
    // Soft delete - set is_active to false
    executeQuery("UPDATE products SET is_active = FALSE WHERE product_id = ?", [$productId]);
    
    // Log the action
    logActivity('product_deleted', 'product', $productId);
    
    setFlashMessage('success', 'Product moved to trash');
    header("Location: products.php");
    exit();
}

// Handle status toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_status'])) {
    $productId = (int)$_POST['product_id'];
    $currentStatus = fetchOne("SELECT is_active FROM products WHERE product_id = ?", [$productId]);
    $newStatus = $currentStatus['is_active'] ? 0 : 1;
    
    logActivity('product_status_updated', 'product', $productId);
    
    executeQuery("UPDATE products SET is_active = ? WHERE product_id = ?", [$newStatus, $productId]);
    setFlashMessage('success', 'Product status updated');
    header("Location: products.php");
    exit();
}

// Get filter parameters
$search = $_GET['search'] ?? '';
$categoryFilter = $_GET['category'] ?? '';
$stockFilter = $_GET['stock'] ?? '';
$statusFilter = $_GET['status'] ?? 'active'; // Default to showing only active products
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 20;
$offset = ($page - 1) * $perPage;

// Build query
$whereConditions = [];
$params = [];

// Status filter
if ($statusFilter === 'active') {
    $whereConditions[] = 'p.is_active = TRUE';
} elseif ($statusFilter === 'inactive') {
    $whereConditions[] = 'p.is_active = FALSE';
}
// If 'all', don't add any status condition

if ($search) {
    $whereConditions[] = '(p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?)';
    $searchTerm = "%$search%";
    $params = array_merge($params, [$searchTerm, $searchTerm, $searchTerm]);
}

if ($categoryFilter) {
    $whereConditions[] = 'p.category_id = ?';
    $params[] = $categoryFilter;
}

if ($stockFilter === 'low') {
    $whereConditions[] = 'COALESCE(SUM(pv.stock_quantity), 0) <= 5';
} elseif ($stockFilter === 'out') {
    $whereConditions[] = 'COALESCE(SUM(pv.stock_quantity), 0) = 0';
}

$whereClause = !empty($whereConditions) ? implode(' AND ', $whereConditions) : '1=1';

// Get total count
$countSql = "SELECT COUNT(DISTINCT p.product_id) as total 
             FROM products p 
             LEFT JOIN product_variants pv ON p.product_id = pv.product_id
             WHERE $whereClause";
$totalResult = fetchOne($countSql, $params);
$totalProducts = $totalResult['total'];
$totalPages = ceil($totalProducts / $perPage);

// Get products
$sql = "SELECT p.*, c.name as category_name, 
               COALESCE(SUM(pv.stock_quantity), 0) as total_stock,
               COUNT(DISTINCT pv.variant_id) as variant_count,
               pi.image_path as primary_image
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN product_variants pv ON p.product_id = pv.product_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = TRUE
        WHERE $whereClause
        GROUP BY p.product_id
        ORDER BY p.created_at DESC
        LIMIT ? OFFSET ?";

$params[] = $perPage;
$params[] = $offset;
$products = fetchAll($sql, $params);

// Get categories for filter
$categories = fetchAll("SELECT * FROM categories WHERE is_active = TRUE ORDER BY name");

// Get flash message
$flash = getFlashMessage();
$adminId = getCurrentAdminId();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - Admin Panel</title>
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
            
            <!-- Page Header -->
            <div class="page-header">
                <h1>Products</h1>
                <a href="product-add.php" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 18px; height: 18px;">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Add Product
                </a>
            </div>
            
            <!-- Filters -->
            <div class="filters-card">
                <form method="GET" class="filters-form">
                    <div class="filter-group">
                        <input type="text" name="search" placeholder="Search products..." 
                               value="<?php echo cleanOutput($search); ?>" class="filter-input">
                    </div>
                    
                    <div class="filter-group">
                        <select name="category" class="filter-select">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['category_id']; ?>" 
                                    <?php echo $categoryFilter == $category['category_id'] ? 'selected' : ''; ?>>
                                    <?php echo cleanOutput($category['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <select name="stock" class="filter-select">
                            <option value="">All Stock</option>
                            <option value="low" <?php echo $stockFilter === 'low' ? 'selected' : ''; ?>>Low Stock (≤5)</option>
                            <option value="out" <?php echo $stockFilter === 'out' ? 'selected' : ''; ?>>Out of Stock</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <select name="status" class="filter-select">
                            <option value="active" <?php echo $statusFilter === 'active' ? 'selected' : ''; ?>>Active Only</option>
                            <option value="inactive" <?php echo $statusFilter === 'inactive' ? 'selected' : ''; ?>>Inactive Only</option>
                            <option value="all" <?php echo $statusFilter === 'all' ? 'selected' : ''; ?>>All Products</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <a href="products.php?status=active" class="btn btn-outline">Clear</a>
                </form>
            </div>
            
            <!-- Products Table -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2>All Products (<?php echo number_format($totalProducts); ?>)</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($products)): ?>
                        <p class="no-data">No products found</p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Category</th>
                                        <th>Base Price</th>
                                        <th>Stock</th>
                                        <th>Variants</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                        <tr style="<?php echo !$product['is_active'] ? 'opacity: 0.6; background: #f9f9f9;' : ''; ?>">
                                            <td>
                                                <div class="product-info">
                                                    <div class="product-image">
                                                        <?php if ($product['primary_image']): ?>
                                                            <img src="../assets/images/products/<?php echo cleanOutput($product['primary_image']); ?>" 
                                                                 alt="<?php echo cleanOutput($product['name']); ?>">
                                                        <?php else: ?>
                                                            <div class="product-placeholder">
                                                                <?php 
                                                                $emojis = ['clothes' => '👕', 'shoes' => '👟', 'bags' => '👜', 'caps' => '🧢'];
                                                                echo $emojis[$product['category_name']] ?? '📦';
                                                                ?>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="product-details">
                                                        <span class="product-name"><?php echo cleanOutput($product['name']); ?></span>
                                                        <span class="product-brand"><?php echo cleanOutput($product['brand'] ?: 'No brand'); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo cleanOutput($product['category_name'] ?: 'Uncategorized'); ?></td>
                                            <td><?php echo formatPrice($product['base_price']); ?></td>
                                            <td>
                                                <span class="stock-badge <?php echo $product['total_stock'] <= 5 ? 'stock-low' : ($product['total_stock'] == 0 ? 'stock-out' : 'stock-ok'); ?>">
                                                    <?php echo $product['total_stock']; ?> units
                                                </span>
                                            </td>
                                            <td><?php echo $product['variant_count']; ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $product['is_active'] ? 'active' : 'inactive'; ?>">
                                                    <?php echo $product['is_active'] ? 'Active' : 'Inactive'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="product-edit.php?id=<?php echo $product['product_id']; ?>" 
                                                       class="btn btn-sm btn-outline" title="Edit">
                                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                                            <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                            <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                        </svg>
                                                    </a>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Toggle status for this product?');">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                                        <button type="submit" name="toggle_status" class="btn btn-sm btn-outline" 
                                                                title="<?php echo $product['is_active'] ? 'Deactivate' : 'Activate'; ?>"
                                                                style="<?php echo !$product['is_active'] ? 'opacity: 0.5;' : ''; ?>">
                                                            <?php if ($product['is_active']): ?>
                                                                <!-- Open Eye (Active) -->
                                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                                                    <circle cx="12" cy="12" r="3"></circle>
                                                                </svg>
                                                            <?php else: ?>
                                                                <!-- Closed Eye (Inactive) -->
                                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                                                    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
                                                                    <line x1="1" y1="1" x2="23" y2="23"></line>
                                                                </svg>
                                                            <?php endif; ?>
                                                        </button>
                                                    </form>
                                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                                        <input type="hidden" name="product_id" value="<?php echo $product['product_id']; ?>">
                                                        <button type="submit" name="delete_product" class="btn btn-sm btn-danger" title="Delete">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 16px; height: 16px;">
                                                                <polyline points="3 6 5 6 21 6"></polyline>
                                                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                            </svg>
                                                        </button>
                                                    </form>
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
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>&stock=<?php echo $stockFilter; ?>&status=<?php echo $statusFilter; ?>" 
                                       class="btn btn-outline">Previous</a>
                                <?php endif; ?>
                                
                                <span class="page-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></span>
                                
                                <?php if ($page < $totalPages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&category=<?php echo $categoryFilter; ?>&stock=<?php echo $stockFilter; ?>&status=<?php echo $statusFilter; ?>" 
                                       class="btn btn-outline">Next</a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        // Auto-hide flash messages
        setTimeout(() => {
            const flash = document.getElementById('flashMessage');
            if (flash) flash.remove();
        }, 5000);
        
        // Auto-submit filters on change
        document.querySelectorAll('.filter-select').forEach(select => {
            select.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });
    </script>
</body>
</html>
