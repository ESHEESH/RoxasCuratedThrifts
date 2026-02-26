<?php
/**
 * Landing Page
 * 
 * Main entry point of the website with:
 * - Shop Me / Girls view toggle
 * - Featured products
 * - Hero section
 * - Category navigation
 * 
 */

require_once __DIR__ . '/includes/functions.php';

// Get view mode from URL or cookie (default to 'shop')
$viewMode = $_GET['view'] ?? $_COOKIE['view_mode'] ?? 'shop';
$viewMode = in_array($viewMode, ['shop', 'girls']) ? $viewMode : 'shop';

// Set cookie for view mode preference (30 days)
setcookie('view_mode', $viewMode, time() + (30 * 24 * 60 * 60), '/');

// Fetch featured products
$sql = "SELECT p.*, c.name as category_name, pi.image_path as primary_image,
        (SELECT MIN(pv.stock_quantity) FROM product_variants pv WHERE pv.product_id = p.product_id) as min_stock
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.category_id
        LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = TRUE
        WHERE p.is_active = TRUE AND p.is_featured = TRUE
        ORDER BY p.created_at DESC
        LIMIT 8";
$featuredProducts = fetchAll($sql);

// Fetch all categories
$sql = "SELECT * FROM categories WHERE is_active = TRUE ORDER BY display_order";
$categories = fetchAll($sql);

// Get flash message
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo SITE_NAME; ?> - Curated thrifted clothing, shoes, bags, and caps">
    <title><?php echo SITE_NAME; ?> - Roxas Curated Thrift</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header/Navigation -->
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <!-- Flash Messages -->
    <?php if ($flash): ?>
        <div class="flash-message flash-<?php echo $flash['type']; ?>" id="flashMessage">
            <?php echo cleanOutput($flash['message']); ?>
            <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    <?php endif; ?>
    
    <!-- Hero Section with View Toggle -->
    <section class="hero">
        <div class="hero-background">
            <div class="hero-overlay"></div>
        </div>
        
        <div class="hero-content">
            <!-- View Mode Toggle -->
            <div class="view-toggle">
                <a href="?view=shop" class="view-btn <?php echo $viewMode === 'shop' ? 'active' : ''; ?>" data-view="shop">
                    <span class="view-label">Shop Me</span>
                </a>
                <span class="toggle-divider">/</span>
                <a href="?view=girls" class="view-btn <?php echo $viewMode === 'girls' ? 'active' : ''; ?>" data-view="girls">
                    <span class="view-label">Girls</span>
                </a>
            </div>
            
            <h1 class="hero-title">
                <?php if ($viewMode === 'shop'): ?>
                    Curated Thrift<br>For Everyone
                <?php else: ?>
                    Thrifted Style<br>For Her
                <?php endif; ?>
            </h1>
            
            <p class="hero-subtitle">
                Handpicked vintage & pre-loved fashion.<br>
                Sustainable style, unique finds.
            </p>
            
            <div class="hero-actions">
                <a href="products.php" class="btn btn-primary btn-lg">
                    Shop Now
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"></path>
                    </svg>
                </a>
                <a href="#categories" class="btn btn-outline btn-lg">
                    Explore Categories
                </a>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="scroll-indicator">
            <span>Scroll to explore</span>
            <span class="scroll-arrow"></span>
        </div>
    </section>
    
    <!-- Categories Section -->
    <section class="categories-section" id="categories">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Shop by Category</h2>
                <p class="section-subtitle">Find your perfect piece</p>
            </div>
            
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                    <a href="products.php?category=<?php echo $category['slug']; ?>" class="category-card">
                        <div class="category-image">
                            <?php 
                            // Category images - place your images in assets/images/categories/
                            $categoryImages = [
                                'clothes' => 'clothes.jpg',
                                'shoes' => 'shoes.jpg',
                                'bags' => 'bags.jpg',
                                'caps' => 'caps.jpg'
                            ];
                            $catImage = $categoryImages[$category['slug']] ?? 'default.jpg';
                            ?>
                            <?php if ($category['image']): ?>
                                <img src="assets/images/<?php echo cleanOutput($category['image']); ?>" 
                                     alt="<?php echo cleanOutput($category['name']); ?>">
                            <?php else: ?>
                                <img src="assets/images/categories/<?php echo $catImage; ?>" 
                                     alt="<?php echo cleanOutput($category['name']); ?>"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="category-placeholder" style="display: none; height: 100%; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; font-size: 1rem;">
                                    No Image
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="category-info">
                            <h3><?php echo cleanOutput($category['name']); ?></h3>
                            <p><?php echo cleanOutput($category['description'] ?: 'Explore our collection'); ?></p>
                            <span class="category-link">
                                Shop Now
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M5 12h14M12 5l7 7-7 7"></path>
                                </svg>
                            </span>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Featured Products Section -->
    <section class="featured-section">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Featured Finds</h2>
                <p class="section-subtitle">Handpicked just for you</p>
                <a href="products.php" class="view-all-link">
                    View All
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
            
            <div class="products-grid">
                <?php if (empty($featuredProducts)): ?>
                    <div class="no-products">
                        <p>No featured products yet. Check back soon!</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($featuredProducts as $product): ?>
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
                                    $catSlug = $category['slug'] ?? 'clothes';
                                    $emojis = $productEmojis[$catSlug] ?? $productEmojis['clothes'];
                                    $emoji = $emojis[$product['product_id'] % count($emojis)];
                                    ?>
                                    <?php if ($product['primary_image']): ?>
                                        <img src="assets/images/products/<?php echo cleanOutput($product['primary_image']); ?>" 
                                             alt="<?php echo cleanOutput($product['name']); ?>"
                                             loading="lazy">
                                    <?php else: ?>
                                        <div class="product-placeholder emoji-placeholder">
                                            <?php echo $emoji; ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <!-- Condition Badge -->
                                    <span class="condition-badge condition-<?php echo $product['condition_status']; ?>">
                                        <?php echo ucwords(str_replace('_', ' ', $product['condition_status'])); ?>
                                    </span>
                                    
                                    <!-- Low Stock Badge -->
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
                                </div>
                            </a>
                            
                            <!-- Quick Add Button -->
                            <?php if ($product['min_stock'] > 0): ?>
                                <?php if (isLoggedIn()): ?>
                                    <button class="quick-add-btn" data-product-id="<?php echo $product['product_id']; ?>">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 5v14M5 12h14"></path>
                                        </svg>
                                        Quick Add
                                    </button>
                                <?php else: ?>
                                    <a href="login.php?redirect=<?php echo urlencode('product-detail.php?slug=' . $product['slug']); ?>" class="quick-add-btn" style="text-decoration: none;">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M12 5v14M5 12h14"></path>
                                        </svg>
                                        Sign In to Add
                                    </a>
                                <?php endif; ?>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
    
    <!-- About/Features Section -->
    <section class="features-section">
        <div class="container">
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <h3>Quality Checked</h3>
                    <p>Every item is personally inspected and curated for quality.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                    </div>
                    <h3>Sustainable Fashion</h3>
                    <p>Give pre-loved items a new life and reduce fashion waste.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10"></circle>
                            <path d="M2 12h20"></path>
                            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                        </svg>
                    </div>
                    <h3>Worldwide Shipping</h3>
                    <p>We ship to all continents with competitive rates.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    <h3>Community Driven</h3>
                    <p>Join our community of thrift enthusiasts and unique finders.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Newsletter Section -->
    <section class="newsletter-section">
        <div class="container">
            <div class="newsletter-content">
                <h2>Stay in the Loop</h2>
                <p>Subscribe to get notified about new drops and exclusive offers.</p>
                
                <form class="newsletter-form" action="subscribe.php" method="POST">
                    <?php echo csrfField(); ?>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="Enter your email" required>
                        <button type="submit" class="btn btn-primary">Subscribe</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
