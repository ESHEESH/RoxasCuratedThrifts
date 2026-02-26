<?php
/**
 * Admin Header
 * 
 * Top navigation bar for admin panel.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */
?>
<header class="admin-header">
    <div class="header-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="3" y1="12" x2="21" y2="12"></line>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <line x1="3" y1="18" x2="21" y2="18"></line>
            </svg>
        </button>
        <h1 class="page-title"><?php echo $pageTitle ?? 'Admin Panel'; ?></h1>
    </div>
    
    <div class="header-right">
        <!-- Search -->
        <div class="header-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8"></circle>
                <path d="M21 21l-4.35-4.35"></path>
            </svg>
            <input type="text" placeholder="Search..." id="adminSearch">
        </div>
        
        <!-- Notifications -->
        <button class="header-btn notification-btn" aria-label="Notifications">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span class="notification-badge">3</span>
        </button>
        
        <!-- Admin Profile -->
        <div class="admin-profile">
            <button class="profile-toggle" id="profileToggle">
                <div class="profile-avatar">
                    <?php echo strtoupper(substr($_SESSION['admin_name'] ?? 'A', 0, 1)); ?>
                </div>
                <div class="profile-info">
                    <span class="profile-name"><?php echo cleanOutput($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                    <span class="profile-role"><?php echo ucfirst($_SESSION['admin_role'] ?? 'Admin'); ?></span>
                </div>
                <svg class="profile-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <polyline points="6 9 12 15 18 9"></polyline>
                </svg>
            </button>
            
            <div class="profile-dropdown" id="profileDropdown">
                <div class="dropdown-header">
                    <span class="dropdown-name"><?php echo cleanOutput($_SESSION['admin_name'] ?? 'Admin'); ?></span>
                    <span class="dropdown-email"><?php echo cleanOutput($_SESSION['admin_email'] ?? ''); ?></span>
                </div>
                <ul class="dropdown-menu">
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="settings.php">Settings</a></li>
                    <li class="divider"></li>
                    <li><a href="logout.php" class="logout-link">Logout</a></li>
                </ul>
            </div>
        </div>
    </div>
</header>
