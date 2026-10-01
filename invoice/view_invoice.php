<?php
require_once __DIR__ . '/../db.php';
require_login();

$stmt = $pdo->query("SELECT * FROM invoices ORDER BY id DESC");
$invoices = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Management - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>


<div class="main">
    <div class="box">
        <h2>Invoice List</h2>

        <?php display_flash_message(); ?>

        <table width="100%" border="1" cellpadding="10" cellspacing="0" style="margin-top:20px;">
            <thead>
                <tr>
                    <th>Invoice No</th>
                    <th>Order No</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($invoices) > 0): ?>
                    <?php foreach ($invoices as $row): ?>
                        <tr>
                            <td><b><?php echo e($row['invoice_no']); ?></b></td>
                            <td><?php echo e($row['order_no']); ?></td>
                            <td><?php echo e($row['customer_name']); ?></td>
                            <td><?php echo e($row['product_name']); ?></td>
                            <td><?php echo e($row['quantity']); ?></td>
                            <td><?php echo format_currency($row['price']); ?></td>
                            <td><b><?php echo format_currency($row['total']); ?></b></td>
                            <td>
                                <?php
                                $status = isset($row['payment_status']) ? $row['payment_status'] : 'Unpaid';
                                if ($status === "Paid") {
                                    echo "<span style='color:#10b981; font-weight:bold;'>Paid</span>";
                                } else {
                                    echo "<span style='color:#ef4444; font-weight:bold;'>Unpaid</span>";
                                }
                                ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="edit_invoice.php?id=<?php echo (int)$row['id']; ?>">
                                    Edit
                                </a>
                                &nbsp;|&nbsp;
                                <form method="POST" action="delete_invoice.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this invoice?');">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                    <button type="submit" style="background:none; border:none; color:#ef4444; font-weight:bold; cursor:pointer; padding:0; font-size:inherit; font-family:inherit;">
                                        Delete
                                    </button>
                                </form>
                                &nbsp;|&nbsp;
                                <a href="print_invoice.php?id=<?php echo (int)$row['id']; ?>" target="_blank">
                                    Print
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" align="center" style="padding:20px; color:#666;">No Invoices Found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
