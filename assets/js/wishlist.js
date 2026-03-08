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
        let productId = url.searchParams.get('product_id');
        const removeId = url.searchParams.get('remove');
        
        // Fallback to data attribute if product_id not in URL
        if (!productId && !removeId) {
            productId = wishlistBtn.getAttribute('data-product-id');
        }
        
        if (!productId && !removeId) {
            console.error('No product ID or wishlist ID found');
            console.error('href:', href);
            console.error('data-product-id:', wishlistBtn.getAttribute('data-product-id'));
            return;
        }
        
        // Determine action and ID
        const action = url.searchParams.get('action') || (removeId ? 'remove' : 'add');
        const requestProductId = productId || removeId;
        
        // Disable button during request
        wishlistBtn.style.pointerEvents = 'none';
        wishlistBtn.style.opacity = '0.6';
        
        // Determine the correct path to wishlist-add.php based on current location
        let apiPath = '';
        const currentPath = window.location.pathname;
        
        // Get the base URL from the current location
        const pathParts = currentPath.split('/');
        const baseIndex = pathParts.indexOf('thrift-store');
        
        if (baseIndex !== -1) {
            // Construct absolute path
            const basePath = pathParts.slice(0, baseIndex + 1).join('/');
            apiPath = `${basePath}/shop/wishlist-add.php`;
        } else {
            // Fallback to relative path
            if (currentPath.includes('/shop/')) {
                apiPath = 'wishlist-add.php';
            } else {
                apiPath = 'shop/wishlist-add.php';
            }
        }
        
        // Make AJAX request
        const requestUrl = `${apiPath}?product_id=${requestProductId}&action=${action}`;
        console.log('Wishlist request URL:', requestUrl); // Debug log
        console.log('Product ID:', requestProductId, 'Action:', action); // Debug log
        
        fetch(requestUrl, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            console.log('Wishlist response:', data); // Debug log
            
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
                console.error('Wishlist error response:', data); // Debug log
                window.showToast('Error', data.message + (data.debug ? ' (Check console)' : ''), 'error');
            }
        })
        .catch(error => {
            console.error('Wishlist fetch error:', error);
            window.showToast('Error', 'Failed to update wishlist. Check console for details.', 'error');
        })
        .finally(() => {
            // Re-enable button
            wishlistBtn.style.pointerEvents = '';
            wishlistBtn.style.opacity = '';
        });
    });
    
});
