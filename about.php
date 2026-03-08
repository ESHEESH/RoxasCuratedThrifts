<?php
/**
 * About Us Page
 */

require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'About Us';
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
        .about-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 2rem 0;
        }
        
        .about-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .about-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .about-header p {
            font-size: 1.25rem;
            color: #666;
            line-height: 1.6;
        }
        
        .about-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .about-section h2 {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 1.5rem;
        }
        
        .about-section p {
            line-height: 1.8;
            color: #555;
            margin-bottom: 1rem;
        }
        
        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .value-card {
            text-align: center;
            padding: 1.5rem;
            background: #f8f8f8;
            border-radius: 8px;
        }
        
        .value-card svg {
            width: 48px;
            height: 48px;
            margin-bottom: 1rem;
        }
        
        .value-card h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .value-card p {
            font-size: 0.875rem;
            color: #666;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="container" style="padding-top: 90px; padding-bottom: 3rem;">
        <nav class="breadcrumb">
            <a href="index.php">Home</a>
            <span class="separator">/</span>
            <span class="current">About Us</span>
        </nav>
        
        <div class="about-container">
            <div class="about-header">
                <h1>About Roxas Curated Thrift</h1>
                <p>Sustainable fashion, unique finds, and a passion for pre-loved style</p>
            </div>
            
            <div class="about-section">
                <h2>Our Story</h2>
                <p>Founded in Roxas City, Capiz, Roxas Curated Thrift was born from a love of vintage fashion and a commitment to sustainable living. What started as a small collection of carefully selected pre-loved items has grown into a thriving online community of fashion enthusiasts who believe in the beauty of second-hand style.</p>
                
                <p>We believe that every piece of clothing has a story to tell. Our mission is to give these stories a new chapter by connecting quality pre-loved fashion with people who appreciate unique, sustainable style.</p>
            </div>
            
            <div class="about-section">
                <h2>What We Do</h2>
                <p>We curate high-quality pre-loved clothing, shoes, bags, and accessories from various sources. Each item is carefully inspected, cleaned, and graded for condition before being listed in our store. We're not just selling clothes – we're promoting a more sustainable way to enjoy fashion.</p>
                
                <p>Our collection features everything from vintage gems to modern brands, all at affordable prices. Whether you're looking for a unique statement piece or everyday essentials, we've got something special waiting for you.</p>
            </div>
            
            <div class="about-section">
                <h2>Our Values</h2>
                <div class="values-grid">
                    <div class="value-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                        <h3>Quality First</h3>
                        <p>Every item is carefully inspected to ensure it meets our quality standards</p>
                    </div>
                    
                    <div class="value-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        </svg>
                        <h3>Sustainability</h3>
                        <p>Reducing fashion waste by giving pre-loved items a new life</p>
                    </div>
                    
                    <div class="value-card">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <h3>Community</h3>
                        <p>Building a community of conscious fashion lovers</p>
                    </div>
                </div>
            </div>
            
            <div class="about-section">
                <h2>Why Choose Us?</h2>
                <ul>
                    <li><strong>Curated Selection:</strong> Every item is handpicked for quality and style</li>
                    <li><strong>Transparent Grading:</strong> Clear condition ratings so you know exactly what you're getting</li>
                    <li><strong>Affordable Prices:</strong> High-quality fashion without the high-end price tag</li>
                    <li><strong>Sustainable Choice:</strong> Reduce your environmental impact while looking great</li>
                    <li><strong>Unique Finds:</strong> One-of-a-kind pieces you won't find anywhere else</li>
                    <li><strong>Customer Care:</strong> Dedicated support to ensure your satisfaction</li>
                </ul>
            </div>
            
            <div class="about-section">
                <h2>Join Our Community</h2>
                <p>Follow us on social media to see new arrivals, styling tips, and behind-the-scenes content. Share your Roxas Thrift finds with #RoxasThrift and join our growing community of sustainable fashion lovers!</p>
                
                <p style="text-align: center; margin-top: 2rem;">
                    <a href="shop/products.php" class="btn btn-primary" style="display: inline-block;">Start Shopping</a>
                </p>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
    <script src="<?php echo SITE_URL; ?>/assets/js/main.js"></script>
</body>
</html>
