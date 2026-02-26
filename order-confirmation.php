<?php
/**
 * Order Confirmation Page
 * 
 * Shows order confirmation after successful checkout.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/includes/functions.php';

// Get order number from URL
$orderNumber = $_GET['order'] ?? '';

if (empty($orderNumber)) {
    header("Location: index.php");
    exit();
}

// Fetch order details
$order = fetchOne("SELECT * FROM orders WHERE order_number = ?", [$orderNumber]);

if (!$order) {
    header("Location: index.php");
    exit();
}

// Fetch order items
$orderItems = fetchAll("SELECT * FROM order_items WHERE order_id = ?", [$order['order_id']]);

$pageTitle = 'Order Confirmation';
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
        .confirmation-page {
            min-height: calc(100vh - 70px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: linear-gradient(135deg, #f8f8f8 0%, #fff 100%);
        }
        
        .confirmation-card {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }
        
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 3rem;
        }
        
        .confirmation-card h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .confirmation-card .subtitle {
            color: #666;
            margin-bottom: 2rem;
        }
        
        .order-details {
            background: #f8f8f8;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        
        .order-details h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        .detail-row .label {
            color: #666;
        }
        
        .detail-row .value {
            font-weight: 500;
        }
        
        .detail-row.total {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid #e0e0e0;
            font-size: 1rem;
        }
        
        .detail-row.total .value {
            font-weight: 700;
            font-size: 1.25rem;
        }
        
        .next-steps {
            margin-bottom: 2rem;
        }
        
        .next-steps h3 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .next-steps ul {
            list-style: none;
            text-align: left;
            display: inline-block;
        }
        
        .next-steps li {
            margin-bottom: 0.5rem;
            color: #666;
        }
        
        .next-steps li::before {
            content: '✓';
            color: #22c55e;
            margin-right: 0.5rem;
            font-weight: 700;
        }
        
        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        @media (max-width: 480px) {
            .confirmation-card {
                padding: 2rem 1.5rem;
            }
            
            .confirmation-card h1 {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <?php include __DIR__ . '/includes/header.php'; ?>
    
    <main class="confirmation-page">
        <div class="confirmation-card">
            <div class="success-icon">✓</div>
            <h1>Order Placed Successfully!</h1>
            <p class="subtitle">Thank you for your purchase. We've received your order.</p>
            
            <div class="order-details">
                <h3>Order Summary</h3>
                <div class="detail-row">
                    <span class="label">Order Number</span>
                    <span class="value"><?php echo $order['order_number']; ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Order Date</span>
                    <span class="value"><?php echo date('F j, Y', strtotime($order['created_at'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Items</span>
                    <span class="value"><?php echo count($orderItems); ?> item<?php echo count($orderItems) > 1 ? 's' : ''; ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Payment Method</span>
                    <span class="value"><?php echo ucfirst(str_replace('_', ' ', $order['payment_method'])); ?></span>
                </div>
                <div class="detail-row">
                    <span class="label">Shipping To</span>
                    <span class="value"><?php echo cleanOutput($order['country']); ?></span>
                </div>
                <div class="detail-row total">
                    <span class="label">Total Amount</span>
                    <span class="value"><?php echo formatPrice($order['total_amount']); ?></span>
                </div>
            </div>
            
            <div class="next-steps">
                <h3>What's Next?</h3>
                <ul>
                    <li>You'll receive an order confirmation email</li>
                    <li>We'll notify you when your order ships</li>
                    <li>Track your order in your account</li>
                </ul>
            </div>
            
            <div class="btn-group">
                <a href="orders.php" class="btn btn-primary">View My Orders</a>
                <a href="products.php" class="btn btn-outline">Continue Shopping</a>
            </div>
        </div>
    </main>
    
    <?php include __DIR__ . '/includes/footer.php'; ?>
</body>
</html>
