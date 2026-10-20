<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/newsdesk1.0');
}
$loggedIn = !empty($_SESSION['user_id']);
?>
</main>
<footer class="footer">
    <div class="footer-box">
        <span>NSBM NewsDesk &middot; Campus news, events and announcements</span>
        <span class="footer-links">
            <?php if ($loggedIn): ?>
                <a href="<?= BASE_URL ?>/user_page.php">My feed</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php">Log in</a>
                <i>&middot;</i>
                <a href="<?= BASE_URL ?>/login.php">Register</a>
            <?php endif; ?>
            <i>&middot;</i>
            <a href="<?= BASE_URL ?>/contact.php">Contact</a>
        </span>
    </div>
</footer>
</body>
</html>