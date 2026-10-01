<?php
require_once __DIR__ . '/../db.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute(array($id));
$order = $stmt->fetch();

if (!$order) {
    set_flash_message('error', 'Order not found.');
    header("Location: view_order.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    verify_csrf_token();

    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $price    = isset($_POST['price']) ? (float)$_POST['price'] : 0.0;
    $status   = isset($_POST['order_status']) ? trim($_POST['order_status']) : 'Pending';

    if ($quantity <= 0 || $price <= 0) {
        $error = "Please provide valid quantity and price.";
    } else {
        try {
            $total = round($quantity * $price, 2);

            $updateStmt = $pdo->prepare("UPDATE orders SET quantity = ?, price = ?, total = ?, order_status = ? WHERE id = ?");
            $updateStmt->execute(array($quantity, $price, $total, $status, $id));

            set_flash_message('success', 'Order updated successfully.');
            header("Location: view_order.php");
            exit();
        } catch (PDOException $e) {
            error_log("Edit Order Error: " . $e->getMessage());
            $error = "Failed to update order.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Order - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>


<div class="main">
    <div class="box">
        <h2>Edit Order (<?php echo e($order['order_no']); ?>)</h2>

        <?php if (!empty($error)): ?>
            <div style="background:#ef4444; color:#fff; padding:12px; border-radius:8px; margin-bottom:20px;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit_order.php?id=<?php echo $id; ?>">
            <?php echo csrf_field(); ?>

            <div class="input-box">
                <label>Order Number</label>
                <input type="text" value="<?php echo e($order['order_no']); ?>" readonly style="background:#f1f5f9;">
            </div>

            <div class="input-box">
                <label>Quotation Number</label>
                <input type="text" value="<?php echo e($order['quotation_no']); ?>" readonly style="background:#f1f5f9;">
            </div>

            <div class="input-box">
                <label>Customer</label>
                <input type="text" value="<?php echo e($order['customer_name']); ?>" readonly style="background:#f1f5f9;">
            </div>

            <div class="input-box">
                <label>Product</label>
                <input type="text" value="<?php echo e($order['product_name']); ?>" readonly style="background:#f1f5f9;">
            </div>

            <div class="input-box">
                <label>Quantity *</label>
                <input type="number" name="quantity" min="1" value="<?php echo e($order['quantity']); ?>" required>
            </div>

            <div class="input-box">
                <label>Price (₹) *</label>
                <input type="number" step="0.01" min="0" name="price" value="<?php echo e($order['price']); ?>" required>
            </div>

            <div class="input-box">
                <label>Status</label>
                <select name="order_status">
                    <option value="Pending" <?php if ($order['order_status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="In Progress" <?php if ($order['order_status'] === 'In Progress' || $order['order_status'] === 'Processing') echo 'selected'; ?>>In Progress</option>
                    <option value="Completed" <?php if ($order['order_status'] === 'Completed') echo 'selected'; ?>>Completed</option>
                    <option value="Cancelled" <?php if ($order['order_status'] === 'Cancelled' || $order['order_status'] === 'Cancel') echo 'selected'; ?>>Cancelled</option>
                </select>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="login-btn" name="update" style="width:auto; padding:12px 25px;">
                    Update Order
                </button>
                <a href="view_order.php" class="login-btn" style="width:auto; padding:12px 25px; background:#6b7280;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
