<?php
/**
 * Site Header
 * 
 * Navigation header with:
 * - Logo
 * - Navigation links
 * - Search functionality
 * - Cart icon with item count
 * - User account menu
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

// Get cart count if user is logged in
$cartCount = 0;
if (isLoggedIn()) {
    $userId = getCurrentUserId();
    $sql = "SELECT SUM(quantity) as count FROM cart WHERE user_id = ?";
    $result = fetchOne($sql, [$userId]);
    $cartCount = $result['count'] ?? 0;
}
?>
<header class="site-header" id="header">
    <div class="header-container">
        <!-- Logo -->
        <a href="index.php" class="logo">
            <span class="logo-text"><?php echo "Roxas Thrift Store"; ?></span>
        </a>
        
        <!-- Desktop Navigation -->
        <nav class="main-nav">
            <ul class="nav-list">
                <li><a href="index.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'index.php' ? 'active' : ''; ?>">Home</a></li>
                <li><a href="products.php" class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'products.php' ? 'active' : ''; ?>">Shop</a></li>
                <li><a href="products.php?category=clothes" class="nav-link">Clothes</a></li>
                <li><a href="products.php?category=shoes" class="nav-link">Shoes</a></li>
                <li><a href="products.php?category=bags" class="nav-link">Bags</a></li>
                <li><a href="products.php?category=caps" class="nav-link">Caps</a></li>
            </ul>
        </nav>
        
        <!-- Header Actions -->
        <div class="header-actions">
            <!-- Search Toggle -->
            <button class="action-btn search-toggle" aria-label="Search">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>
                    <path d="M21 21l-4.35-4.35"></path>
                </svg>
            </button>
            
            <!-- Cart -->
            <a href="cart.php" class="action-btn cart-btn" aria-label="Cart">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <path d="M16 10a4 4 0 0 1-8 0"></path>
                </svg>
                <?php if ($cartCount > 0): ?>
                    <span class="cart-badge"><?php echo $cartCount; ?></span>
                <?php endif; ?>
            </a>
            
            <!-- User Menu -->
            <?php if (isLoggedIn()): ?>
                <div class="user-menu">
                    <button class="action-btn user-toggle" aria-label="Account">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </button>
                    <div class="user-dropdown">
                        <div class="dropdown-header">
                            <span class="user-name"><?php echo cleanOutput($_SESSION['username'] ?? 'User'); ?></span>
                            <span class="user-email"><?php echo cleanOutput($_SESSION['email'] ?? ''); ?></span>
                        </div>
                        <ul class="dropdown-menu">
                            <li><a href="profile.php">My Profile</a></li>
                            <li><a href="orders.php">My Orders</a></li>
                            <li><a href="wishlist.php">Wishlist</a></li>
                            <li class="divider"></li>
                            <li><a href="logout.php" class="logout-link">Logout</a></li>
                        </ul>
                    </div>
                </div>
            <?php else: ?>
                <a href="login.php" class="btn btn-outline btn-sm">Sign In</a>
            <?php endif; ?>
            
            <!-- Mobile Menu Toggle -->
            <button class="action-btn menu-toggle" aria-label="Menu">
                <svg class="menu-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="3" y1="12" x2="21" y2="12"></line>
                    <line x1="3" y1="6" x2="21" y2="6"></line>
                    <line x1="3" y1="18" x2="21" y2="18"></line>
                </svg>
                <svg class="close-icon hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>
    </div>
    
    <!-- Search Bar (Hidden by default) -->
    <div class="search-bar" id="searchBar">
        <div class="search-container">
            <form action="products.php" method="GET" class="search-form">
                <input type="text" name="search" placeholder="Search for products..." autocomplete="off">
                <button type="button" class="search-close" aria-label="Close search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </form>
        </div>
    </div>
    
    <!-- Mobile Navigation -->
    <nav class="mobile-nav" id="mobileNav">
        <ul class="mobile-nav-list">
            <li><a href="index.php">Home</a></li>
            <li><a href="products.php">Shop All</a></li>
            <li><a href="products.php?category=clothes">Clothes</a></li>
            <li><a href="products.php?category=shoes">Shoes</a></li>
            <li><a href="products.php?category=bags">Bags</a></li>
            <li><a href="products.php?category=caps">Caps</a></li>
        </ul>
        
        <?php if (!isLoggedIn()): ?>
            <div class="mobile-nav-actions">
                <a href="login.php" class="btn btn-primary btn-block">Sign In</a>
                <a href="register.php" class="btn btn-outline btn-block">Create Account</a>
            </div>
        <?php endif; ?>
    </nav>
</header>

<!-- Spacer for fixed header -->
<div class="header-spacer"></div>
