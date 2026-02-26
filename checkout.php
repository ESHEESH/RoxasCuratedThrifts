<?php
/**
 * Checkout Page
 * 
 * Order placement with:
 * - Shipping address form
 * - Location-based shipping calculation
 * - Order summary
 * - Payment method selection
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/includes/functions.php';

// Require login
requireLogin('checkout.php');

$userId = getCurrentUserId();

// Fetch user details
$user = fetchOne("SELECT * FROM users WHERE user_id = ?", [$userId]);

// Fetch cart items
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

// Redirect if cart is empty
if (empty($cartItems)) {
    header("Location: cart.php");
    exit();
}

// Check for out of stock items
foreach ($cartItems as $item) {
    if ($item['stock_quantity'] < $item['quantity']) {
        setFlashMessage('error', 'Some items in your cart are out of stock. Please update your cart.');
        header("Location: cart.php");
        exit();
    }
}

// Calculate subtotal
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += $item['final_price'] * $item['quantity'];
}

// Fetch shipping rates
$shippingRates = fetchAll("SELECT * FROM shipping_rates WHERE is_active = TRUE ORDER BY continent");

// Group shipping rates by continent
$continents = [];
foreach ($shippingRates as $rate) {
    $continents[$rate['continent']] = $rate;
}

// Handle form submission
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        // Get form data
        $receiverName = sanitizeInput($_POST['receiver_name'] ?? '');
        $phoneNumber = sanitizeInput($_POST['phone_number'] ?? '');
        $continent = sanitizeInput($_POST['continent'] ?? '');
        $country = sanitizeInput($_POST['country'] ?? '');
        $city = sanitizeInput($_POST['city'] ?? '');
        $address = sanitizeInput($_POST['address'] ?? '');
        $postalCode = sanitizeInput($_POST['postal_code'] ?? '');
        $landmarkNotes = sanitizeInput($_POST['landmark_notes'] ?? '');
        $paymentMethod = sanitizeInput($_POST['payment_method'] ?? '');
        
        // Validation
        if (empty($receiverName)) {
            $errors[] = "Receiver name is required.";
        }
        
        if (empty($phoneNumber)) {
            $errors[] = "Phone number is required.";
        } elseif (!validatePhoneNumber($phoneNumber)) {
            $errors[] = "Please enter a valid phone number.";
        }
        
        if (empty($continent) || !isset($continents[$continent])) {
            $errors[] = "Please select a valid continent.";
        }
        
        if (empty($country)) {
            $errors[] = "Country is required.";
        }
        
        if (empty($city)) {
            $errors[] = "City is required.";
        }
        
        if (empty($address)) {
            $errors[] = "Address is required.";
        }
        
        if (empty($paymentMethod)) {
            $errors[] = "Please select a payment method.";
        }
        
        // If no errors, create order
        if (empty($errors)) {
            try {
                beginTransaction();
                
                // Get shipping rate
                $shippingRate = $continents[$continent];
                $shippingFee = (float)$shippingRate['base_rate'];
                
                // Generate order number
                $orderNumber = generateOrderNumber();
                
                // Create order
                $sql = "INSERT INTO orders (
                    order_number, user_id, receiver_name, phone_number,
                    continent, country, city, address, postal_code, landmark_notes,
                    subtotal, shipping_fee, total_amount, payment_method
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                executeQuery($sql, [
                    $orderNumber,
                    $userId,
                    $receiverName,
                    $phoneNumber,
                    $continent,
                    $country,
                    $city,
                    $address,
                    $postalCode,
                    $landmarkNotes,
                    $subtotal,
                    $shippingFee,
                    $subtotal + $shippingFee,
                    $paymentMethod
                ]);
                
                $orderId = getLastInsertId();
                
                // Create order items and update stock
                foreach ($cartItems as $item) {
                    // Insert order item
                    $sql = "INSERT INTO order_items (
                        order_id, variant_id, product_name, size, color,
                        quantity, unit_price, total_price
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    
                    executeQuery($sql, [
                        $orderId,
                        $item['variant_id'],
                        $item['product_name'],
                        $item['size'],
                        $item['color'],
                        $item['quantity'],
                        $item['final_price'],
                        $item['final_price'] * $item['quantity']
                    ]);
                    
                    // Update stock
                    $sql = "UPDATE product_variants 
                            SET stock_quantity = stock_quantity - ? 
                            WHERE variant_id = ?";
                    executeQuery($sql, [$item['quantity'], $item['variant_id']]);
                }
                
                // Clear cart
                $sql = "DELETE FROM cart WHERE user_id = ?";
                executeQuery($sql, [$userId]);
                
                // Create transaction record
                $transactionCode = 'TXN-' . strtoupper(bin2hex(random_bytes(8)));
                $sql = "INSERT INTO transactions (order_id, transaction_code, amount, payment_method, status) 
                        VALUES (?, ?, ?, ?, 'pending')";
                executeQuery($sql, [$orderId, $transactionCode, $subtotal + $shippingFee, $paymentMethod]);
                
                commitTransaction();
                
                // Log activity
                logActivity('order_created', 'order', (int)$orderId);
                
                // Redirect to order confirmation
                setFlashMessage('success', 'Order placed successfully! Order number: ' . $orderNumber);
                header("Location: order-confirmation.php?order=" . $orderNumber);
                exit();
                
            } catch (Exception $e) {
                rollbackTransaction();
                error_log("Order creation error: " . $e->getMessage());
                $errors[] = "An error occurred while processing your order. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Checkout - Complete your order">
    <title>Checkout - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="checkout-page">
        <div class="container">
            <!-- Page Header -->
            <div class="page-header">
                <nav class="breadcrumb">
                    <a href="index.php">Home</a>
                    <span class="separator">/</span>
                    <a href="cart.php">Cart</a>
                    <span class="separator">/</span>
                    <span class="current">Checkout</span>
                </nav>
                <h1 class="page-title">Checkout</h1>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo cleanOutput($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="checkout.php" class="checkout-layout" id="checkoutForm">
                <?php echo csrfField(); ?>
                
                <!-- Left Column - Forms -->
                <div class="checkout-forms">
                    <!-- Shipping Information -->
                    <section class="checkout-section">
                        <h2 class="section-title">
                            <span class="step-number">1</span>
                            Shipping Information
                        </h2>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="receiver_name">Full Name *</label>
                                <input type="text" id="receiver_name" name="receiver_name" 
                                       value="<?php echo cleanOutput($_POST['receiver_name'] ?? $user['full_name'] ?? ''); ?>"
                                       placeholder="Enter receiver's full name"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone_number">Phone Number *</label>
                                <input type="tel" id="phone_number" name="phone_number" 
                                       value="<?php echo cleanOutput($_POST['phone_number'] ?? $user['phone_number'] ?? ''); ?>"
                                       placeholder="+63 912 345 6789"
                                       required>
                                <small class="form-hint">Include country code for international numbers</small>
                            </div>
                            
                            <div class="form-group">
                                <label for="continent">Continent *</label>
                                <select id="continent" name="continent" required onchange="updateShippingFee()">
                                    <option value="">Select Continent</option>
                                    <?php foreach ($continents as $continentName => $rate): ?>
                                        <option value="<?php echo $continentName; ?>" 
                                                data-rate="<?php echo $rate['base_rate']; ?>"
                                                <?php echo ($_POST['continent'] ?? $user['continent'] ?? '') === $continentName ? 'selected' : ''; ?>>
                                            <?php echo $continentName; ?> 
                                            (<?php echo $continentName === 'Asia' && $rate['country'] === 'Philippines' ? '₱' . $rate['base_rate'] : '$' . $rate['base_rate']; ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="country">Country *</label>
                                <input type="text" id="country" name="country" 
                                       value="<?php echo cleanOutput($_POST['country'] ?? $user['country'] ?? ''); ?>"
                                       placeholder="Enter country"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" 
                                       value="<?php echo cleanOutput($_POST['city'] ?? $user['city'] ?? ''); ?>"
                                       placeholder="Enter city"
                                       required>
                            </div>
                            
                            <div class="form-group">
                                <label for="postal_code">Postal Code</label>
                                <input type="text" id="postal_code" name="postal_code" 
                                       value="<?php echo cleanOutput($_POST['postal_code'] ?? $user['postal_code'] ?? ''); ?>"
                                       placeholder="Enter postal code">
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="address">Complete Address *</label>
                                <textarea id="address" name="address" rows="3" 
                                          placeholder="Street address, building, apartment, etc."
                                          required><?php echo cleanOutput($_POST['address'] ?? $user['address'] ?? ''); ?></textarea>
                            </div>
                            
                            <div class="form-group full-width">
                                <label for="landmark_notes">Delivery Notes / Landmarks</label>
                                <textarea id="landmark_notes" name="landmark_notes" rows="2" 
                                          placeholder="Any specific landmarks or delivery instructions"><?php echo cleanOutput($_POST['landmark_notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </section>
                    
                    <!-- Payment Method -->
                    <section class="checkout-section">
                        <h2 class="section-title">
                            <span class="step-number">2</span>
                            Payment Method
                        </h2>
                        
                        <div class="payment-options">
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="gcash" 
                                       <?php echo ($_POST['payment_method'] ?? '') === 'gcash' ? 'checked' : ''; ?> required>
                                <div class="payment-card">
                                    <div class="payment-icon gcash">GCash</div>
                                    <div class="payment-info">
                                        <span class="payment-name">GCash</span>
                                        <span class="payment-desc">Pay with your GCash wallet</span>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="maya" 
                                       <?php echo ($_POST['payment_method'] ?? '') === 'maya' ? 'checked' : ''; ?> required>
                                <div class="payment-card">
                                    <div class="payment-icon maya">Maya</div>
                                    <div class="payment-info">
                                        <span class="payment-name">Maya</span>
                                        <span class="payment-desc">Pay with your Maya wallet</span>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="bank_transfer" 
                                       <?php echo ($_POST['payment_method'] ?? '') === 'bank_transfer' ? 'checked' : ''; ?> required>
                                <div class="payment-card">
                                    <div class="payment-icon bank">Bank</div>
                                    <div class="payment-info">
                                        <span class="payment-name">Bank Transfer</span>
                                        <span class="payment-desc">Transfer to our bank account</span>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="cod" 
                                       <?php echo ($_POST['payment_method'] ?? '') === 'cod' ? 'checked' : ''; ?> required>
                                <div class="payment-card">
                                    <div class="payment-icon cod">COD</div>
                                    <div class="payment-info">
                                        <span class="payment-name">Cash on Delivery</span>
                                        <span class="payment-desc">Pay when you receive</span>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </section>
                </div>
                
                <!-- Right Column - Order Summary -->
                <aside class="checkout-summary">
                    <div class="summary-card sticky">
                        <h2>Order Summary</h2>
                        
                        <!-- Cart Items -->
                        <div class="summary-items">
                            <?php foreach ($cartItems as $item): ?>
                                <div class="summary-item">
                                    <div class="item-image">
                                        <?php if ($item['primary_image']): ?>
                                            <img src="assets/images/products/<?php echo cleanOutput($item['primary_image']); ?>" 
                                                 alt="<?php echo cleanOutput($item['product_name']); ?>">
                                        <?php endif; ?>
                                        <span class="item-qty"><?php echo $item['quantity']; ?></span>
                                    </div>
                                    <div class="item-info">
                                        <span class="item-name"><?php echo cleanOutput($item['product_name']); ?></span>
                                        <span class="item-variant"><?php echo $item['size']; ?> / <?php echo $item['color']; ?></span>
                                    </div>
                                    <span class="item-price"><?php echo formatPrice($item['final_price'] * $item['quantity']); ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <!-- Totals -->
                        <div class="summary-row">
                            <span>Subtotal</span>
                            <span id="subtotal"><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span id="shippingFee">Select continent</span>
                        </div>
                        
                        <div class="summary-divider"></div>
                        
                        <div class="summary-row total">
                            <span>Total</span>
                            <span class="total-price" id="totalPrice"><?php echo formatPrice($subtotal); ?></span>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-lg btn-block" id="placeOrderBtn">
                            Place Order
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M5 12h14M12 5l7 7-7 7"></path>
                            </svg>
                        </button>
                        
                        <p class="secure-notice">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                            Secure checkout
                        </p>
                    </div>
                </aside>
            </form>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <script>
        const subtotal = <?php echo $subtotal; ?>;
        const shippingRates = <?php echo json_encode($continents); ?>;
        
        /**
         * Update shipping fee based on selected continent
         */
        function updateShippingFee() {
            const continentSelect = document.getElementById('continent');
            const selectedOption = continentSelect.options[continentSelect.selectedIndex];
            const shippingFeeEl = document.getElementById('shippingFee');
            const totalPriceEl = document.getElementById('totalPrice');
            
            if (selectedOption.value) {
                const rate = parseFloat(selectedOption.dataset.rate);
                const isPhilippines = selectedOption.text.includes('Philippines');
                const currency = isPhilippines ? '₱' : '$';
                
                shippingFeeEl.textContent = currency + rate.toLocaleString('en-US', {minimumFractionDigits: 2});
                
                const total = subtotal + rate;
                totalPriceEl.textContent = currency + total.toLocaleString('en-US', {minimumFractionDigits: 2});
            } else {
                shippingFeeEl.textContent = 'Select continent';
                totalPriceEl.textContent = '₱' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2});
            }
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', updateShippingFee);
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>
