<?php

// Suppress Symfony and Termwind E_DEPRECATED warnings on PHP 8.4
error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', 0);

// ─── Serve static files directly (bypasses Laravel entirely) ─────────────────
// On Vercel all repo files live at /var/task/user/
$requestUri = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');

$staticMimeTypes = [
    'css'   => 'text/css; charset=utf-8',
    'js'    => 'application/javascript; charset=utf-8',
    'png'   => 'image/png',
    'jpg'   => 'image/jpeg',
    'jpeg'  => 'image/jpeg',
    'gif'   => 'image/gif',
    'svg'   => 'image/svg+xml',
    'ico'   => 'image/x-icon',
    'webp'  => 'image/webp',
    'woff'  => 'font/woff',
    'woff2' => 'font/woff2',
    'ttf'   => 'font/ttf',
    'eot'   => 'application/vnd.ms-fontobject',
    'pdf'   => 'application/pdf',
    'json'  => 'application/json',
    'xml'   => 'application/xml',
    'map'   => 'application/json',
    'apk'   => 'application/vnd.android.package-archive',
];

$ext = strtolower(pathinfo($requestUri, PATHINFO_EXTENSION));

if ($ext && isset($staticMimeTypes[$ext])) {
    // Try to find the file in public/
    $publicFile = __DIR__ . '/../public' . $requestUri;
    if (is_file($publicFile)) {
        header('Content-Type: ' . $staticMimeTypes[$ext]);
        header('Cache-Control: public, max-age=31536000, immutable');
        header('Content-Length: ' . filesize($publicFile));
        readfile($publicFile);
        exit;
    }
}
// ─────────────────────────────────────────────────────────────────────────────

// Force log channel to stderr to prevent read-only filesystem errors on Vercel
$_ENV['LOG_CHANNEL'] = 'stderr';
putenv('LOG_CHANNEL=stderr');

// Dynamically set APP_URL to match the current Vercel request host. Only local
// development runs on plain HTTP, so anything else stays https:// even when the
// forwarded scheme is missing — otherwise generated links downgrade to http://.
if (isset($_SERVER['HTTP_HOST'])) {
    $isLocal = (bool) preg_match('/^(localhost|127\.0\.0\.1|10\.0\.2\.2)(:|$)/', $_SERVER['HTTP_HOST']);
    $proto = $isLocal ? 'http' : 'https';
    $_ENV['APP_URL'] = $proto . '://' . $_SERVER['HTTP_HOST'];
    putenv('APP_URL=' . $_ENV['APP_URL']);
}

// Sanitize database username to lowercase 'root'
if (isset($_ENV['DB_USERNAME']) && strtoupper($_ENV['DB_USERNAME']) === 'ROOT') {
    $_ENV['DB_USERNAME'] = 'root';
    putenv('DB_USERNAME=root');
}

// Create temporary storage directories in /tmp for Vercel Serverless environment
$tmpStorage = '/tmp/storage';

foreach ([
    $tmpStorage . '/framework/views',
    $tmpStorage . '/framework/cache/data',
    $tmpStorage . '/framework/sessions',
    $tmpStorage . '/logs',
] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Set environment paths for Vercel read-only filesystem
$_ENV['VIEW_COMPILED_PATH'] = $tmpStorage . '/framework/views';
$_ENV['APP_CONFIG_CACHE']   = '/tmp/config.php';
$_ENV['APP_EVENTS_CACHE']   = '/tmp/events.php';
$_ENV['APP_PACKAGES_CACHE'] = '/tmp/packages.php';
$_ENV['APP_ROUTES_CACHE']   = '/tmp/routes.php';
$_ENV['APP_SERVICES_CACHE'] = '/tmp/services.php';

putenv('VIEW_COMPILED_PATH=' . $tmpStorage . '/framework/views');
putenv('APP_CONFIG_CACHE=/tmp/config.php');
putenv('APP_EVENTS_CACHE=/tmp/events.php');
putenv('APP_PACKAGES_CACHE=/tmp/packages.php');
putenv('APP_ROUTES_CACHE=/tmp/routes.php');
putenv('APP_SERVICES_CACHE=/tmp/services.php');

// Force SCRIPT_NAME to root to fix Laravel's base URL detection and routing on Vercel
$_SERVER['SCRIPT_NAME']      = '/index.php';
$_SERVER['PHP_SELF']          = '/index.php';
$_SERVER['ORIG_SCRIPT_NAME'] = '/index.php';

// Forward request to Laravel's public/index.php
require __DIR__ . '/../public/index.php';
