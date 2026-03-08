<?php
/**
 * Add to Wishlist Handler
 * 
 * Adds a product to the user's wishlist.
 * Supports both AJAX and regular requests.
 * 
 */

require_once __DIR__ . '/../includes/functions.php';

// Check if AJAX request
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

// Require login
if (!isLoggedIn()) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Please login to add items to wishlist']);
        exit();
    }
    requireLogin();
}

$userId = getCurrentUserId();
$productId = (int)($_GET['product_id'] ?? $_POST['product_id'] ?? 0);
$action = $_GET['action'] ?? $_POST['action'] ?? 'add'; // 'add' or 'remove'

if ($productId <= 0) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid product']);
        exit();
    }
    setFlashMessage('error', 'Invalid product.');
    header("Location: ../shop/products.php");
    exit();
}

// Check if product exists
$product = fetchOne("SELECT product_id, name, is_active FROM products WHERE product_id = ?", [$productId]);

if (!$product) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'Product not found in database',
            'debug' => ['product_id' => $productId]
        ]);
        exit();
    }
    setFlashMessage('error', 'Product not found.');
    header("Location: ../shop/products.php");
    exit();
}

// Check if product is active
if ($product['is_active'] != 1) {
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false, 
            'message' => 'This product is no longer available',
            'debug' => ['product_id' => $productId, 'is_active' => $product['is_active']]
        ]);
        exit();
    }
    setFlashMessage('error', 'This product is no longer available.');
    header("Location: ../shop/products.php");
    exit();
}

// Check if already in wishlist
$existing = fetchOne("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?", [$userId, $productId]);

if ($action === 'remove' && $existing) {
    // Remove from wishlist
    executeQuery("DELETE FROM wishlist WHERE wishlist_id = ?", [$existing['wishlist_id']]);
    $message = 'Removed from wishlist';
    $inWishlist = false;
} elseif ($action === 'add') {
    if ($existing) {
        $message = 'Already in your wishlist';
        $inWishlist = true;
    } else {
        // Add to wishlist
        executeQuery("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)", [$userId, $productId]);
        $message = 'Added to wishlist!';
        $inWishlist = true;
    }
} else {
    $message = 'Item not in wishlist';
    $inWishlist = false;
}

// Return JSON for AJAX requests
if ($isAjax) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true, 
        'message' => $message,
        'inWishlist' => $inWishlist
    ]);
    exit();
}

// Regular redirect for non-AJAX
setFlashMessage('success', $message);
$redirect = $_GET['redirect'] ?? '../shop/wishlist.php';
header("Location: $redirect");
exit();
