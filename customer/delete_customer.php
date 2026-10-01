<?php
require_once __DIR__ . '/../db.php';
require_login();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message('success', 'Customer deleted successfully.');
        } catch (PDOException $e) {
            error_log("Delete Customer Error: " . $e->getMessage());
            set_flash_message('error', 'Failed to delete customer. The record may be linked to existing quotations or orders.');
        }
    }
}

header("Location: view_customer.php");
exit();