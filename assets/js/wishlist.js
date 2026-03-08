/**
 * Wishlist JavaScript
 * 
 * Handles wishlist add/remove with AJAX
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Handle all wishlist button clicks
    document.addEventListener('click', function(e) {
        const wishlistBtn = e.target.closest('.wishlist-btn') || e.target.closest('.wishlist-btn-large');
        
        if (!wishlistBtn) return;
        
        e.preventDefault();
        e.stopPropagation();
        
        const href = wishlistBtn.getAttribute('href');
        
        // Check if user needs to login
        if (href && href.includes('login.php')) {
            window.location.href = href;
            return;
        }
        
        // Parse the URL to get parameters
        let url = new URL(href, window.location.origin + window.location.pathname);
        const productId = url.searchParams.get('product_id');
        const removeId = url.searchParams.get('remove');
        
        if (!productId && !removeId) {
            console.error('No product ID or wishlist ID found');
            return;
        }
        
        // Determine action and ID
        const action = removeId ? 'remove' : 'add';
        const requestProductId = productId || removeId;
        
        // Disable button during request
        wishlistBtn.style.pointerEvents = 'none';
        wishlistBtn.style.opacity = '0.6';
        
        // Determine the correct path to wishlist-add.php based on current location
        let basePath = '';
        if (window.location.pathname.includes('/shop/')) {
            basePath = '';
        } else {
            basePath = 'shop/';
        }
        
        // Make AJAX request
        fetch(`${basePath}wishlist-add.php?product_id=${requestProductId}&action=${action}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show toast notification
                window.showToast(
                    action === 'add' ? 'Added to Wishlist' : 'Removed from Wishlist',
                    data.message,
                    'success'
                );
                
                // Update button state
                const heartSvg = wishlistBtn.querySelector('svg');
                if (heartSvg) {
                    if (data.inWishlist) {
                        // Item is now in wishlist - show filled heart
                        heartSvg.setAttribute('fill', 'currentColor');
                        wishlistBtn.classList.add('active');
                        wishlistBtn.setAttribute('title', 'Remove from Wishlist');
                        
                        // Update href to remove action
                        const newHref = href.replace(/product_id=\d+/, `product_id=${requestProductId}`).replace('wishlist-add.php', 'wishlist.php?remove=' + requestProductId);
                        // Better approach: construct new URL
                        if (window.location.pathname.includes('/shop/')) {
                            wishlistBtn.setAttribute('href', `wishlist.php?remove=${requestProductId}`);
                        } else {
                            wishlistBtn.setAttribute('href', `shop/wishlist.php?remove=${requestProductId}`);
                        }
                    } else {
                        // Item is not in wishlist - show empty heart
                        heartSvg.setAttribute('fill', 'none');
                        wishlistBtn.classList.remove('active');
                        wishlistBtn.setAttribute('title', 'Add to Wishlist');
                        
                        // Update href to add action
                        if (window.location.pathname.includes('/shop/')) {
                            wishlistBtn.setAttribute('href', `wishlist-add.php?product_id=${requestProductId}`);
                        } else {
                            wishlistBtn.setAttribute('href', `shop/wishlist-add.php?product_id=${requestProductId}`);
                        }
                    }
                }
                
                // If on wishlist page, reload to update the list
                if (window.location.pathname.includes('wishlist.php')) {
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                }
            } else {
                window.showToast('Error', data.message, 'error');
            }
        })
        .catch(error => {
            console.error('Wishlist error:', error);
            window.showToast('Error', 'Failed to update wishlist', 'error');
        })
        .finally(() => {
            // Re-enable button
            wishlistBtn.style.pointerEvents = '';
            wishlistBtn.style.opacity = '';
        });
    });
    
});
