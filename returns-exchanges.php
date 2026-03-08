<?php
/**
 * Returns & Exchanges Page
 */

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Returns & Exchanges';
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
        
        .highlight-box {
            background: #f0f7ff;
            border-left: 4px solid #2196F3;
            padding: 1.25rem;
            margin: 1.5rem 0;
            border-radius: 4px;
        }
        
        .warning-box {
            background: #fff3e0;
            border-left: 4px solid #ff9800;
            padding: 1.25rem;
            margin: 1.5rem 0;
            border-radius: 4px;
        }
        
        .step-box {
            background: #f8f8f8;
            border-radius: 8px;
            padding: 1.5rem;
            margin: 1rem 0;
        }
        
        .step-box strong {
            display: block;
            font-size: 1.125rem;
            margin-bottom: 0.5rem;
            color: #1a1a1a;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="container" style="padding-top: 90px; padding-bottom: 3rem;">
        <nav class="breadcrumb">
            <a href="index.php">Home</a>
            <span class="separator">/</span>
            <span class="current">Returns & Exchanges</span>
        </nav>
        
        <div class="info-container">
            <div class="info-header">
                <h1>Returns & Exchanges</h1>
                <p>We want you to love your purchase. If not, we're here to help.</p>
            </div>
            
            <!-- Return Policy -->
            <div class="info-section">
                <h2>Return Policy</h2>
                <p>We accept returns within <strong>7 days</strong> of delivery for items that meet our return criteria.</p>
                
                <h3>Eligible for Return:</h3>
                <ul>
                    <li>Item is unworn, unwashed, and in original condition</li>
                    <li>All original tags are still attached</li>
                    <li>Item is in its original packaging (if applicable)</li>
                    <li>Return request is made within 7 days of delivery</li>
                    <li>Proof of purchase (order number or receipt)</li>
                </ul>
                
                <h3>Not Eligible for Return:</h3>
                <ul>
                    <li>Items marked as "Final Sale"</li>
                    <li>Worn, washed, or altered items</li>
                    <li>Items without original tags</li>
                    <li>Undergarments and swimwear (for hygiene reasons)</li>
                    <li>Items damaged by customer</li>
                    <li>Returns requested after 7 days</li>
                </ul>
                
                <div class="warning-box">
                    <strong>Important:</strong> Due to the unique nature of thrifted items, each piece is one-of-a-kind. 
                    Please review product descriptions and measurements carefully before purchasing.
                </div>
            </div>
            
            <!-- How to Return -->
            <div class="info-section">
                <h2>How to Return an Item</h2>
                
                <div class="step-box">
                    <strong>Step 1: Contact Us</strong>
                    <p>Email us at returns@roxasthrift.com or contact customer service within 7 days of receiving your order. 
                    Include your order number and reason for return.</p>
                </div>
                
                <div class="step-box">
                    <strong>Step 2: Get Return Authorization</strong>
                    <p>We'll review your request and send you a Return Authorization (RA) number and return instructions 
                    within 24-48 hours.</p>
                </div>
                
                <div class="step-box">
                    <strong>Step 3: Pack Your Item</strong>
                    <p>Securely pack the item in its original packaging with all tags attached. Include a copy of your 
                    order confirmation and the RA number.</p>
                </div>
                
                <div class="step-box">
                    <strong>Step 4: Ship the Return</strong>
                    <p>Ship the package to the address provided in your return instructions. Keep your tracking number 
                    for reference.</p>
                </div>
                
                <div class="step-box">
                    <strong>Step 5: Receive Your Refund</strong>
                    <p>Once we receive and inspect your return, we'll process your refund within 5-7 business days. 
                    You'll receive an email confirmation.</p>
                </div>
            </div>
            
            <!-- Exchange Policy -->
            <div class="info-section">
                <h2>Exchange Policy</h2>
                <p>We're happy to exchange items for a different size or color if available.</p>
                
                <h3>Exchange Eligibility:</h3>
                <ul>
                    <li>Same conditions as returns apply</li>
                    <li>Requested item must be in stock</li>
                    <li>Exchange request within 7 days of delivery</li>
                    <li>One exchange per order</li>
                </ul>
                
                <h3>How to Exchange:</h3>
                <ol>
                    <li>Contact us at exchanges@roxasthrift.com with your order number</li>
                    <li>Specify the item you want to exchange and your preferred replacement</li>
                    <li>We'll check availability and provide exchange instructions</li>
                    <li>Ship the original item back to us</li>
                    <li>We'll send your replacement once we receive the return</li>
                </ol>
                
                <div class="highlight-box">
                    <strong>Note:</strong> Since our items are unique thrifted pieces, exact exchanges may not always be 
                    possible. We'll work with you to find the best alternative.
                </div>
            </div>
            
            <!-- Refund Information -->
            <div class="info-section">
                <h2>Refund Information</h2>
                
                <h3>Refund Method:</h3>
                <p>Refunds will be issued to your original payment method:</p>
                <ul>
                    <li><strong>Credit/Debit Card:</strong> 5-10 business days</li>
                    <li><strong>GCash/PayMaya:</strong> 3-5 business days</li>
                    <li><strong>Bank Transfer:</strong> 5-7 business days</li>
                    <li><strong>Cash on Delivery:</strong> Bank transfer or store credit</li>
                </ul>
                
                <h3>Refund Amount:</h3>
                <ul>
                    <li>Full product price will be refunded</li>
                    <li>Original shipping fees are non-refundable</li>
                    <li>Return shipping costs are customer's responsibility</li>
                    <li>If item is defective, we cover return shipping</li>
                </ul>
                
                <h3>Store Credit Option:</h3>
                <p>Choose store credit instead of a refund and receive an additional <strong>10% bonus</strong> to use 
                on your next purchase!</p>
            </div>
            
            <!-- Damaged or Defective Items -->
            <div class="info-section">
                <h2>Damaged or Defective Items</h2>
                <p>We carefully inspect all items before shipping, but if you receive a damaged or defective item:</p>
                
                <ol>
                    <li>Contact us immediately (within 48 hours of delivery)</li>
                    <li>Provide photos of the damage or defect</li>
                    <li>Include your order number</li>
                </ol>
                
                <p>We'll arrange for:</p>
                <ul>
                    <li>Free return shipping</li>
                    <li>Full refund including original shipping</li>
                    <li>Or replacement item (if available)</li>
                </ul>
                
                <div class="warning-box">
                    <strong>Inspection Required:</strong> All returns are inspected upon receipt. Items not meeting 
                    return criteria may be sent back to the customer.
                </div>
            </div>
            
            <!-- Return Shipping -->
            <div class="info-section">
                <h2>Return Shipping</h2>
                
                <h3>Domestic Returns (Philippines):</h3>
                <p>Ship returns to:</p>
                <p style="background: #f8f8f8; padding: 1rem; border-radius: 8px; font-family: monospace;">
                    Roxas Curated Thrift - Returns Department<br>
                    123 Thrift Street, Barangay Fashion<br>
                    Roxas City, Capiz 5800<br>
                    Philippines
                </p>
                
                <p>Recommended couriers: J&T Express, LBC, or Philippine Post</p>
                
                <h3>International Returns:</h3>
                <p>Contact us for international return instructions. Additional customs fees may apply.</p>
                
                <h3>Return Shipping Costs:</h3>
                <ul>
                    <li><strong>Standard Returns:</strong> Customer pays return shipping</li>
                    <li><strong>Defective Items:</strong> We provide prepaid return label</li>
                    <li><strong>Wrong Item Sent:</strong> We cover all return costs</li>
                </ul>
            </div>
            
            <!-- Contact -->
            <div class="info-section">
                <h2>Questions About Returns?</h2>
                <p>Our customer service team is here to help!</p>
                <ul>
                    <li><strong>Email:</strong> returns@roxasthrift.com</li>
                    <li><strong>Phone:</strong> +63 912 345 6789</li>
                    <li><strong>Hours:</strong> Monday - Saturday, 9:00 AM - 6:00 PM</li>
                </ul>
                <p><a href="contact.php" class="btn btn-primary" style="display: inline-block; margin-top: 1rem;">Contact Customer Service</a></p>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
