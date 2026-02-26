/**
 * Product Detail JavaScript
 * 
 * Handles variant selection, size-based pricing, and add to cart
 * functionality on the product detail page.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // =====================================================
    // VARIANT SELECTION
    // =====================================================
    
    const sizeButtons = document.querySelectorAll('.size-btn');
    const colorGroup = document.getElementById('colorGroup');
    const colorOptions = document.getElementById('colorOptions');
    const variantInfo = document.getElementById('variantInfo');
    const variantPrice = variantInfo?.querySelector('.variant-price');
    const variantStock = variantInfo?.querySelector('.variant-stock');
    const variantIdInput = document.getElementById('variantId');
    const addToCartBtn = document.getElementById('addToCartBtn');
    
    let selectedSize = null;
    let selectedVariant = null;
    
    /**
     * Update color options based on selected size
     * @param {string} size - The selected size
     */
    function updateColorOptions(size) {
        if (!colorOptions || !sizeGroups[size]) return;
        
        // Clear previous options
        colorOptions.innerHTML = '';
        
        // Get variants for this size
        const sizeVariants = sizeGroups[size];
        
        // Create color buttons
        sizeVariants.forEach(variant => {
            if (variant.stock_quantity > 0) {
                const colorBtn = document.createElement('button');
                colorBtn.type = 'button';
                colorBtn.className = 'color-btn';
                colorBtn.dataset.variantId = variant.variant_id;
                colorBtn.dataset.price = variant.price_adjustment;
                colorBtn.dataset.stock = variant.stock_quantity;
                colorBtn.dataset.color = variant.color;
                
                // Color swatch
                const swatch = document.createElement('span');
                swatch.className = 'color-swatch';
                swatch.style.backgroundColor = variant.color_hex || '#ccc';
                colorBtn.appendChild(swatch);
                
                // Color name
                const name = document.createElement('span');
                name.className = 'color-name';
                name.textContent = variant.color;
                colorBtn.appendChild(name);
                
                colorBtn.addEventListener('click', () => selectColor(variant, colorBtn));
                colorOptions.appendChild(colorBtn);
            }
        });
        
        // Show color group
        colorGroup.style.display = 'block';
    }
    
    /**
     * Handle color selection
     * @param {Object} variant - The selected variant
     * @param {HTMLElement} button - The clicked button
     */
    function selectColor(variant, button) {
        selectedVariant = variant;
        
        // Update active state
        colorOptions.querySelectorAll('.color-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        button.classList.add('active');
        
        // Update variant info
        updateVariantInfo(variant);
        
        // Update hidden input
        if (variantIdInput) {
            variantIdInput.value = variant.variant_id;
        }
        
        // Enable add to cart button
        if (addToCartBtn) {
            addToCartBtn.disabled = false;
        }
    }
    
    /**
     * Update variant information display
     * @param {Object} variant - The selected variant
     */
    function updateVariantInfo(variant) {
        if (!variantInfo || !variantPrice || !variantStock) return;
        
        const finalPrice = basePrice + parseFloat(variant.price_adjustment);
        
        variantPrice.textContent = formatPrice(finalPrice);
        variantStock.textContent = `${variant.stock_quantity} in stock`;
        variantStock.className = 'variant-stock ' + (variant.stock_quantity <= 3 ? 'low' : '');
        
        variantInfo.style.display = 'block';
    }
    
    /**
     * Handle size selection
     * @param {string} size - The selected size
     * @param {HTMLElement} button - The clicked button
     */
    function selectSize(size, button) {
        selectedSize = size;
        selectedVariant = null;
        
        // Update active state
        sizeButtons.forEach(btn => btn.classList.remove('active'));
        button.classList.add('active');
        
        // Update color options
        updateColorOptions(size);
        
        // Hide variant info and disable add to cart
        if (variantInfo) {
            variantInfo.style.display = 'none';
        }
        if (addToCartBtn) {
            addToCartBtn.disabled = true;
        }
        if (variantIdInput) {
            variantIdInput.value = '';
        }
    }
    
    // Attach size button handlers
    sizeButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            if (!this.disabled) {
                selectSize(this.dataset.size, this);
            }
        });
    });
    
    // =====================================================
    // ADD TO CART
    // =====================================================
    
    const addToCartForm = document.getElementById('addToCartForm');
    
    if (addToCartForm) {
        addToCartForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            if (!selectedVariant) {
                alert('Please select a size and color');
                return;
            }
            
            const formData = new FormData(this);
            
            try {
                addToCartBtn.disabled = true;
                addToCartBtn.innerHTML = '<span class="spinner"></span> Adding...';
                
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Update cart badge
                    const cartBadge = document.querySelector('.cart-badge');
                    if (cartBadge) {
                        cartBadge.textContent = data.cart_count;
                        cartBadge.style.display = 'flex';
                    }
                    
                    // Show success message
                    showNotification('Item added to cart!', 'success');
                    
                    // Reset button
                    addToCartBtn.disabled = false;
                    addToCartBtn.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        Add to Cart
                    `;
                } else {
                    showNotification(data.message || 'Failed to add item', 'error');
                    addToCartBtn.disabled = false;
                    addToCartBtn.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                            <line x1="3" y1="6" x2="21" y2="6"></line>
                            <path d="M16 10a4 4 0 0 1-8 0"></path>
                        </svg>
                        Add to Cart
                    `;
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
                addToCartBtn.disabled = false;
                addToCartBtn.innerHTML = `
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <path d="M16 10a4 4 0 0 1-8 0"></path>
                    </svg>
                    Add to Cart
                `;
            }
        });
    }
    
    // =====================================================
    // NOTIFICATION
    // =====================================================
    
    /**
     * Show notification toast
     * @param {string} message - Message to display
     * @param {string} type - Notification type (success, error, warning)
     */
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <span>${message}</span>
            <button onclick="this.parentElement.remove()">&times;</button>
        `;
        
        document.body.appendChild(notification);
        
        // Animate in
        setTimeout(() => notification.classList.add('show'), 10);
        
        // Auto dismiss
        setTimeout(() => {
            notification.classList.remove('show');
            setTimeout(() => notification.remove(), 300);
        }, 3000);
    }
    
});
