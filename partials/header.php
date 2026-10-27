<?php

declare(strict_types=1);

$pageTitle = $pageTitle ?? 'Customer Credit Monitoring System';
$activePage = $activePage ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Customer Credit Monitoring System</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="site-header">
        <div class="container header-content">
            <a class="brand" href="index.php">
                <span class="brand-mark" aria-hidden="true">₱</span>
                <span>
                    <strong>Customer Credit</strong>
                    <small>Monitoring System</small>
                </span>
            </a>

            <nav aria-label="Main navigation">
                <a class="nav-link <?= $activePage === 'customers' ? 'active' : '' ?>" href="index.php">Customers</a>
                <?php if (!empty($_SESSION['logged_in'])): ?>
                    <a class="nav-link <?= $activePage === 'transfers' ? 'active' : '' ?>" href="transfers.php">Transfers</a>
                    <a href="logout.php" class="nav-link logout-item">
                        <span>Log Out</span>
                    </a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container page-content">
