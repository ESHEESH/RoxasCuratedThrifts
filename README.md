# Thrift Store E-Commerce Platform

A clean, secure, and feature-rich e-commerce platform for curated thrifted clothing, shoes, bags, and caps.

## Features

### User Interface
- **Landing Page** with "Shop Me" / "Girls" view toggle
- **Product Browsing** with filters (category, condition, price range)
- **Size-based Pricing** - Different sizes can have different prices
- **Shopping Cart** with persistent storage and toast notifications
- **Checkout** with card-based payment method selection and location-based shipping fees
- **User Authentication** (login, register, forgot password)
- **Wishlist** functionality for saving favorite products
- **Order Tracking** with status updates and order history
- **Dashboard** with sales statistics and quick actions
- **User Management** (view, edit, ban, delete users)
- **Product Management** (CRUD operations, stock management)
- **Order Management** with status tracking
- **Activity Logs** for audit trail
- **Transaction Logs** for payment tracking

### Security Features
- SQL Injection Protection (prepared statements)
- XSS Protection (input sanitization)
- CSRF Token Protection
- Password Hashing (bcrypt)
- Rate Limiting (login attempts)
- Session Security
- File Upload Validation

## Tech Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 8.0+
- **Database**: MySQL (via XAMPP)
- **Server**: Apache

## Installation

### Prerequisites
- XAMPP (or similar Apache/MySQL/PHP stack)
- PHP 8.0 or higher
- MySQL 5.7 or higher

### Setup Steps

1. **Clone/Extract the project** to your XAMPP htdocs folder:
2. **Create the database**:
- Open phpMyAdmin (http://localhost/phpmyadmin)
- Create a new database named `thrift_store`
- Import the database schema from `database/schema.sql`

3. **Configure database connection**:
- Edit `config/database.php` if needed
- Default XAMPP credentials:
  - Host: `localhost`
  - Username: `root`
  - Password: `` (empty)
  - Database: `thrift_store`

4. **Set up upload directory**:
- Create `assets/images/products/` folder
- Create `assets/images/uploads/` folder
- Ensure PHP has write permissions

5. **Access the website**:
- User site: http://localhost/thrift-store
- Admin panel: http://localhost/thrift-store/admin
- Login: http://localhost/thrift-store/auth/login.php
- Shop: http://localhost/thrift-store/shop/products.php

### Default Admin Account
- **Username**: `admin`
- **Password**: `Admin@123`

**Important**: Change the default password after first login!

## Project Structure

thrift-store/
├── admin/                 # Admin panel
│   ├── includes/          # Admin components
│   └── *.php              # Admin pages
├── assets/                # Static assets
│   ├── css/               # Stylesheets
│   │   ├── main.css       # Main styles
│   │   ├── auth.css       # Authentication styles
│   │   ├── admin.css      # Admin panel styles
│   │   └── responsive.css # Responsive design
│   ├── js/                # JavaScript files
│   │   ├── main.js        # Main functionality
│   │   ├── auth.js        # Authentication
│   │   ├── product.js     # Product page
│   │   ├── cart.js        # Shopping cart
│   │   └── admin.js       # Admin functionality
│   └── images/            # Image assets
│       ├── products/      # Product images
│       └── uploads/       # User uploads
├── auth/                  # Authentication pages
│   ├── login.php          # User login
│   ├── register.php       # User registration
│   ├── logout.php         # Logout handler
│   ├── forgot-password.php # Password recovery
│   └── reset-password.php  # Password reset
├── config/                # Configuration files
│   └── database.php       # Database connection
├── database/              # Database files
│   └── schema.sql         # Database schema
├── documents/             # Documentation
│   ├── SYSTEM_DOCUMENTATION.md
│   ├── ERD_MERMAID.md
│   ├── ERD_QUICK_GUIDE.md
│   ├── LAZY_LOADING_ANIMATION.md
│   └── PERFORMANCE_OPTIMIZATION.md
├── includes/              # Reusable PHP components
│   ├── header.php         # Site header
│   ├── footer.php         # Site footer
│   └── functions.php      # Helper functions
├── shop/                  # Shopping pages
│   ├── products.php       # Product listing
│   ├── product-detail.php # Single product view
│   ├── cart.php           # Shopping cart
│   ├── checkout.php       # Checkout page
│   ├── wishlist.php       # User wishlist
│   └── wishlist-add.php   # Add to wishlist handler
├── user/                  # User account pages
│   ├── profile.php        # User profile
│   ├── orders.php         # Order history
│   └── order-confirmation.php # Order confirmation
├── index.php              # Landing page
├── about.php              # About page
├── contact.php            # Contact page
├── faq.php                # FAQ page
├── privacy.php            # Privacy policy
├── terms.php              # Terms of service
├── size-guide.php         # Size guide
├── shipping-info.php      # Shipping information
├── returns-exchanges.php # Returns & exchanges
├── .htaccess              # Apache configuration
└── README.md              # This file
Database SchemaCore Tablesusers - Customer accountsadmins - Admin accounts (separate from users)products - Product informationproduct_variants - Size/color variations with pricingcategories - Product categoriescart - Shopping cart itemsorders - Order informationorder_items - Individual order itemsshipping_rates - Shipping fees by continenttransactions - Payment transaction logslogin_attempts - Failed login tracking (security)activity_logs - Admin activity trackingSecurity MeasuresImplemented ProtectionsSQL Injection: All queries use prepared statements with parameter bindingXSS Attacks: Input sanitization with htmlspecialchars() and strip_tags()CSRF Attacks: Token validation on all formsPassword Security: Bcrypt hashing with cost factor 12Rate Limiting: 5 login attempts per 15 minutesSession Security: Secure session handling with regenerationFile Uploads: MIME type validation, size limits, unique filenamesPassword RequirementsMinimum 8 charactersAt least one uppercase letterAt least one lowercase letterAt least one numberAt least one special characterShipping FeesContinentBase RatePhilippines (Asia)₱80-170Other Asia₱350North America₱550South America₱650Europe₱500Africa₱600Oceania₱600Antarctica₱1000Payment MethodsGCashMayaBank TransferCash on Delivery (COD)Development NotesAdding New Products  Login to admin panel  Go to Products → Add Product  Fill in product details  Upload product images  Add variants (sizes/colors) with stock quantities  Managing Orders  Orders appear in the admin dashboard  Update order status as it progresses  Add tracking numbers for shipped orders  User Management  View all registered users  Ban/unban users with reasons  Edit user information  Delete users (only if no orders exist)  Customization  Changing Colors  Edit CSS variables in assets/css/main.css:  CSS:root {
    --color-primary: #1a1a1a;
    --color-accent: #ff6b6b;
    /* ... */
}
Adding New Categories  Insert into the categories table in the database.  Modifying Shipping Rates  Update the shipping_rates table in the database.  Troubleshooting  Common Issues  Database connection failed  Check XAMPP is running (Apache and MySQL)  Verify database credentials in config/database.phpEnsure database thrift_store exists  Images not uploading  Check assets/images/products/ folder exists  Verify PHP has write permissions  Check upload_max_filesize in php.ini  Session issues  Clear browser cookies  Check PHP session configuration  License  This project is for educational purposes.  Credits  Developed for a curated thrift store e-commerce platform.  
