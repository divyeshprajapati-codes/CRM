<?php
require_once("../db.php");
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM invoices WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message('success', 'Invoice deleted successfully.');
        } catch (PDOException $e) {
            error_log("Delete Invoice Error: " . $e->getMessage());
            set_flash_message('error', 'Failed to delete invoice.');
        }
    }
}

header("Location: view_invoice.php");
exit();