/**
 * Admin Panel JavaScript
 * 
 * Core functionality for the admin dashboard.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // =====================================================
    // SIDEBAR TOGGLE (Mobile)
    // =====================================================
    
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarClose = document.getElementById('sidebarClose');
    const adminSidebar = document.getElementById('adminSidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    
    function openSidebar() {
        adminSidebar?.classList.add('active');
        sidebarOverlay?.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeSidebar() {
        adminSidebar?.classList.remove('active');
        sidebarOverlay?.classList.remove('active');
        document.body.style.overflow = '';
    }
    
    sidebarToggle?.addEventListener('click', openSidebar);
    sidebarClose?.addEventListener('click', closeSidebar);
    sidebarOverlay?.addEventListener('click', closeSidebar);
    
    // =====================================================
    // FLASH MESSAGE AUTO-DISMISS
    // =====================================================
    
    const flashMessage = document.getElementById('flashMessage');
    
    if (flashMessage) {
        setTimeout(() => {
            flashMessage.style.opacity = '0';
            flashMessage.style.transform = 'translateY(-10px)';
            setTimeout(() => flashMessage.remove(), 300);
        }, 5000);
    }
    
    // =====================================================
    // DATA TABLE SORTING
    // =====================================================
    
    document.querySelectorAll('.data-table th[data-sort]').forEach(th => {
        th.addEventListener('click', function() {
            const table = this.closest('table');
            const tbody = table.querySelector('tbody');
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const column = this.cellIndex;
            const sortType = this.dataset.sort;
            const isAsc = !this.classList.contains('asc');
            
            // Remove sort classes from all headers
            table.querySelectorAll('th').forEach(header => {
                header.classList.remove('asc', 'desc');
            });
            
            // Add sort class to current header
            this.classList.add(isAsc ? 'asc' : 'desc');
            
            // Sort rows
            rows.sort((a, b) => {
                const aVal = a.cells[column].textContent.trim();
                const bVal = b.cells[column].textContent.trim();
                
                if (sortType === 'number') {
                    return isAsc 
                        ? parseFloat(aVal) - parseFloat(bVal)
                        : parseFloat(bVal) - parseFloat(aVal);
                } else if (sortType === 'date') {
                    return isAsc
                        ? new Date(aVal) - new Date(bVal)
                        : new Date(bVal) - new Date(aVal);
                } else {
                    return isAsc
                        ? aVal.localeCompare(bVal)
                        : bVal.localeCompare(aVal);
                }
            });
            
            // Re-append rows in sorted order
            rows.forEach(row => tbody.appendChild(row));
        });
    });
    
    // =====================================================
    // CONFIRM DIALOGS
    // =====================================================
    
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const message = this.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // =====================================================
    // BULK ACTIONS
    // =====================================================
    
    const selectAllCheckbox = document.getElementById('selectAll');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    const bulkActions = document.getElementById('bulkActions');
    
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkActions();
        });
    }
    
    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkActions);
    });
    
    function updateBulkActions() {
        const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
        if (bulkActions) {
            bulkActions.style.display = checkedCount > 0 ? 'flex' : 'none';
            bulkActions.querySelector('.selected-count').textContent = `${checkedCount} selected`;
        }
    }
    
    // =====================================================
    // SEARCH DEBOUNCE
    // =====================================================
    
    const adminSearch = document.getElementById('adminSearch');
    
    if (adminSearch) {
        let searchTimeout;
        
        adminSearch.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                // Implement search functionality
                console.log('Searching:', this.value);
            }, 300);
        });
    }
    
    // =====================================================
    // CHART INITIALIZATION (if Chart.js is available)
    // =====================================================
    
    if (typeof Chart !== 'undefined') {
        // Sales chart
        const salesChartEl = document.getElementById('salesChart');
        if (salesChartEl) {
            new Chart(salesChartEl, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Sales',
                        data: [12000, 19000, 15000, 25000, 22000, 30000],
                        borderColor: '#1a1a1a',
                        backgroundColor: 'rgba(26, 26, 26, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '₱' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
        }
    }
    
});

// =====================================================
// UTILITY FUNCTIONS
// =====================================================

/**
 * Format number with commas
 * @param {number} num - Number to format
 * @returns {string} Formatted number
 */
function formatNumber(num) {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
}

/**
 * Format price
 * @param {number} amount - Amount to format
 * @returns {string} Formatted price
 */
function formatPrice(amount) {
    return '₱' + parseFloat(amount).toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Show loading spinner
 * @param {HTMLElement} element - Element to show spinner in
 */
function showSpinner(element) {
    element.innerHTML = '<span class="spinner"></span>';
}

/**
 * Hide loading spinner
 * @param {HTMLElement} element - Element to hide spinner from
 * @param {string} content - Content to restore
 */
function hideSpinner(element, content) {
    element.innerHTML = content;
}
