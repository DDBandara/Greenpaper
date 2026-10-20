<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['login_error'] = 'Please log in to submit an article.';
    $_SESSION['active_form'] = 'login';
    header('Location: login.php');
    exit();
}

if (empty($_SESSION['csrf'])) { $_SESSION['csrf'] = bin2hex(random_bytes(32)); }

$categories = getRows($conn, "SELECT id, name FROM categories ORDER BY name") ?? [];

$errors = $_SESSION['submit_errors'] ?? [];
$old    = $_SESSION['submit_old'] ?? [];
$ok     = $_SESSION['submit_ok'] ?? '';
unset($_SESSION['submit_errors'], $_SESSION['submit_old'], $_SESSION['submit_ok']);

$pageTitle = 'Submit an article - Green Paper';
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/submit.css">

<div class="submit-page">
    <p class="eyebrow">Write for NSBM NewsDesk</p>
    <h1>Submit an article</h1>
    <p class="lede">An admin reviews every submission before it is published.</p>

    <?php if ($ok): ?>
        <div class="notice notice-ok"><?= safe($ok) ?></div>
    <?php endif; ?>
    <?php if ($errors): ?>
        <div class="notice notice-error">
            <?php foreach ($errors as $err): ?><div><?= safe($err) ?></div><?php endforeach; ?>
        </div>
    <?php endif; ?>

    <form method="post" action="<?= BASE_URL ?>/submit_article.php" enctype="multipart/form-data">
        <input type="hidden" name="csrf" value="<?= safe($_SESSION['csrf']) ?>">

        <div class="left">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" class="title" maxlength="200" placeholder="Article title" value="<?= safe($old['title'] ?? '') ?>" required>

            <label for="summary">Summary (one or two sentences, shown on the card)</label>
            <textarea id="summary" name="summary" class="summary" maxlength="300" placeholder="A short summary of the story" required><?= safe($old['summary'] ?? '') ?></textarea>

            <label for="body">Body</label>
            <textarea id="body" name="body" class="body-text" placeholder="Write the article here" required><?= safe($old['body'] ?? '') ?></textarea>
        </div>

        <div class="right">
            <label for="category">Category</label>
            <select id="category" name="category" required>
                <option value="" disabled <?= empty($old['category']) ? 'selected' : '' ?>>Choose a category</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= (($old['category'] ?? '') == $c['id']) ? 'selected' : '' ?>><?= safe($c['name']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="image">Image (optional)</label>
            <input type="file" id="image" name="image" class="upload" accept="image/png, image/jpeg, image/webp">
            <small>JPG, PNG or WebP, max 2 MB</small>

            <button type="submit" name="action" value="submit" class="primary">Submit for approval</button>
            <button type="submit" name="action" value="draft" class="secondary" formnovalidate>Save draft</button>

            <div class="how">
                <h2>How it works</h2>
                <p><span>1</span> You submit the article.</p>
                <p><span>2</span> An admin reads it.</p>
                <p><span>3</span> It is published, or sent back with feedback.</p>
            </div>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>