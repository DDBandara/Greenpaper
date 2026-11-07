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
<script>
try {
  document.documentElement.setAttribute('data-theme', localStorage.getItem('theme') === 'light' ? 'light' : 'dark');
} catch (e) {
  document.documentElement.setAttribute('data-theme', 'dark');
}
</script>
<title><?= safe($pageTitle) ?></title>
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= BASE_URL ?>/news.css">
<link rel="icon" type="image/png" href="<?= BASE_URL ?>/uploads/favicon.png">
<link rel="stylesheet" href="<?= BASE_URL ?>/animations.css">
<script src="<?= BASE_URL ?>/animations.js" defer></script>
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
      <button type="button" class="theme-toggle" aria-label="Switch to dark mode" aria-pressed="false">
        <svg class="moon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><path d="M13.5 9.5A5.5 5.5 0 0 1 6.5 2.5a5.5 5.5 0 1 0 7 7z" fill="none" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/></svg>
        <svg class="sun" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true"><circle cx="8" cy="8" r="3" fill="none" stroke="currentColor" stroke-width="1.3"/><path d="M8 1v2M8 13v2M1 8h2M13 8h2M3 3l1.4 1.4M11.6 11.6L13 13M3 13l1.4-1.4M11.6 4.4L13 3" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/></svg>
      </button>
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
      <a class="menu-logo" href="<?= BASE_URL ?>/index.php">Green Paper</a>
      <button type="button" class="menu-toggle" aria-expanded="false" aria-controls="menuLinks">Menu</button>
      <div class="menu-links" id="menuLinks">
      <a href="/index.php"<?= active($page === 'index.php') ?>>Latest</a>
      <a href="<?= BASE_URL ?>/category.php?name=Academic"<?= active($cat === 'Academic') ?>>Academic</a>
      <a href="<?= BASE_URL ?>/category.php?name=Events"<?= active($cat === 'Events') ?>>Events</a>
      <a href="<?= BASE_URL ?>/category.php?name=Sports"<?= active($cat === 'Sports') ?>>Sports</a>
      <a href="<?= BASE_URL ?>/category.php?name=General"<?= active($cat === 'General') ?>>General</a>
      </div>

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
<script>
(function () {
  var root = document.documentElement;
  var themeButton = document.querySelector('.theme-toggle');
  var syncTheme = function () {
    var dark = root.getAttribute('data-theme') === 'dark';
    if (themeButton) {
      themeButton.setAttribute('aria-pressed', dark ? 'true' : 'false');
      themeButton.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
    }
  };
  if (themeButton) {
    themeButton.addEventListener('click', function () {
      var next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.classList.add('theme-fade');
      root.setAttribute('data-theme', next);
      try { localStorage.setItem('theme', next); } catch (e) {}
      syncTheme();
      setTimeout(function () { root.classList.remove('theme-fade'); }, 450);
    });
  }
  syncTheme();

  var toggle = document.querySelector('.menu-toggle');
  var links = document.getElementById('menuLinks');
  if (toggle && links) {
    toggle.addEventListener('click', function () {
      var open = links.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  var menu = document.querySelector('.menu');
  if (menu) {
    var check = function () {
      menu.classList.toggle('stuck', menu.getBoundingClientRect().top <= 0);
    };
    window.addEventListener('scroll', check, {passive: true});
    check();
  }
})();
</script>

<main class="wrap">