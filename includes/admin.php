<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    'overview'     => ['Overview', '/admin_page.php'],
    'articles'     => ['Articles', '/admin/admin_articles.php'],
    'announcements' => ['Announcements', '/submit_announcement.php'],
    'comments'     => ['Comments', '/admin/comments.php'],
    'users'        => ['Users', '/admin/users.php'],
    'home'      => ['home', '/index.php'],
    
];
    $message = $_SESSION['admin_msg'] ?? '';
    unset($_SESSION['admin_msg']);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<script>
try {
  document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') === 'light' ? 'light' : 'dark');
} catch (e) {
  document.documentElement.setAttribute('data-theme', 'dark');
}
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.theme-toggle');
    if (toggle) {
        toggle.addEventListener('click', function () {
            var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme', next);
            try { localStorage.setItem('theme', next); } catch (e) {}
        });
    }
});
</script>
<title><?= safe($title) ?> - Admin</title>
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/admin.css?v=2">
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/uploads/favicon.png">
<link rel="stylesheet" href="<?= BASE_URL ?>/admin/admin.css?v=2">
</head>
<body>
<header class="topbar">
    <a class="logo" href="<?= BASE_URL ?>/index.php">Green Paper</a>

    <nav>
        <?php foreach ($items as $key => $item): ?>
            <a
                href="<?= BASE_URL . $item[1] ?>"
                <?= $key === $active ? 'class="active"' : '' ?>>
                <?= safe($item[0]) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="user">
        Hi <?= safe($_SESSION['name'] ?? '') ?>
        <button type="button" class="theme-toggle" aria-label="Switch theme">
            <svg class="moon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M13.5 9.5A5.5 5.5 0 0 1 6.5 2.5a5.5 5.5 0 1 0 7 7z" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
            <svg class="sun" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="3" fill="none" stroke="currentColor" stroke-width="1.3"/><path d="M8 1v2M8 13v2M1 8h2M13 8h2M3 3l1.4 1.4M11.6 11.6L13 13M3 13l1.4-1.4M11.6 4.4L13 3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
        </button>
        <a href="<?= BASE_URL ?>/logout.php">Log Out</a>
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