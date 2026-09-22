<?php
/**
 * IndustrialCRM Common Utility Functions
 * Simple, beginner-friendly helper functions for security, escaping, and formatting.
 */

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

/**
 * Safely escape string output to prevent Cross-Site Scripting (XSS).
 */
function e($string) {
    if ($string === null) {
        $string = '';
    }
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate cryptographically secure random token.
 */
function secure_random_token($length = 32) {
    if (function_exists('random_bytes')) {
        return bin2hex(random_bytes($length));
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        return bin2hex(openssl_random_pseudo_bytes($length));
    } else {
        return md5(uniqid(mt_rand(), true));
    }
}

/**
 * Generate or retrieve the current CSRF token from the session.
 */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = secure_random_token(32);
    }
    return $_SESSION['csrf_token'];
}

/**
 * Render a hidden HTML input containing the CSRF token.
 */
function csrf_field() {
    $token = csrf_token();
    return '<input type="hidden" name="csrf_token" value="' . e($token) . '">';
}

/**
 * Constant-time string comparison for token validation.
 */
function safe_hash_equals($known, $user) {
    if (function_exists('hash_equals')) {
        return hash_equals($known, $user);
    }
    if (strlen($known) !== strlen($user)) {
        return false;
    }
    $res = 0;
    for ($i = 0; $i < strlen($known); $i++) {
        $res |= ord($known[$i]) ^ ord($user[$i]);
    }
    return ($res === 0);
}

/**
 * Verify the submitted CSRF token from a POST request.
 */
function verify_csrf_token() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $submitted_token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : '';
        $session_token   = isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : '';
        
        if (empty($submitted_token) || empty($session_token) || !safe_hash_equals($session_token, $submitted_token)) {
            die("Invalid or expired security token (CSRF). Please refresh the page and try again.");
        }
    }
}

/**
 * Format a number as currency (with fallback for null/empty).
 */
function format_currency($amount, $decimals = 2) {
    $numeric = is_numeric($amount) ? (float)$amount : 0.0;
    return '₹' . number_format($numeric, $decimals);
}

/**
 * Generate sequential formatted document numbers (e.g. QT0001, ORD0001, INV0001).
 */
function generate_doc_number($pdo, $table, $prefix, $id_col = 'id', $pad_length = 4) {
    $allowed_tables = array('quotations', 'orders', 'invoices', 'customers', 'products', 'users');
    if (!in_array($table, $allowed_tables, true)) {
        throw new InvalidArgumentException("Invalid table name for document numbering.");
    }

    $stmt = $pdo->query("SELECT MAX(`{$id_col}`) AS last_id FROM `{$table}`");
    $row = $stmt->fetch();
    $next_id = ($row && !empty($row['last_id'])) ? ((int)$row['last_id'] + 1) : 1;
    return $prefix . str_pad((string)$next_id, $pad_length, '0', STR_PAD_LEFT);
}

/**
 * Set a one-time flash notification message.
 */
function set_flash_message($type, $message) {
    $_SESSION['flash_message'] = array(
        'type'    => $type, // 'success', 'error', 'info', 'warning'
        'message' => $message
    );
}

/**
 * Display and clear the flash notification message if present.
 */
function display_flash_message() {
    if (!empty($_SESSION['flash_message'])) {
        $msg = $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        $color = ($msg['type'] === 'success') ? '#10b981' : (($msg['type'] === 'error') ? '#ef4444' : '#3b82f6');
        echo '<div style="padding:12px 18px; margin-bottom:20px; border-radius:8px; background:' . $color . '; color:#fff; font-weight:500;">' . e($msg['message']) . '</div>';
    }
}
