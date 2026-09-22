<?php
require_once("db.php");
require_login();

// Fetch summary metrics using PDO
$totalCustomers  = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$totalProducts   = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalQuotations = (int)$pdo->query("SELECT COUNT(*) FROM quotations")->fetchColumn();
$totalOrders     = (int)$pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$totalInvoices   = (int)$pdo->query("SELECT COUNT(*) FROM invoices")->fetchColumn();
$totalRevenue    = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM invoices")->fetchColumn();

$displayName = (!empty($_SESSION['full_name'])) ? $_SESSION['full_name'] : (isset($_SESSION['username']) ? $_SESSION['username'] : '');
$displayRole = isset($_SESSION['role']) ? $_SESSION['role'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Industrial CRM Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<?php include("sidebar.php"); ?>

<div class="main">

    <div class="topbar">
        <div>
            <h1>Dashboard</h1>
            <p>Welcome back, <b><?php echo e($displayName); ?></b> (<?php echo e($displayRole); ?>)</p>
        </div>
        <div>
            <h2><?php echo date("d M Y"); ?></h2>
        </div>
    </div>

    <?php display_flash_message(); ?>

    <div class="cards">
        <div class="card">
            <h3>Customers</h3>
            <h1><?php echo $totalCustomers; ?></h1>
        </div>

        <div class="card">
            <h3>Products</h3>
            <h1><?php echo $totalProducts; ?></h1>
        </div>

        <div class="card">
            <h3>Quotations</h3>
            <h1><?php echo $totalQuotations; ?></h1>
        </div>

        <div class="card">
            <h3>Orders</h3>
            <h1><?php echo $totalOrders; ?></h1>
        </div>

        <div class="card">
            <h3>Invoices</h3>
            <h1><?php echo $totalInvoices; ?></h1>
        </div>

        <div class="card">
            <h3>Revenue</h3>
            <h1><?php echo format_currency($totalRevenue); ?></h1>
        </div>
    </div>

    <div class="box">
        <h2>Quick Actions</h2>
        <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:15px;">
            <a class="login-btn" style="width:auto; padding:10px 20px;" href="customer/add_customer.php">+ New Customer</a>
            <a class="login-btn" style="width:auto; padding:10px 20px;" href="products/add_product.php">+ New Product</a>
            <a class="login-btn" style="width:auto; padding:10px 20px;" href="quotation/add_quotation.php">+ New Quotation</a>
        </div>
    </div>

    <div class="box">
        <h2>CRM Overview Chart</h2>
        <div style="margin-top:20px; max-height:400px;">
            <canvas id="myChart" height="90"></canvas>
        </div>
    </div>

    <script>
    var customers  = <?php echo $totalCustomers; ?>;
    var products   = <?php echo $totalProducts; ?>;
    var quotations = <?php echo $totalQuotations; ?>;
    var orders     = <?php echo $totalOrders; ?>;
    var invoices   = <?php echo $totalInvoices; ?>;

    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Customers', 'Products', 'Quotations', 'Orders', 'Invoices'],
            datasets: [{
                label: 'Total Count',
                data: [customers, products, quotations, orders, invoices],
                backgroundColor: [
                    '#3b82f6', // Blue for Customers
                    '#10b981', // Green for Products
                    '#f59e0b', // Yellow for Quotations
                    '#8b5cf6', // Purple for Orders
                    '#ef4444'  // Red for Invoices
                ],
                borderRadius: 6,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    </script>

    <div class="box">
        <h2>Industrial CRM Status</h2>
        <table width="100%" border="1" cellpadding="10" style="margin-top:15px;">
            <tr>
                <th>Module</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>Customers</td>
                <td><span style="color:#10b981; font-weight:bold;">Active & Modernized</span></td>
            </tr>
            <tr>
                <td>Products</td>
                <td><span style="color:#10b981; font-weight:bold;">Active & Modernized</span></td>
            </tr>
            <tr>
                <td>Quotations</td>
                <td><span style="color:#10b981; font-weight:bold;">Active & Modernized</span></td>
            </tr>
            <tr>
                <td>Orders</td>
                <td><span style="color:#10b981; font-weight:bold;">Active & Modernized</span></td>
            </tr>
            <tr>
                <td>Invoices</td>
                <td><span style="color:#10b981; font-weight:bold;">Active & Modernized</span></td>
            </tr>
        </table>
    </div>

</div>

</body>
</html>
