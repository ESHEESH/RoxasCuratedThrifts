<?php
/**
 * Products Listing Page
 * 
 * Displays all products with filtering and sorting options.
 */

require_once __DIR__ . '/includes/functions.php';

// Get filter parameters
$categorySlug = $_GET['category'] ?? '';
$searchQuery = sanitizeInput($_GET['search'] ?? '');
$sortBy = in_array($_GET['sort'] ?? '', ['newest', 'price_low', 'price_high', 'name']) ? $_GET['sort'] : 'newest';
$condition = $_GET['condition'] ?? '';
$gender = $_GET['gender'] ?? '';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : null;

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 12;
$offset = ($page - 1) * $perPage;

// Build query
$whereConditions = ['p.is_active = TRUE'];
$params = [];

if ($categorySlug) {
    $whereConditions[] = 'c.slug = ?';
    $params[] = $categorySlug;
}

if ($searchQuery) {
    $whereConditions[] = '(p.name LIKE ? OR p.description LIKE ? OR p.brand LIKE ?)';
    $searchTerm = '%' . $searchQuery . '%';
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

if ($condition && in_array($condition, ['new', 'like_new', 'good', 'fair'])) {
    $whereConditions[] = 'p.condition_status = ?';
    $params[] = $condition;
}

if ($gender && in_array($gender, ['male', 'female', 'unisex'])) {
    $whereConditions[] = 'p.gender = ?';
    $params[] = $gender;
}

if ($minPrice !== null) {
    $whereConditions[] = 'p.base_price >= ?';
    $params[] = $minPrice;
}

if ($maxPrice !== null) {
    $whereConditions[] = 'p.base_price <= ?';
    $params[] = $maxPrice;
}

$whereClause = implode(' AND ', $whereConditions);

// Sort order
$orderBy = match($sortBy) {
    'price_low' => 'p.base_price ASC',
    'price_high' => 'p.base_price DESC',
    'name' => 'p.name ASC',
    default => 'p.created_at DESC'
};

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM products p 
             LEFT JOIN categories c ON p.category_id = c.category_id 
             WHERE $whereClause";
$countResult = fetchOne($countSql, $params);
$totalProducts = $countResult['total'];
$totalPages = ceil($totalProducts / $perPage);

// Get products
$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug, 
        pi.image_path as primary_image,
        (SELECT MIN(pv.stock_quantity) FROM product_variants pv WHERE pv.product_id = p.product_id) as min_stock,
        (SELECT COUNT(*) FROM product_variants pv WHERE pv.product_id = p.product_id AND pv.stock_quantity > 0) as variant_count
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = TRUE
        WHERE $whereClause
        ORDER BY $orderBy
        LIMIT ? OFFSET ?";

$params[] = $perPage;
$params[] = $offset;
$products = fetchAll($sql, $params);

// Get all categories for filter
$categories = fetchAll("SELECT * FROM categories WHERE is_active = TRUE ORDER BY display_order");

// Get current category name
$currentCategory = null;
if ($categorySlug) {
    $currentCategory = fetchOne("SELECT * FROM categories WHERE slug = ?", [$categorySlug]);
}

// Page title
$pageTitle = $currentCategory ? $currentCategory['name'] : ($searchQuery ? 'Search: ' . $searchQuery : 'All Products');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Browse our curated collection of thrifted fashion">
    <title><?php echo cleanOutput($pageTitle); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="products-page">
        <!-- Page Header -->
        <div class="page-header">
            <div class="container">
                <nav class="breadcrumb">
                    <a href="index.php">Home</a>
                    <span class="separator">/</span>
                    <?php if ($currentCategory): ?>
                        <a href="products.php">Shop</a>
                        <span class="separator">/</span>
                        <span class="current"><?php echo cleanOutput($currentCategory['name']); ?></span>
                    <?php elseif ($searchQuery): ?>
                        <span class="current">Search Results</span>
                    <?php else: ?>
                        <span class="current">All Products</span>
                    <?php endif; ?>
                </nav>
                
                <h1 class="page-title"><?php echo cleanOutput($pageTitle); ?></h1>
                
                <?php if ($searchQuery): ?>
                    <p class="search-results-info">Found <?php echo $totalProducts; ?> result<?php echo $totalProducts !== 1 ? 's' : ''; ?> for "<?php echo cleanOutput($searchQuery); ?>"</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="container">
            <div class="products-layout">
                <!-- Sidebar Filters -->
                <aside class="filters-sidebar">
                    <div class="filters-header">
                        <h3>Filters</h3>
                        <button class="filter-toggle" id="filterToggle">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="4" y1="21" x2="4" y2="14"></line>
                                <line x1="4" y1="10" x2="4" y2="3"></line>
                                <line x1="12" y1="21" x2="12" y2="12"></line>
                                <line x1="12" y1="8" x2="12" y2="3"></line>
                                <line x1="20" y1="21" x2="20" y2="16"></line>
                                <line x1="20" y1="12" x2="20" y2="3"></line>
                                <line x1="1" y1="14" x2="7" y2="14"></line>
                                <line x1="9" y1="8" x2="15" y2="8"></line>
                                <line x1="17" y1="16" x2="23" y2="16"></line>
                            </svg>
                        </button>
                    </div>
                    
                    <form class="filters-form" id="filtersForm" method="GET" action="products.php">
                        <!-- Search (if searching) -->
                        <?php if ($searchQuery): ?>
                            <input type="hidden" name="search" value="<?php echo cleanOutput($searchQuery); ?>">
                        <?php endif; ?>
                        
                        <!-- Categories -->
                        <div class="filter-group">
                            <h4 class="filter-title">Categories</h4>
                            <ul class="filter-list">
                                <li>
                                    <label class="filter-option">
                                        <input type="radio" name="category" value="" <?php echo !$categorySlug ? 'checked' : ''; ?>>
                                        <span>All Categories</span>
                                    </label>
                                </li>
                                <?php foreach ($categories as $cat): ?>
                                    <li>
                                        <label class="filter-option">
                                            <input type="radio" name="category" value="<?php echo $cat['slug']; ?>" <?php echo $categorySlug === $cat['slug'] ? 'checked' : ''; ?>>
                                            <span><?php echo cleanOutput($cat['name']); ?></span>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <!-- Condition -->
                        <div class="filter-group">
                            <h4 class="filter-title">Condition</h4>
                            <ul class="filter-list">
                                <?php foreach (['new' => 'New', 'like_new' => 'Like New', 'good' => 'Good', 'fair' => 'Fair'] as $value => $label): ?>
                                    <li>
                                        <label class="filter-option">
                                            <input type="radio" name="condition" value="<?php echo $value; ?>" <?php echo $condition === $value ? 'checked' : ''; ?>>
                                            <span><?php echo $label; ?></span>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <!-- Gender -->
                        <div class="filter-group">
                            <h4 class="filter-title">Gender</h4>
                            <ul class="filter-list">
                                <li>
                                    <label class="filter-option">
                                        <input type="radio" name="gender" value="" <?php echo empty($gender) ? 'checked' : ''; ?>>
                                        <span>All</span>
                                    </label>
                                </li>
                                <?php foreach (['male' => 'Male', 'female' => 'Female', 'unisex' => 'Unisex'] as $value => $label): ?>
                                    <li>
                                        <label class="filter-option">
                                            <input type="radio" name="gender" value="<?php echo $value; ?>" <?php echo ($gender ?? '') === $value ? 'checked' : ''; ?>>
                                            <span><?php echo $label; ?></span>
                                        </label>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="filter-group">
                            <h4 class="filter-title">Price Range</h4>
                            <div class="price-inputs">
                                <div class="price-field">
                                    <span class="currency">₱</span>
                                    <input type="number" name="min_price" placeholder="Min" value="<?php echo $minPrice ?? ''; ?>" min="0">
                                </div>
                                <span class="price-separator">-</span>
                                <div class="price-field">
                                    <span class="currency">₱</span>
                                    <input type="number" name="max_price" placeholder="Max" value="<?php echo $maxPrice ?? ''; ?>" min="0">
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                        <a href="products.php" class="btn btn-outline btn-block">Clear All</a>
                    </form>
                </aside>
                
                <!-- Products Grid -->
                <div class="products-content">
                    <!-- Toolbar -->
                    <div class="products-toolbar">
                        <p class="results-count">Showing <?php echo count($products); ?> of <?php echo $totalProducts; ?> products</p>
                        
                        <div class="sort-dropdown">
                            <label for="sort">Sort by:</label>
                            <select name="sort" id="sort" onchange="window.location.href = updateQueryParam('sort', this.value)">
                                <option value="newest" <?php echo $sortBy === 'newest' ? 'selected' : ''; ?>>Newest</option>
                                <option value="price_low" <?php echo $sortBy === 'price_low' ? 'selected' : ''; ?>>Price: Low to High</option>
                                <option value="price_high" <?php echo $sortBy === 'price_high' ? 'selected' : ''; ?>>Price: High to Low</option>
                                <option value="name" <?php echo $sortBy === 'name' ? 'selected' : ''; ?>>Name</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Products Grid -->
                    <div class="products-grid">
                        <?php if (empty($products)): ?>
                            <div class="no-results">
                                <div class="no-results-icon">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <circle cx="11" cy="11" r="8"></circle>
                                        <path d="M21 21l-4.35-4.35"></path>
                                    </svg>
                                </div>
                                <h3>No products found</h3>
                                <p>Try adjusting your filters or search query.</p>
                                <a href="products.php" class="btn btn-primary">View All Products</a>
                            </div>
                        <?php else: ?>
                            <?php foreach ($products as $product): ?>
                                <article class="product-card">
                                    <a href="product-detail.php?slug=<?php echo $product['slug']; ?>" class="product-link">
                                        <div class="product-image">
                                            <?php 
                                            // Product emojis based on category
                                            $productEmojis = [
                                                'clothes' => ['👕', '👗', '🧥', '👖', '🩳', '🧣'],
                                                'shoes' => ['👟', '👠', '👢', '🩴', '👞', '👡'],
                                                'bags' => ['👜', '🎒', '💼', '🧳', '👛', '👝'],
                                                'caps' => ['🧢', '👒', '🎩', '🎓', '⛑️', '🪖']
                                            ];
                                            $catSlug = $product['category_slug'] ?? 'clothes';
                                            $emojis = $productEmojis[$catSlug] ?? $productEmojis['clothes'];
                                            $emoji = $emojis[$product['product_id'] % count($emojis)];
                                            ?>
                                            <?php if ($product['primary_image']): ?>
                                                <img src="assets/images/products/<?php echo cleanOutput($product['primary_image']); ?>" 
                                                     alt="<?php echo cleanOutput($product['name']); ?>"
                                                     loading="lazy">
                                            <?php else: ?>
                                                <div class="product-placeholder">
                                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                        <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                        <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                        <polyline points="21 15 16 10 5 21"></polyline>
                                                    </svg>
                                                    <span class="placeholder-text">No Image</span>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <span class="condition-badge condition-<?php echo $product['condition_status']; ?>">
                                                <?php echo ucwords(str_replace('_', ' ', $product['condition_status'])); ?>
                                            </span>
                                            
                                            <?php if ($product['min_stock'] <= 3 && $product['min_stock'] > 0): ?>
                                                <span class="stock-badge low-stock">Only <?php echo $product['min_stock']; ?> left</span>
                                            <?php elseif ($product['min_stock'] == 0): ?>
                                                <span class="stock-badge out-of-stock">Out of Stock</span>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <div class="product-info">
                                            <span class="product-category"><?php echo cleanOutput($product['category_name']); ?></span>
                                            <h3 class="product-name"><?php echo cleanOutput($product['name']); ?></h3>
                                            
                                            <div class="product-price">
                                                <span class="current-price"><?php echo formatPrice($product['base_price']); ?></span>
                                                <?php if ($product['original_price']): ?>
                                                    <span class="original-price"><?php echo formatPrice($product['original_price']); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <?php if ($product['variant_count'] > 1): ?>
                                                <span class="variant-count"><?php echo $product['variant_count']; ?> options available</span>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </article>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <nav class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="<?php echo updateQueryParam('page', $page - 1); ?>" class="page-link prev">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M15 18l-6-6 6-6"></path>
                                    </svg>
                                    Previous
                                </a>
                            <?php endif; ?>
                            
                            <div class="page-numbers">
                                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                    <?php if ($i === $page): ?>
                                        <span class="page-number current"><?php echo $i; ?></span>
                                    <?php else: ?>
                                        <a href="<?php echo updateQueryParam('page', $i); ?>" class="page-number"><?php echo $i; ?></a>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            
                            <?php if ($page < $totalPages): ?>
                                <a href="<?php echo updateQueryParam('page', $page + 1); ?>" class="page-link next">
                                    Next
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M9 18l6-6-6-6"></path>
                                    </svg>
                                </a>
                            <?php endif; ?>
                        </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <script>
        /**
         * Update URL query parameter without reloading
         * @param {string} param - Parameter name
         * @param {string} value - Parameter value
         * @returns {string} Updated URL
         */
        function updateQueryParam(param, value) {
            const url = new URL(window.location.href);
            url.searchParams.set(param, value);
            return url.toString();
        }
        
        // Mobile filter toggle
        document.getElementById('filterToggle')?.addEventListener('click', function() {
            document.getElementById('filtersForm').classList.toggle('active');
        });
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>
