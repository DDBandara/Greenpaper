<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/newsdesk1.0');
}

$activeAdminPage = $activeAdminPage ?? '';

$adminLinks = [
    'overview'     => ['Overview', '/admin_page.php'],
    'articles'     => ['Articles', '/admin/admin_articles.php'],
    'announcements' => ['Announcements', '/submit_announcement.php'],
    'comments'     => ['Comments', '/admin/comments.php'],
    'users'        => ['Users', '/admin/users.php'],
    'home'      => ['home', '/index.php'],
];

$adminName = htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<header class="topbar">
    <a href="<?= htmlspecialchars(BASE_URL . '/index.php', ENT_QUOTES, 'UTF-8') ?>">
        <h2 class="logo">Green Paper</h2>
    </a>
    <nav>
        <?php foreach ($adminLinks as $key => $link): ?>
            <a href="<?= htmlspecialchars(BASE_URL . $link[1], ENT_QUOTES, 'UTF-8') ?>"
               <?= $activeAdminPage === $key ? 'class="active"' : '' ?>>
                <?= htmlspecialchars($link[0], ENT_QUOTES, 'UTF-8') ?>
            </a>
        <?php endforeach; ?>
    </nav>
    <div class="user">
        Hi <?= $adminName ?>
        <button type="button" class="theme-toggle" aria-label="Switch theme">
            <svg class="moon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M13.5 9.5A5.5 5.5 0 0 1 6.5 2.5a5.5 5.5 0 1 0 7 7z" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
            <svg class="sun" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="3" fill="none" stroke="currentColor" stroke-width="1.3"/><path d="M8 1v2M8 13v2M1 8h2M13 8h2M3 3l1.4 1.4M11.6 11.6L13 13M3 13l1.4-1.4M11.6 4.4L13 3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
        </button>
        <a href="<?= htmlspecialchars(BASE_URL . '/logout.php', ENT_QUOTES, 'UTF-8') ?>">Log Out</a>
    </div>
</header>