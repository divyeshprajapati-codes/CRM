<?php
require_once("../db.php");
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->execute(array($id));
$invoice = $stmt->fetch();

if (!$invoice) {
    set_flash_message('error', 'Invoice not found.');
    header("Location: view_invoice.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    verify_csrf_token();

    $status = isset($_POST['payment_status']) ? trim($_POST['payment_status']) : 'Unpaid';

    try {
        $updateStmt = $pdo->prepare("UPDATE invoices SET payment_status = ? WHERE id = ?");
        $updateStmt->execute(array($status, $id));

        set_flash_message('success', 'Invoice payment status updated successfully.');
        header("Location: view_invoice.php");
        exit();
    } catch (PDOException $e) {
        error_log("Edit Invoice Error: " . $e->getMessage());
        $error = "Failed to update invoice.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Invoice - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include("../sidebar.php"); ?>

<div class="main">
    <div class="box">
        <h2>Edit Invoice (<?php echo e($invoice['invoice_no']); ?>)</h2>

        <?php if (!empty($error)): ?>
            <div style="background:#ef4444; color:#fff; padding:12px; border-radius:8px; margin-bottom:20px;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit_invoice.php?id=<?php echo $id; ?>">
            <?php echo csrf_field(); ?>

            <div class="input-box">
                <label>Invoice Number</label>
                <input type="text" value="<?php echo e($invoice['invoice_no']); ?>" readonly style="background:#f1f5f9;">
            </div>

            <div class="input-box">
                <label>Order Number</label>
                <input type="text" value="<?php echo e($invoice['order_no']); ?>" readonly style="background:#f1f5f9;">
            </div>

            <div class="input-box">
                <label>Customer</label>
                <input type="text" value="<?php echo e($invoice['customer_name']); ?>" readonly style="background:#f1f5f9;">
            </div>

            <div class="input-box">
                <label>Product</label>
                <input type="text" value="<?php echo e($invoice['product_name']); ?>" readonly style="background:#f1f5f9;">
            </div>

            <div class="input-box">
                <label>Total Amount</label>
                <input type="text" value="<?php echo format_currency($invoice['total']); ?>" readonly style="background:#f1f5f9;">
            </div>

            <div class="input-box">
                <label>Payment Status</label>
                <select name="payment_status">
                    <option value="Unpaid" <?php if ($invoice['payment_status'] === 'Unpaid') echo 'selected'; ?>>Unpaid</option>
                    <option value="Paid" <?php if ($invoice['payment_status'] === 'Paid') echo 'selected'; ?>>Paid</option>
                </select>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="login-btn" name="update" style="width:auto; padding:12px 25px;">
                    Update Invoice
                </button>
                <a href="view_invoice.php" class="login-btn" style="width:auto; padding:12px 25px; background:#6b7280;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
