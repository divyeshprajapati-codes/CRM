<?php
require_once __DIR__ . '/db.php';


// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header("Location: dashboard.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    verify_csrf_token();

    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';

    if ($username === '' || $password === '') {
        $error = "Please enter both username and password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute(array($username));
        $user = $stmt->fetch();

        if ($user && $user['status'] === 'Active') {
            $authenticated = false;

            // Check if password matches modern hash
            if (function_exists('password_verify') && password_verify($password, $user['password'])) {
                $authenticated = true;
            } 
            // Fallback for legacy plain-text passwords: auto-upgrade to secure hash
            elseif ($password === $user['password']) {
                if (function_exists('password_hash')) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $updateStmt->execute(array($newHash, $user['id']));
                }
                $authenticated = true;
            }

            if ($authenticated) {
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
            <div style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; padding: 12px 14px; border-radius: 8px; margin-bottom: 20px; font-size: 13px; line-height: 1.5; text-align: left;">
                <div style="font-weight: 600; display: flex; align-items: center; gap: 6px; margin-bottom: 4px;">
                    <span>⚡</span> <span>Vercel Live Demo Mode</span>
                </div>
                <div>Default Login: <strong>admin</strong> / <strong>admin123</strong></div>
                <div style="color: #64748b; font-size: 11px; margin-top: 4px;">To connect persistent MySQL, add cloud database credentials in Vercel Settings.</div>
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
                    value="<?php echo e(isset($_POST['username']) ? $_POST['username'] : ''); ?>"
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
</script>

</body>
</html>
