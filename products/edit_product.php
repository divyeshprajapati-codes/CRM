<?php
require_once __DIR__ . '/../db.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute(array($id));
$product = $stmt->fetch();

if (!$product) {
    set_flash_message('error', 'Product not found.');
    header("Location: view_product.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    verify_csrf_token();

    $product_name = isset($_POST['product_name']) ? trim($_POST['product_name']) : '';
    $category     = isset($_POST['category']) ? trim($_POST['category']) : '';
    $price        = isset($_POST['price']) ? trim($_POST['price']) : '';
    $unit         = isset($_POST['unit']) ? trim($_POST['unit']) : '';
    $description  = isset($_POST['description']) ? trim($_POST['description']) : '';

    if ($product_name === '' || $category === '' || $price === '' || $unit === '') {
        $error = "Product Name, Category, Price, and Unit are required.";
    } elseif (!is_numeric($price) || (float)$price < 0) {
        $error = "Price must be a valid positive number.";
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE products SET product_name = ?, category = ?, price = ?, unit = ?, description = ? WHERE id = ?");
            $updateStmt->execute(array($product_name, $category, (float)$price, $unit, $description, $id));

            set_flash_message('success', 'Product updated successfully.');
            header("Location: view_product.php");
            exit();
        } catch (PDOException $e) {
            error_log("Edit Product Error: " . $e->getMessage());
            $error = "Failed to update product.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>


<div class="main">
    <div class="box">
        <h2>Edit Product</h2>

        <?php if (!empty($error)): ?>
            <div style="background:#ef4444; color:#fff; padding:12px; border-radius:8px; margin-bottom:20px;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit_product.php?id=<?php echo $id; ?>">
            <?php echo csrf_field(); ?>

            <div class="input-box">
                <label>Product Name *</label>
                <input type="text" name="product_name" value="<?php echo e($product['product_name']); ?>" required>
            </div>

            <div class="input-box">
                <label>Category *</label>
                <input type="text" name="category" value="<?php echo e($product['category']); ?>" required>
            </div>

            <div class="input-box">
                <label>Price (₹) *</label>
                <input type="number" step="0.01" min="0" name="price" value="<?php echo e($product['price']); ?>" required>
            </div>

            <div class="input-box">
                <label>Unit *</label>
                <input type="text" name="unit" value="<?php echo e($product['unit']); ?>" required>
            </div>

            <div class="input-box">
                <label>Description</label>
                <textarea name="description" rows="3"><?php echo e($product['description']); ?></textarea>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="login-btn" name="update" style="width:auto; padding:12px 25px;">
                    Update Product
                </button>
                <a href="view_product.php" class="login-btn" style="width:auto; padding:12px 25px; background:#6b7280;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
