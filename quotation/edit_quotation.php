<?php
require_once __DIR__ . '/../db.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM quotations WHERE id = ?");
$stmt->execute(array($id));
$quotation = $stmt->fetch();

if (!$quotation) {
    set_flash_message('error', 'Quotation not found.');
    header("Location: view_quotation.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    verify_csrf_token();

    $customer = isset($_POST['customer']) ? trim($_POST['customer']) : '';
    $product  = isset($_POST['product']) ? trim($_POST['product']) : '';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $price    = isset($_POST['price']) ? (float)$_POST['price'] : 0.0;
    $status   = isset($_POST['status']) ? trim($_POST['status']) : 'Pending';

    if ($customer === '' || $product === '' || $quantity <= 0 || $price <= 0) {
        $error = "Please provide valid customer, product, quantity, and price.";
    } else {
        try {
            $total = round($quantity * $price, 2);

            $updateStmt = $pdo->prepare("UPDATE quotations SET customer_name = ?, product = ?, quantity = ?, price = ?, total = ?, status = ? WHERE id = ?");
            $updateStmt->execute(array($customer, $product, $quantity, $price, $total, $status, $id));

            set_flash_message('success', 'Quotation updated successfully.');
            header("Location: view_quotation.php");
            exit();
        } catch (PDOException $e) {
            error_log("Edit Quotation Error: " . $e->getMessage());
            $error = "Failed to update quotation.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Quotation - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>


<div class="main">
    <div class="box">
        <h2>Edit Quotation (<?php echo e($quotation['quotation_no']); ?>)</h2>

        <?php if (!empty($error)): ?>
            <div style="background:#ef4444; color:#fff; padding:12px; border-radius:8px; margin-bottom:20px;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit_quotation.php?id=<?php echo $id; ?>">
            <?php echo csrf_field(); ?>

            <div class="input-box">
                <label>Customer *</label>
                <input type="text" name="customer" value="<?php echo e($quotation['customer_name']); ?>" required>
            </div>

            <div class="input-box">
                <label>Product *</label>
                <input type="text" name="product" value="<?php echo e($quotation['product']); ?>" required>
            </div>

            <div class="input-box">
                <label>Quantity *</label>
                <input type="number" name="quantity" id="quantity" min="1" value="<?php echo e($quotation['quantity']); ?>" required>
            </div>

            <div class="input-box">
                <label>Price (₹) *</label>
                <input type="number" step="0.01" min="0" name="price" id="price" value="<?php echo e($quotation['price']); ?>" required>
            </div>

            <div class="input-box">
                <label>Status</label>
                <select name="status">
                    <option value="Pending" <?php if ($quotation['status'] === 'Pending') echo 'selected'; ?>>Pending</option>
                    <option value="Approved" <?php if ($quotation['status'] === 'Approved') echo 'selected'; ?>>Approved</option>
                    <option value="Converted" <?php if ($quotation['status'] === 'Converted') echo 'selected'; ?>>Converted</option>
                    <option value="Rejected" <?php if ($quotation['status'] === 'Rejected') echo 'selected'; ?>>Rejected</option>
                </select>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="login-btn" name="update" style="width:auto; padding:12px 25px;">
                    Update Quotation
                </button>
                <a href="view_quotation.php" class="login-btn" style="width:auto; padding:12px 25px; background:#6b7280;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
