<?php
require_once __DIR__ . '/../includes/admin.php';

$tab = $_GET['tab'] ?? 'visible';
if (!in_array($tab, ['visible', 'hidden', 'all'], true)) {
    $tab = 'visible';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $action = $_POST['action'] ?? '';
    $tab = in_array($_POST['tab'] ?? '', ['visible', 'hidden', 'all'], true) ? $_POST['tab'] : 'visible';

    try {
        if (!csrfOk()) {
            flash('The form expired. Please try again.');
        } elseif ($action === 'hide' || $action === 'show') {
            $status = $action === 'hide' ? 'hidden' : 'visible';
            $stmt = $conn->prepare('UPDATE comments SET status = ? WHERE id = ?');
            $stmt->bind_param('si', $status, $id);
            $stmt->execute();
            flash($action === 'hide' ? 'The comment is now hidden.' : 'The comment is visible again.');
        } elseif ($action === 'delete') {
            $stmt = $conn->prepare('DELETE FROM comments WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            flash('The comment was deleted.');
        }
    } catch (mysqli_sql_exception $error) {
        flash('That could not be saved. Please try again.');
    }
    header('Location: comments.php?tab=' . $tab);
    exit();
}

$counts = ['visible' => 0, 'hidden' => 0];
$comments = [];
$problem = '';
try {
    foreach (getRows($conn, 'SELECT status, COUNT(*) AS total FROM comments GROUP BY status') ?? [] as $row) {
        $counts[$row['status']] = (int)$row['total'];
    }
    $sql = 'SELECT cm.id, cm.body, cm.status, cm.created_at, u.name, a.id AS article_id, a.title
            FROM comments cm
            JOIN users u ON u.id = cm.user_id
            JOIN articles a ON a.id = cm.article_id';
    if ($tab !== 'all') {
        $sql .= " WHERE cm.status = '" . $tab . "'";
    }
    $sql .= ' ORDER BY cm.created_at DESC LIMIT 200';
    $comments = getRows($conn, $sql) ?? [];
} catch (mysqli_sql_exception $error) {
    $problem = 'The comments table could not be read.';
}
$all = $counts['visible'] + $counts['hidden'];

adminHeader('Comments', 'comments');
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/admin/admin.css">


<div class="page-top">
    <span class="page-label">Comments &bull; <?= $all ?></span>
</div>

<nav class="tabs">
    <a href="comments.php?tab=visible"<?= $tab === 'visible' ? ' class="active"' : '' ?>>Visible <span><?= $counts['visible'] ?></span></a>
    <a href="comments.php?tab=hidden"<?= $tab === 'hidden' ? ' class="active"' : '' ?>>Hidden <span><?= $counts['hidden'] ?></span></a>
    <a href="comments.php?tab=all"<?= $tab === 'all' ? ' class="active"' : '' ?>>All <span><?= $all ?></span></a>
</nav>

<div class="comment-list">
    <?php if ($problem): ?>
        <p class="grey"><?= safe($problem) ?></p>
    <?php endif; ?>

    <?php foreach ($comments as $comment): ?>
        <div class="comment-item">
            <div class="comment-content">
                <?php if ($comment['status'] === 'hidden'): ?>
                    <div class="badge">Hidden</div>
                <?php endif; ?>
                <p class="comment-text<?= $comment['status'] === 'hidden' ? ' faded' : '' ?>"><?= nl2br(safe($comment['body'])) ?></p>
                <div class="comment-meta">
                    <span><?= safe($comment['name']) ?></span>
                    <i>&bull;</i>
                    <span>on <a href="<?= storyLink($comment['article_id']) ?>">&ldquo;<?= safe($comment['title']) ?>&rdquo;</a></span>
                    <i>&bull;</i>
                    <span><?= timeAgo($comment['created_at']) ?></span>
                </div>
            </div>

            <form class="comment-actions" method="post" action="comments.php">
                <input type="hidden" name="csrf" value="<?= safe($_SESSION['csrf']) ?>">
                <input type="hidden" name="id" value="<?= (int)$comment['id'] ?>">
                <input type="hidden" name="tab" value="<?= safe($tab) ?>">
                <?php if ($comment['status'] === 'hidden'): ?>
                    <button type="submit" name="action" value="show" class="link-btn">Show</button>
                <?php else: ?>
                    <button type="submit" name="action" value="hide" class="link-btn">Hide</button>
                <?php endif; ?>
                <button type="submit" name="action" value="delete" class="link-btn danger" onclick="return confirm('Delete this comment for good?')">Delete</button>
            </form>
        </div>
    <?php endforeach; ?>

    <?php if (!$comments && !$problem): ?>
        <p class="grey">No comments here.</p>
    <?php endif; ?>
</div>

<?php adminFooter(); ?>