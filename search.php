<?php
require_once DIR . '/includes/db.php';
require_once DIR . '/includes/header.php';

$q = trim($_GET['q'] ?? '');
$results = [];
if ($q !== '') {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE status='approved' AND (title LIKE ? OR content LIKE ?) ORDER BY created_at DESC");
    $like = "%$q%";
    $stmt->execute([$like, $like]);
    $results = $stmt->fetchAll();
}
?>
<h2 style="margin:20px 0;">Search results for: <?= e($q) ?></h2>
<?php if(!$results): ?>
<p>No results found.</p>
<?php else: ?>
<div class="card-grid">
<?php foreach($results as $r): ?>
<div class="card">
  <div class="card-body">
    <h3><a href="article.php?id=<?= $r['id'] ?>"><?= e($r['title']) ?></a></h3>
    <p><?= e(substr($r['content'],0,120)) ?>...</p>
  </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php require_once DIR . '/includes/footer.php'; ?>