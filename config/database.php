<?php
/**
 * IndustrialCRM Database Configuration
 * -------------------------------------------------------------
 * INSTRUCTIONS FOR SHARED HOSTING:
 * 1. Create a MySQL database in your hosting control panel (e.g. cPanel / Plesk).
 * 2. Create a MySQL user and set a password.
 * 3. Assign the user to the database with ALL PRIVILEGES.
 * 4. Enter your hosting database credentials below:
 * -------------------------------------------------------------
 */

// Automatically detect if running locally on WAMP or online on hosting
$is_localhost = (
    (!empty($_SERVER['HTTP_HOST']) && (strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false)) ||
    (!empty($_SERVER['SERVER_NAME']) && (strpos($_SERVER['SERVER_NAME'], 'localhost') !== false || strpos($_SERVER['SERVER_NAME'], '127.0.0.1') !== false)) ||
    (php_sapi_name() === 'cli')
);

if ($is_localhost) {
    // -------------------------------------------------------------
    // 1. LOCAL WAMP CONFIGURATION (When running on your computer)
    // -------------------------------------------------------------
    $db_host = "localhost";
    $db_name = "industrial_crm";
    $db_user = "root";
    $db_pass = "";
    $db_port = "3306";
} else {
    // -------------------------------------------------------------
    // 2. ONLINE HOSTING CONFIGURATION (When running on InfinityFree)
    // -------------------------------------------------------------
    $db_host = "sql300.infinityfree.com";          // InfinityFree MySQL Hostname
    $db_name = "if0_42987683_businessgrow42_crm"; // Your InfinityFree Database Name
    $db_user = "if0_42987683";                     // Your InfinityFree Database Username
    $db_pass = "divyesh2006";                      // Your InfinityFree hosting password
    $db_port = "3306";                             // MySQL Port
}

// Fallback to environment variables if configured in the server environment
if (getenv('DB_HOST'))     $db_host = getenv('DB_HOST');
if (getenv('DB_NAME'))     $db_name = getenv('DB_NAME');
if (getenv('DB_USER'))     $db_user = getenv('DB_USER');
if (getenv('DB_PASSWORD')) $db_pass = getenv('DB_PASSWORD');
if (getenv('DB_PORT'))     $db_port = getenv('DB_PORT');


$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    $conn = $pdo; // Alias for backward compatibility
} catch (PDOException $e) {
    // If localhost failed due to Linux Unix-socket location, try TCP fallback on 127.0.0.1
    if (($db_host === 'localhost' || $db_host === '127.0.0.1') && strpos($e->getMessage(), '2002') !== false) {
        try {
            $fallback_host = ($db_host === 'localhost') ? '127.0.0.1' : 'localhost';
            $fallback_dsn = "mysql:host={$fallback_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
            $pdo = new PDO($fallback_dsn, $db_user, $db_pass, $options);
            $conn = $pdo;
        } catch (PDOException $e2) {
            $e = $e2; // Keep the latest error
        }
    }
}

if (!isset($pdo)) {
    // Log technical error details to server error log
    error_log("Database Connection Error: " . (isset($e) ? $e->getMessage() : 'Unknown error'));

    // Provide a clear, actionable error message to help during hosting setup
    $error_details = htmlspecialchars(isset($e) ? $e->getMessage() : '', ENT_QUOTES, 'UTF-8');
    die("
    <div style='font-family: Arial, sans-serif; max-width: 680px; margin: 40px auto; padding: 25px; border: 1px solid #f87171; border-radius: 8px; background: #fef2f2; color: #991b1b;'>
        <h2 style='margin-top:0; color:#b91c1c;'>Database Connection Failed</h2>
        <p><strong>Error details:</strong> <code>{$error_details}</code></p>
        <hr style='border:0; border-top:1px solid #fecaca; margin:15px 0;'>
        <p><strong>How to fix this in <code>config/database.php</code>:</strong></p>
        <ol style='padding-left: 20px; line-height: 1.8;'>
            <li><strong>MySQL Hostname:</strong> On free/shared hosting (e.g. InfinityFree / cPanel), check your control panel's <em>MySQL Details</em> for the exact <strong>MySQL Hostname</strong> (e.g. <code>sql105.infinityfree.com</code> or <code>127.0.0.1</code>).</li>
            <li><strong>Database Credentials:</strong> Replace <code>YOUR_HOSTING_DATABASE_NAME</code>, <code>YOUR_HOSTING_DATABASE_USERNAME</code>, and <code>YOUR_HOSTING_DATABASE_PASSWORD</code> with your actual hosting details.</li>
            <li><strong>Import SQL:</strong> Make sure you have imported <code>sql/database.sql</code> into your database using phpMyAdmin.</li>
        </ol>
    </div>
    ");
}


