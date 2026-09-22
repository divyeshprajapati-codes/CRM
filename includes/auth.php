<?php
/**
 * IndustrialCRM Authentication & Session Guards
 * Provides simple, clean access control functions.
 */

if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    session_start();
}

/**
 * Check if a user is currently logged in.
 */
function is_logged_in() {
    return (!empty($_SESSION['username']) && !empty($_SESSION['id']));
}

/**
 * Ensure the user is logged in; otherwise redirect to the login page.
 */
function require_login() {
    if (!is_logged_in()) {
        $login_path = file_exists("login.php") ? "login.php" : "../login.php";
        header("Location: " . $login_path);
        exit();
    }
}

/**
 * Ensure the current user has Administrator privileges.
 */
function require_admin() {
    require_login();
    if (empty($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
        $dashboard_path = file_exists("dashboard.php") ? "dashboard.php" : "../dashboard.php";
        header("Location: " . $dashboard_path);
        exit("Access Denied: Administrator role required.");
    }
}

/**
 * Get the currently logged-in user data from session.
 */
function current_user() {
    return array(
        'id'        => isset($_SESSION['id']) ? $_SESSION['id'] : null,
        'username'  => isset($_SESSION['username']) ? $_SESSION['username'] : '',
        'full_name' => isset($_SESSION['full_name']) ? $_SESSION['full_name'] : '',
        'role'      => isset($_SESSION['role']) ? $_SESSION['role'] : ''
    );
}
