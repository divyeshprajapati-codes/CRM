<?php
require_once("../db.php");
require_login();

$id = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf_token();
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
} elseif (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
}

if ($id > 0) {
    try {
        $stmt = $pdo->prepare("UPDATE quotations SET status = 'Approved' WHERE id = ?");
        $stmt->execute([$id]);
        set_flash_message('success', 'Quotation approved successfully.');
    } catch (PDOException $e) {
        error_log("Approve Quotation Error: " . $e->getMessage());
        set_flash_message('error', 'Failed to approve quotation.');
    }
}

header("Location: view_quotation.php");
exit();