<?php
// Shared header. Every page includes this at the top:
//   $pageTitle = 'Page name';
//   require_once __DIR__ . '/includes/header.php';   (use ../includes/ from inside admin/)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Your project folder name inside htdocs. Change it if your folder is called something else.
if (!defined('BASE_URL')) {
    define('BASE_URL', '/newsdesk1.0');
}

// Short helper: print text safely. Use e($value) for ALL user/database text.
if (!function_exists('e')) {
    function e($value) {
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

$pageTitle = $pageTitle ?? 'NSBM NewsDesk';
$loggedIn  = isset($_SESSION['user_id']);
$isAdmin   = ($_SESSION['role'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>/news.css">
</head>
<body>
<header class="site-header">
    <div class="header-inner">
        <a class="brand" href="<?= BASE_URL ?>/home.php">NSBM <span>NewsDesk</span></a>
        <nav class="main-nav">
            <a href="<?= BASE_URL ?>/home.php">Home</a>
            <?php if ($loggedIn): ?>
                <a href="<?= BASE_URL ?>/user_page.php">My feed</a>
            <?php endif; ?>
            <?php if ($isAdmin): ?>
                <a href="<?= BASE_URL ?>/admin_page.php">Admin</a>
            <?php endif; ?>
        </nav>
        <div class="user-area">
            <?php if ($loggedIn): ?>
                <span class="who">Hi, <?= e($_SESSION['name']) ?></span>
                <a class="btn btn-outline" href="<?= BASE_URL ?>/logout.php">Log out</a>
            <?php else: ?>
                <a class="btn" href="<?= BASE_URL ?>/index.php">Log in</a>
            <?php endif; ?>
        </div>
    </div>
</header>
<main class="container"></main>