<?php
require_once __DIR__ . '/../db.php';
require_login();


$order_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $order_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
} elseif (isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];
}

if ($order_id <= 0) {
    set_flash_message('error', 'Invalid order ID.');
    header("Location: ../orders/view_order.php");
    exit();
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? FOR UPDATE");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order) {
        $pdo->rollBack();
        set_flash_message('error', 'Order not found.');
        header("Location: ../orders/view_order.php");
        exit();
    }

    // Check if invoice already exists for this order
    $checkStmt = $pdo->prepare("SELECT invoice_no FROM invoices WHERE order_no = ?");
    $checkStmt->execute([$order['order_no']]);
    $existingInvoice = $checkStmt->fetch();

    if ($existingInvoice) {
        $pdo->rollBack();
        set_flash_message('warning', "Invoice {$existingInvoice['invoice_no']} has already been generated for Order {$order['order_no']}.");
        header("Location: view_invoice.php");
        exit();
    }

    // Generate Invoice Number
    $invoice_no = generate_doc_number($pdo, 'invoices', 'INV', 'id', 4);

    // Insert Invoice
    $insertStmt = $pdo->prepare("
        INSERT INTO invoices (
            invoice_no,
            order_no,
            customer_name,
            product_name,
            quantity,
            price,
            total,
            payment_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Unpaid')
    ");

    $insertStmt->execute([
        $invoice_no,
        $order['order_no'],
        $order['customer_name'],
        $order['product_name'],
        $order['quantity'],
        $order['price'],
        $order['total']
    ]);

    $pdo->commit();

    set_flash_message('success', "Invoice {$invoice_no} generated successfully for Order {$order['order_no']}.");
    header("Location: view_invoice.php");
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Generate Invoice Error: " . $e->getMessage());
    set_flash_message('error', 'Failed to generate invoice. Please try again.');
    header("Location: ../orders/view_order.php");
    exit();
}