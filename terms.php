<?php
/**
 * Terms of Service Page
 */

require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Terms of Service';
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
        .terms-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        
        .terms-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .terms-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .terms-header p {
            font-size: 1rem;
            color: #666;
        }
        
        .terms-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .terms-section h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #1a1a1a;
        }
        
        .terms-section h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 1.5rem 0 1rem;
        }
        
        .terms-section p {
            line-height: 1.8;
            color: #555;
            margin-bottom: 1rem;
        }
        
        .terms-section ul, .terms-section ol {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .terms-section li {
            line-height: 1.8;
            color: #555;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="container" style="padding-top: 90px; padding-bottom: 3rem;">
        <nav class="breadcrumb">
            <a href="index.php">Home</a>
            <span class="separator">/</span>
            <span class="current">Terms of Service</span>
        </nav>
        
        <div class="terms-container">
            <div class="terms-header">
                <h1>Terms of Service</h1>
                <p>Last updated: February 27, 2026</p>
            </div>
            
            <div class="terms-section">
                <h2>1. Agreement to Terms</h2>
                <p>By accessing and using Roxas Curated Thrift ("the Website"), you agree to be bound by these Terms of Service and all applicable laws and regulations. If you do not agree with any of these terms, you are prohibited from using this site.</p>
            </div>
            
            <div class="terms-section">
                <h2>2. Use License</h2>
                <p>Permission is granted to temporarily access the materials on Roxas Curated Thrift for personal, non-commercial use only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
                <ul>
                    <li>Modify or copy the materials</li>
                    <li>Use the materials for any commercial purpose</li>
                    <li>Attempt to decompile or reverse engineer any software on the Website</li>
                    <li>Remove any copyright or proprietary notations from the materials</li>
                    <li>Transfer the materials to another person or "mirror" the materials on any other server</li>
                </ul>
            </div>
            
            <div class="terms-section">
                <h2>3. Account Registration</h2>
                <p>To make purchases, you must create an account. You agree to:</p>
                <ul>
                    <li>Provide accurate, current, and complete information</li>
                    <li>Maintain and update your information to keep it accurate</li>
                    <li>Maintain the security of your password</li>
                    <li>Accept responsibility for all activities under your account</li>
                    <li>Notify us immediately of any unauthorized use</li>
                </ul>
            </div>
            
            <div class="terms-section">
                <h2>4. Product Information</h2>
                <p>We strive to provide accurate product descriptions and images. However:</p>
                <ul>
                    <li>All items are pre-owned unless stated otherwise</li>
                    <li>Colors may vary slightly due to screen settings</li>
                    <li>Measurements are approximate</li>
                    <li>Each item is unique and may have minor imperfections</li>
                    <li>We reserve the right to limit quantities</li>
                </ul>
            </div>
            
            <div class="terms-section">
                <h2>5. Pricing and Payment</h2>
                <p>All prices are in Philippine Pesos (₱) unless otherwise stated. We reserve the right to:</p>
                <ul>
                    <li>Change prices at any time without notice</li>
                    <li>Refuse or cancel orders if pricing errors occur</li>
                    <li>Require additional verification for large orders</li>
                </ul>
                <p>Payment must be received before items are shipped. We accept various payment methods as displayed at checkout.</p>
            </div>
            
            <div class="terms-section">
                <h2>6. Shipping and Delivery</h2>
                <p>Shipping times are estimates and not guaranteed. We are not responsible for delays caused by:</p>
                <ul>
                    <li>Courier services</li>
                    <li>Weather conditions</li>
                    <li>Customs processing (international orders)</li>
                    <li>Incorrect shipping addresses provided by customer</li>
                </ul>
            </div>
            
            <div class="terms-section">
                <h2>7. Returns and Refunds</h2>
                <p>Please refer to our <a href="returns-exchanges.php" style="color: #1a1a1a; text-decoration: underline;">Returns & Exchanges</a> page for detailed information. Returns must meet our criteria and be requested within 7 days of delivery.</p>
            </div>
            
            <div class="terms-section">
                <h2>8. Prohibited Uses</h2>
                <p>You may not use the Website:</p>
                <ul>
                    <li>For any unlawful purpose</li>
                    <li>To solicit others to perform unlawful acts</li>
                    <li>To violate any international, federal, or local regulations</li>
                    <li>To infringe upon intellectual property rights</li>
                    <li>To harass, abuse, or harm another person</li>
                    <li>To submit false or misleading information</li>
                    <li>To upload viruses or malicious code</li>
                    <li>To spam or send unsolicited communications</li>
                </ul>
            </div>
            
            <div class="terms-section">
                <h2>9. Intellectual Property</h2>
                <p>All content on this Website, including text, graphics, logos, images, and software, is the property of Roxas Curated Thrift and protected by copyright laws. Unauthorized use is prohibited.</p>
            </div>
            
            <div class="terms-section">
                <h2>10. Limitation of Liability</h2>
                <p>Roxas Curated Thrift shall not be liable for any damages arising from the use or inability to use our Website or products, including but not limited to direct, indirect, incidental, or consequential damages.</p>
            </div>
            
            <div class="terms-section">
                <h2>11. Modifications</h2>
                <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting. Your continued use of the Website constitutes acceptance of the modified terms.</p>
            </div>
            
            <div class="terms-section">
                <h2>12. Governing Law</h2>
                <p>These terms shall be governed by and construed in accordance with the laws of the Philippines, without regard to its conflict of law provisions.</p>
            </div>
            
            <div class="terms-section">
                <h2>13. Contact Information</h2>
                <p>For questions about these Terms of Service, please contact us:</p>
                <ul>
                    <li>Email: legal@roxasthrift.com</li>
                    <li>Phone: +63 912 345 6789</li>
                    <li>Address: 123 Thrift Street, Roxas City, Capiz 5800, Philippines</li>
                </ul>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
