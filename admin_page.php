<?php
require_once __DIR__ . '/includes/admin.php';

$totals = getRows($conn, "SELECT SUM(status = 'approved') AS published,
                                 SUM(status = 'pending') AS pending,
                                 COALESCE(SUM(views), 0) AS views
                          FROM articles")[0] ?? [];
$published = (int)($totals['published'] ?? 0);
$pending = (int)($totals['pending'] ?? 0);
$views = (int)($totals['views'] ?? 0);
$average = $published ? (int)round($views / $published) : 0;
$users = (int)(getRows($conn, 'SELECT COUNT(*) AS total FROM users')[0]['total'] ?? 0);

$waiting = getRows($conn, "SELECT a.id, a.title, a.created_at, c.name AS category, u.name AS author
                           FROM articles a
                           JOIN categories c ON c.id = a.category_id
                           JOIN users u ON u.id = a.author_id
                           WHERE a.status = 'pending'
                           ORDER BY a.created_at ASC
                           LIMIT 5") ?? [];

$popular = getRows($conn, "SELECT id, title, views FROM articles
                           WHERE status = 'approved'
                           ORDER BY views DESC, published_at DESC
                           LIMIT 5") ?? [];

$byCategory = getRows($conn, "SELECT c.name, COALESCE(SUM(a.views), 0) AS views
                              FROM categories c
                              LEFT JOIN articles a ON a.category_id = c.id AND a.status = 'approved'
                              GROUP BY c.id, c.name
                              ORDER BY views DESC, c.name") ?? [];
$mostViews = 1;
foreach ($byCategory as $row) {
    $mostViews = max($mostViews, (int)$row['views']);
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Green Paper Admin</title>
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
        <link rel="stylesheet" href="<?= BASE_URL ?>/admin/admin.css">
        <link href="https://fonts.googleapis.com/css2?family=Lora&display=swap" rel="stylesheet">
        <link rel="icon" type="image/png" href="<?= BASE_URL ?>/uploads/favicon.png">
        <style>
            :root{
                --green:#1f6b3e;
                --bg:#f4f1ea;
                --card:#fff;
                --line:#ddd8cc;
                --red:#a32a2a;
            }
            [data-theme="dark"]{
                color-scheme: dark;
                --green: #58b784;
                --bg: #121614;
                --card: #171f1a;
                --line: #2b332f;
                --red: #f0a39e;
                --ink: #e9e8e2;
                --muted: #a8a69d;
            }
            [data-theme="dark"] body{
                background: var(--bg);
                color: var(--ink);
            }
            [data-theme="dark"] h1,
            [data-theme="dark"] h2,
            [data-theme="dark"] h3,
            [data-theme="dark"] .card h2,
            [data-theme="dark"] .stat .num{
                color: var(--ink);
            }
            [data-theme="dark"] .stat,
            [data-theme="dark"] .card{
                background: var(--card);
                border-color: var(--line);
            }
            [data-theme="dark"] .card-head,
            [data-theme="dark"] .row,
            [data-theme="dark"] .pop-row,
            [data-theme="dark"] .bar-row{
                border-bottom-color: var(--line);
            }
            [data-theme="dark"] .stat .label,
            [data-theme="dark"] .stat .note,
            [data-theme="dark"] .eyebrow,
            [data-theme="dark"] .date,
            [data-theme="dark"] small,
            [data-theme="dark"] .empty{
                color: var(--muted);
            }
            [data-theme="dark"] .btn{
                background: var(--card);
                border-color: var(--green);
                color: var(--green);
            }
            [data-theme="dark"] .btn:hover{
                background: var(--green);
                color: #0c1410;
            }
            [data-theme="dark"] .track{
                background: #232e26;
            }
            [data-theme="dark"] .fill{
                background: var(--green);
            }
            [data-theme="dark"] .stat .num.warn{
                color: var(--red);
            }
            *{
                box-sizing: border-box;
                margin: 0;
                padding: 0;
            
            }
            body{
                background: var(--bg);
                font-family: Arial;

            }
            h1,h2,h3{
                font-family: 'Lora',serif;
                font-weight: 400;
            }
            .topbar{
                background: var(--green);
                color: #fff;
                display: flex;
                align-items: center;
                gap: 24px;
                padding: 0 40px;
                height: 56px;
            }
            .topbar nav{
                display: flex;
                gap: 22px ;
            }
            .topbar a{
                color: #fff;
                text-decoration: none;
                font-size: 16px;
            }
            .topbar a.active{
                border-bottom: 2px solid #fff;
                padding-bottom: 4px;
            }
            .user{
                margin-left: auto;
            }
            .container{
                max-width: 1152px;
                margin: 0 auto;
                padding: 40px 24px;
            }
            .eyebrow{
                color: var(--green);
                font-size: 13px;
                font-weight: bold;
                letter-spacing: 1px;
            }
            .eyebrow-row{
                display: flex;
                justify-content: space-between;
            }
            h1{
                font-size: 42px;
                margin: 16px 0 22px;
            }
            .stats{
                display: grid;
                grid-template-columns: repeat(4,1fr);
                gap: 18px;
                margin-bottom: 30px;
            }
            .stat{
                background: var(--card);
                border: 1px solid var(--line);
                padding: 20px;
            }
            .num{
                font-family: 'Lora',serif;
                font-size: 44px;
            }
            .label{
                font-size: 11px;
                letter-spacing: 1px;
                color: #555;
                font-weight: bold;
            }
            .card-head h2{
                font-size: 22px;
            }
            .card-head a{
                color: var(--green);
                font-weight: bold;
                font-size: 14px;
                text-decoration: none;
            }
            .layout{
                display: grid;
                grid-template-columns: 1.6fr 1fr;
                gap: 24px;
                align-items: start;
            }
            .col-left,.col-right{
                display: flex;
                flex-direction: column;
                gap: 24px;
            }
            .card{
                background: var(--card);
                border: 1px solid var(--line);


            }
            .card-head{
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 20px 22px;
                border-bottom: 1px solid var(--line);
            }
            .row{
                border-bottom: 1px solid var(--line);
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 18px 22px;
                
            }
            .btn{
                cursor: pointer;
                border: 1px solid var(--green);
                color: var(--green);
                background: #fff;
                padding: 10px 18px;
                font-weight: bold;

            }
            .bar-row{
                display: grid;
                grid-template-columns:100px 1fr 40px;
                gap: 12px;
                padding: 12px 20px;
            }
            .track{
                background: #eee9e0;
                height: 18px;

            }
            .fill{
                background: var(--green);
                height: 100%;
            }
            .read{
                list-style: none;

            }
            .read li{
                display: grid;
                grid-template-columns: 36px 1fr auto;
                padding: 10px 22px;
            }
            .rank{
                color: var(--green);
                font-size: 22px;
            }
            .action{
                list-style: none;
            }
            .action a{
                display: flex;
                justify-content: space-between;
                padding: 14px 22px;
                border-top: 1px solid var(--line);
                text-decoration: none;
                color: #222;
            }
            .num.warn{
                color: var(--red);
            }
            .stat a{
                color: var(--green);
                font-weight: bold;
                text-decoration: none;
            }
            .btn:hover{
                background: var(--green);
                color: #fff;
            }
            @media(max-width:900px){
                .stats{
                    grid-template-columns: repeat(2,1fr);
                }
                .layout{
                    grid-template-columns: 1fr;

                }
            }


            .btn{
                display: inline-block;
                text-decoration: none;
                font-size: 14px;
            }
            .row h3 a{
                color: inherit;
                text-decoration: none;
            }
            .row h3 a:hover, .read li a:hover{
                color: var(--green);
            }
            .read li a{
                color: #222;
                text-decoration: none;
            }
            .read li em{
                color: #666;
                font-style: normal;
                font-size: 13px;
            }
            .empty{
                padding: 22px;
                color: #666;
            }
            .stat .note{
                margin-top: 6px;
                font-size: 14px;
                color: #555;
            }
            [data-theme="dark"] .action a {
                color: var(--ink) !important;
            }
            [data-theme="dark"] .action a:hover {
                color: var(--green) !important;
            }
            [data-theme="dark"] .action a span {
                color: var(--muted) !important;
            }
            [data-theme="dark"] .read li a {
                color: var(--ink) !important;
            }
            [data-theme="dark"] .read li a:hover {
                color: var(--green) !important;
            }
            [data-theme="dark"] .read li em {
                color: var(--muted) !important;
            }
            [data-theme="dark"] .bar-row span,
            [data-theme="dark"] .bar-row b {
                color: var(--ink) !important;
            }
            [data-theme="dark"] .row h3 a {
                color: var(--ink) !important;
            }
            [data-theme="dark"] .row h3 a:hover {
                color: var(--green) !important;
            }
            [data-theme="dark"] .row small {
                color: var(--muted) !important;
            }
            [data-theme="dark"] .card-head a {
                color: var(--green) !important;
            }
            [data-theme="dark"] .stat .label,
            [data-theme="dark"] .stat .note,
            [data-theme="dark"] .empty {
                color: var(--muted) !important;
            }
            [data-theme="dark"] .stat a {
                color: var(--green) !important;
            }
            [data-theme="dark"] .rank {
                color: var(--green) !important;
            }
            .user a{
                margin-left: 12px;
                font-size: 14px;
            }
        </style>
    </head>
    <body>
        <header class="topbar">
            <?php
$activeAdminPage = 'overview';
require __DIR__ . '/includes/admin_topbar.php';
?></header>
        <main class="container">
            <div class="eyebrow-row">
                <span class="eyebrow">OVERVIEW</span>
                <span class="date"><?= date('l, j F Y') ?></span>
            </div>
            <h1>Today at Green Paper</h1>

            <section class="stats">
                <div class="stat">
                    <div class="num"><?= $published ?></div>
                    <div class="label">PUBLISHED ARTICLES</div>
                    <p class="note">Live on the site</p>
                </div>
                <div class="stat">
                    <div class="num<?= $pending ? ' warn' : '' ?>"><?= $pending ?></div>
                    <div class="label">WAITING FOR APPROVAL</div>
                    <p class="note"><?= $pending ? 'Needs an admin' : 'All caught up' ?></p>
                </div>
                <div class="stat">
                    <div class="num"><?= $views ?></div>
                    <div class="label">TOTAL VIEWS</div>
                    <p class="note"><?= $average ?> per published article</p>
                </div>
                <div class="stat">
                    <div class="num"><?= $users ?></div>
                    <div class="label">REGISTERED USERS</div>
                    <p class="note"><a href="<?= BASE_URL ?>/admin/users.php">Manage users</a></p>
                </div>
            </section>

            <div class="layout">
                <div class="col-left">
                    <section class="card">
                        <div class="card-head"><h2>Waiting for approval</h2><a href="<?= BASE_URL ?>/admin/admin_articles.php">Review all</a></div>
                        <?php foreach ($waiting as $item): ?>
                            <div class="row">
                                <div>
                                    <h3><a href="<?= storyLink($item['id']) ?>"><?= safe($item['title']) ?></a></h3>
                                    <small>By <?= safe($item['author']) ?> &bull; <?= safe($item['category']) ?> &bull; <?= showDate($item['created_at']) ?></small>
                                </div>
                                <a class="btn" href="<?= BASE_URL ?>/admin/admin_articles.php">Review</a>
                            </div>
                        <?php endforeach; ?>
                        <?php if (!$waiting): ?>
                            <p class="empty">Nothing is waiting for approval.</p>
                        <?php endif; ?>
                    </section>

                    <section class="card">
                        <div class="card-head"><h2>Views by category</h2></div>
                        <?php foreach ($byCategory as $row): ?>
                            <div class="bar-row">
                                <span><?= safe($row['name']) ?></span>
                                <div class="track"><div class="fill" style="width: <?= (int)round($row['views'] / $mostViews * 100) ?>%;"></div></div>
                                <b><?= (int)$row['views'] ?></b>
                            </div>
                        <?php endforeach; ?>
                    </section>
                </div>

                <div class="col-right">
                    <section class="card">
                        <div class="card-head"><h2>Most read</h2></div>
                        <ol class="read">
                            <?php foreach ($popular as $i => $item): ?>
                                <li><span class="rank"><?= $i + 1 ?></span><a href="<?= storyLink($item['id']) ?>"><?= safe($item['title']) ?></a><em><?= (int)$item['views'] ?></em></li>
                            <?php endforeach; ?>
                        </ol>
                        <?php if (!$popular): ?>
                            <p class="empty">No published articles yet.</p>
                        <?php endif; ?>
                    </section>

                    <section class="card">
                        <div class="card-head"><h2>Quick actions</h2></div>
                        <ul class="action">
                            <li><a href="<?= BASE_URL ?>/admin/article_form.php">Write a new article<span>&rarr;</span></a></li>
                            <li><a href="<?= BASE_URL ?>/submit_announcement.php">Announcements<span>&rarr;</span></a></li>
                        </ul>
                    </section>
                </div>
            </div>
        </main>
    </body>
</html>