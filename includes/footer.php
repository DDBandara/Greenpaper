</main>
<footer class="site-footer">
    <div class="footer-inner">
        <span>NSBM NewsDesk &middot; Campus news, events and announcements</span>
        <span class="footer-links">
            <?php if (!empty($isLoggedIn)): ?>
                <a href="<?= BASE_URL ?>/user_page.php">My feed</a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>/login.php">Log in</a>
                <i>&middot;</i>
                <a href="<?= BASE_URL ?>/register.php">Register</a>
            <?php endif; ?>
            <i>&middot;</i>
            <a href="<?= BASE_URL ?>/contact.php">Contact</a>
        </span>
    </div>
</footer>
</body>
</html>