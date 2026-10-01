<?php
require_once __DIR__ . '/../db.php';
require_login();


$quotation_id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $quotation_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
} elseif (isset($_GET['id'])) {
    $quotation_id = (int)$_GET['id'];
}

if ($quotation_id <= 0) {
    set_flash_message('error', 'Invalid quotation ID.');
    header("Location: ../quotation/view_quotation.php");
    exit();
}

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Fetch and lock the quotation record
    $stmt = $pdo->prepare("SELECT * FROM quotations WHERE id = ? FOR UPDATE");
    $stmt->execute([$quotation_id]);
    $quotation = $stmt->fetch();

    if (!$quotation) {
        $pdo->rollBack();
        set_flash_message('error', 'Quotation not found.');
        header("Location: ../quotation/view_quotation.php");
        exit();
    }

    // Check if quotation is already converted
    if ($quotation['status'] === 'Converted') {
        $pdo->rollBack();
        set_flash_message('warning', 'This quotation has already been converted to an order.');
        header("Location: view_order.php");
        exit();
    }

    // Generate new Order Number
    $order_no = generate_doc_number($pdo, 'orders', 'ORD', 'id', 4);

    // Insert Order
    $insertStmt = $pdo->prepare("
        INSERT INTO orders (
            quotation_no,
            order_no,
            customer_name,
            product_name,
            quantity,
            price,
            total,
            order_status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')
    ");

    $insertStmt->execute([
        $quotation['quotation_no'],
        $order_no,
        $quotation['customer_name'],
        $quotation['product'],
        $quotation['quantity'],
        $quotation['price'],
        $quotation['total']
    ]);

    // Update Quotation status to Converted
    $updateStmt = $pdo->prepare("UPDATE quotations SET status = 'Converted' WHERE id = ?");
    $updateStmt->execute([$quotation_id]);

    // Commit transaction
    $pdo->commit();

    set_flash_message('success', "Order {$order_no} created successfully from Quotation {$quotation['quotation_no']}.");
    header("Location: view_order.php");
    exit();

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Quotation to Order Conversion Error: " . $e->getMessage());
    set_flash_message('error', 'Failed to convert quotation to order. Please try again.');
    header("Location: ../quotation/view_quotation.php");
    exit();
}