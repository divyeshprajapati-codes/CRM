<?php
require_once __DIR__ . '/../db.php';
require_login();

$error = "";

// Fetch customers and products for dropdowns
$customers = $pdo->query("SELECT id, name, company FROM customers ORDER BY company ASC")->fetchAll();
$products  = $pdo->query("SELECT id, product_name, price FROM products ORDER BY product_name ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    verify_csrf_token();

    $customer = isset($_POST['customer']) ? trim($_POST['customer']) : '';
    $product  = isset($_POST['product']) ? trim($_POST['product']) : '';
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
    $price    = isset($_POST['price']) ? (float)$_POST['price'] : 0.0;

    if ($customer === '' || $product === '' || $quantity <= 0 || $price <= 0) {
        $error = "Please select a customer, product, valid quantity, and price.";
    } else {
        try {
            $total = round($quantity * $price, 2);
            $quotation_no = generate_doc_number($pdo, 'quotations', 'QT', 'id', 4);

            $stmt = $pdo->prepare("INSERT INTO quotations (quotation_no, customer_name, product, quantity, price, total, status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
            $stmt->execute(array($quotation_no, $customer, $product, $quantity, $price, $total));

            set_flash_message('success', "Quotation {$quotation_no} created successfully.");
            header("Location: view_quotation.php");
            exit();
        } catch (PDOException $e) {
            error_log("Add Quotation Error: " . $e->getMessage());
            $error = "Failed to save quotation.";
        }
    }
}

$selectedCustomer = isset($_POST['customer']) ? $_POST['customer'] : '';
$selectedProduct  = isset($_POST['product']) ? $_POST['product'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Quotation - Industrial CRM</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

<?php include __DIR__ . '/../sidebar.php'; ?>


<div class="main">
    <div class="box">
        <h2>Create Quotation</h2>

        <?php if (!empty($error)): ?>
            <div style="background:#ef4444; color:#fff; padding:12px; border-radius:8px; margin-bottom:20px;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="add_quotation.php">
            <?php echo csrf_field(); ?>

            <!-- Customer -->
            <div class="input-box">
                <label>Customer *</label>
                <select name="customer" required>
                    <option value="">Select Customer</option>
                    <?php foreach ($customers as $c): ?>
                        <option value="<?php echo e($c['company']); ?>" <?php if ($c['company'] === $selectedCustomer) echo 'selected'; ?>>
                            <?php echo e($c['company'] . ' (' . $c['name'] . ')'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Product -->
            <div class="input-box">
                <label>Product *</label>
                <select name="product" id="product" required>
                    <option value="">Select Product</option>
                    <?php foreach ($products as $p): ?>
                        <option value="<?php echo e($p['product_name']); ?>" data-price="<?php echo e($p['price']); ?>" <?php if ($p['product_name'] === $selectedProduct) echo 'selected'; ?>>
                            <?php echo e($p['product_name'] . ' - ₹' . number_format($p['price'], 2)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Quantity -->
            <div class="input-box">
                <label>Quantity *</label>
                <input type="number" name="quantity" id="quantity" min="1" value="<?php echo e(isset($_POST['quantity']) ? $_POST['quantity'] : '1'); ?>" required>
            </div>

            <!-- Price -->
            <div class="input-box">
                <label>Unit Price (₹) *</label>
                <input type="number" step="0.01" name="price" id="price" value="<?php echo e(isset($_POST['price']) ? $_POST['price'] : ''); ?>" readonly required>
            </div>

            <!-- Total -->
            <div class="input-box">
                <label>Total Amount (₹)</label>
                <input type="number" step="0.01" name="total" id="total" value="<?php echo e(isset($_POST['total']) ? $_POST['total'] : ''); ?>" readonly>
            </div>

            <div style="display:flex; gap:10px; margin-top:20px;">
                <button type="submit" name="save" class="login-btn" style="width:auto; padding:12px 25px;">
                    Save Quotation
                </button>
                <a href="view_quotation.php" class="login-btn" style="width:auto; padding:12px 25px; background:#6b7280;">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
var productElem  = document.getElementById("product");
var quantityElem = document.getElementById("quantity");
var priceElem    = document.getElementById("price");
var totalElem    = document.getElementById("total");

function updatePrice() {
    var selected = productElem.options[productElem.selectedIndex];
    if (selected.value === "") {
        priceElem.value = "";
        totalElem.value = "";
        return;
    }
    priceElem.value = selected.getAttribute("data-price") || "";
    calculateTotal();
}

function calculateTotal() {
    var p = parseFloat(priceElem.value) || 0;
    var q = parseFloat(quantityElem.value) || 0;
    totalElem.value = (p * q).toFixed(2);
}

productElem.onchange = updatePrice;
quantityElem.onkeyup = calculateTotal;
quantityElem.onchange = calculateTotal;

// Initial calculation on load if values exist
if (productElem.value !== "") {
    updatePrice();
}
</script>

</body>
</html>
