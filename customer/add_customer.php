<?php
require_once __DIR__ . '/../db.php';
require_login();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    verify_csrf_token();

    $name    = isset($_POST['name']) ? trim($_POST['name']) : '';
    $company = isset($_POST['company']) ? trim($_POST['company']) : '';
    $phone   = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $email   = isset($_POST['email']) ? trim($_POST['email']) : '';
    $address = isset($_POST['address']) ? trim($_POST['address']) : '';

    if ($name === '' || $company === '' || $phone === '') {
        $error = "Name, Company, and Phone are required fields.";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO customers (name, company, phone, email, address) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute(array($name, $company, $phone, $email, $address));

            set_flash_message('success', 'Customer added successfully.');
            header("Location: view_customer.php");
            exit();
        } catch (PDOException $e) {
            error_log("Add Customer Error: " . $e->getMessage());
            $error = "Failed to save customer. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Customer - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>


<div class="main">
    <div class="box">
        <h2>Add New Customer</h2>

        <?php if (!empty($error)): ?>
            <div style="background:#ef4444; color:#fff; padding:12px; border-radius:8px; margin-bottom:20px;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="add_customer.php">
            <?php echo csrf_field(); ?>

            <div class="input-box">
                <label>Contact Name *</label>
                <input type="text" name="name" value="<?php echo e(isset($_POST['name']) ? $_POST['name'] : ''); ?>" placeholder="Enter Contact Person Name" required>
            </div>

            <div class="input-box">
                <label>Company Name *</label>
                <input type="text" name="company" value="<?php echo e(isset($_POST['company']) ? $_POST['company'] : ''); ?>" placeholder="Enter Company Name" required>
            </div>

            <div class="input-box">
                <label>Phone *</label>
                <input type="text" name="phone" value="<?php echo e(isset($_POST['phone']) ? $_POST['phone'] : ''); ?>" placeholder="Enter Phone / Mobile" required>
            </div>

            <div class="input-box">
                <label>Email Address</label>
                <input type="email" name="email" value="<?php echo e(isset($_POST['email']) ? $_POST['email'] : ''); ?>" placeholder="Enter Email Address">
            </div>

            <div class="input-box">
                <label>Address / City</label>
                <input type="text" name="address" value="<?php echo e(isset($_POST['address']) ? $_POST['address'] : ''); ?>" placeholder="Enter Address / City">
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" class="login-btn" name="save" style="width:auto; padding:12px 25px;">
                    Save Customer
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
