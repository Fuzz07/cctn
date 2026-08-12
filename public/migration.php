<?php

/**
 * Remote Database Migration Runner for Laravel
 */

define('LARAVEL_START', microtime(true));

header('Content-Type: text/html; charset=utf-8');

$vendorPath = __DIR__ . '/../vendor/autoload.php';
$appPath    = __DIR__ . '/../bootstrap/app.php';

if (!file_exists($vendorPath)) {
    die("<h1>Error</h1><p>vendor/autoload.php not found. Please run 'composer install' first.</p>");
}

if (!file_exists($appPath)) {
    die("<h1>Error</h1><p>bootstrap/app.php not found.</p>");
}

require $vendorPath;
$app = require_once $appPath;

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$action = isset($_GET['action']) ? $_GET['action'] : 'migrate';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Migration Tool</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            padding: 40px 20px;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background-color: #1e293b;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5);
        }
        h1 {
            margin-top: 0;
            color: #38bdf8;
            font-size: 24px;
        }
        .actions {
            margin-bottom: 20px;
        }
        .btn {
            display: inline-block;
            background-color: #0284c7;
            color: #fff;
            padding: 10px 18px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            margin-right: 10px;
            margin-bottom: 10px;
            transition: background-color 0.2s;
        }
        .btn:hover {
            background-color: #0369a1;
        }
        .btn-secondary {
            background-color: #475569;
        }
        .btn-secondary:hover {
            background-color: #334155;
        }
        pre {
            background-color: #090d16;
            color: #4ade80;
            padding: 20px;
            border-radius: 8px;
            overflow-x: auto;
            font-family: "Courier New", Courier, monospace;
            font-size: 14px;
            line-height: 1.5;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .error {
            background-color: #7f1d1d;
            color: #fca5a5;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #064e3b;
            color: #6ee7b7;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Laravel Remote Migration Tool</h1>
        <p>Run database migrations and seeders remotely without SSH access.</p>
        
        <div class="actions">
            <a href="?action=migrate" class="btn">Run Migrations (migrate --force)</a>
            <a href="?action=status" class="btn btn-secondary">Migration Status</a>
            <a href="?action=seed" class="btn btn-secondary">Run Seeders (db:seed --force)</a>
        </div>

        <?php
        try {
            if ($action === 'status') {
                echo "<h3>Running: <code>php artisan migrate:status</code></h3>";
                $exitCode = $kernel->call('migrate:status');
                $output = $kernel->output();
                echo "<pre>" . htmlspecialchars($output ?: "Command executed (Exit code: $exitCode)") . "</pre>";
            } elseif ($action === 'seed') {
                echo "<h3>Running: <code>php artisan db:seed --force</code></h3>";
                $exitCode = $kernel->call('db:seed', ['--force' => true]);
                $output = $kernel->output();
                echo "<div class='success'>Database seeding finished successfully.</div>";
                echo "<pre>" . htmlspecialchars($output ?: "Command executed successfully.") . "</pre>";
            } else {
                echo "<h3>Running: <code>php artisan migrate --force</code></h3>";
                $exitCode = $kernel->call('migrate', ['--force' => true]);
                $output = $kernel->output();
                echo "<div class='success'>Database migration finished successfully.</div>";
                echo "<pre>" . htmlspecialchars($output ?: "Command executed successfully.") . "</pre>";
            }
        } catch (\Throwable $e) {
            echo "<div class='error'><strong>Migration Failed:</strong><br>" . htmlspecialchars($e->getMessage()) . "</div>";
            echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
        }
        ?>
    </div>
</body>
</html>
