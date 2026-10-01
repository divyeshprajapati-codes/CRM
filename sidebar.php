<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root_dir    = realpath(__DIR__);
$current_dir = isset($_SERVER['SCRIPT_FILENAME']) ? realpath(dirname($_SERVER['SCRIPT_FILENAME'])) : '';
$is_subdir   = ($current_dir && strcasecmp($root_dir, $current_dir) !== 0);
$path_prefix = $is_subdir ? "../" : "";
$user_role   = isset($_SESSION['role']) ? $_SESSION['role'] : '';


?>

<div class="sidebar">

    <h2>Industrial CRM</h2>

    <a href="<?php echo $path_prefix; ?>dashboard.php">
        Dashboard
    </a>

    <a href="<?php echo $path_prefix; ?>customer/view_customer.php">
        Customers
    </a>

    <a href="<?php echo $path_prefix; ?>products/view_product.php">
        Products
    </a>

    <a href="<?php echo $path_prefix; ?>quotation/view_quotation.php">
        Quotations
    </a>

    <a href="<?php echo $path_prefix; ?>orders/view_order.php">
        Orders
    </a>

    <a href="<?php echo $path_prefix; ?>invoice/view_invoice.php">
        Invoices
    </a>

    <?php if ($user_role === "Admin"): ?>
    <a href="<?php echo $path_prefix; ?>users/view_user.php">
        User Management
    </a>
    <?php endif; ?>

    <hr style="margin:15px 0; border:0; border-top:1px solid #374151;">

    <a href="<?php echo $path_prefix; ?>logout.php" class="logout">
        Logout
    </a>

</div>

<!-- Floating Dark Mode Button -->
<button onclick="toggleDarkMode(event)" class="theme-toggle-btn">
    🌙 Dark Mode
</button>

<script>
function toggleDarkMode(e) {
    if(e) e.preventDefault();
    document.body.classList.toggle("dark-mode");
    if (document.body.classList.contains("dark-mode")) {
        localStorage.setItem("theme", "dark");
    } else {
        localStorage.setItem("theme", "light");
    }
}

// Apply immediately if already set
if (localStorage.getItem("theme") === "dark") {
    document.body.classList.add("dark-mode");
}
</script>