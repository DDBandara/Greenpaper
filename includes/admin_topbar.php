<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/newsdesk1.0');
}

$activeAdminPage = $activeAdminPage ?? '';

$adminLinks = [
    'overview'     => ['Overview', '/admin_page.php'],
    'articles'     => ['Articles', '/admin/admin_articles.php'],
    'announcements' => ['Announcements', '/admin/announcements.php'],
    'comments'     => ['Comments', '/admin/comments.php'],
    'users'        => ['Users', '/admin/users.php'],
    'reports'      => ['Reports', '/admin/reports.php'],
];

$adminName = htmlspecialchars($_SESSION['name'] ?? '', ENT_QUOTES, 'UTF-8');
?>

<header class="topbar">
    <h2 class="logo">Green Paper</h2>
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
        <a href="<?= htmlspecialchars(BASE_URL . '/logout.php', ENT_QUOTES, 'UTF-8') ?>">Log Out</a>
    </div>
</header>