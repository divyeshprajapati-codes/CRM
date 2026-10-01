<?php
require_once __DIR__ . '/../db.php';
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute(array($id));
$user = $stmt->fetch();

if (!$user) {
    set_flash_message('error', 'User not found.');
    header("Location: view_user.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    verify_csrf_token();

    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $username  = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password  = isset($_POST['password']) ? trim($_POST['password']) : '';
    $role      = isset($_POST['role']) ? trim($_POST['role']) : 'Sales';
    $status    = isset($_POST['status']) ? trim($_POST['status']) : 'Active';

    $allowed_roles  = array('Admin', 'Manager', 'Sales');
    $allowed_status = array('Active', 'Inactive');

    if ($full_name === '' || $username === '') {
        $error = "Full Name and Username are required.";
    } elseif (!in_array($role, $allowed_roles, true) || !in_array($status, $allowed_status, true)) {
        $error = "Invalid role or status selected.";
    } else {
        // Check if username is already taken by another user
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $checkStmt->execute(array($username, $id));
        if ($checkStmt->fetch()) {
            $error = "Username '{$username}' is already in use by another account.";
        } else {
            try {
                if ($password !== '') {
                    $hashedPassword = function_exists('password_hash') ? password_hash($password, PASSWORD_DEFAULT) : $password;
                    $updateStmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, password = ?, role = ?, status = ? WHERE id = ?");
                    $updateStmt->execute(array($full_name, $username, $hashedPassword, $role, $status, $id));
                } else {
                    $updateStmt = $pdo->prepare("UPDATE users SET full_name = ?, username = ?, role = ?, status = ? WHERE id = ?");
                    $updateStmt->execute(array($full_name, $username, $role, $status, $id));
                }

                // If updating own session info
                if (isset($_SESSION['id']) && (int)$_SESSION['id'] === $id) {
                    $_SESSION['username']  = $username;
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['role']      = $role;
                }

                set_flash_message('success', "User '{$username}' updated successfully.");
                header("Location: view_user.php");
                exit();
            } catch (PDOException $e) {
                error_log("Edit User Error: " . $e->getMessage());
                $error = "Failed to update user.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>


<div class="main">
    <div class="box">
        <h2>Edit User</h2>

        <?php if (!empty($error)): ?>
            <div style="background:#ef4444; color:#fff; padding:12px; border-radius:8px; margin-bottom:20px;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit_user.php?id=<?php echo $id; ?>">
            <?php echo csrf_field(); ?>

            <div class="input-box">
                <label>Full Name *</label>
                <input type="text" name="full_name" value="<?php echo e($user['full_name']); ?>" required>
            </div>

            <div class="input-box">
                <label>Username *</label>
                <input type="text" name="username" value="<?php echo e($user['username']); ?>" required>
            </div>

            <div class="input-box">
                <label>New Password</label>
                <input type="password" name="password" placeholder="Leave blank to keep existing password">
            </div>

            <div class="input-box">
                <label>Role</label>
                <select name="role">
                    <option value="Sales" <?php if ($user['role'] === 'Sales') echo 'selected'; ?>>Sales</option>
                    <option value="Manager" <?php if ($user['role'] === 'Manager') echo 'selected'; ?>>Manager</option>
                    <option value="Admin" <?php if ($user['role'] === 'Admin') echo 'selected'; ?>>Admin</option>
                </select>
            </div>

            <div class="input-box">
                <label>Status</label>
                <select name="status">
                    <option value="Active" <?php if ($user['status'] === 'Active') echo 'selected'; ?>>Active</option>
                    <option value="Inactive" <?php if ($user['status'] === 'Inactive') echo 'selected'; ?>>Inactive</option>
                </select>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="login-btn" name="update" style="width:auto; padding:12px 25px;">
                    Update User
                </button>
                <a href="view_user.php" class="login-btn" style="width:auto; padding:12px 25px; background:#6b7280;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>