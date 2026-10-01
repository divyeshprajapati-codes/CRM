<?php
require_once __DIR__ . '/../db.php';
require_login();


// Set headers for CSV download
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=customers_export_' . date('Y-m-d') . '.csv');

// Open the output stream
$output = fopen('php://output', 'w');

// UTF-8 BOM for Excel compatibility
fputs($output, "\xEF\xBB\xBF");

// Write header row
fputcsv($output, ['ID', 'Name', 'Company', 'Phone', 'Email', 'Address / City', 'Created Date']);

// Fetch all customers using PDO
$stmt = $pdo->query("SELECT id, name, company, phone, email, address, created_at FROM customers ORDER BY id DESC");

while ($row = $stmt->fetch()) {
    // Prefix phone with tab to preserve formatting in Excel
    $row['phone'] = "\t" . $row['phone'];
    fputcsv($output, $row);
}

fclose($output);
exit();
