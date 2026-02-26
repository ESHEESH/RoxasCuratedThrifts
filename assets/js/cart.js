/**
 * Shopping Cart JavaScript
 * 
 * Handles cart item updates, quantity changes, and removals.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // =====================================================
    // QUANTITY UPDATES
    // =====================================================
    
    document.querySelectorAll('.cart-item').forEach(item => {
        const cartId = item.dataset.cartId;
        const qtyInput = item.querySelector('.qty-input');
        const minusBtn = item.querySelector('.qty-minus');
        const plusBtn = item.querySelector('.qty-plus');
        const removeBtn = item.querySelector('.item-remove');
        
        /**
         * Update item quantity via AJAX
         * @param {number} newQuantity - The new quantity
         */
        async function updateQuantity(newQuantity) {
            try {
                const formData = new FormData();
                formData.append('action', 'update_quantity');
                formData.append('cart_id', cartId);
                formData.append('quantity', newQuantity);
                
                const response = await fetch('cart.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Reload page to show updated totals
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to update quantity');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        }
        
        /**
         * Remove item from cart
         */
        async function removeItem() {
            if (!confirm('Are you sure you want to remove this item?')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'remove_item');
                formData.append('cart_id', cartId);
                
                const response = await fetch('cart.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Animate removal
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(-20px)';
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 300);
                } else {
                    alert(data.message || 'Failed to remove item');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        }
        
        // Minus button
        if (minusBtn) {
            minusBtn.addEventListener('click', () => {
                const currentQty = parseInt(qtyInput.value) || 1;
                if (currentQty > 1) {
                    updateQuantity(currentQty - 1);
                }
            });
        }
        
        // Plus button
        if (plusBtn) {
            plusBtn.addEventListener('click', () => {
                const currentQty = parseInt(qtyInput.value) || 1;
                const maxQty = parseInt(qtyInput.max) || 99;
                if (currentQty < maxQty) {
                    updateQuantity(currentQty + 1);
                }
            });
        }
        
        // Remove button
        if (removeBtn) {
            removeBtn.addEventListener('click', removeItem);
        }
    });
    
    // =====================================================
    // CART SUMMARY UPDATES
    // =====================================================
    
    /**
     * Update cart summary totals
     * This is called after quantity changes
     */
    function updateCartSummary() {
        let subtotal = 0;
        let itemCount = 0;
        
        document.querySelectorAll('.cart-item').forEach(item => {
            const qty = parseInt(item.querySelector('.qty-input').value) || 0;
            const priceText = item.querySelector('.item-price .price')?.textContent || '₱0';
            const price = parseFloat(priceText.replace(/[₱,]/g, '')) || 0;
            
            subtotal += price;
            itemCount += qty;
        });
        
        // Update subtotal display
        const subtotalEl = document.querySelector('.summary-row .subtotal');
        if (subtotalEl) {
            subtotalEl.textContent = formatPrice(subtotal);
        }
        
        // Update total display
        const totalEl = document.querySelector('.summary-row.total .total-price');
        if (totalEl) {
            totalEl.textContent = formatPrice(subtotal);
        }
        
        // Update item count
        const countEl = document.querySelector('.cart-count');
        if (countEl) {
            countEl.textContent = `${itemCount} item${itemCount !== 1 ? 's' : ''}`;
        }
    }
    
});
