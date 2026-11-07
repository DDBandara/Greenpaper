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
            <img id="preview" class="preview" alt="Selected image preview" hidden>
            <p id="imageNote" class="counter over" hidden></p>

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

<script>
(function () {
    var limits = {title: 200, summary: 300};
    Object.keys(limits).forEach(function (id) {
        var field = document.getElementById(id);
        var out = document.createElement('div');
        out.className = 'counter';
        field.insertAdjacentElement('afterend', out);
        var update = function () {
            var count = field.value.length;
            out.textContent = count + ' / ' + limits[id];
            out.classList.toggle('over', count > limits[id]);
        };
        field.addEventListener('input', update);
        update();
    });

    var body = document.getElementById('body');
    var bodyOut = document.createElement('div');
    bodyOut.className = 'counter';
    body.insertAdjacentElement('afterend', bodyOut);
    var updateBody = function () {
        var count = body.value.trim().length;
        bodyOut.textContent = count + ' characters (at least 50 to submit)';
    };
    body.addEventListener('input', updateBody);
    updateBody();

    var input = document.getElementById('image');
    var preview = document.getElementById('preview');
    var note = document.getElementById('imageNote');
    input.addEventListener('change', function () {
        var file = input.files[0];
        note.hidden = true;
        preview.hidden = true;
        if (!file) { return; }
        var okType = ['image/jpeg', 'image/png', 'image/webp'].indexOf(file.type) !== -1;
        if (!okType || file.size > 2 * 1024 * 1024) {
            note.textContent = !okType ? 'Choose a JPG, PNG or WebP image.' : 'The image must be 2 MB or smaller.';
            note.hidden = false;
            input.value = '';
            return;
        }
        preview.src = URL.createObjectURL(file);
        preview.hidden = false;
    });
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>