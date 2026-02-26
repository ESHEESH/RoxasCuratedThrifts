<?php
/**
 * Admin Product Edit
 * 
 * Edit existing product with variant management.
 * 
 */

require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$adminId = getCurrentAdminId();
$productId = (int)($_GET['id'] ?? 0);

// Get product
$product = fetchOne("SELECT p.*, c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.category_id 
                     WHERE p.product_id = ?", [$productId]);

if (!$product) {
    setFlashMessage('error', 'Product not found');
    header("Location: products.php");
    exit();
}

// Get product variants
$variants = fetchAll("SELECT * FROM product_variants WHERE product_id = ? ORDER BY size, color", [$productId]);

// Get product images
$images = fetchAll("SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC", [$productId]);

// Get categories
$categories = fetchAll("SELECT * FROM categories WHERE is_active = TRUE ORDER BY name");

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlashMessage('error', 'Invalid request');
        header("Location: products.php");
        exit();
    }
    
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $basePrice = (float)($_POST['base_price'] ?? 0);
    $condition = $_POST['condition_status'] ?? 'good';
    $brand = trim($_POST['brand'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $isFeatured = isset($_POST['is_featured']) ? 1 : 0;
    $isActive = isset($_POST['is_active']) ? 1 : 0;
    
    // Validation
    $errors = [];
    if (empty($name)) $errors[] = 'Product name is required';
    if ($categoryId === 0) $errors[] = 'Category is required';
    if ($basePrice <= 0) $errors[] = 'Valid base price is required';
    
    if (empty($errors)) {
        // Store old values for logging
        $oldValues = [
            'name' => $product['name'],
            'category_id' => $product['category_id'],
            'base_price' => $product['base_price'],
            'condition_status' => $product['condition_status'],
            'brand' => $product['brand'],
            'description' => $product['description'],
            'is_featured' => $product['is_featured'],
            'is_active' => $product['is_active']
        ];
        
        // Update product
        executeQuery(
            "UPDATE products SET 
                name = ?, category_id = ?, base_price = ?, condition_status = ?, 
                brand = ?, description = ?, is_featured = ?, is_active = ? 
             WHERE product_id = ?",
            [$name, $categoryId, $basePrice, $condition, $brand, $description, $isFeatured, $isActive, $productId]
        );
        
        // Update variants
        if (isset($_POST['variant_id'])) {
            foreach ($_POST['variant_id'] as $index => $variantId) {
                $size = $_POST['variant_size'][$index] ?? '';
                $color = $_POST['variant_color'][$index] ?? '';
                $stock = (int)($_POST['variant_stock'][$index] ?? 0);
                $priceAdj = (float)($_POST['variant_price_adj'][$index] ?? 0);
                
                if ($variantId === 'new') {
                    // Add new variant
                    executeQuery(
                        "INSERT INTO product_variants (product_id, size, color, stock_quantity, price_adjustment) 
                         VALUES (?, ?, ?, ?, ?)",
                        [$productId, $size, $color, $stock, $priceAdj]
                    );
                } else {
                    // Update existing variant
                    executeQuery(
                        "UPDATE product_variants SET size = ?, color = ?, stock_quantity = ?, price_adjustment = ? 
                         WHERE variant_id = ? AND product_id = ?",
                        [$size, $color, $stock, $priceAdj, $variantId, $productId]
                    );
                }
            }
        }
        
        // Handle new image uploads
        if (!empty($_FILES['new_images']['name'][0])) {
            $uploadDir = __DIR__ . '/../assets/images/products/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            foreach ($_FILES['new_images']['tmp_name'] as $index => $tmpName) {
                if ($_FILES['new_images']['error'][$index] === UPLOAD_ERR_OK) {
                    $filename = uniqid() . '_' . basename($_FILES['new_images']['name'][$index]);
                    $filepath = $uploadDir . $filename;
                    
                    if (move_uploaded_file($tmpName, $filepath)) {
                        $isPrimary = empty($images) && $index === 0 ? 1 : 0;
                        executeQuery(
                            "INSERT INTO product_images (product_id, image_path, is_primary) VALUES (?, ?, ?)",
                            [$productId, $filename, $isPrimary]
                        );
                    }
                }
            }
        }
        
        // Log the action
        logActivity('product_updated', 'product', $productId);
        
        setFlashMessage('success', 'Product updated successfully');
        header("Location: products.php");
        exit();
    }
}

// Handle image deletion
if (isset($_GET['delete_image'])) {
    $imageId = (int)$_GET['delete_image'];
    $image = fetchOne("SELECT * FROM product_images WHERE image_id = ? AND product_id = ?", [$imageId, $productId]);
    
    if ($image) {
        $filepath = __DIR__ . '/../assets/images/products/' . $image['image_path'];
        if (file_exists($filepath)) unlink($filepath);
        
        executeQuery("DELETE FROM product_images WHERE image_id = ?", [$imageId]);
        logActivity('product_image_deleted', 'product_image', $imageId);
        
        setFlashMessage('success', 'Image deleted');
        header("Location: product-edit.php?id=" . $productId);
        exit();
    }
}

// Handle variant deletion
if (isset($_GET['delete_variant'])) {
    $variantId = (int)$_GET['delete_variant'];
    executeQuery("DELETE FROM product_variants WHERE variant_id = ? AND product_id = ?", [$variantId, $productId]);
    logActivity('product_variant_deleted', 'product_variant', $variantId);
    
    setFlashMessage('success', 'Variant deleted');
    header("Location: product-edit.php?id=" . $productId);
    exit();
}

// Generate CSRF token
$csrfToken = generateCsrfToken();
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="admin-page">
    <?php include __DIR__ . '/includes/sidebar.php'; ?>
    
    <main class="admin-main">
        <?php include __DIR__ . '/includes/header.php'; ?>
        
        <div class="admin-content">
            <?php if ($flash): ?>
                <div class="alert alert-<?php echo $flash['type']; ?>" id="flashMessage">
                    <?php echo cleanOutput($flash['message']); ?>
                    <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>
            
            <div class="page-header">
                <h1>Edit Product</h1>
                <a href="products.php" class="btn btn-outline">Back to Products</a>
            </div>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-error">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo cleanOutput($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data" class="product-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrfToken; ?>">
                
                <div class="form-grid">
                    <!-- Basic Info -->
                    <div class="form-section">
                        <h3>Basic Information</h3>
                        
                        <div class="form-group">
                            <label for="name">Product Name *</label>
                            <input type="text" id="name" name="name" 
                                   value="<?php echo cleanOutput($product['name']); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="category_id">Category *</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">Select Category</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>" 
                                        <?php echo $product['category_id'] == $category['category_id'] ? 'selected' : ''; ?>>
                                        <?php echo cleanOutput($category['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="base_price">Base Price (₱) *</label>
                            <input type="number" id="base_price" name="base_price" step="0.01" min="0"
                                   value="<?php echo $product['base_price']; ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="condition_status">Condition</label>
                            <select id="condition_status" name="condition_status">
                                <option value="excellent" <?php echo $product['condition_status'] === 'excellent' ? 'selected' : ''; ?>>Excellent</option>
                                <option value="good" <?php echo $product['condition_status'] === 'good' ? 'selected' : ''; ?>>Good</option>
                                <option value="fair" <?php echo $product['condition_status'] === 'fair' ? 'selected' : ''; ?>>Fair</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="brand">Brand</label>
                            <input type="text" id="brand" name="brand" 
                                   value="<?php echo cleanOutput($product['brand']); ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4"><?php echo cleanOutput($product['description']); ?></textarea>
                        </div>
                        
                        <div class="form-row">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_featured" <?php echo $product['is_featured'] ? 'checked' : ''; ?>>
                                Featured Product
                            </label>
                        </div>
                        
                        <div class="form-row">
                            <label class="checkbox-label">
                                <input type="checkbox" name="is_active" <?php echo $product['is_active'] ? 'checked' : ''; ?>>
                                Active
                            </label>
                        </div>
                    </div>
                    
                    <!-- Variants -->
                    <div class="form-section">
                        <h3>Variants</h3>
                        <div id="variantsContainer">
                            <?php foreach ($variants as $index => $variant): ?>
                                <div class="variant-row">
                                    <input type="hidden" name="variant_id[]" value="<?php echo $variant['variant_id']; ?>">
                                    <input type="text" name="variant_size[]" placeholder="Size" 
                                           value="<?php echo cleanOutput($variant['size']); ?>" class="variant-input">
                                    <input type="text" name="variant_color[]" placeholder="Color" 
                                           value="<?php echo cleanOutput($variant['color']); ?>" class="variant-input">
                                    <input type="number" name="variant_stock[]" placeholder="Stock" 
                                           value="<?php echo $variant['stock_quantity']; ?>" class="variant-input" min="0">
                                    <input type="number" name="variant_price_adj[]" placeholder="Price Adj" 
                                           value="<?php echo $variant['price_adjustment']; ?>" class="variant-input" step="0.01">
                                    <a href="?id=<?php echo $productId; ?>&delete_variant=<?php echo $variant['variant_id']; ?>" 
                                       class="btn btn-sm btn-danger" onclick="return confirm('Delete this variant?')">Remove</a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-outline" onclick="addVariant()">+ Add Variant</button>
                    </div>
                    
                    <!-- Images -->
                    <div class="form-section">
                        <h3>Product Images</h3>
                        
                        <?php if (!empty($images)): ?>
                            <div class="current-images">
                                <?php foreach ($images as $image): ?>
                                    <div class="image-item <?php echo $image['is_primary'] ? 'primary' : ''; ?>">
                                        <img src="../assets/images/products/<?php echo cleanOutput($image['image_path']); ?>" alt="">
                                        <?php if ($image['is_primary']): ?>
                                            <span class="primary-badge">Primary</span>
                                        <?php endif; ?>
                                        <a href="?id=<?php echo $productId; ?>&delete_image=<?php echo $image['image_id']; ?>" 
                                           class="delete-image" onclick="return confirm('Delete this image?')">&times;</a>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-group">
                            <label for="new_images">Add New Images</label>
                            <input type="file" id="new_images" name="new_images[]" multiple accept="image/*">
                            <span class="form-hint">Hold Ctrl/Cmd to select multiple images</span>
                        </div>
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary btn-lg">Update Product</button>
                    <a href="products.php" class="btn btn-outline btn-lg">Cancel</a>
                </div>
            </form>
        </div>
    </main>
    
    <script>
        // Auto-hide flash messages
        setTimeout(() => {
            const flash = document.getElementById('flashMessage');
            if (flash) flash.remove();
        }, 5000);
        
        // Add variant row
        function addVariant() {
            const container = document.getElementById('variantsContainer');
            const row = document.createElement('div');
            row.className = 'variant-row';
            row.innerHTML = `
                <input type="hidden" name="variant_id[]" value="new">
                <input type="text" name="variant_size[]" placeholder="Size" class="variant-input">
                <input type="text" name="variant_color[]" placeholder="Color" class="variant-input">
                <input type="number" name="variant_stock[]" placeholder="Stock" class="variant-input" min="0">
                <input type="number" name="variant_price_adj[]" placeholder="Price Adj" class="variant-input" step="0.01">
                <button type="button" class="btn btn-sm btn-danger" onclick="this.parentElement.remove()">Remove</button>
            `;
            container.appendChild(row);
        }
    </script>
</body>
</html>
