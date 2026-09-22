<?php
require_once("../db.php");
require_admin();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    verify_csrf_token();

    $full_name = isset($_POST['full_name']) ? trim($_POST['full_name']) : '';
    $username  = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password  = isset($_POST['password']) ? trim($_POST['password']) : '';
    $role      = isset($_POST['role']) ? trim($_POST['role']) : 'Sales';
    $status    = isset($_POST['status']) ? trim($_POST['status']) : 'Active';

    $allowed_roles  = array('Admin', 'Manager', 'Sales');
    $allowed_status = array('Active', 'Inactive');

    if ($full_name === '' || $username === '' || $password === '') {
        $error = "Full Name, Username, and Password are required.";
    } elseif (!in_array($role, $allowed_roles, true) || !in_array($status, $allowed_status, true)) {
        $error = "Invalid role or status selected.";
    } else {
        // Check if username already exists
        $checkStmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $checkStmt->execute(array($username));
        if ($checkStmt->fetch()) {
            $error = "Username '{$username}' is already in use. Please choose another.";
        } else {
            try {
                $hashedPassword = function_exists('password_hash') ? password_hash($password, PASSWORD_DEFAULT) : $password;

                $stmt = $pdo->prepare("INSERT INTO users (full_name, username, password, role, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute(array($full_name, $username, $hashedPassword, $role, $status));

                set_flash_message('success', "User '{$username}' created successfully.");
                header("Location: view_user.php");
                exit();
            } catch (PDOException $e) {
                error_log("Add User Error: " . $e->getMessage());
                $error = "Failed to create user.";
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
    <title>Add User - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include("../sidebar.php"); ?>

<div class="main">
    <div class="box">
        <h2>Add New User</h2>

        <?php if (!empty($error)): ?>
            <div style="background:#ef4444; color:#fff; padding:12px; border-radius:8px; margin-bottom:20px;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="add_user.php">
            <?php echo csrf_field(); ?>

            <div class="input-box">
                <label>Full Name *</label>
                <input type="text" name="full_name" value="<?php echo e(isset($_POST['full_name']) ? $_POST['full_name'] : ''); ?>" placeholder="Enter Full Name" required>
            </div>

            <div class="input-box">
                <label>Username *</label>
                <input type="text" name="username" value="<?php echo e(isset($_POST['username']) ? $_POST['username'] : ''); ?>" placeholder="Enter Username" required>
            </div>

            <div class="input-box">
                <label>Password *</label>
                <input type="password" name="password" placeholder="Enter Secure Password" required>
            </div>

            <div class="input-box">
                <label>Role</label>
                <select name="role">
                    <option value="Sales" <?php if ((isset($_POST['role']) ? $_POST['role'] : '') === 'Sales') echo 'selected'; ?>>Sales</option>
                    <option value="Manager" <?php if ((isset($_POST['role']) ? $_POST['role'] : '') === 'Manager') echo 'selected'; ?>>Manager</option>
                    <option value="Admin" <?php if ((isset($_POST['role']) ? $_POST['role'] : '') === 'Admin') echo 'selected'; ?>>Admin</option>
                </select>
            </div>

            <div class="input-box">
                <label>Status</label>
                <select name="status">
                    <option value="Active" <?php if ((isset($_POST['status']) ? $_POST['status'] : '') === 'Active') echo 'selected'; ?>>Active</option>
                    <option value="Inactive" <?php if ((isset($_POST['status']) ? $_POST['status'] : '') === 'Inactive') echo 'selected'; ?>>Inactive</option>
                </select>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="login-btn" name="save" style="width:auto; padding:12px 25px;">
                    Save User
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