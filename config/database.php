<?php
/**
 * IndustrialCRM Centralized Database Configuration & Connection
 * -----------------------------------------------------------------
 * Supports:
 * - Local WAMP / XAMPP development
 * - Cloud MySQL (TiDB Cloud, Aiven, Railway, AWS RDS, PlanetScale, etc.)
 * - Standard Vercel Serverless environment variables & DATABASE_URL
 * - Resilient SQLite Demo Fallback for instant live preview on Vercel
 * - InfinityFree / Shared cPanel hosting (when hosted directly on their servers)
 * -----------------------------------------------------------------
 */

// Helper to safely retrieve environment variables across $_ENV, $_SERVER, and getenv()
if (!function_exists('crm_get_env')) {
    function crm_get_env($key, $default = null) {
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
        $val = getenv($key);
        return ($val !== false && $val !== '') ? $val : $default;
    }
}

// Custom PDO wrapper to ensure seamless SQLite compatibility (strips MySQL-specific FOR UPDATE when in demo mode)
if (!class_exists('CrmPDO')) {
    class CrmPDO extends PDO {
        #[\ReturnTypeWillChange]
        public function prepare($statement, $driver_options = array()) {
            if ($this->getAttribute(PDO::ATTR_DRIVER_NAME) === 'sqlite') {
                $statement = preg_replace('/\s+FOR\s+UPDATE\b/i', '', $statement);
            }
            return parent::prepare($statement, $driver_options);
        }
    }
}

// Environment Detection
$host_header = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
$server_name = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';

$vercel_env = crm_get_env('VERCEL');
$is_vercel = (
    $vercel_env !== null ||
    (!empty($host_header) && strpos($host_header, 'vercel.app') !== false) ||
    crm_get_env('VERCEL_URL') !== null
);

$is_localhost = (
    (!empty($host_header) && (strpos($host_header, 'localhost') !== false || strpos($host_header, '127.0.0.1') !== false)) ||
    (!empty($server_name) && (strpos($server_name, 'localhost') !== false || strpos($server_name, '127.0.0.1') !== false)) ||
    (php_sapi_name() === 'cli' && !$is_vercel)
);

// Check for single DATABASE_URL or MYSQL_URL connection string
$db_url = crm_get_env('DATABASE_URL') ?: crm_get_env('MYSQL_URL');
$parsed_url = $db_url ? parse_url($db_url) : null;

// Determine environment-based database defaults
if ($is_localhost) {
    // 1. LOCAL WAMP CONFIGURATION
    $db_host = "localhost";
    $db_name = "industrial_crm";
    $db_user = "root";
    $db_pass = "";
    $db_port = "3306";
    $db_ssl  = false;
} elseif ($is_vercel) {
    // 2. VERCEL SERVERLESS ENVIRONMENT
    // Note: InfinityFree MySQL hostnames (e.g. sql300.infinityfree.com) cannot be resolved from Vercel
    // because InfinityFree blocks remote MySQL. Remote cloud MySQL should be configured via Vercel env vars.
    $db_host = crm_get_env('DB_HOST', '');
    $db_name = crm_get_env('DB_NAME', crm_get_env('DB_DATABASE', ''));
    $db_user = crm_get_env('DB_USER', crm_get_env('DB_USERNAME', ''));
    $db_pass = crm_get_env('DB_PASSWORD', crm_get_env('DB_PASS', ''));
    $db_port = crm_get_env('DB_PORT', '3306');
    $db_ssl  = crm_get_env('DB_SSL', false);
} else {
    // 3. SHARED HOSTING (When running directly on InfinityFree or cPanel)
    $db_host = "sql300.infinityfree.com";
    $db_name = "if0_42987683_businessgrow42_crm";
    $db_user = "if0_42987683";
    $db_pass = "divyesh2006";
    $db_port = "3306";
    $db_ssl  = false;
}

// Override with URL parameters if provided
if ($parsed_url) {
    if (!empty($parsed_url['host'])) $db_host = $parsed_url['host'];
    if (!empty($parsed_url['port'])) $db_port = (string)$parsed_url['port'];
    if (!empty($parsed_url['user'])) $db_user = $parsed_url['user'];
    if (!empty($parsed_url['pass'])) $db_pass = $parsed_url['pass'];
    if (!empty($parsed_url['path'])) $db_name = ltrim($parsed_url['path'], '/');
    if (!empty($parsed_url['query'])) {
        parse_str($parsed_url['query'], $q);
        if (isset($q['sslmode']) || isset($q['ssl'])) $db_ssl = true;
    }
}

// Environment variables take highest priority
$db_host = crm_get_env('DB_HOST', $db_host);
$db_name = crm_get_env('DB_NAME', crm_get_env('DB_DATABASE', $db_name));
$db_user = crm_get_env('DB_USER', crm_get_env('DB_USERNAME', $db_user));
$db_pass = crm_get_env('DB_PASSWORD', crm_get_env('DB_PASS', $db_pass));
$db_port = crm_get_env('DB_PORT', $db_port);
if (crm_get_env('DB_SSL') !== null) {
    $db_ssl = filter_var(crm_get_env('DB_SSL'), FILTER_VALIDATE_BOOLEAN);
}

// Configure PDO MySQL options
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

if (extension_loaded('pdo_mysql') && defined('PDO::MYSQL_ATTR_INIT_COMMAND')) {
    $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES utf8mb4";
}

// Enable SSL encryption for cloud MySQL providers (TiDB Cloud, Aiven, PlanetScale, etc.)
$is_cloud_mysql = (
    $db_ssl ||
    $db_port == '4000' ||
    strpos($db_host, 'tidbcloud.com') !== false ||
    strpos($db_host, 'aivencloud.com') !== false ||
    strpos($db_host, 'planetscale.net') !== false
);

if ($is_cloud_mysql && defined('PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT')) {
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
    $ca_cert = crm_get_env('MYSQL_ATTR_SSL_CA');
    if ($ca_cert && file_exists($ca_cert) && defined('PDO::MYSQL_ATTR_SSL_CA')) {
        $options[PDO::MYSQL_ATTR_SSL_CA] = $ca_cert;
        $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }
}

$pdo = null;
$conn = null;
$connection_error = null;

// Check if an external InfinityFree host is configured while running on Vercel
// (InfinityFree free tier explicitly blocks remote MySQL connections from outside their hosting)
$is_infinityfree_on_vercel = (
    $is_vercel &&
    strpos($db_host, 'infinityfree.com') !== false
);

// Attempt MySQL Connection if host and db name are present and not impossible
if (!empty($db_host) && !empty($db_name) && !$is_infinityfree_on_vercel && extension_loaded('pdo_mysql')) {
    try {
        $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
        $pdo = new CrmPDO($dsn, $db_user, $db_pass, $options);
        $conn = $pdo;
    } catch (PDOException $e) {
        $connection_error = $e;
        // If localhost failed due to Linux socket vs TCP, try 127.0.0.1
        if (($db_host === 'localhost' || $db_host === '127.0.0.1') && strpos($e->getMessage(), '2002') !== false) {
            try {
                $fallback_host = ($db_host === 'localhost') ? '127.0.0.1' : 'localhost';
                $fallback_dsn = "mysql:host={$fallback_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
                $pdo = new CrmPDO($fallback_dsn, $db_user, $db_pass, $options);
                $conn = $pdo;
                $connection_error = null;
            } catch (PDOException $e2) {
                $connection_error = $e2;
            }
        }
    }
}

// Resilient SQLite Fallback for Vercel / Cloud Demo Mode
// If MySQL is not configured or fails to connect on Vercel, boot SQLite so the app is immediately usable!
$sqlite_allowed = ($is_vercel || crm_get_env('APP_ENV') === 'development' || crm_get_env('DEMO_MODE') === 'true');

if (!$pdo && $sqlite_allowed && extension_loaded('pdo_sqlite')) {
    try {
        $tmp_dir = sys_get_temp_dir();
        $sqlite_file = $tmp_dir . '/industrial_crm_demo.sqlite';
        $needs_init = !file_exists($sqlite_file) || filesize($sqlite_file) === 0;

        $sqlite_pdo = new CrmPDO("sqlite:" . $sqlite_file);
        $sqlite_pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sqlite_pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        if ($needs_init) {
            $sqlite_pdo->exec("
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    full_name TEXT NOT NULL,
                    username TEXT NOT NULL UNIQUE,
                    password TEXT NOT NULL,
                    role TEXT NOT NULL DEFAULT 'Sales',
                    status TEXT NOT NULL DEFAULT 'Active',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                CREATE TABLE IF NOT EXISTS customers (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    name TEXT DEFAULT NULL,
                    company TEXT DEFAULT NULL,
                    phone TEXT DEFAULT NULL,
                    email TEXT DEFAULT NULL,
                    address TEXT DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                CREATE TABLE IF NOT EXISTS products (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    product_name TEXT DEFAULT NULL,
                    category TEXT DEFAULT NULL,
                    price REAL DEFAULT NULL,
                    unit TEXT DEFAULT NULL,
                    description TEXT DEFAULT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                CREATE TABLE IF NOT EXISTS quotations (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    quotation_no TEXT DEFAULT NULL,
                    customer_name TEXT DEFAULT NULL,
                    product TEXT DEFAULT NULL,
                    quantity INTEGER DEFAULT NULL,
                    price REAL DEFAULT NULL,
                    total REAL DEFAULT NULL,
                    status TEXT DEFAULT 'Pending',
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                CREATE TABLE IF NOT EXISTS orders (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    quotation_no TEXT NOT NULL,
                    order_no TEXT NOT NULL,
                    customer_name TEXT NOT NULL,
                    product_name TEXT NOT NULL,
                    quantity INTEGER NOT NULL,
                    price REAL NOT NULL,
                    total REAL NOT NULL,
                    order_status TEXT NOT NULL DEFAULT 'Pending',
                    order_date DATETIME DEFAULT CURRENT_TIMESTAMP
                );
                CREATE TABLE IF NOT EXISTS invoices (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    invoice_no TEXT DEFAULT NULL,
                    order_no TEXT DEFAULT NULL,
                    customer_name TEXT DEFAULT NULL,
                    product_name TEXT DEFAULT NULL,
                    quantity INTEGER DEFAULT NULL,
                    price REAL DEFAULT NULL,
                    total REAL DEFAULT NULL,
                    payment_status TEXT DEFAULT 'Unpaid',
                    invoice_date DATETIME DEFAULT CURRENT_TIMESTAMP
                );
            ");

            // Insert default Admin (admin / admin123)
            $adminHash = '$2y$10$5W4is3aT3MGgsP.x.hBODODprEjKwD0od3Laz8zfZdP7Kb3W/oMFe';
            $stmt = $sqlite_pdo->prepare("INSERT OR IGNORE INTO users (id, full_name, username, password, role, status) VALUES (1, 'Administrator', 'admin', ?, 'Admin', 'Active')");
            $stmt->execute([$adminHash]);
            $sqlite_pdo->prepare("UPDATE users SET password = ? WHERE username = 'admin'")->execute([$adminHash]);

            // Insert initial demonstration records
            $sqlite_pdo->exec("
                INSERT OR IGNORE INTO customers (id, name, company, phone, email, address) VALUES (1, 'Rajesh Patel', 'Patel Engineering Works', '+91 9876543210', 'rajesh@pateleng.com', 'GIDC Vatva, Ahmedabad');
                INSERT OR IGNORE INTO customers (id, name, company, phone, email, address) VALUES (2, 'Amit Shah', 'Precision Fasteners Ltd', '+91 9825012345', 'amit@precisionfasteners.in', 'Makarpura GIDC, Vadodara');

                INSERT OR IGNORE INTO products (id, product_name, category, price, unit, description) VALUES (1, 'M12 High Tensile Hex Bolt Grade 8.8', 'Fasteners', 45.00, 'Piece', 'High tensile hex bolt DIN 933 black finish');
                INSERT OR IGNORE INTO products (id, product_name, category, price, unit, description) VALUES (2, 'M16 Stainless Steel Nut SS 304', 'Fasteners', 28.50, 'Piece', 'Stainless steel hex nut grade 304 DIN 934');
                INSERT OR IGNORE INTO products (id, product_name, category, price, unit, description) VALUES (3, 'Spring Washer M12 Heavy Duty', 'Washers', 6.50, 'Piece', 'Spring steel lock washer DIN 127');

                INSERT OR IGNORE INTO quotations (id, quotation_no, customer_name, product, quantity, price, total, status) VALUES (1, 'QUO-0001', 'Patel Engineering Works', 'M12 High Tensile Hex Bolt Grade 8.8', 500, 45.00, 22500.00, 'Approved');
                INSERT OR IGNORE INTO quotations (id, quotation_no, customer_name, product, quantity, price, total, status) VALUES (2, 'QUO-0002', 'Precision Fasteners Ltd', 'M16 Stainless Steel Nut SS 304', 1000, 28.50, 28500.00, 'Pending');

                INSERT OR IGNORE INTO orders (id, quotation_no, order_no, customer_name, product_name, quantity, price, total, order_status) VALUES (1, 'QUO-0001', 'ORD-0001', 'Patel Engineering Works', 'M12 High Tensile Hex Bolt Grade 8.8', 500, 45.00, 22500.00, 'In Progress');

                INSERT OR IGNORE INTO invoices (id, invoice_no, order_no, customer_name, product_name, quantity, price, total, payment_status) VALUES (1, 'INV-0001', 'ORD-0001', 'Patel Engineering Works', 'M12 High Tensile Hex Bolt Grade 8.8', 500, 45.00, 22500.00, 'Paid');
            ");
        }

        // Always guarantee Admin user exists with valid hash even in pre-existing /tmp files
        $adminHash = '$2y$10$5W4is3aT3MGgsP.x.hBODODprEjKwD0od3Laz8zfZdP7Kb3W/oMFe';
        $sqlite_pdo->prepare("INSERT OR REPLACE INTO users (id, full_name, username, password, role, status) VALUES (1, 'Administrator', 'admin', ?, 'Admin', 'Active')")->execute([$adminHash]);

        $pdo = $sqlite_pdo;
        $conn = $pdo;
        if (!defined('CRM_DEMO_MODE')) {
            define('CRM_DEMO_MODE', true);
        }
    } catch (Exception $sqle) {
        error_log("SQLite Fallback Error: " . $sqle->getMessage());
    }
}

// If all connection attempts failed, render an actionable diagnostic setup page
if (!$pdo) {
    $err_msg = $connection_error ? $connection_error->getMessage() : ($is_infinityfree_on_vercel ? 'InfinityFree MySQL (sql300.infinityfree.com) does not permit remote connections from Vercel.' : 'Database credentials missing or invalid.');
    error_log("Database Connection Error: " . $err_msg);

    $safe_err = htmlspecialchars($err_msg, ENT_QUOTES, 'UTF-8');
    $safe_host = htmlspecialchars($db_host ?: '(Not set)', ENT_QUOTES, 'UTF-8');

    die("
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Database Setup Required - Industrial CRM</title>
        <style>
            :root {
                --primary: #2563eb;
                --bg: #0f172a;
                --card-bg: #1e293b;
                --border: #334155;
                --text: #f8fafc;
                --text-muted: #94a3b8;
                --danger: #ef4444;
                --warning: #f59e0b;
                --code-bg: #0b1120;
            }
            * { box-sizing: border-box; margin:0; padding:0; }
            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
                background: var(--bg);
                color: var(--text);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
            }
            .setup-card {
                background: var(--card-bg);
                border: 1px solid var(--border);
                border-radius: 14px;
                max-width: 720px;
                width: 100%;
                padding: 32px;
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4);
            }
            .header-tag {
                display: inline-block;
                padding: 4px 10px;
                background: rgba(239, 68, 68, 0.15);
                color: #f87171;
                border: 1px solid rgba(239, 68, 68, 0.3);
                border-radius: 6px;
                font-size: 12px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-bottom: 12px;
            }
            h1 { font-size: 24px; font-weight: 700; margin-bottom: 8px; color: #fff; }
            .subtitle { color: var(--text-muted); font-size: 14px; line-height: 1.5; margin-bottom: 24px; }
            .alert-box {
                background: rgba(239, 68, 68, 0.1);
                border-left: 4px solid var(--danger);
                padding: 14px 16px;
                border-radius: 6px;
                margin-bottom: 24px;
                font-size: 13px;
                line-height: 1.6;
            }
            .alert-box strong { color: #fca5a5; }
            .code-block {
                background: var(--code-bg);
                border: 1px solid var(--border);
                border-radius: 8px;
                padding: 14px;
                font-family: 'Consolas', 'Monaco', monospace;
                font-size: 13px;
                color: #38bdf8;
                word-break: break-all;
                margin: 10px 0;
            }
            h2 { font-size: 17px; font-weight: 600; margin: 20px 0 10px 0; color: #e2e8f0; }
            ol { padding-left: 20px; color: var(--text-muted); font-size: 14px; line-height: 1.8; }
            ol li strong { color: #f8fafc; }
            .env-table {
                width: 100%;
                border-collapse: collapse;
                margin: 14px 0;
                font-size: 13px;
            }
            .env-table th, .env-table td {
                padding: 10px 12px;
                border: 1px solid var(--border);
                text-align: left;
            }
            .env-table th { background: rgba(255,255,255,0.04); color: #cbd5e1; }
            .env-table td code { color: #38bdf8; }
            .btn {
                display: inline-block;
                padding: 10px 18px;
                background: var(--primary);
                color: #fff;
                font-weight: 600;
                border-radius: 8px;
                text-decoration: none;
                margin-top: 15px;
                transition: background 0.2s;
            }
            .btn:hover { background: #1d4ed8; }
        </style>
    </head>
    <body>
        <div class='setup-card'>
            <div class='header-tag'>Vercel Deployment Notice</div>
            <h1>Database Configuration Required</h1>
            <p class='subtitle'>Your Industrial CRM code is running on Vercel Serverless, but cannot reach the configured database.</p>

            <div class='alert-box'>
                <strong>Root Cause:</strong> InfinityFree (<code>sql300.infinityfree.com</code>) does <u>not</u> allow remote MySQL connections from outside their hosting servers. Vercel runs in the AWS cloud and cannot connect to InfinityFree's internal database.<br>
                <strong>System Error:</strong> <code>{$safe_err}</code>
            </div>

            <h2>How to fix in 2 minutes (100% Free):</h2>
            <ol>
                <li><strong>Create a Free Remote MySQL Database:</strong>
                    Sign up at <a href='https://tidbcloud.com' target='_blank' style='color:#38bdf8;'>TiDB Cloud (Serverless)</a> or <a href='https://aiven.io' target='_blank' style='color:#38bdf8;'>Aiven</a>. Both provide 100% free MySQL clusters with no credit card required.
                </li>
                <li><strong>Import Database Schema:</strong>
                    In your database console (SQL Editor), paste and run the contents of <code>sql/database.sql</code>.
                </li>
                <li><strong>Add Environment Variables in Vercel:</strong>
                    Go to your <strong>Vercel Dashboard &rarr; Project &rarr; Settings &rarr; Environment Variables</strong> and add:
                    <table class='env-table'>
                        <thead><tr><th>Variable Key</th><th>Example Value</th></tr></thead>
                        <tbody>
                            <tr><td><code>DB_HOST</code></td><td>gateway01.us-east-1.prod.aws.tidbcloud.com</td></tr>
                            <tr><td><code>DB_PORT</code></td><td>4000 (TiDB) or 3306</td></tr>
                            <tr><td><code>DB_NAME</code></td><td>industrial_crm</td></tr>
                            <tr><td><code>DB_USER</code></td><td>your_username.root</td></tr>
                            <tr><td><code>DB_PASSWORD</code></td><td>your_secure_password</td></tr>
                            <tr><td><code>DB_SSL</code></td><td>true</td></tr>
                        </tbody>
                    </table>
                </li>
                <li><strong>Redeploy:</strong> In Vercel, trigger a redeployment. The CRM will connect to your cloud database automatically!</li>
            </ol>

            <a href='javascript:location.reload()' class='btn'>🔄 Retry Connection</a>
        </div>
    </body>
    </html>
    ");
}
