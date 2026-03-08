<?php
/**
 * Privacy Policy Page
 */

require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Privacy Policy';
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
        .privacy-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        
        .privacy-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .privacy-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .privacy-header p {
            font-size: 1rem;
            color: #666;
        }
        
        .privacy-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .privacy-section h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #1a1a1a;
        }
        
        .privacy-section h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 1.5rem 0 1rem;
        }
        
        .privacy-section p {
            line-height: 1.8;
            color: #555;
            margin-bottom: 1rem;
        }
        
        .privacy-section ul {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .privacy-section li {
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
            <span class="current">Privacy Policy</span>
        </nav>
        
        <div class="privacy-container">
            <div class="privacy-header">
                <h1>Privacy Policy</h1>
                <p>Last updated: February 27, 2026</p>
            </div>
            
            <div class="privacy-section">
                <h2>1. Introduction</h2>
                <p>Roxas Curated Thrift ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and make purchases.</p>
            </div>
            
            <div class="privacy-section">
                <h2>2. Information We Collect</h2>
                
                <h3>Personal Information</h3>
                <p>We collect information that you provide directly to us, including:</p>
                <ul>
                    <li>Name and contact information (email, phone number, address)</li>
                    <li>Account credentials (username, password)</li>
                    <li>Payment information (processed securely by payment providers)</li>
                    <li>Shipping and billing addresses</li>
                    <li>Order history and preferences</li>
                    <li>Communications with customer service</li>
                </ul>
                
                <h3>Automatically Collected Information</h3>
                <p>When you visit our website, we automatically collect:</p>
                <ul>
                    <li>IP address and browser type</li>
                    <li>Device information</li>
                    <li>Pages visited and time spent</li>
                    <li>Referring website</li>
                    <li>Cookies and similar technologies</li>
                </ul>
            </div>
            
            <div class="privacy-section">
                <h2>3. How We Use Your Information</h2>
                <p>We use the collected information to:</p>
                <ul>
                    <li>Process and fulfill your orders</li>
                    <li>Communicate about your orders and account</li>
                    <li>Provide customer support</li>
                    <li>Send marketing communications (with your consent)</li>
                    <li>Improve our website and services</li>
                    <li>Prevent fraud and enhance security</li>
                    <li>Comply with legal obligations</li>
                    <li>Analyze website usage and trends</li>
                </ul>
            </div>
            
            <div class="privacy-section">
                <h2>4. Information Sharing</h2>
                <p>We do not sell your personal information. We may share your information with:</p>
                
                <h3>Service Providers</h3>
                <ul>
                    <li>Payment processors</li>
                    <li>Shipping and delivery services</li>
                    <li>Email service providers</li>
                    <li>Website hosting and maintenance</li>
                </ul>
                
                <h3>Legal Requirements</h3>
                <p>We may disclose your information if required by law or to:</p>
                <ul>
                    <li>Comply with legal processes</li>
                    <li>Protect our rights and property</li>
                    <li>Prevent fraud or illegal activities</li>
                    <li>Protect the safety of our users</li>
                </ul>
            </div>
            
            <div class="privacy-section">
                <h2>5. Cookies and Tracking</h2>
                <p>We use cookies and similar technologies to:</p>
                <ul>
                    <li>Remember your preferences</li>
                    <li>Keep you logged in</li>
                    <li>Analyze website traffic</li>
                    <li>Personalize your experience</li>
                </ul>
                <p>You can control cookies through your browser settings. Note that disabling cookies may affect website functionality.</p>
            </div>
            
            <div class="privacy-section">
                <h2>6. Data Security</h2>
                <p>We implement appropriate security measures to protect your information, including:</p>
                <ul>
                    <li>Secure Socket Layer (SSL) encryption</li>
                    <li>Secure password storage</li>
                    <li>Regular security audits</li>
                    <li>Limited employee access to personal data</li>
                </ul>
                <p>However, no method of transmission over the internet is 100% secure. We cannot guarantee absolute security.</p>
            </div>
            
            <div class="privacy-section">
                <h2>7. Your Rights</h2>
                <p>You have the right to:</p>
                <ul>
                    <li><strong>Access:</strong> Request a copy of your personal information</li>
                    <li><strong>Correction:</strong> Update or correct your information</li>
                    <li><strong>Deletion:</strong> Request deletion of your account and data</li>
                    <li><strong>Opt-out:</strong> Unsubscribe from marketing emails</li>
                    <li><strong>Data Portability:</strong> Request your data in a portable format</li>
                </ul>
                <p>To exercise these rights, contact us at privacy@roxasthrift.com</p>
            </div>
            
            <div class="privacy-section">
                <h2>8. Children's Privacy</h2>
                <p>Our website is not intended for children under 13 years of age. We do not knowingly collect personal information from children. If you believe we have collected information from a child, please contact us immediately.</p>
            </div>
            
            <div class="privacy-section">
                <h2>9. Third-Party Links</h2>
                <p>Our website may contain links to third-party websites. We are not responsible for the privacy practices of these sites. We encourage you to read their privacy policies.</p>
            </div>
            
            <div class="privacy-section">
                <h2>10. International Users</h2>
                <p>If you are accessing our website from outside the Philippines, please note that your information may be transferred to and processed in the Philippines. By using our website, you consent to this transfer.</p>
            </div>
            
            <div class="privacy-section">
                <h2>11. Changes to This Policy</h2>
                <p>We may update this Privacy Policy from time to time. We will notify you of significant changes by posting the new policy on this page and updating the "Last updated" date.</p>
            </div>
            
            <div class="privacy-section">
                <h2>12. Contact Us</h2>
                <p>If you have questions about this Privacy Policy or our data practices, please contact us:</p>
                <ul>
                    <li><strong>Email:</strong> privacy@roxasthrift.com</li>
                    <li><strong>Phone:</strong> +63 912 345 6789</li>
                    <li><strong>Address:</strong> 123 Thrift Street, Roxas City, Capiz 5800, Philippines</li>
                </ul>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
