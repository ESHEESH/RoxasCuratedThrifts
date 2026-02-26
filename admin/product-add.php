<?php
/**
 * Add Product Page
 * 
 * Allows admins to add new products with variants.
 * 
 * @author Thrift Store Team
 * @version 1.0
 */

require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$adminId = getCurrentAdminId();
$pageTitle = 'Add Product';

$errors = [];
$success = false;

// Get categories
$categories = fetchAll("SELECT * FROM categories WHERE is_active = TRUE ORDER BY name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        $errors[] = "Invalid request. Please try again.";
    } else {
        $name = sanitizeInput($_POST['name'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $description = sanitizeInput($_POST['description'] ?? '');
        $basePrice = (float)($_POST['base_price'] ?? 0);
        $originalPrice = !empty($_POST['original_price']) ? (float)$_POST['original_price'] : null;
        $condition = $_POST['condition_status'] ?? 'good';
        $brand = sanitizeInput($_POST['brand'] ?? '');
        $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
        
        // Validation
        if (empty($name)) {
            $errors[] = "Product name is required.";
        }
        
        if ($categoryId <= 0) {
            $errors[] = "Please select a category.";
        }
        
        if ($basePrice <= 0) {
            $errors[] = "Base price must be greater than 0.";
        }
        
        // Generate slug
        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $slug = trim($slug, '-');
        
        // Check if slug exists
        $existing = fetchOne("SELECT product_id FROM products WHERE slug = ?", [$slug]);
        if ($existing) {
            $slug .= '-' . time();
        }
        
        if (empty($errors)) {
            try {
                beginTransaction();
                
                // Insert product
                $sql = "INSERT INTO products (category_id, name, slug, description, base_price, original_price, condition_status, brand, is_featured, sku) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                
                $sku = 'SKU-' . strtoupper(substr(uniqid(), -6));
                
                executeQuery($sql, [
                    $categoryId,
                    $name,
                    $slug,
                    $description,
                    $basePrice,
                    $originalPrice,
                    $condition,
                    $brand,
                    $isFeatured,
                    $sku
                ]);
                
                $productId = getLastInsertId();
                
                // Handle variants
                $sizes = $_POST['sizes'] ?? [];
                $colors = $_POST['colors'] ?? [];
                $stocks = $_POST['stocks'] ?? [];
                $priceAdjustments = $_POST['price_adjustments'] ?? [];
                
                if (!empty($sizes) && !empty($colors)) {
                    foreach ($sizes as $i => $size) {
                        if (empty($size)) continue;
                        
                        foreach ($colors as $j => $color) {
                            if (empty($color)) continue;
                            
                            $stock = (int)($stocks[$i][$j] ?? 0);
                            $priceAdj = (float)($priceAdjustments[$i][$j] ?? 0);
                            
                            $variantSku = $sku . '-' . $size . '-' . strtoupper(substr($color, 0, 3));
                            
                            executeQuery("INSERT INTO product_variants (product_id, size, color, price_adjustment, stock_quantity, sku) 
                                         VALUES (?, ?, ?, ?, ?, ?)", [
                                $productId,
                            $size,
                            $color,
                            $priceAdj,
                            $stock,
                            $variantSku
                            ]);
                        }
                    }
                }
                
                // Handle image upload
                if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = validateFileUpload($_FILES['product_image']);
                    
                    if ($uploadResult['valid']) {
                        move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadResult['path']);
                        
                        $imagePath = 'uploads/' . $uploadResult['filename'];
                        executeQuery("INSERT INTO product_images (product_id, image_path, is_primary, display_order) 
                                     VALUES (?, ?, 1, 0)", [$productId, $imagePath]);
                    }
                }
                
                commitTransaction();
                
                logActivity('product_created', 'product', $productId);
                
                $success = true;
                setFlashMessage('success', 'Product created successfully!');
                header("Location: products.php");
                exit();
                
            } catch (Exception $e) {
                rollbackTransaction();
                error_log("Product creation error: " . $e->getMessage());
                $errors[] = "An error occurred. Please try again.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .form-card {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            max-width: 900px;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1.5rem;
        }
        
        .form-group.full-width {
            grid-column: 1 / -1;
        }
        
        .form-group label {
            display: block;
            font-weight: 500;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 0.875rem;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #1a1a1a;
        }
        
        .variants-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #f0f0f0;
        }
        
        .variants-section h3 {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        .variant-row {
            display: grid;
            grid-template-columns: 1fr 1fr 100px 100px 40px;
            gap: 0.75rem;
            margin-bottom: 0.75rem;
            align-items: center;
        }
        
        .variant-row input {
            padding: 0.5rem;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            font-size: 0.875rem;
        }
        
        .remove-variant {
            background: #fee2e2;
            color: #dc2626;
            border: none;
            border-radius: 6px;
            padding: 0.5rem;
            cursor: pointer;
            font-size: 1rem;
        }
        
        .add-variant-btn {
            background: #f5f5f5;
            border: 1px dashed #ccc;
            border-radius: 8px;
            padding: 0.75rem;
            width: 100%;
            cursor: pointer;
            margin-top: 0.5rem;
        }
        
        .add-variant-btn:hover {
            background: #e5e5e5;
        }
        
        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        
        .checkbox-label input {
            width: auto;
        }
        
        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e0e0e0;
        }
        
        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .variant-row {
                grid-template-columns: 1fr 1fr;
            }
        }
    </style>
</head>
<body class="admin-page">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <?php include __DIR__ . '/includes/header.php'; ?>
        
        <div class="admin-content">
            <div class="page-header">
                <h1><?php echo $pageTitle; ?></h1>
                <a href="products.php" class="btn btn-outline btn-sm">← Back to Products</a>
            </div>
            
            <div class="form-card">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                        <?php foreach ($errors as $error): ?>
                            <p><?php echo cleanOutput($error); ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="product-add.php" enctype="multipart/form-data">
                    <?php echo csrfField(); ?>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" required placeholder="Enter product name">
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Category *</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['category_id']; ?>"><?php echo cleanOutput($cat['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="base_price">Base Price (₱) *</label>
                            <input type="number" id="base_price" name="base_price" step="0.01" min="0" required placeholder="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label for="original_price">Original Price (₱) <small>(for discount display)</small></label>
                            <input type="number" id="original_price" name="original_price" step="0.01" min="0" placeholder="0.00">
                        </div>
                        
                        <div class="form-group">
                            <label for="condition_status">Condition</label>
                            <select id="condition_status" name="condition_status">
                                <option value="new">New</option>
                                <option value="like_new">Like New</option>
                                <option value="good" selected>Good</option>
                                <option value="fair">Fair</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="brand">Brand</label>
                            <input type="text" id="brand" name="brand" placeholder="Brand name (optional)">
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4" placeholder="Product description..."></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label for="product_image">Product Image</label>
                            <input type="file" id="product_image" name="product_image" accept="image/*">
                            <small style="color: #666;">Max 5MB. JPG, PNG, GIF, WebP accepted.</small>
                        </div>
                        
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_featured" value="1">
                                <span>Feature on homepage</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Variants Section -->
                    <div class="variants-section">
                        <h3>Product Variants (Size & Color)</h3>
                        <p style="color: #666; margin-bottom: 1rem; font-size: 0.875rem;">Add size and color combinations with stock quantities.</p>
                        
                        <div id="variantsContainer">
                            <div class="variant-row">
                                <input type="text" name="sizes[]" placeholder="Size (e.g., S, M, L)" required>
                                <input type="text" name="colors[]" placeholder="Color (e.g., Black)" required>
                                <input type="number" name="stocks[][]" placeholder="Stock" min="0" value="0">
                                <input type="number" name="price_adjustments[][]" placeholder="Price +/-" step="0.01" value="0">
                                <button type="button" class="remove-variant" onclick="this.parentElement.remove()">×</button>
                            </div>
                        </div>
                        
                        <button type="button" class="add-variant-btn" onclick="addVariant()">+ Add Another Variant</button>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Create Product</button>
                        <a href="products.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>
    
    <script src="../assets/js/admin.js"></script>
    <script>
        function addVariant() {
            const container = document.getElementById('variantsContainer');
            const row = document.createElement('div');
            row.className = 'variant-row';
            row.innerHTML = `
                <input type="text" name="sizes[]" placeholder="Size (e.g., S, M, L)" required>
                <input type="text" name="colors[]" placeholder="Color (e.g., Black)" required>
                <input type="number" name="stocks[][]" placeholder="Stock" min="0" value="0">
                <input type="number" name="price_adjustments[][]" placeholder="Price +/-" step="0.01" value="0">
                <button type="button" class="remove-variant" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(row);
        }
    </script>
</body>
</html>
