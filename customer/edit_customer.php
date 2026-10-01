<?php
require_once __DIR__ . '/../db.php';
require_login();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute(array($id));
$customer = $stmt->fetch();

if (!$customer) {
    set_flash_message('error', 'Customer not found.');
    header("Location: view_customer.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    verify_csrf_token();

    $name    = isset($_POST['name']) ? trim($_POST['name']) : '';
    $company = isset($_POST['company']) ? trim($_POST['company']) : '';
    $phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email   = isset($_POST['email']) ? trim($_POST['email']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';

    if ($name === '' || $company === '' || $phone === '') {
        $error = "Name, Company, and Phone are required.";
    } else {
        try {
            $updateStmt = $pdo->prepare("UPDATE customers SET name = ?, company = ?, phone = ?, email = ?, address = ? WHERE id = ?");
            $updateStmt->execute(array($name, $company, $phone, $email, $address, $id));

            set_flash_message('success', 'Customer updated successfully.');
            header("Location: view_customer.php");
            exit();
        } catch (PDOException $e) {
            error_log("Edit Customer Error: " . $e->getMessage());
            $error = "Failed to update customer.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>


<div class="main">
    <div class="box">
        <h2>Edit Customer</h2>

        <?php if (!empty($error)): ?>
            <div style="background:#ef4444; color:#fff; padding:12px; border-radius:8px; margin-bottom:20px;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="edit_customer.php?id=<?php echo $id; ?>">
            <?php echo csrf_field(); ?>

            <div class="input-box">
                <label>Contact Name *</label>
                <input type="text" name="name" value="<?php echo e($customer['name']); ?>" required>
            </div>

            <div class="input-box">
                <label>Company Name *</label>
                <input type="text" name="company" value="<?php echo e($customer['company']); ?>" required>
            </div>

            <div class="input-box">
                <label>Phone *</label>
                <input type="text" name="phone" value="<?php echo e($customer['phone']); ?>" required>
            </div>

            <div class="input-box">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo e($customer['email']); ?>">
            </div>

            <div class="input-box">
                <label>Address / City</label>
                <input type="text" name="address" value="<?php echo e($customer['address']); ?>">
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="login-btn" name="update" style="width:auto; padding:12px 25px;">
                    Update Customer
                </button>
                <a href="view_customer.php" class="login-btn" style="width:auto; padding:12px 25px; background:#6b7280;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

</body>
</html>
