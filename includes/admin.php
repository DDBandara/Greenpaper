<?php
session_start();
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/functions.php';

if (($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}
header('Cache-Control: no-store');

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

if (!function_exists('timeAgo')) {
    function timeAgo($date) {
        $seconds = time() - strtotime($date);
        if ($seconds < 60) {
            return 'just now';
        }
        if ($seconds < 3600) {
            $n = (int)floor($seconds / 60);
            return $n . ' minute' . ($n === 1 ? '' : 's') . ' ago';
        }
        if ($seconds < 86400) {
            $n = (int)floor($seconds / 3600);
            return $n . ' hour' . ($n === 1 ? '' : 's') . ' ago';
        }
        if ($seconds < 2592000) {
            $n = (int)floor($seconds / 86400);
            return $n . ' day' . ($n === 1 ? '' : 's') . ' ago';
        }
        return date('d M Y', strtotime($date));
    }
}

function csrfOk() {
    return hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '');
}

function flash($message) {
    $_SESSION['admin_msg'] = $message;
}

function plural($n, $word) {
    return $n . ' ' . $word . ($n === 1 ? '' : 's');
}

function adminHeader($title, $active) {
    $items = [
        'overview' => ['Overview', '/admin_page.php'],
        'comments' => ['Comments', '/admin/comments.php'],
        'users'    => ['Users', '/admin/users.php'],
    ];
    $message = $_SESSION['admin_msg'] ?? '';
    unset($_SESSION['admin_msg']);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= safe($title) ?> - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/admin.css">
</head>
<body>
<header class="admin-bar">
    <div class="admin-bar-box">
        <a class="admin-logo" href="<?= BASE_URL ?>/index.php">Green Paper</a>
        <span class="admin-tag">Admin</span>
        <nav class="admin-menu">
            <?php foreach ($items as $key => $item): ?>
                <a href="<?= BASE_URL . $item[1] ?>"<?= $key === $active ? ' class="active"' : '' ?>><?= $item[0] ?></a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-user">
            <span>Hi, <?= safe($_SESSION['name'] ?? '') ?></span>
            <a href="<?= BASE_URL ?>/logout.php">Log out</a>
        </div>
    </div>
</header>
<main class="admin-wrap">
<?php if ($message): ?>
    <div class="admin-note"><?= safe($message) ?></div>
<?php endif; ?>
<?php
}

function adminFooter() {
    ?>
</main>
</body>
</html>
<?php
}