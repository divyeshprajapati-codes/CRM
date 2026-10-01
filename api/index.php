<?php
/**
 * Vercel Serverless Entrypoint for PHP
 * Routes all incoming requests to the respective PHP files.
 */

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$root_dir = realpath(__DIR__ . '/..');

// Route root to login.php
if ($uri === '/' || $uri === '') {
    require $root_dir . '/login.php';
    exit;
}

$target_file = realpath($root_dir . $uri);

// Security: Prevent path traversal outside project root
if ($target_file && strpos($target_file, $root_dir) === 0 && file_exists($target_file)) {
    if (is_file($target_file) && pathinfo($target_file, PATHINFO_EXTENSION) === 'php') {
        $_SERVER['SCRIPT_FILENAME'] = $target_file;
        $_SERVER['SCRIPT_NAME'] = '/' . ltrim($uri, '/');
        chdir(dirname($target_file));
        require $target_file;
        exit;
    } elseif (is_dir($target_file)) {
        if (file_exists($target_file . '/index.php')) {
            $_SERVER['SCRIPT_FILENAME'] = $target_file . '/index.php';
            $_SERVER['SCRIPT_NAME'] = rtrim('/' . ltrim($uri, '/'), '/') . '/index.php';
            chdir($target_file);
            require $target_file . '/index.php';
            exit;
        }
    }
}

// Fallback for paths missing .php extension
$target_with_ext = realpath($root_dir . $uri . '.php');
if ($target_with_ext && strpos($target_with_ext, $root_dir) === 0 && file_exists($target_with_ext)) {
    $_SERVER['SCRIPT_FILENAME'] = $target_with_ext;
    $_SERVER['SCRIPT_NAME'] = '/' . ltrim($uri, '/') . '.php';
    chdir(dirname($target_with_ext));
    require $target_with_ext;
    exit;
}

http_response_code(404);
echo "404 - Page Not Found";
