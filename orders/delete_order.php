<?php
require_once("../db.php");
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("SELECT quotation_no FROM orders WHERE id = ? FOR UPDATE");
            $stmt->execute([$id]);
            $order = $stmt->fetch();

            if ($order) {
                // Delete the order
                $delStmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
                $delStmt->execute([$id]);

                // Change associated quotation status back to Approved
                $quoteStmt = $pdo->prepare("UPDATE quotations SET status = 'Approved' WHERE quotation_no = ?");
                $quoteStmt->execute([$order['quotation_no']]);

                $pdo->commit();
                set_flash_message('success', 'Order deleted successfully. Associated quotation reverted to Approved.');
            } else {
                $pdo->rollBack();
                set_flash_message('error', 'Order not found.');
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Delete Order Error: " . $e->getMessage());
            set_flash_message('error', 'Failed to delete order. It may be linked to an invoice.');
        }
    }
}

header("Location: view_order.php");
exit();