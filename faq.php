<?php
/**
 * FAQ Page
 * Frequently Asked Questions
 */

require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'FAQ';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="assets/css/responsive.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .faq-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        
        .faq-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .faq-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .faq-header p {
            font-size: 1.125rem;
            color: #666;
        }
        
        .faq-category {
            margin-bottom: 3rem;
        }
        
        .faq-category h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid #1a1a1a;
        }
        
        .faq-item {
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            margin-bottom: 1rem;
            overflow: hidden;
        }
        
        .faq-question {
            width: 100%;
            text-align: left;
            padding: 1.25rem 1.5rem;
            font-size: 1rem;
            font-weight: 600;
            background: white;
            border: none;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.2s;
        }
        
        .faq-question:hover {
            background: #f8f8f8;
        }
        
        .faq-question svg {
            width: 20px;
            height: 20px;
            transition: transform 0.3s;
        }
        
        .faq-item.active .faq-question svg {
            transform: rotate(180deg);
        }
        
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
        }
        
        .faq-answer-content {
            padding: 0 1.5rem 1.25rem;
            color: #555;
            line-height: 1.6;
        }
        
        .faq-item.active .faq-answer {
            max-height: 500px;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="container" style="padding-top: 90px; padding-bottom: 3rem;">
        <nav class="breadcrumb">
            <a href="index.php">Home</a>
            <span class="separator">/</span>
            <span class="current">FAQ</span>
        </nav>
        
        <div class="faq-container">
            <div class="faq-header">
                <h1>Frequently Asked Questions</h1>
                <p>Find answers to common questions about our thrift store</p>
            </div>
            
            <!-- Shopping & Orders -->
            <div class="faq-category">
                <h2>Shopping & Orders</h2>
                
                <div class="faq-item">
                    <button class="faq-question">
                        <span>How do I place an order?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"></path>
                        </svg>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            Browse our products, select your desired item, choose size and color, then click "Add to Cart". 
                            Once you're ready, go to your cart and proceed to checkout. You'll need to create an account or 
                            log in to complete your purchase.
                        </div>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question">
                        <span>Can I modify or cancel my order?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"></path>
                        </svg>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            You can cancel your order within 24 hours of placing it by contacting us immediately. 
                            Once the order has been processed or shipped, modifications are not possible. Please contact 
                            our customer service for assistance.
                        </div>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question">
                        <span>What payment methods do you accept?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"></path>
                        </svg>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            We accept various payment methods including credit/debit cards (Visa, Mastercard), 
                            GCash, PayMaya, bank transfers, and cash on delivery (COD) for select areas.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Shipping & Delivery -->
            <div class="faq-category">
                <h2>Shipping & Delivery</h2>
                
                <div class="faq-item">
                    <button class="faq-question">
                        <span>How long does shipping take?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"></path>
                        </svg>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            Shipping within Metro Manila takes 2-3 business days. Provincial deliveries take 5-7 business days. 
                            International shipping takes 10-15 business days depending on the destination country.
                        </div>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question">
                        <span>How much is the shipping fee?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"></path>
                        </svg>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            Shipping fees vary by location: Metro Manila ₱80-120, Luzon ₱120-150, Visayas/Mindanao ₱150-200. 
                            Free shipping on orders over ₱2,000 within Metro Manila.
                        </div>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question">
                        <span>Can I track my order?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"></path>
                        </svg>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            Yes! Once your order ships, you'll receive a tracking number via email. You can also track 
                            your order by logging into your account and viewing "My Orders".
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Products & Quality -->
            <div class="faq-category">
                <h2>Products & Quality</h2>
                
                <div class="faq-item">
                    <button class="faq-question">
                        <span>Are all items pre-owned?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"></path>
                        </svg>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            Yes, we specialize in curated pre-loved fashion. Each item is carefully inspected and 
                            graded by condition (New, Like New, Good, Fair). We ensure all items are clean and in 
                            wearable condition.
                        </div>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question">
                        <span>What do the condition ratings mean?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"></path>
                        </svg>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            <strong>New:</strong> Brand new with tags<br>
                            <strong>Like New:</strong> Worn once or twice, no visible wear<br>
                            <strong>Good:</strong> Gently used, minor signs of wear<br>
                            <strong>Fair:</strong> Used with visible wear but still functional
                        </div>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question">
                        <span>Are items washed before shipping?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"></path>
                        </svg>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            Yes, all items are professionally cleaned and sanitized before being listed. However, 
                            we recommend washing items again before wearing as per your preference.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Returns & Exchanges -->
            <div class="faq-category">
                <h2>Returns & Exchanges</h2>
                
                <div class="faq-item">
                    <button class="faq-question">
                        <span>What is your return policy?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"></path>
                        </svg>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            We accept returns within 7 days of delivery. Items must be unworn, unwashed, and in 
                            original condition with tags attached. Return shipping is at the customer's expense 
                            unless the item is defective.
                        </div>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question">
                        <span>Can I exchange an item for a different size?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"></path>
                        </svg>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            Yes, exchanges are available within 7 days if the item is in stock. Please contact us 
                            to arrange an exchange. Note that each item is unique, so availability may vary.
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account & Support -->
            <div class="faq-category">
                <h2>Account & Support</h2>
                
                <div class="faq-item">
                    <button class="faq-question">
                        <span>Do I need an account to shop?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"></path>
                        </svg>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            Yes, you need to create an account to place orders. This allows you to track orders, 
                            save items to your wishlist, and manage your profile. Registration is quick and free!
                        </div>
                    </div>
                </div>
                
                <div class="faq-item">
                    <button class="faq-question">
                        <span>How do I contact customer service?</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M6 9l6 6 6-6"></path>
                        </svg>
                    </button>
                    <div class="faq-answer">
                        <div class="faq-answer-content">
                            You can reach us through our <a href="contact.php" style="color: #1a1a1a; text-decoration: underline;">Contact Us</a> 
                            page, email us at support@roxasthrift.com, or message us on our social media channels. 
                            We respond within 24 hours.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    
    <script>
        // FAQ Accordion
        document.querySelectorAll('.faq-question').forEach(button => {
            button.addEventListener('click', function() {
                const item = this.parentElement;
                const isActive = item.classList.contains('active');
                
                // Close all items
                document.querySelectorAll('.faq-item').forEach(i => i.classList.remove('active'));
                
                // Open clicked item if it wasn't active
                if (!isActive) {
                    item.classList.add('active');
                }
            });
        });
    </script>
    <script src="assets/js/main.js"></script>
</body>
</html>
