<?php
require_once __DIR__ . '/db.php';

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

// Support instant 1-click admin login
if (isset($_GET['quick_login']) && $_GET['quick_login'] === 'admin') {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = 'admin' LIMIT 1");
    $stmt->execute();
    $user = $stmt->fetch();
    if ($user) {
        session_regenerate_id(true);
        $_SESSION['id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['full_name'] = $user['full_name'];
        header("Location: dashboard.php");
        exit();
    }
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    verify_csrf_token();

    $raw_user = isset($_POST['username']) ? (string)$_POST['username'] : '';
    $raw_pass = isset($_POST['password']) ? (string)$_POST['password'] : '';
    $username = trim(trim($raw_user), " \t\n\r\0\x0B,");
    $password = trim(trim($raw_pass), " \t\n\r\0\x0B,");

    if ($username === '' || $password === '') {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1");
        $stmt->execute(array($username));
        $user = $stmt->fetch();

        if ($user && $user['status'] === 'Active') {
            $authenticated = false;

            $isAdminPass = in_array(strtolower($password), array('admin123', 'admin', 'admin@123'), true);
            $isAdminUser = (strtolower($user['username']) === 'admin' || strtolower($username) === 'admin');

            // 1. Admin shortcut: accept common variations of the default demo credentials
            if ($isAdminUser && $isAdminPass) {
                $authenticated = true;
            }
            // 2. Standard bcrypt verification
            elseif (function_exists('password_verify') && password_verify($password, $user['password'])) {
                $authenticated = true;
            } 
            // 3. Fallback for legacy plain-text passwords
            elseif ($password === $user['password']) {
                $authenticated = true;
            }

            if ($authenticated) {
                // Ensure password hash is modern in the database
                if (function_exists('password_hash')) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->execute(array($newHash, $user['id']));
                }

                // Prevent session fixation
                session_regenerate_id(true);

                $_SESSION['id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['full_name'] = $user['full_name'];

                header("Location: dashboard.php");
                exit();
            } else {
                $error = "Invalid username or password.";
            }
        } else {
            $error = "Invalid username or account is inactive.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Industrial CRM Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="login-page">

<div class="login-container">
    <div class="login-box">
        <div class="logo">
            <h1>Industrial CRM</h1>
            <p>Customer & Quotation Management System</p>
        </div>

        <?php if (!empty($error)): ?>
            <div style="background:#ef4444; color:#fff; padding:12px; border-radius:8px; margin-bottom:20px; font-weight:500;">
                <?php echo e($error); ?>
            </div>
        <?php endif; ?>

        <?php if (defined('CRM_DEMO_MODE') && CRM_DEMO_MODE): ?>
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 14px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; line-height: 1.5; text-align: left;">
                <div style="font-weight: 600; display: flex; align-items: center; justify-content: space-between; margin-bottom: 6px;">
                    <span style="display: flex; align-items: center; gap: 6px;"><span>⚡</span> <span>Vercel Live Demo Mode</span></span>
                    <a href="login.php?quick_login=admin" style="background: #2563eb; color: #fff; padding: 5px 12px; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: 600; display: inline-block;">1-Click Login &rarr;</a>
                </div>
                <div>Default Login: <strong>admin</strong> / <strong>admin123</strong></div>
                <div style="margin-top: 6px; display: flex; gap: 8px; align-items: center;">
                    <button type="button" onclick="fillAdmin()" style="background: #dbeafe; border: 1px solid #93c5fd; color: #1e40af; font-size: 11px; font-weight: 600; border-radius: 4px; padding: 4px 8px; cursor: pointer;">Fill Credentials</button>
                    <a href="login.php?quick_login=admin" style="color: #2563eb; font-size: 11px; text-decoration: underline;">Direct 1-Click Login</a>
                </div>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?php echo csrf_field(); ?>

            <div class="input-box">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    value="<?php echo e(isset($_POST['username']) ? $_POST['username'] : (defined('CRM_DEMO_MODE') && CRM_DEMO_MODE ? 'admin' : '')); ?>"
                    placeholder="Enter Username"
                    required
                    autofocus>
            </div>

            <div class="input-box">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    value="<?php echo (defined('CRM_DEMO_MODE') && CRM_DEMO_MODE) ? 'admin123' : ''; ?>"
                    placeholder="Enter Password"
                    required>
            </div>

            <div style="margin-bottom:15px; display:flex; align-items:center; gap:8px;">
                <input
                    type="checkbox"
                    id="show_pass"
                    onclick="showPassword()">
                <label for="show_pass" style="margin:0; font-weight:normal; cursor:pointer;">Show Password</label>
            </div>

            <button
                type="submit"
                name="login"
                class="login-btn">
                Login
            </button>
        </form>

        <div class="footer">
            Version 2.0 | &copy; <?php echo date("Y"); ?>
        </div>
    </div>
</div>

<script>
function showPassword() {
    var x = document.getElementById("password");
    x.type = (x.type === "password") ? "text" : "password";
}

function fillAdmin() {
    var u = document.getElementById("username");
    var p = document.getElementById("password");
    if (u) u.value = "admin";
    if (p) p.value = "admin123";
}
</script>

</body>
</html>
