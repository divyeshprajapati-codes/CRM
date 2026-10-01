<?php
require_once __DIR__ . '/../db.php';
require_login();

$stmt = $pdo->query("SELECT * FROM customers ORDER BY id DESC");
$customers = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>


<div class="main">
    <div class="box">
        <h2>Customer List</h2>
        
        <?php display_flash_message(); ?>

        <div style="display:flex; gap:12px; margin-top:15px; margin-bottom:20px;">
            <a href="add_customer.php" class="login-btn" style="width:auto; padding:10px 20px;">
                + Add Customer
            </a>
            <a href="export_customers.php" class="login-btn" style="width:auto; padding:10px 20px; background:#10b981;">
                Export to Excel (CSV)
            </a>
        </div>

        <table width="100%" border="1" cellpadding="10">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Company</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>Address / City</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($customers) > 0): ?>
                    <?php foreach ($customers as $row): ?>
                        <tr>
                            <td><?php echo e($row['id']); ?></td>
                            <td><?php echo e($row['name']); ?></td>
                            <td><b><?php echo e($row['company']); ?></b></td>
                            <td><?php echo e($row['phone']); ?></td>
                            <td><?php echo e($row['email']); ?></td>
                            <td><?php echo e($row['address']); ?></td>
                            <td>
                                <a href="edit_customer.php?id=<?php echo (int)$row['id']; ?>">
                                    Edit
                                </a>
                                &nbsp;|&nbsp;
                                <form method="POST" action="delete_customer.php" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this customer?');">
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
                        <td colspan="7" align="center" style="padding:20px; color:#666;">No customers found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
