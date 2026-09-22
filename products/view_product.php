<?php
require_once("../db.php");
require_login();

$stmt = $pdo->query("SELECT * FROM products ORDER BY id DESC");
$products = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include("../sidebar.php"); ?>

<div class="main">
    <div class="box">
        <h2>Product List</h2>

        <?php display_flash_message(); ?>

        <div style="margin-top:15px; margin-bottom:20px;">
            <a href="add_product.php" class="login-btn" style="width:auto; padding:10px 20px;">
                + Add Product
            </a>
        </div>

        <table width="100%" border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Unit</th>
                    <th>Description</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($products) > 0): ?>
                    <?php foreach ($products as $row): ?>
                        <tr>
                            <td><?php echo e($row['id']); ?></td>
                            <td><b><?php echo e($row['product_name']); ?></b></td>
                            <td><?php echo e($row['category']); ?></td>
                            <td><?php echo format_currency($row['price']); ?></td>
                            <td><?php echo e($row['unit']); ?></td>
                            <td><?php echo e($row['description']); ?></td>
                            <td>
                                <a href="edit_product.php?id=<?php echo (int)$row['id']; ?>">
                                    Edit
                                </a>
                                &nbsp;|&nbsp;
                                <form method="POST" action="delete_product.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                    <button type="submit" style="background:none; border:none; color:#ef4444; font-weight:bold; cursor:pointer; padding:0; font-size:inherit; font-family:inherit;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" align="center" style="padding:20px; color:#666;">No products found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
