<?php
session_start();
require_once __DIR__ . '/config.php';

// If already signed in, send the visitor to the correct page.
if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header('Location: admin_page.php');
    } else {
        header('Location: index.php');
    }
    exit();
}

$errors = [
    'login' => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
$success = $_SESSION['success'] ?? '';
$activeForm = $_SESSION['active_form'] ?? 'login';
unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['success'], $_SESSION['active_form']);

$categories = [];
$result = $conn->query('SELECT id, name FROM categories ORDER BY name');
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

$latestNews = [];
try {
    $result = $conn->query(
        "SELECT a.id, a.title, a.summary, a.published_at, c.name AS category
         FROM articles a
         JOIN categories c ON c.id = a.category_id
         WHERE a.status = 'approved'
         ORDER BY a.published_at DESC
         LIMIT 6"
    );
    $latestNews = $result->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $error) {
    // Keep authentication usable even if the latest-news query fails.
    $latestNews = [];
}

$leftNews = array_slice($latestNews, 0, 3);
$rightNews = array_slice($latestNews, 3, 3);

function showError($error) {
    return $error ? "<p class='error-message' role='alert'>" . htmlspecialchars($error, ENT_QUOTES, 'UTF-8') . '</p>' : '';
}

function showSuccess($message) {
    return $message ? "<p class='success-message' role='status'>" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . '</p>' : '';
}

function isActiveForm($formName, $activeForm) {
    return $activeForm === $formName ? 'active' : '';
}

function renderLatestNewsStack(array $stories, string $side): string {
    ob_start();
    ?>
    <aside class="auth-news-column auth-news-column--<?= htmlspecialchars($side, ENT_QUOTES, 'UTF-8') ?>" aria-label="Latest news articles">
        <div class="auth-news-heading">
            <span class="auth-kicker">Latest stories</span>
            <span class="auth-news-hint">Hover to browse</span>
        </div>
        <?php if ($stories): ?>
            <div class="auth-news-stack">
                <?php foreach ($stories as $index => $story): ?>
                    <a class="auth-news-card"
                       href="<?= BASE_URL ?>/article.php?id=<?= (int)$story['id'] ?>"
                       style="--stack-index: <?= (int)$index ?>; z-index: <?= count($stories) - (int)$index ?>;">
                        <span class="auth-news-meta">
                            <span><?= htmlspecialchars($story['category'], ENT_QUOTES, 'UTF-8') ?></span>
                            <time><?= !empty($story['published_at']) ? htmlspecialchars(date('d M Y', strtotime($story['published_at'])), ENT_QUOTES, 'UTF-8') : '' ?></time>
                        </span>
                        <strong><?= htmlspecialchars($story['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                        <?php if (!empty($story['summary'])): ?>
                            <span class="auth-news-summary"><?= htmlspecialchars($story['summary'], ENT_QUOTES, 'UTF-8') ?></span>
                        <?php endif; ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="auth-news-empty">Latest stories will appear here.</p>
        <?php endif; ?>
    </aside>
    <?php
    return ob_get_clean();
}

$pageTitle = 'Log in or register - Green Paper';
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-page">
    <div class="auth-intro">
        <span class="auth-kicker">Green Paper account</span>
        <h1>Campus news, your way.</h1>
        <p>Sign in to follow categories and keep up with the stories that matter to you.</p>
    </div>

    <div class="auth-layout">
        <?= renderLatestNewsStack($leftNews, 'left') ?>

        <div class="auth-form-column">
            <div class="auth-tabs" role="tablist">
                <button type="button" class="auth-tab" role="tab" data-target="login-form" aria-selected="<?= $activeForm === 'register' ? 'false' : 'true' ?>">Log in</button>
                <button type="button" class="auth-tab" role="tab" data-target="register-form" aria-selected="<?= $activeForm === 'register' ? 'true' : 'false' ?>">Create account</button>
            </div>
            <section class="form-box auth-form-box <?= isActiveForm('login', $activeForm) ?>" id="login-form">
                <form action="<?= BASE_URL ?>/login_register.php" method="post">
                    <h2>Welcome back</h2>
                    <?= showSuccess($success) ?>
                    <?= showError($errors['login']) ?>
                    <input type="email" name="email" placeholder="Email address" autocomplete="email" required>
                    <div class="pw-wrap">
                        <input type="password" name="password" placeholder="Password" autocomplete="current-password" required>
                        <button type="button" class="pw-toggle" aria-label="Show password">Show</button>
                    </div>
                    <button type="submit" name="login">Log in</button>
                    <p>New to Green Paper? <a href="#register-form" onclick="showform('register-form'); return false;">Create an account</a></p>
                </form>
            </section>

            <section class="form-box auth-form-box <?= isActiveForm('register', $activeForm) ?>" id="register-form">
                <form action="<?= BASE_URL ?>/login_register.php" method="post">
                    <h2>Create your account</h2>
                    <?= showError($errors['register']) ?>
                    <input type="text" name="name" placeholder="Full name" autocomplete="name" required>
                    <input type="email" name="email" placeholder="Email address" autocomplete="email" required>
                    <div class="pw-wrap">
                        <input type="password" name="password" id="register-password" placeholder="Password (at least 8 characters)" autocomplete="new-password" minlength="8" required>
                        <button type="button" class="pw-toggle" aria-label="Show password">Show</button>
                    </div>
                    <div class="pw-meter" id="pwMeter" data-level="0" aria-hidden="true"><span></span></div>
                    <p class="pw-hint" id="pwHint">Use 8 or more characters</p>

                    <p class="auth-field-label">Choose the categories you want to follow</p>
                    <div class="auth-category-options">
                        <?php foreach ($categories as $category): ?>
                            <label>
                                <input type="checkbox" name="categories[]" value="<?= (int)$category['id'] ?>">
                                <span><?= htmlspecialchars($category['name'], ENT_QUOTES, 'UTF-8') ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>

                    <button type="submit" name="register">Create account</button>
                    <p>Already registered? <a href="#login-form" onclick="showform('login-form'); return false;">Log in</a></p>
                </form>
            </section>
            <p class="auth-note">Create an account to personalize your feed with the campus categories you follow.</p>
        </div>

        <?= renderLatestNewsStack($rightNews, 'right') ?>
    </div>
</div>
<script src="<?= BASE_URL ?>/script.js"></script>
<script>
(function () {
    var tabs = document.querySelectorAll('.auth-tab');
    var sync = function () {
        tabs.forEach(function (tab) {
            var on = document.getElementById(tab.dataset.target).classList.contains('active');
            tab.setAttribute('aria-selected', on ? 'true' : 'false');
        });
    };
    tabs.forEach(function (tab) {
        tab.addEventListener('click', function () { showform(tab.dataset.target); });
    });
    document.querySelectorAll('.form-box').forEach(function (box) {
        new MutationObserver(sync).observe(box, {attributes: true, attributeFilter: ['class']});
    });
    sync();

    document.querySelectorAll('.pw-toggle').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = button.previousElementSibling;
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.textContent = show ? 'Hide' : 'Show';
            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    });

    var password = document.getElementById('register-password');
    var meter = document.getElementById('pwMeter');
    var hint = document.getElementById('pwHint');
    if (password && meter && hint) {
        var bar = meter.firstElementChild;
        password.addEventListener('input', function () {
            var value = password.value;
            var score = 0;
            if (value.length >= 8) { score++; }
            if (value.length >= 12) { score++; }
            if (/[a-z]/.test(value) && /[A-Z]/.test(value)) { score++; }
            if (/\d/.test(value)) { score++; }
            if (/[^A-Za-z0-9]/.test(value)) { score++; }
            var level = value.length === 0 ? 0 : (score >= 4 ? 3 : (score >= 2 ? 2 : 1));
            meter.setAttribute('data-level', level);
            bar.style.width = (value.length === 0 ? 0 : Math.max(12, score * 20)) + '%';
            hint.textContent = ['Use 8 or more characters', 'Weak: add more characters or numbers', 'Fair: add capitals or symbols', 'Strong password'][level];
        });
    }

    document.querySelectorAll('.auth-form-box form').forEach(function (form) {
        var button = form.querySelector('button[type="submit"]');
        var label = button.textContent;
        form.addEventListener('submit', function (event) {
            if (form.dataset.sent) {
                event.preventDefault();
                return;
            }
            form.dataset.sent = '1';
            button.textContent = 'Please wait...';
        });
        window.addEventListener('pageshow', function () {
            delete form.dataset.sent;
            button.textContent = label;
        });
    });
})();
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>