<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

function timeAgo($date) {
    $seconds = time() - strtotime($date);
    if ($seconds < 60) {
        return 'just now';
    }
    if ($seconds < 3600) {
        $n = (int)floor($seconds / 60);
        return $n . ' minute' . ($n === 1 ? '' : 's') . ' ago';
    }
    if ($seconds < 86400) {
        $n = (int)floor($seconds / 3600);
        return $n . ' hour' . ($n === 1 ? '' : 's') . ' ago';
    }
    if ($seconds < 2592000) {
        $n = (int)floor($seconds / 86400);
        return $n . ' day' . ($n === 1 ? '' : 's') . ' ago';
    }
    return date('d M Y', strtotime($date));
}

$id = (int)($_GET['id'] ?? 0);
$userId = (int)($_SESSION['user_id'] ?? 0);
$isAdmin = ($_SESSION['role'] ?? '') === 'admin';

$stmt = $conn->prepare("SELECT a.id, a.title, a.summary, a.body, a.image, a.status, a.published_at, a.author_id, a.category_id,
                               c.name AS category, u.name AS author
                        FROM articles a
                        JOIN categories c ON c.id = a.category_id
                        JOIN users u ON u.id = a.author_id
                        WHERE a.id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$article = $stmt->get_result()->fetch_assoc();

$allowed = $article && ($article['status'] === 'approved' || $isAdmin || ($userId && $userId === (int)$article['author_id']));
if (!$allowed) {
    http_response_code(404);
    $pageTitle = 'Article not found';
    require_once __DIR__ . '/includes/header.php';
    echo '<p class="main-text" style="padding:40px 0">That article could not be found.</p>';
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

$published = $article['status'] === 'approved';

if ($published && empty($_SESSION['viewed'][$id])) {
    $stmt = $conn->prepare('UPDATE articles SET views = views + 1 WHERE id = ?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $_SESSION['viewed'][$id] = true;
}

$saved = false;
if ($userId) {
    $stmt = $conn->prepare('SELECT 1 FROM bookmarks WHERE user_id = ? AND article_id = ?');
    $stmt->bind_param('ii', $userId, $id);
    $stmt->execute();
    $saved = $stmt->get_result()->num_rows > 0;
}

$comments = [];
try {
    $stmt = $conn->prepare("SELECT cm.body, cm.created_at, u.name
                            FROM comments cm
                            JOIN users u ON u.id = cm.user_id
                            WHERE cm.article_id = ? AND cm.status = 'visible'
                            ORDER BY cm.created_at ASC");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $comments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (mysqli_sql_exception $error) {
    $comments = [];
}

$related = [];
$stmt = $conn->prepare("SELECT id, title, published_at FROM articles
                        WHERE status = 'approved' AND category_id = ? AND id <> ?
                        ORDER BY published_at DESC LIMIT 3");
$stmt->bind_param('ii', $article['category_id'], $id);
$stmt->execute();
$related = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$minutes = max(1, (int)ceil(str_word_count(strip_tags($article['body'])) / 200));
$paragraphs = preg_split('/\R{2,}/', trim($article['body']));

$commentError = $_SESSION['comment_error'] ?? '';
$commentOk = $_SESSION['comment_ok'] ?? '';
unset($_SESSION['comment_error'], $_SESSION['comment_ok']);

$pageTitle = $article['title'] . ' - Green Paper';
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/article.css">

<div class="article-layout">

    <article class="article-main">
        <?php if (!$published): ?>
            <div class="note">This article is not published yet. Its status is <strong><?= safe($article['status']) ?></strong>.</div>
        <?php endif; ?>

        <div class="label"><?= safe($article['category']) ?></div>
        <h1 class="article-title"><?= safe($article['title']) ?></h1>
        <p class="article-sub"><?= safe($article['summary']) ?></p>

        <div class="article-meta">
            <span>By <?= safe($article['author']) ?> &nbsp;|&nbsp; <?= $published ? showDate($article['published_at']) : 'Not published' ?> &nbsp;|&nbsp; <?= $minutes ?> min read</span>

            <?php if ($userId && $published): ?>
                <form action="<?= BASE_URL ?>/toggle_bookmark.php" method="post">
                    <input type="hidden" name="csrf" value="<?= safe($_SESSION['csrf']) ?>">
                    <input type="hidden" name="article" value="<?= $id ?>">
                    <button type="submit" class="bookmark-btn<?= $saved ? ' saved' : '' ?>">
                        <svg width="12" height="16" viewBox="0 0 12 16"><path d="M1 1h10v14l-5-3.5L1 15z" fill="none" stroke="currentColor" stroke-width="1.2"/></svg>
                        <?= $saved ? 'Saved' : 'Save article' ?>
                    </button>
                </form>
            <?php elseif ($published): ?>
                <a class="bookmark-btn" href="<?= BASE_URL ?>/login.php">
                    <svg width="12" height="16" viewBox="0 0 12 16"><path d="M1 1h10v14l-5-3.5L1 15z" fill="none" stroke="currentColor" stroke-width="1.2"/></svg>
                    Save article
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($article['image'])): ?>
            <div class="article-image">
                <img src="<?= safe(BASE_URL . '/uploads/' . $article['image']) ?>" alt="<?= safe($article['title']) ?>">
            </div>
        <?php else: ?>
            <div class="pic pic-wide"><span>IMAGE</span></div>
        <?php endif; ?>

        <div class="article-text">
            <?php foreach ($paragraphs as $paragraph): ?>
                <p><?= nl2br(safe($paragraph)) ?></p>
            <?php endforeach; ?>
        </div>

        <section class="comments" id="comments">
            <div class="label">Comments &middot; <?= count($comments) ?></div>

            <?php if ($commentOk): ?>
                <div class="note"><?= safe($commentOk) ?></div>
            <?php endif; ?>
            <?php if ($commentError): ?>
                <div class="note note-error"><?= safe($commentError) ?></div>
            <?php endif; ?>

            <?php if ($userId && $published): ?>
                <form class="comment-form" action="<?= BASE_URL ?>/comment_add.php" method="post">
                    <input type="hidden" name="csrf" value="<?= safe($_SESSION['csrf']) ?>">
                    <input type="hidden" name="article" value="<?= $id ?>">
                    <textarea name="comment" maxlength="1000" placeholder="Write a comment..." required></textarea>
                    <button type="submit">Post comment</button>
                </form>
            <?php elseif ($published): ?>
                <p class="comment-login"><a href="<?= BASE_URL ?>/login.php">Log in</a> to write a comment.</p>
            <?php endif; ?>

            <?php foreach ($comments as $comment): ?>
                <div class="comment">
                    <div class="comment-name"><?= safe($comment['name']) ?> <span><?= timeAgo($comment['created_at']) ?></span></div>
                    <div class="comment-text"><?= nl2br(safe($comment['body'])) ?></div>
                </div>
            <?php endforeach; ?>
        </section>
    </article>

    <aside class="article-side">
        <div class="side-list">
            <h2>More from <?= safe($article['category']) ?></h2>
            <?php foreach ($related as $item): ?>
                <div class="side-item">
                    <div class="side-date"><?= strtoupper(date('d M', strtotime($item['published_at']))) ?></div>
                    <a href="<?= storyLink($item['id']) ?>"><?= safe($item['title']) ?></a>
                </div>
            <?php endforeach; ?>
            <?php if (!$related): ?>
                <div class="side-item"><a href="<?= BASE_URL ?>/category.php?name=<?= urlencode($article['category']) ?>">See all <?= safe($article['category']) ?> stories</a></div>
            <?php endif; ?>
        </div>
    </aside>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
