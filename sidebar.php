<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root_dir    = realpath(__DIR__);
$current_dir = isset($_SERVER['SCRIPT_FILENAME']) ? realpath(dirname($_SERVER['SCRIPT_FILENAME'])) : '';
$is_subdir   = ($current_dir && strcasecmp($root_dir, $current_dir) !== 0);
$path_prefix = $is_subdir ? "../" : "";
$user_role   = isset($_SESSION['role']) ? $_SESSION['role'] : '';

// Helper to determine active menu link
$current_script = basename(isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '');
$current_uri    = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
function is_active_nav($pattern, $uri) {
    return (strpos($uri, $pattern) !== false) ? 'active-nav' : '';
}
?>

<!-- Mobile Header Bar (Visible on mobile/tablet screens) -->
<header class="mobile-header">
    <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Toggle Navigation" onclick="toggleSidebar()">
        <span class="bar"></span>
        <span class="bar"></span>
        <span class="bar"></span>
    </button>
    <div class="mobile-header-title">
        <span>🏭</span> Industrial CRM
    </div>
    <button onclick="toggleDarkMode(event)" class="mobile-theme-btn" aria-label="Toggle Theme">
        <span id="mobileThemeIcon">🌙</span>
    </button>
</header>

<!-- Backdrop overlay for mobile drawer -->
<div class="sidebar-backdrop" id="sidebarBackdrop" onclick="closeSidebar()"></div>

<!-- Main Sidebar Navigation -->
<nav class="sidebar" id="mainSidebar">
    <div class="sidebar-brand">
        <h2><span>🏭</span> Industrial CRM</h2>
        <button class="sidebar-close-btn" onclick="closeSidebar()" aria-label="Close Navigation">&times;</button>
    </div>

    <div class="sidebar-user-badge">
        <div class="user-avatar"><?php echo strtoupper(substr(isset($_SESSION['username']) ? $_SESSION['username'] : 'U', 0, 1)); ?></div>
        <div class="user-info">
            <strong><?php echo htmlspecialchars(isset($_SESSION['full_name']) && !empty($_SESSION['full_name']) ? $_SESSION['full_name'] : (isset($_SESSION['username']) ? $_SESSION['username'] : 'User')); ?></strong>
            <small><?php echo htmlspecialchars($user_role); ?></small>
        </div>
    </div>

    <div class="sidebar-links">
        <a href="<?php echo $path_prefix; ?>dashboard.php" class="<?php echo ($current_script === 'dashboard.php') ? 'active-nav' : ''; ?>">
            <span class="nav-icon">📊</span> Dashboard
        </a>

        <a href="<?php echo $path_prefix; ?>customer/view_customer.php" class="<?php echo is_active_nav('/customer/', $current_uri); ?>">
            <span class="nav-icon">👥</span> Customers
        </a>

        <a href="<?php echo $path_prefix; ?>products/view_product.php" class="<?php echo is_active_nav('/products/', $current_uri); ?>">
            <span class="nav-icon">📦</span> Products
        </a>

        <a href="<?php echo $path_prefix; ?>quotation/view_quotation.php" class="<?php echo is_active_nav('/quotation/', $current_uri); ?>">
            <span class="nav-icon">📑</span> Quotations
        </a>

        <a href="<?php echo $path_prefix; ?>orders/view_order.php" class="<?php echo is_active_nav('/orders/', $current_uri); ?>">
            <span class="nav-icon">🛒</span> Orders
        </a>

        <a href="<?php echo $path_prefix; ?>invoice/view_invoice.php" class="<?php echo is_active_nav('/invoice/', $current_uri); ?>">
            <span class="nav-icon">🧾</span> Invoices
        </a>

        <?php if ($user_role === "Admin"): ?>
        <a href="<?php echo $path_prefix; ?>users/view_user.php" class="<?php echo is_active_nav('/users/', $current_uri); ?>">
            <span class="nav-icon">⚙️</span> User Management
        </a>
        <?php endif; ?>
    </div>

    <div class="sidebar-footer">
        <a href="<?php echo $path_prefix; ?>logout.php" class="logout">
            <span class="nav-icon">🚪</span> Logout
        </a>
    </div>
</nav>

<!-- Floating Desktop Dark Mode Button -->
<button onclick="toggleDarkMode(event)" class="theme-toggle-btn" id="desktopThemeBtn" title="Toggle Light/Dark Theme">
    <span id="desktopThemeIcon">🌙</span> <span id="desktopThemeText">Dark Mode</span>
</button>

<script>
function toggleSidebar() {
    var sidebar = document.getElementById('mainSidebar');
    var backdrop = document.getElementById('sidebarBackdrop');
    var menuBtn = document.getElementById('mobileMenuBtn');
    
    sidebar.classList.toggle('open');
    backdrop.classList.toggle('active');
    if (menuBtn) menuBtn.classList.toggle('active');
}

function closeSidebar() {
    var sidebar = document.getElementById('mainSidebar');
    var backdrop = document.getElementById('sidebarBackdrop');
    var menuBtn = document.getElementById('mobileMenuBtn');
    
    if (sidebar) sidebar.classList.remove('open');
    if (backdrop) backdrop.classList.remove('active');
    if (menuBtn) menuBtn.classList.remove('active');
}

// Close drawer when pressing Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeSidebar();
    }
});

// Update Theme UI state
function updateThemeUI(isDark) {
    var desktopIcon = document.getElementById("desktopThemeIcon");
    var desktopText = document.getElementById("desktopThemeText");
    var mobileIcon  = document.getElementById("mobileThemeIcon");

    if (isDark) {
        if (desktopIcon) desktopIcon.innerText = "☀️";
        if (desktopText) desktopText.innerText = "Light Mode";
        if (mobileIcon) mobileIcon.innerText = "☀️";
    } else {
        if (desktopIcon) desktopIcon.innerText = "🌙";
        if (desktopText) desktopText.innerText = "Dark Mode";
        if (mobileIcon) mobileIcon.innerText = "🌙";
    }
}

function toggleDarkMode(e) {
    if (e) e.preventDefault();
    var isDark = document.body.classList.toggle("dark-mode");
    localStorage.setItem("theme", isDark ? "dark" : "light");
    updateThemeUI(isDark);
}

// Apply theme on initial load
(function() {
    var savedTheme = localStorage.getItem("theme");
    var isDark = (savedTheme === "dark");
    if (isDark) {
        document.body.classList.add("dark-mode");
    }
    updateThemeUI(isDark);
})();
</script>