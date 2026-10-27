<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../classes/Customer.php';
require_once __DIR__ . '/functions.php';

/**
 * Ensures the user is authenticated before accessing protected pages.
 */
function requireLogin(): void {
    if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: login.php');
        exit;
    }
}

set_exception_handler(static function (Throwable $exception): void {
    error_log('Unhandled application error: ' . $exception->getMessage());
    http_response_code(500);

    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Application error</title>';
    echo '<style>body{font-family:Arial,sans-serif;background:#f3f6fb;padding:40px;color:#172033}';
    echo '.box{max-width:720px;margin:auto;background:#fff;padding:28px;border-radius:14px;box-shadow:0 10px 30px #18243a18}';
    echo 'h1{color:#a62b2b}a{color:#2758c7}</style></head><body>';
    echo '<main class="box"><h1>The request could not be completed</h1>';
    echo '<p>Please return to the customer list and try again.</p>';
    echo '<p><a href="index.php">Return to customers</a></p></main></body></html>';
});

try {
    $database = new Database();
    $pdo = $database->connect();
    $customerModel = new Customer($pdo);
} catch (RuntimeException $exception) {
    http_response_code(500);
    $safeMessage = htmlspecialchars($exception->getMessage(), ENT_QUOTES, 'UTF-8');

    echo '<!doctype html><html lang="en"><head><meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<title>Database connection problem</title>';
    echo '<style>body{font-family:Arial,sans-serif;background:#f3f6fb;padding:40px;color:#172033}';
    echo '.box{max-width:720px;margin:auto;background:#fff;padding:28px;border-radius:14px;box-shadow:0 10px 30px #18243a18}';
    echo 'h1{color:#a62b2b}code{background:#eef1f6;padding:2px 6px;border-radius:4px}</style></head><body>';
    echo '<main class="box"><h1>Database connection problem</h1><p>' . $safeMessage . '</p>';
    echo '<p>Setup help is available in <code>README.md</code>.</p></main></body></html>';
    exit;
}