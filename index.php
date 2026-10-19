<?php
// Home page. Every story, announcement and category comes from the database.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Green Paper - Home';
require_once __DIR__ . '/includes/header.php';

// Set to true to shuffle the stories every time the page loads, false to keep them newest first.
$shuffleStories = true;

// ---------- 1. load the newest approved articles (one query) ----------
$tail = "FROM articles a
         JOIN categories c ON c.id = a.category_id
         JOIN users u ON u.id = a.author_id
         WHERE a.status = 'approved'
         ORDER BY a.published_at DESC
         LIMIT 30";

$articles = tryRows($conn, "SELECT a.id, a.title, a.summary, a.image, a.published_at, a.is_featured, a.is_breaking,
                                   c.name AS category, u.name AS author $tail");
if ($articles === null) {
    // The is_featured / is_breaking columns are not in the table yet: load without them
    $articles = tryRows($conn, "SELECT a.id, a.title, a.summary, a.image, a.published_at, 0 AS is_featured, 0 AS is_breaking,
                                       c.name AS category, u.name AS author $tail") ?? [];
}

// ---------- 2. split them into the sections of the page ----------
$featured = null;
$flagged = array_values(array_filter($articles, function ($a) { return !empty($a['is_featured']); }));
if ($flagged) {
    $featured = $shuffleStories ? $flagged[array_rand($flagged)] : $flagged[0];
} elseif ($articles) {
    $featured = $shuffleStories ? $articles[array_rand($articles)] : $articles[0];
}

$breaking = null;
foreach ($articles as $a) { if (!empty($a['is_breaking'])) { $breaking = $a; break; } }

$rest = array_values(array_filter($articles, function ($a) use ($featured) { return !$featured || $a['id'] != $featured['id']; }));
if ($shuffleStories) { shuffle($rest); }   // new order on every refresh
$topStories = array_slice($rest, 0, 2);     // the two stories beside the featured one
$moreNews   = array_slice($rest, 2, 3);     // the wide rows
$quickReads = array_slice($rest, 5, 4);     // the four small text cards

// ---------- 3. announcements and categories ----------
$announcements = tryRows($conn, "SELECT title, created_at FROM announcements WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3") ?? [];
$categories    = tryRows($conn, "SELECT name FROM categories ORDER BY name") ?? [];

// ---------- 4. for the "Load more" button: which stories are already shown, and are there more? ----------
$shownIds = [];
if ($featured) { $shownIds[] = (int)$featured['id']; }
foreach (array_merge($topStories, $moreNews, $quickReads) as $s) { $shownIds[] = (int)$s['id']; }
$totalRow = tryRows($conn, "SELECT COUNT(*) AS n FROM articles WHERE status = 'approved'");
$hasMore  = $totalRow && (int)$totalRow[0]['n'] > count($shownIds);
?>
<style>
    /* "Load more" button. Move these rules into news.css if you prefer. */
    .load-more-wrap { text-align: center; margin: 30px 0 10px; }
    .load-more-btn { font: inherit; font-size: 12px; font-weight: 700; letter-spacing: .5px; padding: 14px 34px; background: transparent; color: var(--accent); border: 1.5px solid var(--accent); cursor: pointer; }
    .load-more-btn:hover { background: var(--accent); color: #fff; }
    .load-more-btn:disabled { opacity: .6; cursor: default; }
</style>

<?php if ($breaking): ?>
    <div class="breaking">
        <strong>Breaking</strong>
        <span><a href="<?= artUrl($breaking['id']) ?>"><?= e($breaking['title']) ?></a></span>
    </div>
<?php endif; ?>

<div class="kicker-row">
    <span class="kicker">The stories shaping today</span>
    <span class="kicker-note">A closer look at what connects us on campus.</span>
</div>

<!-- ===== TOP GRID: featured / two stories / sidebar ===== -->
<section class="top-grid">

    <?php if ($featured): ?>
        <article class="lead">
            <?= imgBlock($featured, 'img-wide') ?>
            <div class="kicker kicker-meta"><?= e($featured['category']) ?> <i>&middot;</i> Featured</div>
            <h1 class="lead-title"><a href="<?= artUrl($featured['id']) ?>"><?= e($featured['title']) ?></a></h1>
            <p class="lead-summary"><?= e($featured['summary']) ?></p>
            <div class="meta">By <?= e($featured['author']) ?> <i>&middot;</i> <?= fmtDate($featured['published_at']) ?></div>
        </article>
    <?php else: ?>
        <article class="lead">
            <p class="lead-summary">No stories have been published yet.</p>
        </article>
    <?php endif; ?>

    <div class="mid-col">
        <?php foreach ($topStories as $s): ?>
            <article class="story">
                <?= imgBlock($s, 'img-box') ?>
                <div class="kicker kicker-meta"><?= e($s['category']) ?></div>
                <h3 class="story-title"><a href="<?= artUrl($s['id']) ?>"><?= e($s['title']) ?></a></h3>
                <div class="meta"><?= fmtDate($s['published_at']) ?></div>
            </article>
        <?php endforeach; ?>
    </div>

    <aside class="sidebar">
        <section class="side-block">
            <h2 class="side-title">Announcements <span class="live">Live</span></h2>
            <ul class="ann-list">
                <?php foreach ($announcements as $a): ?>
                    <li>
                        <span class="ann-date"><?= strtoupper(date('d M', strtotime($a['created_at']))) ?></span>
                        <span class="ann-title"><?= e($a['title']) ?></span>
                    </li>
                <?php endforeach; ?>
                <?php if (!$announcements): ?>
                    <li><span class="ann-title">No announcements right now.</span></li>
                <?php endif; ?>
            </ul>
        </section>

        <section class="side-block">
            <h2 class="side-title">Categories</h2>
            <ul class="cat-list">
                <?php foreach ($categories as $c): ?>
                    <li><a href="<?= BASE_URL ?>/category.php?name=<?= urlencode($c['name']) ?>"><span><?= e($c['name']) ?></span><i>&gt;</i></a></li>
                <?php endforeach; ?>
            </ul>
        </section>
    </aside>

</section>

<!-- ===== MORE NEWS ===== -->
<?php if ($moreNews): ?>
<section class="more-news">
    <div class="kicker">More news</div>
    <?php foreach ($moreNews as $n) { echo renderRow($n); } ?>
</section>
<?php endif; ?>

<!-- ===== QUICK READS ===== -->
<?php if ($quickReads): ?>
<section class="quick-reads">
    <div class="kicker">Quick reads</div>
    <div class="quick-grid">
        <?php foreach ($quickReads as $q): ?>
            <article class="quick">
                <h3><a href="<?= artUrl($q['id']) ?>"><?= e($q['title']) ?></a></h3>
                <p><?= e($q['summary']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- ===== STORIES ADDED BY "LOAD MORE" (hidden until the first click) ===== -->
<section class="more-news" id="loadedStories" hidden>
    <div class="kicker">More stories</div>
    <div id="loadedList"></div>
</section>

<div class="load-more-wrap" id="loadMoreWrap"<?= $hasMore ? '' : ' hidden' ?>>
    <button type="button" id="loadMoreBtn" class="load-more-btn">Load more stories</button>
</div>

<script>
// ----- Load more: asks load_more.php for the next stories and adds them under the page -----
(function () {
    var shown  = <?= json_encode($shownIds) ?>;     // ids already on the page, so nothing repeats
    var btn    = document.getElementById('loadMoreBtn');
    var wrap   = document.getElementById('loadMoreWrap');
    var box    = document.getElementById('loadedStories');
    var list   = document.getElementById('loadedList');

    btn.addEventListener('click', function () {
        btn.disabled = true;
        btn.textContent = 'Loading...';
        fetch('<?= BASE_URL ?>/load_more.php?exclude=' + shown.join(','))
            .then(function (r) { return r.json(); })
            .then(function (data) {
                list.insertAdjacentHTML('beforeend', data.html);
                box.hidden = false;
                shown = shown.concat(data.ids);
                if (data.hasMore) { btn.disabled = false; btn.textContent = 'Load more stories'; }
                else { wrap.hidden = true; }
            })
            .catch(function () { btn.disabled = false; btn.textContent = 'Could not load. Try again'; });
    });
})();

// ----- Save button toggle (visual only for now; also works on stories added by "Load more") -----
document.addEventListener('click', function (e) {
    var b = e.target.closest('.save-btn');
    if (!b) { return; }
    b.classList.toggle('saved');
    b.querySelector('.save-label').textContent = b.classList.contains('saved') ? 'Saved' : 'Save';
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>