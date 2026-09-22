<?php
require_once("../db.php");
require_login();

$stmt = $pdo->query("SELECT * FROM orders ORDER BY id DESC");
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include("../sidebar.php"); ?>

<div class="main">
    <div class="box">
        <h2>Order List</h2>

        <?php display_flash_message(); ?>

        <table width="100%" border="1" cellpadding="10" cellspacing="0" style="margin-top:20px;">
            <thead>
                <tr>
                    <th>Order No</th>
                    <th>Quotation No</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Action</th>
                    <th>Invoice</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($orders) > 0): ?>
                    <?php foreach ($orders as $row): ?>
                        <tr>
                            <td><b><?php echo e($row['order_no']); ?></b></td>
                            <td><?php echo e($row['quotation_no']); ?></td>
                            <td><?php echo e($row['customer_name']); ?></td>
                            <td><?php echo e($row['product_name']); ?></td>
                            <td><?php echo e($row['quantity']); ?></td>
                            <td><?php echo format_currency($row['price']); ?></td>
                            <td><b><?php echo format_currency($row['total']); ?></b></td>
                            <td>
                                <?php
                                $status = isset($row['order_status']) ? $row['order_status'] : 'Pending';
                                switch ($status) {
                                    case "Pending":
                                        echo "<span style='color:#f59e0b; font-weight:bold;'>Pending</span>";
                                        break;
                                    case "In Progress":
                                    case "Processing":
                                        echo "<span style='color:#3b82f6; font-weight:bold;'>In Progress</span>";
                                        break;
                                    case "Completed":
                                        echo "<span style='color:#10b981; font-weight:bold;'>Completed</span>";
                                        break;
                                    case "Cancelled":
                                    case "Cancel":
                                        echo "<span style='color:#ef4444; font-weight:bold;'>Cancelled</span>";
                                        break;
                                    default:
                                        echo e($status);
                                }
                                ?>
                            </td>
                            <td style="white-space:nowrap;">
                                <a href="edit_order.php?id=<?php echo (int)$row['id']; ?>">
                                    Edit
                                </a>
                                &nbsp;|&nbsp;
                                <form method="POST" action="delete_order.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this order? (The associated quotation will revert to Pending)');">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                    <button type="submit" style="background:none; border:none; color:#ef4444; font-weight:bold; cursor:pointer; padding:0; font-size:inherit; font-family:inherit;">
                                        Delete
                                    </button>
                                </form>
                            </td>
                            <td>
                                <?php if ($status === "Completed"): ?>
                                    <form method="POST" action="../invoice/add_invoice.php" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">
                                        <button type="submit" style="background:none; border:none; color:#10b981; font-weight:bold; cursor:pointer; padding:0; font-size:inherit; font-family:inherit;">
                                            Generate Invoice
                                        </button>
                                    </form>
                                <?php elseif ($status === "In Progress" || $status === "Processing"): ?>
                                    <span style="color:#3b82f6;">Production</span>
                                <?php elseif ($status === "Pending"): ?>
                                    <span style="color:#f59e0b;">Waiting</span>
                                <?php elseif ($status === "Cancelled" || $status === "Cancel"): ?>
                                    <span style="color:#ef4444;">Cancelled</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="10" align="center" style="padding:20px; color:#666;">No Orders Found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>