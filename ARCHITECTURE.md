# Thrift Store E-Commerce - Clean Code Architecture

## Project Overview
A curated thrifted clothing e-commerce platform with separate user and admin interfaces.

## Tech Stack
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 8.0+
- **Database**: MySQL (via XAMPP)
- **Server**: Apache (via XAMPP)

## Folder Structure
```
thrift-store/
├── assets/
│   ├── css/              # Stylesheets
│   │   ├── main.css      # Main styles
│   │   ├── auth.css      # Authentication styles
│   │   ├── admin.css     # Admin panel styles
│   │   └── responsive.css # Responsive design
│   ├── js/               # JavaScript files
│   │   ├── main.js       # Main functionality
│   │   ├── auth.js       # Authentication (show password, validation)
│   │   ├── cart.js       # Shopping cart
│   │   └── admin.js      # Admin functionality
│   └── images/           # Image assets
│       ├── products/     # Product images
│       └── uploads/      # User/admin uploads
├── includes/             # Reusable PHP components
│   ├── header.php        # Site header
│   ├── footer.php        # Site footer
│   ├── navbar.php        # Navigation
│   └── functions.php     # Helper functions
├── api/                  # API endpoints
│   ├── auth.php          # Authentication API
│   ├── cart.php          # Cart operations
│   ├── products.php      # Product API
│   └── orders.php        # Order processing
├── admin/                # Admin panel
│   ├── index.php         # Admin dashboard
│   ├── users.php         # User management
│   ├── products.php      # Product management
│   ├── orders.php        # Order management
│   └── statistics.php    # Sales statistics
├── config/               # Configuration files
│   └── database.php      # Database connection
├── database/             # Database files
│   └── schema.sql        # Database schema
├── logs/                 # Application logs
├── index.php             # Landing page
├── login.php             # Login page
├── register.php          # Registration page
├── forgot-password.php   # Password recovery
├── products.php          # Product listing
├── product-detail.php    # Single product view
├── cart.php              # Shopping cart page
├── checkout.php          # Checkout page
├── profile.php           # User profile
└── .htaccess             # Apache configuration
```

## Security Measures Implemented

### 1. SQL Injection Protection
- All database queries use prepared statements with parameter binding
- Input sanitization using `htmlspecialchars()` and `strip_tags()`
- Custom validation functions for each input type

### 2. Password Security
- Minimum 8 characters with special characters requirement
- Bcrypt hashing (password_hash() with PASSWORD_BCRYPT)
- Show/hide password toggle functionality

### 3. User Validation
- Email format validation with regex
- Phone number validation
- Username uniqueness check
- Birthdate validation (18+ requirement)

### 4. Session Security
- Secure session handling with regeneration
- CSRF token protection on forms
- Session timeout after 30 minutes of inactivity
- HttpOnly and Secure cookie flags

### 5. Rate Limiting
- Login attempt limiter (5 attempts per 15 minutes)
- Registration rate limiting
- API request throttling

### 6. File Upload Security
- Allowed extensions whitelist
- File size limits
- MIME type validation
- Unique filename generation

## Database Architecture

### Tables:
1. **users** - Customer accounts
2. **admins** - Admin accounts (separate from users)
3. **products** - Product information
4. **product_variants** - Size/color variations with pricing
5. **categories** - Product categories
6. **cart** - Shopping cart items
7. **orders** - Order information
8. **order_items** - Individual order items
9. **shipping_rates** - Shipping fee by continent
10. **transactions** - Payment transaction logs
11. **login_attempts** - Failed login tracking (security)
12. **activity_logs** - Admin activity tracking

## Key Features

### User Interface:
- Landing page with "Shop Me" / "Girls" view toggle
- Product browsing with filters
- Size-based pricing
- Shopping cart with persistent storage
- Checkout with location-based shipping
- Order history

### Admin Interface:
- Sales statistics dashboard
- Customer management (view, edit, ban, delete)
- Product management (CRUD operations)
- Stock management
- Order management
- Transaction logs
- Activity monitoring

## Coding Standards
- All functions documented with comments
- Consistent naming conventions (snake_case for PHP, camelCase for JS)
- Separation of concerns (logic, presentation, data)
- DRY principle (Don't Repeat Yourself)
- Input validation at every entry point
