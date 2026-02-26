<?php
/**
 * Shopping Cart Page
 * 
 * Displays cart items with:
 * - Quantity management
 * - Remove items
 * - Price calculations
 * - Proceed to checkout
 * 
 * Users can view cart but need to login to proceed to checkout.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/includes/functions.php';

$userId = getCurrentUserId();
$isLoggedIn = isLoggedIn();

// Handle cart updates via AJAX (requires login)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isLoggedIn) {
    header('Content-Type: application/json');
    
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'update_quantity':
            $cartId = (int)($_POST['cart_id'] ?? 0);
            $quantity = (int)($_POST['quantity'] ?? 1);
            
            if ($quantity < 1) {
                $sql = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
                executeQuery($sql, [$cartId, $userId]);
            } else {
                $sql = "SELECT pv.stock_quantity FROM cart c 
                        JOIN product_variants pv ON c.variant_id = pv.variant_id 
                        WHERE c.cart_id = ? AND c.user_id = ?";
                $stock = fetchOne($sql, [$cartId, $userId]);
                
                if ($stock && $quantity <= $stock['stock_quantity']) {
                    $sql = "UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?";
                    executeQuery($sql, [$quantity, $cartId, $userId]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Quantity exceeds available stock']);
                    exit();
                }
            }
            
            echo json_encode(['success' => true, 'message' => 'Cart updated']);
            exit();
            
        case 'remove_item':
            $cartId = (int)($_POST['cart_id'] ?? 0);
            $sql = "DELETE FROM cart WHERE cart_id = ? AND user_id = ?";
            executeQuery($sql, [$cartId, $userId]);
            echo json_encode(['success' => true, 'message' => 'Item removed']);
            exit();
    }
}

// Fetch cart items (for logged in users)
$cartItems = [];
$subtotal = 0;
$totalItems = 0;

if ($isLoggedIn) {
    $sql = "SELECT 
                c.cart_id,
                c.quantity,
                pv.variant_id,
                pv.size,
                pv.color,
                pv.stock_quantity,
                p.product_id,
                p.name as product_name,
                p.slug,
                p.base_price,
                pv.price_adjustment,
                (p.base_price + pv.price_adjustment) as final_price,
                pi.image_path as primary_image
            FROM cart c
            JOIN product_variants pv ON c.variant_id = pv.variant_id
            JOIN products p ON pv.product_id = p.product_id
            LEFT JOIN product_images pi ON p.product_id = pi.product_id AND pi.is_primary = TRUE
            WHERE c.user_id = ?";
    
    $cartItems = fetchAll($sql, [$userId]);
    
    foreach ($cartItems as $item) {
        $subtotal += $item['final_price'] * $item['quantity'];
        $totalItems += $item['quantity'];
    }
}

// Check for out of stock items
$hasOutOfStock = false;
foreach ($cartItems as $item) {
    if ($item['stock_quantity'] < $item['quantity']) {
        $hasOutOfStock = true;
        break;
    }
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Your shopping cart">
    <title>Shopping Cart - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .login-prompt {
            text-align: center;
            padding: 4rem 2rem;
            background: linear-gradient(135deg, #f8f8f8 0%, #fff 100%);
            border-radius: 16px;
            margin: 2rem 0;
        }
        
        .login-prompt .icon {
            font-size: 5rem;
            margin-bottom: 1.5rem;
        }
        
        .login-prompt h2 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.75rem;
        }
        
        .login-prompt p {
            color: #666;
            margin-bottom: 2rem;
            max-width: 400px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .login-prompt .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .continue-shopping {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.875rem;
        }
        
        .continue-shopping:hover {
            color: #1a1a1a;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="cart-page">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <nav class="breadcrumb">
                    <a href="index.php">Home</a>
                    <span class="separator">/</span>
                    <span class="current">Shopping Cart</span>
                </nav>
                <h1 class="page-title">Shopping Cart</h1>
            </div>
            
            <!-- Flash Messages -->
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>" style="margin-bottom: 1rem;">
                    <?php echo cleanOutput($flash['message']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (!$isLoggedIn): ?>
                <!-- Login Prompt for Guests -->
                <div class="login-prompt">
                    <div class="icon">🛒</div>
                    <h2>Your Cart is Waiting</h2>
                    <p>Sign in to view your cart, save items for later, and checkout faster. Don't have an account? Create one in seconds!</p>
                    <div class="btn-group">
                        <a href="login.php?redirect=cart.php" class="btn btn-primary btn-lg">Sign In</a>
                        <a href="register.php" class="btn btn-outline btn-lg">Create Account</a>
                    </div>
                    <p style="margin-top: 1.5rem; margin-bottom: 0;">
                        <a href="products.php" class="continue-shopping">← Continue Shopping</a>
                    </p>
                </div>
            <?php elseif (empty($cartItems)): ?>
                <!-- Empty Cart -->
                <div class="empty-cart" style="text-align: center; padding: 4rem 2rem;">
                    <div style="font-size: 5rem; margin-bottom: 1.5rem;">🛒</div>
                    <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.75rem;">Your cart is empty</h2>
                    <p style="color: #666; margin-bottom: 2rem;">Looks like you haven't added anything to your cart yet.</p>
                    <a href="products.php" class="btn btn-primary btn-lg">Start Shopping</a>
                </div>
            <?php else: ?>
                <div class="cart-layout">
                    <!-- Cart Items -->
                    <div class="cart-items-section">
                        <?php if ($hasOutOfStock): ?>
                            <div class="alert alert-warning" style="margin-bottom: 1rem;">
                                <strong>Attention:</strong> Some items in your cart are out of stock or have insufficient quantity. Please update or remove them to proceed.
                            </div>
                        <?php endif; ?>
                        
                        <div class="cart-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                            <span style="color: #666;"><?php echo $totalItems; ?> item<?php echo $totalItems !== 1 ? 's' : ''; ?></span>
                            <a href="products.php" class="continue-shopping">Continue Shopping →</a>
                        </div>
                        
                        <div class="cart-items">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="cart-item <?php echo $item['stock_quantity'] < $item['quantity'] ? 'out-of-stock' : ''; ?>" 
                                     data-cart-id="<?php echo $item['cart_id']; ?>"
                                     style="display: flex; gap: 1.5rem; padding: 1.5rem; border: 1px solid #e0e0e0; border-radius: 12px; margin-bottom: 1rem;">
                                    
                                    <!-- Product Image -->
                                    <a href="product-detail.php?slug=<?php echo $item['slug']; ?>" style="width: 100px; height: 130px; flex-shrink: 0; background: #f5f5f5; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 2.5rem;">
                                        <?php if ($item['primary_image']): ?>
                                            <img src="assets/images/products/<?php echo cleanOutput($item['primary_image']); ?>" 
                                                 alt="<?php echo cleanOutput($item['product_name']); ?>"
                                                 style="width: 100%; height: 100%; object-fit: cover; border-radius: 8px;">
                                        <?php else: ?>
                                            👕
                                        <?php endif; ?>
                                    </a>
                                    
                                    <!-- Product Info -->
                                    <div style="flex: 1;">
                                        <a href="product-detail.php?slug=<?php echo $item['slug']; ?>" style="font-weight: 600; font-size: 1.1rem; display: block; margin-bottom: 0.25rem;">
                                            <?php echo cleanOutput($item['product_name']); ?>
                                        </a>
                                        <div style="color: #666; font-size: 0.875rem; margin-bottom: 0.5rem;">
                                            Size: <?php echo $item['size']; ?> | Color: <?php echo $item['color']; ?>
                                        </div>
                                        
                                        <?php if ($item['stock_quantity'] < $item['quantity']): ?>
                                            <span style="color: #ef4444; font-size: 0.875rem;">
                                                Only <?php echo $item['stock_quantity']; ?> available
                                            </span>
                                        <?php endif; ?>
                                        
                                        <div style="margin-top: 0.75rem; font-weight: 600;">
                                            <?php echo formatPrice($item['final_price']); ?>
                                        </div>
                                    </div>
                                    
                                    <!-- Quantity -->
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <button type="button" class="qty-btn qty-minus" data-action="decrease" 
                                                style="width: 32px; height: 32px; border: 1px solid #e0e0e0; border-radius: 6px; background: white; font-size: 1.25rem;">-</button>
                                        <input type="number" class="qty-input" value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="<?php echo $item['stock_quantity']; ?>" readonly
                                               style="width: 50px; text-align: center; border: 1px solid #e0e0e0; border-radius: 6px; padding: 0.5rem;">
                                        <button type="button" class="qty-btn qty-plus" data-action="increase"
                                                style="width: 32px; height: 32px; border: 1px solid #e0e0e0; border-radius: 6px; background: white; font-size: 1.25rem;">+</button>
                                    </div>
                                    
                                    <!-- Price & Remove -->
                                    <div style="text-align: right;">
                                        <div style="font-weight: 700; font-size: 1.1rem; margin-bottom: 0.5rem;">
                                            <?php echo formatPrice($item['final_price'] * $item['quantity']); ?>
                                        </div>
                                        <button type="button" class="item-remove" data-action="remove" 
                                                style="color: #ef4444; font-size: 0.875rem; background: none; border: none; cursor: pointer;">
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <!-- Order Summary -->
                    <aside class="cart-summary" style="width: 320px;">
                        <div style="background: white; border-radius: 12px; padding: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); position: sticky; top: 90px;">
                            <h2 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem;">Order Summary</h2>
                            
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                                <span style="color: #666;">Subtotal</span>
                                <span style="font-weight: 600;"><?php echo formatPrice($subtotal); ?></span>
                            </div>
                            
                            <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                                <span style="color: #666;">Shipping</span>
                                <span style="color: #666; font-size: 0.875rem;">Calculated at checkout</span>
                            </div>
                            
                            <div style="border-top: 1px solid #e0e0e0; padding-top: 1rem; margin-bottom: 1.5rem;">
                                <div style="display: flex; justify-content: space-between;">
                                    <span style="font-weight: 600;">Estimated Total</span>
                                    <span style="font-weight: 700; font-size: 1.25rem;"><?php echo formatPrice($subtotal); ?></span>
                                </div>
                            </div>
                            
                            <a href="checkout.php" class="btn btn-primary btn-lg btn-block <?php echo $hasOutOfStock ? 'disabled' : ''; ?>"
                               style="width: 100%; justify-content: center;">
                                Proceed to Checkout
                            </a>
                            
                            <?php if ($hasOutOfStock): ?>
                                <p style="color: #ef4444; font-size: 0.875rem; text-align: center; margin-top: 0.75rem;">
                                    Please resolve stock issues before checkout.
                                </p>
                            <?php endif; ?>
                            
                            <div style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #e0e0e0; text-align: center;">
                                <p style="font-size: 0.75rem; color: #666; margin-bottom: 0.5rem;">We accept:</p>
                                <div style="display: flex; gap: 0.5rem; justify-content: center;">
                                    <span style="padding: 0.25rem 0.5rem; background: #f5f5f5; border-radius: 4px; font-size: 0.75rem;">GCash</span>
                                    <span style="padding: 0.25rem 0.5rem; background: #f5f5f5; border-radius: 4px; font-size: 0.75rem;">Maya</span>
                                    <span style="padding: 0.25rem 0.5rem; background: #f5f5f5; border-radius: 4px; font-size: 0.75rem;">COD</span>
                                </div>
                            </div>
                        </div>
                    </aside>
                </div>
            <?php endif; ?>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <?php if ($isLoggedIn): ?>
        <script src="assets/js/cart.js"></script>
    <?php endif; ?>
    <script src="assets/js/main.js"></script>
</body>
</html>
