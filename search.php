<?php
// Search results page. Uses the same database connection (config.php) as the rest of the site.
require_once __DIR__ . '/config.php';
$pageTitle = 'Search - Green Paper';
require_once __DIR__ . '/includes/header.php';

if (!function_exists('fmtDate')) {
    function fmtDate($d) { return $d ? date('d M Y', strtotime($d)) : ''; }
}

$q = trim($_GET['q'] ?? '');
$results = [];

if ($q !== '') {
    // Escape the special characters % and _ so the visitor's text is searched literally
    $like = '%' . addcslashes($q, '%_\\') . '%';

    $stmt = $conn->prepare(
        "SELECT a.id, a.title, a.summary, a.body, a.published_at, c.name AS category
         FROM articles a
         JOIN categories c ON c.id = a.category_id
         WHERE a.status = 'approved'
           AND (a.title LIKE ? OR a.summary LIKE ? OR a.body LIKE ?)
         ORDER BY a.published_at DESC"
    );
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>
<style>
    /* Search page only. Move these rules into news.css if you prefer. */
    .search-page-form { display: flex; gap: 10px; margin: 6px 0 18px; }
    .search-page-form input { flex: 1; font: inherit; font-size: 15px; padding: 11px 12px; border: 1px solid var(--line); background: #fff; }
    .search-page-form button { font: inherit; font-size: 11px; font-weight: 700; padding: 0 22px; background: var(--accent); color: #fff; border: 0; cursor: pointer; }
    .search-empty { font-size: 13px; color: var(--muted); padding: 18px 0; }
</style>

<div class="kicker-row">
    <span class="kicker">Search</span>
    <span class="kicker-note">
        <?php if ($q !== ''): ?>
            <?= count($results) ?> result<?= count($results) === 1 ? '' : 's' ?> for &ldquo;<?= e($q) ?>&rdquo;
        <?php else: ?>
            Search every approved story
        <?php endif; ?>
    </span>
</div>

<form class="search-page-form" action="<?= BASE_URL ?>/search.php" method="get">
    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search the news" autocomplete="off">
    <button type="submit">Search</button>
</form>

<section class="more-news">
    <?php if ($q === ''): ?>
        <p class="search-empty">Type a word above to search titles, summaries and article text.</p>
    <?php elseif (!$results): ?>
        <p class="search-empty">No results found. Try a different word.</p>
    <?php else: ?>
        <?php foreach ($results as $r):
            $blurb = $r['summary'] !== null && $r['summary'] !== '' ? $r['summary'] : mb_substr(strip_tags($r['body']), 0, 160) . '...';
        ?>
            <article class="row">
                <a href="<?= BASE_URL ?>/article.php?id=<?= (int)$r['id'] ?>" class="img-ph img-row"><span>IMAGE</span></a>
                <div class="row-body">
                    <div class="kicker kicker-meta"><?= e($r['category']) ?></div>
                    <h3 class="row-title"><a href="<?= BASE_URL ?>/article.php?id=<?= (int)$r['id'] ?>"><?= e($r['title']) ?></a></h3>
                    <p class="row-summary"><?= e($blurb) ?></p>
                    <div class="row-foot"><span class="meta"><?= fmtDate($r['published_at']) ?></span></div>
                </div>
            </article>
        <?php endforeach; ?>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>