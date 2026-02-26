<?php
/**
 * Add to Wishlist Handler
 * 
 * Adds a product to the user's wishlist.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/includes/functions.php';

// Require login
requireLogin();

$userId = getCurrentUserId();
$productId = (int)($_GET['product_id'] ?? 0);
$redirect = $_GET['redirect'] ?? 'wishlist.php';

if ($productId <= 0) {
    setFlashMessage('error', 'Invalid product.');
    header("Location: products.php");
    exit();
}

// Check if product exists
$product = fetchOne("SELECT product_id, name FROM products WHERE product_id = ? AND is_active = TRUE", [$productId]);

if (!$product) {
    setFlashMessage('error', 'Product not found.');
    header("Location: products.php");
    exit();
}

// Check if already in wishlist
$existing = fetchOne("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?", [$userId, $productId]);

if ($existing) {
    setFlashMessage('info', 'This item is already in your wishlist!');
} else {
    // Add to wishlist
    executeQuery("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)", [$userId, $productId]);
    setFlashMessage('success', 'Added to wishlist!');
}

// Redirect back
header("Location: $redirect");
exit();
