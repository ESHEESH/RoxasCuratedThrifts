<?php
/**
 * Site Footer
 * 
 * Footer with links, social media, and copyright.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */
?>
<footer class="site-footer">
    <div class="footer-container">
        <!-- Main Footer Content -->
        <div class="footer-main">
            <!-- Brand Column -->
            <div class="footer-brand">
                <a href="index.php" class="footer-logo">
                    <span class="logo-text"><?php echo "Roxas Thrift Store"; ?></span>
                </a>
                <p class="footer-tagline">Curated thrifted fashion for the unique you.</p>
                <div class="social-links">
                    <a href="#" class="social-link" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect>
                            <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path>
                            <line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line>
                        </svg>
                    </a>
                    <a href="#" class="social-link" aria-label="Facebook">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path>
                        </svg>
                    </a>
                    <a href="#" class="social-link" aria-label="Twitter">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path>
                        </svg>
                    </a>
                    <a href="#" class="social-link" aria-label="TikTok">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M9 12a4 4 0 1 0 4 4V4a5 5 0 0 0 5 5"></path>
                        </svg>
                    </a>
                </div>
            </div>
            
            <!-- Links Columns -->
            <div class="footer-links">
                <div class="footer-column">
                    <h4>Shop</h4>
                    <ul>
                        <li><a href="products.php">All Products</a></li>
                        <li><a href="products.php?category=clothes">Clothes</a></li>
                        <li><a href="products.php?category=shoes">Shoes</a></li>
                        <li><a href="products.php?category=bags">Bags</a></li>
                        <li><a href="products.php?category=caps">Caps</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4>Help</h4>
                    <ul>
                        <li><a href="faq.php">FAQ</a></li>
                        <li><a href="shipping.php">Shipping Info</a></li>
                        <li><a href="returns.php">Returns & Exchanges</a></li>
                        <li><a href="size-guide.php">Size Guide</a></li>
                        <li><a href="contact.php">Contact Us</a></li>
                    </ul>
                </div>
                
                <div class="footer-column">
                    <h4>Company</h4>
                    <ul>
                        <li><a href="about.php">About Us</a></li>
                        <li><a href="terms.php">Terms of Service</a></li>
                        <li><a href="privacy.php">Privacy Policy</a></li>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Payment Methods -->
        <div class="payment-methods">
            <span>We accept:</span>
            <div class="payment-icons">
                <span class="payment-icon" title="GCash">GCash</span>
                <span class="payment-icon" title="Maya">Maya</span>
                <span class="payment-icon" title="Bank Transfer">Bank</span>
                <span class="payment-icon" title="Cash on Delivery">COD</span>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo "Roxas Thrift Store"; ?>. All rights reserved.</p>
            <p class="made-with">Made with love for sustainable fashion</p>
        </div>
    </div>
</footer>
