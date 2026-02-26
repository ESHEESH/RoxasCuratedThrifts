<?php
/**
 * Product Detail Page
 * 
 * Shows detailed product information with:
 * - Image gallery
 * - Size and color selection
 * - Size-based pricing
 * - Stock information
 * - Add to cart functionality
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/includes/functions.php';

// Get product slug from URL
$slug = $_GET['slug'] ?? '';

if (empty($slug)) {
    header("Location: products.php");
    exit();
}

// Fetch product details
$sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE p.slug = ? AND p.is_active = TRUE";
$product = fetchOne($sql, [$slug]);

if (!$product) {
    header("Location: products.php");
    exit();
}

// Fetch product images
$sql = "SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, display_order ASC";
$images = fetchAll($sql, [$product['product_id']]);

// Fetch product variants (sizes and colors with pricing)
$sql = "SELECT * FROM product_variants 
        WHERE product_id = ? AND is_active = TRUE 
        ORDER BY 
            CASE size
                WHEN 'XS' THEN 1
                WHEN 'S' THEN 2
                WHEN 'M' THEN 3
                WHEN 'L' THEN 4
                WHEN 'XL' THEN 5
                WHEN 'XXL' THEN 6
                WHEN 'XXXL' THEN 7
                ELSE 8
            END,
            color";
$variants = fetchAll($sql, [$product['product_id']]);

// Group variants by size for easier display
$sizeGroups = [];
$colors = [];
foreach ($variants as $variant) {
    $size = $variant['size'];
    $color = $variant['color'];
    
    if (!isset($sizeGroups[$size])) {
        $sizeGroups[$size] = [];
    }
    $sizeGroups[$size][] = $variant;
    
    if (!isset($colors[$color])) {
        $colors[$color] = $variant['color_hex'];
    }
}

// Get unique sizes and sort them
$sizes = array_keys($sizeGroups);
usort($sizes, function($a, $b) {
    $order = ['XS' => 1, 'S' => 2, 'M' => 3, 'L' => 4, 'XL' => 5, 'XXL' => 6, 'XXXL' => 7];
    return ($order[$a] ?? 99) - ($order[$b] ?? 99);
});

// Calculate price range
$minPrice = $product['base_price'];
$maxPrice = $product['base_price'];
foreach ($variants as $variant) {
    $finalPrice = $product['base_price'] + $variant['price_adjustment'];
    $minPrice = min($minPrice, $finalPrice);
    $maxPrice = max($maxPrice, $finalPrice);
}

// Related products (same category, excluding current)
$sql = "SELECT p.*, pi.image_path as primary_image
        FROM products p
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = TRUE
        WHERE p.category_id = ? AND p.product_id != ? AND p.is_active = TRUE
        ORDER BY RAND()
        LIMIT 4";
$relatedProducts = fetchAll($sql, [$product['category_id'], $product['product_id']]);

// Increment view count
$sql = "UPDATE products SET view_count = view_count + 1 WHERE product_id = ?";
executeQuery($sql, [$product['product_id']]);

// Check if product is in user's wishlist
$inWishlist = false;
$wishlistId = null;
if (isLoggedIn()) {
    $wishlistItem = fetchOne("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?", 
        [getCurrentUserId(), $product['product_id']]);
    $inWishlist = $wishlistItem !== null;
    $wishlistId = $wishlistItem['wishlist_id'] ?? null;
}

// Handle add to cart (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    header('Content-Type: application/json');
    
    if (!isLoggedIn()) {
        echo json_encode(['success' => false, 'message' => 'Please login to add items to cart']);
        exit();
    }
    
    $variantId = (int)($_POST['variant_id'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));
    
    // Validate variant
    $variant = fetchOne("SELECT * FROM product_variants WHERE variant_id = ? AND stock_quantity >= ?", [$variantId, $quantity]);
    
    if (!$variant) {
        echo json_encode(['success' => false, 'message' => 'Selected variant is out of stock']);
        exit();
    }
    
    $userId = getCurrentUserId();
    
    // Check if item already in cart
    $existing = fetchOne("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND variant_id = ?", [$userId, $variantId]);
    
    if ($existing) {
        // Update quantity
        $newQuantity = $existing['quantity'] + $quantity;
        if ($newQuantity > $variant['stock_quantity']) {
            echo json_encode(['success' => false, 'message' => 'Cannot add more items than available in stock']);
            exit();
        }
        $sql = "UPDATE cart SET quantity = ? WHERE cart_id = ?";
        executeQuery($sql, [$newQuantity, $existing['cart_id']]);
    } else {
        // Add new item
        $sql = "INSERT INTO cart (user_id, variant_id, quantity) VALUES (?, ?, ?)";
        executeQuery($sql, [$userId, $variantId, $quantity]);
    }
    
    // Get updated cart count
    $cartCount = fetchOne("SELECT SUM(quantity) as count FROM cart WHERE user_id = ?", [$userId]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Item added to cart',
        'cart_count' => $cartCount['count'] ?? 0
    ]);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo cleanOutput(truncateText($product['description'], 150)); ?>">
    <title><?php echo cleanOutput($product['name']); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="product-detail-page">
        <div class="container">
            <!-- Breadcrumb -->
            <nav class="breadcrumb">
                <a href="index.php">Home</a>
                <span class="separator">/</span>
                <a href="products.php">Shop</a>
                <span class="separator">/</span>
                <a href="products.php?category=<?php echo $product['category_slug']; ?>"><?php echo cleanOutput($product['category_name']); ?></a>
                <span class="separator">/</span>
                <span class="current"><?php echo cleanOutput($product['name']); ?></span>
            </nav>
            
            <div class="product-detail-layout">
                <!-- Image Gallery - Grid Layout -->
                <div class="product-gallery">
                    <?php if (!empty($images)): ?>
                        <?php foreach ($images as $index => $image): ?>
                            <div class="gallery-image">
                                <img src="assets/images/products/<?php echo cleanOutput($image['image_path']); ?>" 
                                     alt="<?php echo cleanOutput($product['name']); ?> - View <?php echo $index + 1; ?>">
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="gallery-image">
                            <div class="product-placeholder large">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21 15 16 10 5 21"></polyline>
                                </svg>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Product Info -->
                <div class="product-info-panel">
                    <div class="product-header">
                        <span class="product-category"><?php echo cleanOutput($product['category_name']); ?></span>
                        <h1 class="product-title"><?php echo cleanOutput($product['name']); ?></h1>
                        
                        <div class="product-meta">
                            <span class="condition-badge condition-<?php echo $product['condition_status']; ?>">
                                <?php echo ucwords(str_replace('_', ' ', $product['condition_status'])); ?>
                            </span>
                            <?php if ($product['brand']): ?>
                                <span class="product-brand"><?php echo cleanOutput($product['brand']); ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="product-price">
                            <?php if ($minPrice !== $maxPrice): ?>
                                <span class="price-range"><?php echo formatPrice($minPrice); ?> - <?php echo formatPrice($maxPrice); ?></span>
                            <?php else: ?>
                                <span class="current-price"><?php echo formatPrice($minPrice); ?></span>
                            <?php endif; ?>
                            <?php if ($product['original_price']): ?>
                                <span class="original-price"><?php echo formatPrice($product['original_price']); ?></span>
                                <span class="discount-badge">
                                    <?php echo round((1 - $minPrice / $product['original_price']) * 100); ?>% OFF
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="product-description">
                        <p><?php echo nl2br(cleanOutput($product['description'])); ?></p>
                    </div>
                    
                    <!-- Variant Selection -->
                    <?php if (!empty($variants)): ?>
                        <form class="product-options" id="addToCartForm">
                            <?php echo csrfField(); ?>
                            <input type="hidden" name="action" value="add_to_cart">
                            <input type="hidden" name="variant_id" id="variantId" value="">
                            
                            <!-- Size Selection -->
                            <div class="option-group">
                                <label class="option-label">
                                    Size
                                    <a href="size-guide.php" class="size-guide-link" target="_blank">Size Guide</a>
                                </label>
                                <div class="size-options">
                                    <?php foreach ($sizes as $size): ?>
                                        <?php 
                                        $sizeStock = array_sum(array_column($sizeGroups[$size], 'stock_quantity'));
                                        $isOutOfStock = $sizeStock === 0;
                                        ?>
                                        <button type="button" 
                                                class="size-btn <?php echo $isOutOfStock ? 'out-of-stock' : ''; ?>" 
                                                data-size="<?php echo $size; ?>"
                                                <?php echo $isOutOfStock ? 'disabled' : ''; ?>>
                                            <?php echo $size; ?>
                                            <?php if ($isOutOfStock): ?>
                                                <span class="stock-label">Out</span>
                                            <?php endif; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Color Selection -->
                            <div class="option-group" id="colorGroup" style="display: none;">
                                <label class="option-label">Color</label>
                                <div class="color-options" id="colorOptions">
                                    <!-- Populated by JavaScript based on size selection -->
                                </div>
                            </div>
                            
                            <!-- Selected Variant Info -->
                            <div class="variant-info" id="variantInfo" style="display: none;">
                                <p class="variant-price"></p>
                                <p class="variant-stock"></p>
                            </div>
                            
                            <!-- Quantity -->
                            <div class="option-group">
                                <label class="option-label">Quantity</label>
                                <div class="quantity-selector">
                                    <button type="button" class="qty-btn qty-minus">-</button>
                                    <input type="number" name="quantity" id="quantity" value="1" min="1" max="99">
                                    <button type="button" class="qty-btn qty-plus">+</button>
                                </div>
                            </div>
                            
                            <!-- Add to Cart & Wishlist Buttons -->
                            <div class="product-actions">
                                <?php if (isLoggedIn()): ?>
                                    <div style="display: flex; gap: 0.75rem; margin-bottom: 1rem;">
                                        <button type="submit" class="btn btn-primary btn-lg" id="addToCartBtn" disabled style="flex: 1;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="width: 20px; height: 20px;">
                                                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                                                <line x1="3" y1="6" x2="21" y2="6"></line>
                                                <path d="M16 10a4 4 0 0 1-8 0"></path>
                                            </svg>
                                            Add to Cart
                                        </button>
                                        <a href="<?php echo $inWishlist ? 'wishlist.php?remove=' . $wishlistId : 'wishlist-add.php?product_id=' . $product['product_id']; ?>" 
                                           class="btn btn-outline btn-lg" 
                                           style="padding: 0.75rem 1rem;"
                                           title="<?php echo $inWishlist ? 'Remove from Wishlist' : 'Add to Wishlist'; ?>">
                                            <?php echo $inWishlist ? '❤️' : '🤍'; ?>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div style="background: #f8f8f8; border-radius: 12px; padding: 1.5rem; text-align: center;">
                                        <p style="margin-bottom: 1rem; color: #666;">Sign in to add items to your cart</p>
                                        <a href="login.php?redirect=<?php echo urlencode('product-detail.php?slug=' . $product['slug']); ?>" class="btn btn-primary btn-block">
                                            Sign In to Shop
                                        </a>
                                        <p style="margin-top: 0.75rem; font-size: 0.875rem;">
                                            Don't have an account? <a href="register.php" style="color: #1a1a1a; text-decoration: underline;">Create one</a>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="out-of-stock-message">
                            <p>This product is currently out of stock.</p>
                            <button class="btn btn-outline btn-block notify-btn">
                                Notify When Available
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Product Details -->
                    <div class="product-details-accordion">
                        <div class="accordion-item">
                            <button class="accordion-header">
                                <span>Product Details</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 9l6 6 6-6"></path>
                                </svg>
                            </button>
                            <div class="accordion-content">
                                <ul>
                                    <li><strong>SKU:</strong> <?php echo cleanOutput($product['sku'] ?: 'N/A'); ?></li>
                                    <li><strong>Condition:</strong> <?php echo ucwords(str_replace('_', ' ', $product['condition_status'])); ?></li>
                                    <li><strong>Category:</strong> <?php echo cleanOutput($product['category_name']); ?></li>
                                    <?php if ($product['brand']): ?>
                                        <li><strong>Brand:</strong> <?php echo cleanOutput($product['brand']); ?></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        
                        <div class="accordion-item">
                            <button class="accordion-header">
                                <span>Shipping & Returns</span>
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 9l6 6 6-6"></path>
                                </svg>
                            </button>
                            <div class="accordion-content">
                                <p>We ship worldwide with competitive rates. Philippines shipping: ₱80-170.</p>
                                <p>Returns accepted within 7 days of delivery. Item must be in original condition.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Related Products -->
            <?php if (!empty($relatedProducts)): ?>
                <section class="related-products">
                    <h2 class="section-title">You May Also Like</h2>
                    <div class="products-grid small">
                        <?php foreach ($relatedProducts as $related): ?>
                            <article class="product-card">
                                <a href="product-detail.php?slug=<?php echo $related['slug']; ?>" class="product-link">
                                    <div class="product-image">
                                        <?php if ($related['primary_image']): ?>
                                            <img src="assets/images/products/<?php echo cleanOutput($related['primary_image']); ?>" 
                                                 alt="<?php echo cleanOutput($related['name']); ?>"
                                                 loading="lazy">
                                        <?php else: ?>
                                            <div class="product-placeholder">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                    <polyline points="21 15 16 10 5 21"></polyline>
                                                </svg>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="product-info">
                                        <h3 class="product-name"><?php echo cleanOutput($related['name']); ?></h3>
                                        <div class="product-price">
                                            <span class="current-price"><?php echo formatPrice($related['base_price']); ?></span>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <script>
        // Product variants data for JavaScript
        const variants = <?php echo json_encode($variants); ?>;
        const basePrice = <?php echo $product['base_price']; ?>;
        
        // Size groups for color selection
        const sizeGroups = <?php echo json_encode($sizeGroups); ?>;
        
        // Accordion functionality
        document.querySelectorAll('.accordion-header').forEach(header => {
            header.addEventListener('click', function() {
                const item = this.parentElement;
                const isActive = item.classList.contains('active');
                
                // Close all accordions
                document.querySelectorAll('.accordion-item').forEach(i => i.classList.remove('active'));
                
                // Open clicked accordion if it wasn't active
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        });
    </script>
    <script src="assets/js/product.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html>
