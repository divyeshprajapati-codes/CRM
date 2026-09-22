<?php
require_once("../db.php");
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();

    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

    if ($id > 0) {
        try {
            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message('success', 'Product deleted successfully.');
        } catch (PDOException $e) {
            error_log("Delete Product Error: " . $e->getMessage());
            set_flash_message('error', 'Failed to delete product. The product may be used in quotations or orders.');
        }
    }
}

header("Location: view_product.php");
exit();