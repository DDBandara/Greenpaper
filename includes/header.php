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
$pageTitle = $pageTitle ?? 'Green Paper';

// LOGIN CHECK
$isLoggedIn = !empty($_SESSION['user_id']);

// Which nav link is active
$currentFile = basename($_SERVER['PHP_SELF']);
$currentCat  = ($currentFile === 'category.php') ? ($_GET['name'] ?? '') : '';
if (!function_exists('navActive')) {
    function navActive($isActive) { return $isActive ? ' class="active"' : ''; }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&display=swap" rel="stylesheet">
<?php $cssVer = @filemtime(dirname(__DIR__) . '/news.css') ?: time(); ?>
<link rel="stylesheet" href="<?= BASE_URL ?>/news.css?v=<?= $cssVer ?>">
</head>
<body>

<div class="top-strip">
  <div class="top-strip-inner">
    <span>NSBM Green University / Campus News</span>
    <span>Open to everyone / Reviewed by admins</span>
  </div>
</div>

<header class="site-header">
  <div class="header-inner">
    <div class="header-left">
      <div class="date-main"><?= date('l, F j, Y') ?></div>
      <div class="date-sub">The daily edition &middot; Your campus in one place</div>
    </div>

    <div class="header-center">
      <a class="brand" href="<?= BASE_URL ?>/index.php">Green Paper</a>
      <div class="brand-sub">Campus news, events and announcements</div>
    </div>

    <div class="header-right">
      <?php if ($isLoggedIn): ?>
        <a href="<?= BASE_URL ?>/user_page.php">My feed</a>
        <a href="<?= BASE_URL ?>/logout.php">Log out</a>
      <?php else: ?>
        <a href="<?= BASE_URL ?>/login.php">Log in</a>
      <?php endif; ?>
      <a class="btn-submit" href="<?= BASE_URL ?>/submit.php">Submit an article</a>
    </div>
  </div>

  <nav class="cat-nav">
    <div class="cat-nav-inner">
      <a href="<?= BASE_URL ?>/index.php"<?= navActive($currentFile === 'index.php') ?>>Latest</a>
      <a href="<?= BASE_URL ?>/category.php?name=Academic"<?= navActive($currentCat === 'Academic') ?>>Academic</a>
      <a href="<?= BASE_URL ?>/category.php?name=Events"<?= navActive($currentCat === 'Events') ?>>Events</a>
      <a href="<?= BASE_URL ?>/category.php?name=Sports"<?= navActive($currentCat === 'Sports') ?>>Sports</a>
      <a href="<?= BASE_URL ?>/category.php?name=General"<?= navActive($currentCat === 'General') ?>>General</a>

      <form class="search-area" action="<?= BASE_URL ?>/search.php" method="GET">
        <svg class="search-icon" width="16" height="16" viewBox="0 0 16 16" aria-hidden="true">
          <circle cx="6.5" cy="6.5" r="5" fill="none" stroke="currentColor" stroke-width="1.2"/>
          <line x1="10.2" y1="10.2" x2="15" y2="15" stroke="currentColor" stroke-width="1.2"/>
        </svg>
        <input type="text" name="q" id="searchInput" placeholder="Search" autocomplete="off">
      </form>
    </div>
  </nav>
</header>

<script>
// Click anywhere on the search area to focus the input
document.querySelector('.search-area').addEventListener('click', function(){
    document.getElementById('searchInput').focus();
});
</script>

<main class="container">