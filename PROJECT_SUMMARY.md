# Thrift Store E-Commerce - Project Summary

## Overview
A complete, clean-code e-commerce platform for curated thrifted fashion items (clothes, shoes, bags, caps) built with PHP, MySQL, HTML, CSS, and JavaScript.

## Architecture Highlights

### Clean Code Principles
- **Separation of Concerns**: Logic, presentation, and data layers are separated
- **DRY Principle**: Reusable functions and components
- **Commented Functions**: Every function has a docblock explaining its purpose
- **Consistent Naming**: snake_case for PHP, camelCase for JavaScript
- **Security First**: All inputs validated and sanitized

### Security Implementations

| Feature | Implementation |
|---------|---------------|
| SQL Injection | PDO prepared statements with parameter binding |
| XSS | `htmlspecialchars()` + `strip_tags()` on all outputs |
| CSRF | Token generation and validation on all forms |
| Passwords | Bcrypt hashing (cost: 12) |
| Rate Limiting | 5 login attempts per 15 minutes |
| Sessions | Secure cookies, HttpOnly, SameSite=Strict |
| File Uploads | MIME validation, size limits, unique filenames |

## File Structure

```
thrift-store/
├── assets/
│   ├── css/              # 3 stylesheets (main, auth, admin)
│   ├── js/               # 5 JavaScript files
│   └── images/           # Product and upload folders
├── includes/             # Reusable PHP components
│   ├── functions.php     # 50+ helper functions with comments
│   ├── header.php        # Site navigation
│   └── footer.php        # Site footer
├── config/
│   └── database.php      # PDO connection with security options
├── database/
│   └── schema.sql        # Complete database schema
├── admin/                # Admin panel (separate interface)
│   ├── includes/         # Admin components
│   ├── index.php         # Dashboard with statistics
│   ├── users.php         # User management
│   └── login.php         # Admin login
├── index.php             # Landing page with view toggle
├── login.php             # User login
├── register.php          # User registration
├── forgot-password.php   # Password recovery
├── products.php          # Product listing with filters
├── product-detail.php    # Product with size/color selection
├── cart.php              # Shopping cart
├── checkout.php          # Checkout with shipping calculation
└── .htaccess             # Security headers and protections
```

## Key Features Implemented

### User Side
1. **Landing Page**
   - "Shop Me" / "Girls" view toggle
   - Hero section with CTA
   - Featured products
   - Category navigation

2. **Authentication**
   - Registration with validation
   - Login with rate limiting
   - Forgot password with token
   - Password strength indicator
   - Show/hide password toggle

3. **Products**
   - Grid listing with filters
   - Category filtering
   - Price range filtering
   - Condition filtering
   - Size-based pricing
   - Stock indicators

4. **Cart & Checkout**
   - Persistent cart (database)
   - Quantity management
   - Location-based shipping
   - Multiple payment methods

### Admin Side
1. **Dashboard**
   - Sales statistics
   - Recent orders
   - Low stock alerts
   - Quick actions

2. **User Management**
   - View all users
   - Edit user info
   - Ban/unban with reasons
   - Delete users

3. **Product Management**
   - Add/edit products
   - Manage variants
   - Upload images
   - Stock management

## Database Tables

1. **users** - Customer accounts
2. **admins** - Admin accounts (separate table)
3. **products** - Product information
4. **product_variants** - Size/color with pricing
5. **product_images** - Multiple images per product
6. **categories** - Product categories
7. **cart** - Shopping cart items
8. **orders** - Order information
9. **order_items** - Individual items
10. **shipping_rates** - Shipping by continent
11. **transactions** - Payment logs
12. **login_attempts** - Security tracking
13. **activity_logs** - Admin audit trail

## Functions Documented

### Security Functions (functions.php)
- `sanitizeInput()` - XSS protection
- `sanitizeEmail()` - Email sanitization
- `validateEmail()` - Email with MX check
- `validateUsername()` - Username format
- `validatePassword()` - Password strength
- `validatePhoneNumber()` - Phone validation
- `validateBirthdate()` - Age validation
- `validateFileUpload()` - File upload security
- `checkLoginAttempts()` - Rate limiting
- `generateCsrfToken()` - CSRF protection
- `validateCsrfToken()` - CSRF validation

### Database Functions
- `executeQuery()` - Prepared statement wrapper
- `fetchOne()` - Single row fetch
- `fetchAll()` - Multiple rows fetch

### Utility Functions
- `generateOrderNumber()` - Unique order IDs
- `formatPrice()` - Currency formatting
- `formatDate()` - Date formatting
- `truncateText()` - Text truncation
- `getClientIp()` - IP detection
- `logActivity()` - Audit logging

## JavaScript Features

### auth.js
- Password toggle visibility
- Password strength indicator
- Real-time requirement checking
- Form validation

### main.js
- Mobile menu toggle
- Search bar toggle
- Flash message auto-dismiss
- Header scroll effect
- Smooth scrolling
- Lazy loading

### product.js
- Size selection
- Color selection (dynamic)
- Price updates
- AJAX add to cart
- Notification toasts

### cart.js
- Quantity updates (AJAX)
- Item removal
- Real-time totals

### admin.js
- Sidebar toggle
- Data table sorting
- Bulk actions
- Confirm dialogs

## Shipping Rates

| Location | Rate |
|----------|------|
| Philippines | ₱80-170 |
| Asia (other) | ₱350 |
| North America | $550 |
| Europe | $500 |
| Others | $600-1000 |

## Setup Instructions

1. Install XAMPP
2. Create database `thrift_store`
3. Import `database/schema.sql`
4. Place files in `htdocs/thrift-store/`
5. Access at `http://localhost/thrift-store`

Default admin: `admin` / `Admin@123`

## Security Checklist

- [x] SQL injection protection
- [x] XSS protection
- [x] CSRF tokens
- [x] Password hashing
- [x] Rate limiting
- [x] Session security
- [x] File upload validation
- [x] Input sanitization
- [x] Output encoding
- [x] Error logging (no display)
- [x] Secure headers (.htaccess)
- [x] Directory protection
- [x] Sensitive file protection

## Next Steps (Optional Enhancements)

1. Email service integration (PHPMailer)
2. Payment gateway integration (Stripe/PayPal)
3. Product reviews/ratings
4. Wishlist functionality
5 coupon/discount system
6. Inventory reports
7. Export functionality
8. API endpoints for mobile app
