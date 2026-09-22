<?php
/**
 * IndustrialCRM System Health Check & Diagnostic Script
 */

header('Content-Type: text/plain; charset=utf-8');

echo "====================================================\n";
echo " IndustrialCRM System Health & Diagnostics\n";
echo "====================================================\n\n";

// 1. PHP Version
$serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'CLI';
echo "1. PHP Environment:\n";
echo "   - PHP Version: " . PHP_VERSION . "\n";
echo "   - Server Software: " . $serverSoftware . "\n";
echo "   - PDO Extension: " . (extension_loaded('pdo') ? 'Loaded (OK)' : 'MISSING (ERROR)') . "\n";
echo "   - PDO MySQL Driver: " . (extension_loaded('pdo_mysql') ? 'Loaded (OK)' : 'MISSING (ERROR)') . "\n";
echo "   - OpenSSL: " . (extension_loaded('openssl') ? 'Loaded (OK)' : 'MISSING (ERROR)') . "\n\n";

// 2. Database Connection
echo "2. Database Connectivity:\n";
try {
    require_once __DIR__ . '/db.php';
    echo "   - Connection: Successfully connected via PDO (OK)\n";
    $driverName = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $serverVersion = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);
    echo "   - Driver: {$driverName}\n";
    echo "   - Server Version: {$serverVersion}\n";
} catch (Exception $e) {
    echo "   - Connection FAILED: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Database Tables Verification
echo "\n3. Database Tables Check:\n";
$tables = array('users', 'customers', 'products', 'quotations', 'orders', 'invoices');
foreach ($tables as $t) {
    try {
        $count = $pdo->query("SELECT COUNT(*) FROM `{$t}`")->fetchColumn();
        echo "   - Table `{$t}`: OK ({$count} records)\n";
    } catch (Exception $e) {
        echo "   - Table `{$t}`: FAILED (" . $e->getMessage() . ")\n";
    }
}

// 4. Authentication Check
echo "\n4. User Accounts Check:\n";
$users = $pdo->query("SELECT id, username, role, status, password FROM users")->fetchAll();
foreach ($users as $u) {
    $isHashed = (strpos($u['password'], '$2y$') === 0 || strpos($u['password'], '$argon2') === 0);
    $hashStatus = $isHashed ? "Securely Hashed (OK)" : "Legacy Plaintext (Will auto-upgrade on login)";
    echo "   - User #{$u['id']} '{$u['username']}' [{$u['role']}, {$u['status']}]: {$hashStatus}\n";
}

echo "\n====================================================\n";
echo " All diagnostics executed successfully!\n";
echo "====================================================\n";