<?php
/**
 * User Wishlist Page
 * 
 * Displays user's saved/wishlisted products.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/includes/functions.php';

// Require login
requireLogin('wishlist.php');

$userId = getCurrentUserId();

// Handle remove from wishlist
if (isset($_GET['remove']) && is_numeric($_GET['remove'])) {
    $wishlistId = (int)$_GET['remove'];
    executeQuery("DELETE FROM wishlist WHERE wishlist_id = ? AND user_id = ?", [$wishlistId, $userId]);
    setFlashMessage('success', 'Item removed from wishlist');
    header("Location: wishlist.php");
    exit();
}

// Handle add to cart from wishlist
if (isset($_GET['add_to_cart']) && is_numeric($_GET['add_to_cart'])) {
    $productId = (int)$_GET['add_to_cart'];
    
    // Get default variant
    $variant = fetchOne("SELECT variant_id FROM product_variants WHERE product_id = ? AND stock_quantity > 0 LIMIT 1", [$productId]);
    
    if ($variant) {
        // Check if already in cart
        $existing = fetchOne("SELECT cart_id FROM cart WHERE user_id = ? AND variant_id = ?", [$userId, $variant['variant_id']]);
        
        if ($existing) {
            executeQuery("UPDATE cart SET quantity = quantity + 1 WHERE cart_id = ?", [$existing['cart_id']]);
        } else {
            executeQuery("INSERT INTO cart (user_id, variant_id, quantity) VALUES (?, ?, 1)", [$userId, $variant['variant_id']]);
        }
        
        setFlashMessage('success', 'Item added to cart!');
    } else {
        setFlashMessage('error', 'Sorry, this item is out of stock.');
    }
    
    header("Location: wishlist.php");
    exit();
}

// Get wishlist items
$sql = "SELECT w.*, p.*, c.name as category_name,
        (SELECT MIN(pv.stock_quantity) FROM product_variants pv WHERE pv.product_id = p.product_id) as min_stock,
        (SELECT image_path FROM product_images WHERE product_id = p.product_id AND is_primary = TRUE LIMIT 1) as primary_image
        FROM wishlist w
        JOIN products p ON w.product_id = p.product_id
        LEFT JOIN categories c ON p.category_id = c.category_id
        WHERE w.user_id = ?
        ORDER BY w.created_at DESC";
$wishlistItems = fetchAll($sql, [$userId]);

// Get user data for sidebar
$user = fetchOne("SELECT * FROM users WHERE user_id = ?", [$userId]);

$pageTitle = 'My Wishlist';
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-layout {
            display: grid;
            grid-template-columns: 280px 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .profile-sidebar {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            height: fit-content;
        }
        
        .profile-avatar-large {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: white;
            margin: 0 auto 1rem;
        }
        
        .profile-name {
            text-align: center;
            font-weight: 600;
            font-size: 1.125rem;
        }
        
        .profile-email {
            text-align: center;
            color: #666;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
        }
        
        .profile-menu {
            list-style: none;
        }
        
        .profile-menu li {
            margin-bottom: 0.25rem;
        }
        
        .profile-menu a {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            color: #333;
            transition: all 0.2s;
        }
        
        .profile-menu a:hover,
        .profile-menu a.active {
            background: #f5f5f5;
            color: #1a1a1a;
        }
        
        .profile-menu .icon {
            font-size: 1.25rem;
        }
        
        .profile-content {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .wishlist-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1.5rem;
        }
        
        .wishlist-item {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            overflow: hidden;
            transition: box-shadow 0.2s;
        }
        
        .wishlist-item:hover {
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }
        
        .wishlist-image {
            aspect-ratio: 3/4;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            position: relative;
        }
        
        .wishlist-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .remove-btn {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            width: 32px;
            height: 32px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            font-size: 1.25rem;
            transition: all 0.2s;
        }
        
        .remove-btn:hover {
            background: #ff4444;
            color: white;
        }
        
        .wishlist-info {
            padding: 1rem;
        }
        
        .wishlist-category {
            font-size: 0.75rem;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .wishlist-name {
            font-weight: 600;
            margin: 0.25rem 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .wishlist-price {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        
        .wishlist-actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .wishlist-actions .btn {
            flex: 1;
            padding: 0.5rem;
            font-size: 0.875rem;
        }
        
        .empty-wishlist {
            text-align: center;
            padding: 3rem;
        }
        
        .empty-wishlist .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .empty-wishlist h3 {
            font-size: 1.25rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .empty-wishlist p {
            color: #666;
            margin-bottom: 1.5rem;
        }
        
        @media (max-width: 768px) {
            .profile-layout {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="container" style="padding-top: 90px; padding-bottom: 3rem;">
        <!-- Flash Messages -->
        <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo $flash['type']; ?>" id="flashMessage" style="margin-bottom: 1rem;">
                <?php echo cleanOutput($flash['message']); ?>
                <button class="flash-close" onclick="this.parentElement.remove()">&times;</button>
            </div>
        <?php endif; ?>
        
        <!-- Page Header -->
        <div class="page-header" style="background: none; padding: 0; margin-bottom: 0;">
            <nav class="breadcrumb">
                <a href="index.php">Home</a>
                <span class="separator">/</span>
                <span class="current">My Wishlist</span>
            </nav>
            <h1 class="page-title">My Wishlist</h1>
        </div>
        
        <div class="profile-layout">
            <!-- Sidebar -->
            <aside class="profile-sidebar">
                <div class="profile-avatar-large">
                    <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                </div>
                <div class="profile-name"><?php echo cleanOutput($user['username']); ?></div>
                <div class="profile-email"><?php echo cleanOutput($user['email']); ?></div>
                
                <ul class="profile-menu">
                    <li><a href="profile.php"><span class="icon">👤</span> Profile</a></li>
                    <li><a href="orders.php"><span class="icon">📦</span> My Orders</a></li>
                    <li><a href="wishlist.php" class="active"><span class="icon">❤️</span> Wishlist</a></li>
                    <li><a href="logout.php"><span class="icon">🚪</span> Logout</a></li>
                </ul>
            </aside>
            
            <!-- Content -->
            <div class="profile-content">
                <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">
                    Saved Items (<?php echo count($wishlistItems); ?>)
                </h2>
                
                <?php if (empty($wishlistItems)): ?>
                    <div class="empty-wishlist">
                        <div class="icon">❤️</div>
                        <h3>Your wishlist is empty</h3>
                        <p>Save items you love to your wishlist and find them easily later!</p>
                        <a href="products.php" class="btn btn-primary">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="wishlist-grid">
                        <?php foreach ($wishlistItems as $item): ?>
                            <?php
                            // Get emoji based on category
                            $emojis = [
                                'clothes' => ['👕', '👗', '🧥', '👖'],
                                'shoes' => ['👟', '👠', '👢', '🩴'],
                                'bags' => ['👜', '🎒', '💼', '🧳'],
                                'caps' => ['🧢', '👒', '🎩', '🎓']
                            ];
                            $catEmojis = $emojis[$item['category_name']] ?? $emojis['clothes'];
                            $emoji = $catEmojis[$item['product_id'] % count($catEmojis)];
                            ?>
                            <div class="wishlist-item">
                                <div class="wishlist-image">
                                    <?php if ($item['primary_image']): ?>
                                        <img src="assets/images/products/<?php echo cleanOutput($item['primary_image']); ?>" alt="<?php echo cleanOutput($item['name']); ?>">
                                    <?php else: ?>
                                        <?php echo $emoji; ?>
                                    <?php endif; ?>
                                    <a href="?remove=<?php echo $item['wishlist_id']; ?>" class="remove-btn" title="Remove from wishlist">×</a>
                                </div>
                                <div class="wishlist-info">
                                    <div class="wishlist-category"><?php echo cleanOutput($item['category_name']); ?></div>
                                    <div class="wishlist-name"><?php echo cleanOutput($item['name']); ?></div>
                                    <div class="wishlist-price"><?php echo formatPrice($item['base_price']); ?></div>
                                    <div class="wishlist-actions">
                                        <?php if ($item['min_stock'] > 0): ?>
                                            <a href="?add_to_cart=<?php echo $item['product_id']; ?>" class="btn btn-primary">Add to Cart</a>
                                        <?php else: ?>
                                            <button class="btn btn-outline" disabled>Out of Stock</button>
                                        <?php endif; ?>
                                        <a href="product-detail.php?slug=<?php echo $item['slug']; ?>" class="btn btn-outline">View</a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
