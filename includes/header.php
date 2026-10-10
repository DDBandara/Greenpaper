<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
if (!defined('BASE_URL')) { 
    define('BASE_URL', '/newsdesk1.0'); 
}
if (!function_exists('e')) { 
    function e($v){ return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); } 
}
$pageTitle = $pageTitle ?? 'NSBM NewsDesk';

// LOGIN CHECK - important
$isLoggedIn = false;
if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
    $isLoggedIn = true;
}
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

<div class="top-strip">
  <div class="top-strip-inner">
    <span>NSBM GREEN UNIVERSITY / CAMPUS NEWS</span>
    <span>OPEN TO EVERYONE / REVIEWED BY ADMINS</span>
  </div>
</div>

<header class="site-header">
  <div class="header-inner">
    <div class="header-left">
      <div class="date-main">Thursday, October 9, 2026</div>
      <div class="date-sub">The daily edition · Your campus in one place</div>
    </div>
    
    <div class="header-center">
      <a class="brand" href="<?= BASE_URL ?>/index.php">NSBM NewsDesk</a>
      <div class="brand-sub">CAMPUS NEWS, EVENTS AND ANNOUNCEMENTS</div>
    </div>

    <div class="header-right">
      <?php if ($isLoggedIn): ?>
        <!-- LOG In -->
        <a href="<?= BASE_URL ?>/user_page.php">My feed</a>
        <a href="<?= BASE_URL ?>/logout.php">Log out</a>
      <?php else: ?>
        <!-- LOG IN -->
        <a href="<?= BASE_URL ?>/login.php">Log in</a>
      <?php endif; ?>
      <a class="btn-submit" href="<?= BASE_URL ?>/submit.php">Submit an article</a>
    </div>
  </div>

<nav class="cat-nav">
    <div class="cat-nav-inner">
      <a href="<?= BASE_URL ?>/index.php">Latest</a>
      <a href="<?= BASE_URL ?>/category.php?name=Academic">Academic</a>
      <a href="<?= BASE_URL ?>/category.php?name=Events">Events</a>
      <a href="<?= BASE_URL ?>/category.php?name=Sports">Sports</a>
      <a href="<?= BASE_URL ?>/category.php?name=General">General</a>
      
      <form class="search-area" action="<?= BASE_URL ?>/search.php" method="GET">
        <span class="search-icon">⌕</span>
        <input type="text" name="q" id="searchInput" placeholder="Search" autocomplete="off">
      </form>
    </div>
  </nav>
</header>

<script>
// CLICK THE SEARCH BAR AND COMING CURSOR (JS)
document.querySelector('.search-area').addEventListener('click', function(){
    document.getElementById('searchInput').focus();
});
document.querySelector('.search-icon').addEventListener('click', function(){
    document.getElementById('searchInput').focus();
});
</script>

<main class="container">