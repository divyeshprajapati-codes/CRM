<?php
require_once("../db.php");
require_login();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
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
            $stmt = $pdo->prepare("INSERT INTO products (product_name, category, price, unit, description) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(array($product_name, $category, (float)$price, $unit, $description));

            set_flash_message('success', 'Product added successfully.');
            header("Location: view_product.php");
            exit();
        } catch (PDOException $e) {
            error_log("Add Product Error: " . $e->getMessage());
            $error = "Failed to save product.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include("../sidebar.php"); ?>

<div class="main">
    <div class="box">
        <h2>Add New Product</h2>

        <?php if (!empty($error)): ?>
            <div style="background:#ef4444; color:#fff; padding:12px; border-radius:8px; margin-bottom:20px;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="add_product.php">
            <?php echo csrf_field(); ?>

            <div class="input-box">
                <label>Product Name *</label>
                <input type="text" name="product_name" value="<?php echo e(isset($_POST['product_name']) ? $_POST['product_name'] : ''); ?>" placeholder="Enter Product Name" required>
            </div>

            <div class="input-box">
                <label>Category *</label>
                <input type="text" name="category" value="<?php echo e(isset($_POST['category']) ? $_POST['category'] : ''); ?>" placeholder="e.g. Fasteners, Bolts, Nuts, Valves" required>
            </div>

            <div class="input-box">
                <label>Price (₹) *</label>
                <input type="number" step="0.01" min="0" name="price" value="<?php echo e(isset($_POST['price']) ? $_POST['price'] : ''); ?>" placeholder="0.00" required>
            </div>

            <div class="input-box">
                <label>Unit *</label>
                <input type="text" name="unit" value="<?php echo e(isset($_POST['unit']) ? $_POST['unit'] : ''); ?>" placeholder="Nos, Kg, Box, Piece, Set" required>
            </div>

            <div class="input-box">
                <label>Description</label>
                <textarea name="description" rows="3" placeholder="Enter Product Specifications / Details"><?php echo e(isset($_POST['description']) ? $_POST['description'] : ''); ?></textarea>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="login-btn" name="save" style="width:auto; padding:12px 25px;">
                    Save Product
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
