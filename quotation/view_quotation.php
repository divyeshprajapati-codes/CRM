<?php
require_once __DIR__ . '/../db.php';
require_login();

$stmt = $pdo->query("SELECT * FROM quotations ORDER BY id DESC");
$quotations = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quotation Management - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>


<div class="main">
    <div class="box">
        <h2>Quotation List</h2>

        <?php display_flash_message(); ?>

        <div style="margin-top:15px; margin-bottom:20px;">
            <a href="add_quotation.php" class="login-btn" style="width:auto; padding:10px 20px;">
                + Add Quotation
            </a>
        </div>

        <table width="100%" border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>Quotation No</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($quotations) > 0): ?>
                    <?php foreach ($quotations as $row): ?>
                        <tr>
                            <td><b><?php echo e($row['quotation_no']); ?></b></td>
                            <td><?php echo e($row['customer_name']); ?></td>
                            <td><?php echo e($row['product']); ?></td>
                            <td><?php echo e($row['quantity']); ?></td>
                            <td><?php echo format_currency($row['price']); ?></td>
                            <td><b><?php echo format_currency($row['total']); ?></b></td>
                            <td>
                                <?php
                                $status = isset($row['status']) ? $row['status'] : 'Pending';
                                if ($status === "Pending") {
                                    echo "<span style='color:#f59e0b; font-weight:bold;'>Pending</span>";
                                } elseif ($status === "Approved") {
                                    echo "<span style='color:#3b82f6; font-weight:bold;'>Approved</span>";
                                } elseif ($status === "Converted") {
                                    echo "<span style='color:#10b981; font-weight:bold;'>Converted</span>";
                                } elseif ($status === "Rejected") {
                                    echo "<span style='color:#ef4444; font-weight:bold;'>Rejected</span>";
                                } else {
                                    echo e($status);
                                }
                                ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="edit_quotation.php?id=<?php echo (int)$row['id']; ?>">
                                    Edit
                                </a>
                                &nbsp;|&nbsp;
                                <form method="POST" action="delete_quotation.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this quotation?');">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                    <button type="submit" style="background:none; border:none; color:#ef4444; font-weight:bold; cursor:pointer; padding:0; font-size:inherit; font-family:inherit;">
                                        Delete
                                    </button>
                                </form>
                                &nbsp;|&nbsp;
                                <a href="print_quotation.php?id=<?php echo (int)$row['id']; ?>" target="_blank">
                                    Print
                                </a>
                                <?php if ($status === "Pending"): ?>
                                    &nbsp;|&nbsp;
                                    <form method="POST" action="approve_quotation.php" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                        <button type="submit" style="background:none; border:none; color:#3b82f6; font-weight:bold; cursor:pointer; padding:0; font-size:inherit; font-family:inherit;">
                                            Approve
                                        </button>
                                    </form>
                                <?php elseif ($status === "Approved"): ?>
                                    &nbsp;|&nbsp;
                                    <form method="POST" action="../orders/add_order.php" style="display:inline;" onsubmit="return confirm('Convert this approved quotation to an order?');">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                        <button type="submit" style="background:none; border:none; color:#10b981; font-weight:bold; cursor:pointer; padding:0; font-size:inherit; font-family:inherit;">
                                            Convert to Order
                                        </button>
                                    </form>
                                <?php elseif ($status === "Converted"): ?>
                                    &nbsp;|&nbsp;
                                    <span style="color:#10b981; font-weight:bold;">Order Created</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" align="center" style="padding:20px; color:#666;">No quotations found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>