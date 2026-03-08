<?php
/**
 * Shipping Information Page
 */

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Shipping Info';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/main.css">
    <link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .info-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        
        .info-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .info-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .info-header p {
            font-size: 1.125rem;
            color: #666;
        }
        
        .info-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .info-section h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #1a1a1a;
        }
        
        .info-section h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 1.5rem 0 1rem;
        }
        
        .info-section p {
            line-height: 1.8;
            color: #555;
            margin-bottom: 1rem;
        }
        
        .info-section ul, .info-section ol {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .info-section li {
            line-height: 1.8;
            color: #555;
            margin-bottom: 0.5rem;
        }
        
        .shipping-table {
            width: 100%;
            border-collapse: collapse;
            margin: 1.5rem 0;
        }
        
        .shipping-table th,
        .shipping-table td {
            padding: 1rem;
            text-align: left;
            border: 1px solid #e0e0e0;
        }
        
        .shipping-table th {
            background: #f8f8f8;
            font-weight: 600;
        }
        
        .highlight-box {
            background: #f0f7ff;
            border-left: 4px solid #2196F3;
            padding: 1.25rem;
            margin: 1.5rem 0;
            border-radius: 4px;
        }
        
        .highlight-box strong {
            color: #1976D2;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="container" style="padding-top: 90px; padding-bottom: 3rem;">
        <nav class="breadcrumb">
            <a href="index.php">Home</a>
            <span class="separator">/</span>
            <span class="current">Shipping Info</span>
        </nav>
        
        <div class="info-container">
            <div class="info-header">
                <h1>Shipping Information</h1>
                <p>Everything you need to know about our shipping and delivery</p>
            </div>
            
            <!-- Shipping Rates -->
            <div class="info-section">
                <h2>Shipping Rates</h2>
                <p>We offer competitive shipping rates across the Philippines and internationally.</p>
                
                <table class="shipping-table">
                    <thead>
                        <tr>
                            <th>Location</th>
                            <th>Delivery Time</th>
                            <th>Shipping Fee</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Metro Manila</strong></td>
                            <td>2-3 business days</td>
                            <td>₱80 - ₱120</td>
                        </tr>
                        <tr>
                            <td><strong>Luzon (Provincial)</strong></td>
                            <td>3-5 business days</td>
                            <td>₱120 - ₱150</td>
                        </tr>
                        <tr>
                            <td><strong>Visayas</strong></td>
                            <td>5-7 business days</td>
                            <td>₱150 - ₱180</td>
                        </tr>
                        <tr>
                            <td><strong>Mindanao</strong></td>
                            <td>5-7 business days</td>
                            <td>₱150 - ₱200</td>
                        </tr>
                        <tr>
                            <td><strong>International</strong></td>
                            <td>10-15 business days</td>
                            <td>Calculated at checkout</td>
                        </tr>
                    </tbody>
                </table>
                
                <div class="highlight-box">
                    <strong>Free Shipping:</strong> Orders over ₱2,000 within Metro Manila qualify for free standard shipping!
                </div>
            </div>
            
            <!-- Processing Time -->
            <div class="info-section">
                <h2>Order Processing</h2>
                <p>We process orders Monday through Saturday, excluding holidays.</p>
                
                <h3>Processing Timeline:</h3>
                <ul>
                    <li><strong>Standard Orders:</strong> Processed within 1-2 business days</li>
                    <li><strong>Pre-order Items:</strong> Processing time varies (specified on product page)</li>
                    <li><strong>Custom Requests:</strong> 3-5 business days</li>
                </ul>
                
                <p>You'll receive an email confirmation once your order has been processed and shipped, including your tracking number.</p>
            </div>
            
            <!-- Shipping Methods -->
            <div class="info-section">
                <h2>Shipping Methods</h2>
                
                <h3>Domestic Shipping (Philippines)</h3>
                <p>We partner with trusted courier services:</p>
                <ul>
                    <li><strong>J&T Express</strong> - Standard delivery</li>
                    <li><strong>LBC</strong> - Provincial areas</li>
                    <li><strong>Grab Express</strong> - Same-day delivery (Metro Manila only, additional fee)</li>
                    <li><strong>Lalamove</strong> - Same-day delivery (Metro Manila only, additional fee)</li>
                </ul>
                
                <h3>International Shipping</h3>
                <p>We ship worldwide via:</p>
                <ul>
                    <li><strong>DHL Express</strong> - Fast international shipping (5-7 days)</li>
                    <li><strong>FedEx</strong> - Reliable international delivery (7-10 days)</li>
                    <li><strong>Philippine Post (EMS)</strong> - Economy option (10-15 days)</li>
                </ul>
                
                <p><em>Note: International shipping fees and customs duties vary by destination. Customers are responsible for any customs fees or import taxes.</em></p>
            </div>
            
            <!-- Tracking -->
            <div class="info-section">
                <h2>Order Tracking</h2>
                <p>Stay updated on your order's journey:</p>
                
                <ol>
                    <li><strong>Order Confirmation:</strong> Receive email confirmation immediately after placing order</li>
                    <li><strong>Processing:</strong> Get notified when your order is being prepared</li>
                    <li><strong>Shipped:</strong> Receive tracking number via email and SMS</li>
                    <li><strong>Out for Delivery:</strong> Courier will contact you for delivery arrangement</li>
                    <li><strong>Delivered:</strong> Confirmation once package is received</li>
                </ol>
                
                <p>You can also track your order anytime by:</p>
                <ul>
                    <li>Logging into your account and visiting "My Orders"</li>
                    <li>Using the tracking number on the courier's website</li>
                    <li>Contacting our customer service</li>
                </ul>
            </div>
            
            <!-- Delivery Issues -->
            <div class="info-section">
                <h2>Delivery Issues</h2>
                
                <h3>What if I'm not home during delivery?</h3>
                <p>The courier will attempt delivery 2-3 times. If unsuccessful, the package will be held at the nearest branch for pickup. You'll receive a notice with pickup instructions.</p>
                
                <h3>Lost or Damaged Packages</h3>
                <p>If your package is lost or arrives damaged:</p>
                <ol>
                    <li>Contact us immediately at support@roxasthrift.com</li>
                    <li>Provide your order number and photos (if damaged)</li>
                    <li>We'll investigate with the courier and arrange a replacement or refund</li>
                </ol>
                
                <h3>Wrong Address</h3>
                <p>Please ensure your shipping address is correct before checkout. If you need to change the address after placing an order, contact us within 24 hours. Once shipped, address changes are not possible.</p>
            </div>
            
            <!-- Contact -->
            <div class="info-section">
                <h2>Need Help?</h2>
                <p>If you have questions about shipping or your order, we're here to help!</p>
                <ul>
                    <li><strong>Email:</strong> support@roxasthrift.com</li>
                    <li><strong>Phone:</strong> +63 912 345 6789</li>
                    <li><strong>Hours:</strong> Monday - Saturday, 9:00 AM - 6:00 PM</li>
                </ul>
                <p><a href="contact.php" class="btn btn-primary" style="display: inline-block; margin-top: 1rem;">Contact Us</a></p>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
