<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!defined('BASE_URL')) {
    define('BASE_URL', '/newsdesk1.0');
}
if (!function_exists('safe')) {
    function safe($text) {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('active')) {
    function active($yes) {
        return $yes ? ' class="active"' : '';
    }
}

$pageTitle = $pageTitle ?? 'Green Paper';
$loggedIn = !empty($_SESSION['user_id']);
$page = basename($_SERVER['PHP_SELF']);
$cat = ($page === 'category.php') ? ($_GET['name'] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= safe($pageTitle) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/news.css">
</head>
<body>

<header class="header">
  <div class="header-box">
    <div class="header-left">
      <div class="date"><?= date('l, F j, Y') ?></div>
      <div class="date-small">The daily edition &middot; Your campus in one place</div>
    </div>

    <div class="header-center">
      <a class="logo" href="<?= BASE_URL ?>/index.php">Green Paper</a>
      <div class="tagline">Campus news, events and announcements</div>
    </div>

    <div class="header-right">
      <?php if ($loggedIn): ?>
        <a href="<?= BASE_URL ?>/user_page.php">My feed</a>
        <a href="<?= BASE_URL ?>/logout.php">Log out</a>
        <a href="<?= BASE_URL ?>/admin_page.php">Admin</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php">Log in</a>
      <?php endif; ?>
      <a class="submit-btn" href="<?= BASE_URL ?>/submit.php">Submit an article</a>
    </div>
  </div>

  <nav class="menu">
    <div class="menu-box">
      <a href="/index.php"<?= active($page === 'index.php') ?>>Latest</a>
      <a href="<?= BASE_URL ?>/category.php?name=Academic"<?= active($cat === 'Academic') ?>>Academic</a>
      <a href="<?= BASE_URL ?>/category.php?name=Events"<?= active($cat === 'Events') ?>>Events</a>
      <a href="<?= BASE_URL ?>/category.php?name=Sports"<?= active($cat === 'Sports') ?>>Sports</a>
      <a href="<?= BASE_URL ?>/category.php?name=General"<?= active($cat === 'General') ?>>General</a>

      <form class="search-box" action="<?= BASE_URL ?>/search.php" method="get">
        <label>
          <svg width="16" height="16" viewBox="0 0 16 16">
            <circle cx="6.5" cy="6.5" r="5" fill="none" stroke="currentColor" stroke-width="1.2"/>
            <line x1="10.2" y1="10.2" x2="15" y2="15" stroke="currentColor" stroke-width="1.2"/>
          </svg>
          <input type="text" name="q" placeholder="Search" autocomplete="off">
        </label>
      </form>
    </div>
  </nav>
</header>

<main class="wrap">