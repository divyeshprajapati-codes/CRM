<?php
/**
 * IndustrialCRM Database Connection
 * Uses PDO with prepared statements for security and PHP 8.2+ compatibility.
 */

// Load environment variables or fall back to default WAMP/local settings
$db_host = getenv('DB_HOST') ?: (isset($_ENV['DB_HOST']) ? $_ENV['DB_HOST'] : 'localhost');
$db_name = getenv('DB_NAME') ?: (isset($_ENV['DB_NAME']) ? $_ENV['DB_NAME'] : 'industrial_crm');
$db_user = getenv('DB_USER') ?: (isset($_ENV['DB_USER']) ? $_ENV['DB_USER'] : 'root');
$db_pass = getenv('DB_PASSWORD') ?: (isset($_ENV['DB_PASSWORD']) ? $_ENV['DB_PASSWORD'] : '');
$db_port = getenv('DB_PORT') ?: (isset($_ENV['DB_PORT']) ? $_ENV['DB_PORT'] : '3306');

$dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    $conn = $pdo; // Alias for consistency
} catch (PDOException $e) {
    // Log technical error safely and display user-friendly message
    error_log("Database Connection Failed: " . $e->getMessage());
    die("Database connection failed. Please ensure the database server is running and configuration is correct.");
}
