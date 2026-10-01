<?php
require_once __DIR__ . '/../db.php';
require_admin();

$stmt = $pdo->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll();
$current_user_id = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>


<div class="main">
    <div class="box">
        <h2>User Management</h2>

        <?php display_flash_message(); ?>

        <div style="margin-top:15px; margin-bottom:20px;">
            <a href="add_user.php" class="login-btn" style="width:auto; padding:10px 20px;">
                + Add New User
            </a>
        </div>

        <table width="100%" border="1" cellpadding="10" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Username</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($users) > 0): ?>
                    <?php foreach ($users as $row): ?>
                        <tr>
                            <td><?php echo e($row['id']); ?></td>
                            <td><b><?php echo e($row['full_name']); ?></b></td>
                            <td><?php echo e($row['username']); ?></td>
                            <td>
                                <?php
                                $role = $row['role'];
                                if ($role === "Admin") {
                                    echo "<span style='color:#ef4444; font-weight:bold;'>Admin</span>";
                                } elseif ($role === "Manager") {
                                    echo "<span style='color:#3b82f6; font-weight:bold;'>Manager</span>";
                                } else {
                                    echo "<span style='color:#10b981; font-weight:bold;'>Sales</span>";
                                }
                                ?>
                            </td>
                            <td>
                                <?php
                                if ($row['status'] === "Active") {
                                    echo "<span style='color:#10b981; font-weight:bold;'>Active</span>";
                                } else {
                                    echo "<span style='color:#ef4444; font-weight:bold;'>Inactive</span>";
                                }
                                ?>
                            </td>
                            <td>
                                <?php echo date("d-m-Y", strtotime($row['created_at'])); ?>
                            </td>
                            <td>
                                <a href="edit_user.php?id=<?php echo (int)$row['id']; ?>">
                                    Edit
                                </a>
                                <?php if ($current_user_id !== (int)$row['id']): ?>
                                    &nbsp;|&nbsp;
                                    <a href="delete_user.php?id=<?php echo (int)$row['id']; ?>" style="color:#ef4444; font-weight:bold;">
                                        Delete
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" align="center" style="padding:20px; color:#666;">No Users Found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>