<?php
require_once("../db.php");
require_admin();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    set_flash_message('error', 'User not found.');
    header("Location: view_user.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute(array($id));
$deleteUser = $stmt->fetch();

if (!$deleteUser) {
    set_flash_message('error', 'User not found.');
    header("Location: view_user.php");
    exit();
}

// Prevent deleting own account
if (isset($_SESSION['id']) && (int)$deleteUser['id'] === (int)$_SESSION['id']) {
    set_flash_message('error', 'You cannot delete your own account.');
    header("Location: view_user.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete'])) {
    verify_csrf_token();

    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $adminId  = (int)$_SESSION['id'];

    $adminStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $adminStmt->execute(array($adminId));
    $adminUser = $adminStmt->fetch();

    $passwordValid = false;
    if ($adminUser) {
        if (function_exists('password_verify') && password_verify($password, $adminUser['password'])) {
            $passwordValid = true;
        } elseif ($password === $adminUser['password']) {
            $passwordValid = true;
        }
    }

    if (!$passwordValid) {
        $error = "Incorrect administrator password.";
    } else {
        try {
            $delStmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $delStmt->execute(array($id));

            set_flash_message('success', "User '{$deleteUser['username']}' deleted successfully.");
            header("Location: view_user.php");
            exit();
        } catch (PDOException $e) {
            error_log("Delete User Error: " . $e->getMessage());
            $error = "Failed to delete user.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete User - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include("../sidebar.php"); ?>

<div class="main">
    <div class="box">
        <h2>Delete User</h2>

        <p style="margin-top:15px; font-size:16px;">
            You are about to delete user account: <b><?php echo e($deleteUser['full_name']); ?> (<?php echo e($deleteUser['username']); ?>)</b>
        </p>

        <p style="color:#ef4444; font-weight:bold; margin-top:10px;">
            ⚠️ This action cannot be undone.
        </p>

        <?php if (!empty($error)): ?>
            <div style="background:#ef4444; color:#fff; padding:12px; border-radius:8px; margin-top:15px; margin-bottom:15px;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="delete_user.php?id=<?php echo $id; ?>" style="margin-top:20px;">
            <?php echo csrf_field(); ?>

            <div class="input-box">
                <label>Confirm Your Administrator Password *</label>
                <input
                    type="password"
                    name="password"
                    placeholder="Enter your current password"
                    required
                    autofocus>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button
                    type="submit"
                    name="delete"
                    class="login-btn"
                    style="width:auto; padding:12px 25px; background:#ef4444;">
                    Confirm & Delete User
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