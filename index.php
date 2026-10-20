<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Green Paper - Home';
require_once __DIR__ . '/includes/header.php';

$shuffle = true;

$sql = "SELECT a.id, a.title, a.summary, a.image, a.published_at, a.is_featured,
               c.name AS category, u.name AS author
        FROM articles a
        JOIN categories c ON c.id = a.category_id
        JOIN users u ON u.id = a.author_id
        WHERE a.status = 'approved'
        ORDER BY a.published_at DESC
        LIMIT 30";

$all = getRows($conn, $sql);
if ($all === null) {
    $all = getRows($conn, str_replace('a.is_featured,', '0 AS is_featured,', $sql)) ?? [];
}

$main = null;
$starred = array_values(array_filter($all, function ($story) {
    return !empty($story['is_featured']);
}));
if ($starred) {
    $main = $shuffle ? $starred[array_rand($starred)] : $starred[0];
} elseif ($all) {
    $main = $shuffle ? $all[array_rand($all)] : $all[0];
}

$others = array_values(array_filter($all, function ($story) use ($main) {
    return !$main || $story['id'] != $main['id'];
}));
if ($shuffle) {
    shuffle($others);
}
$second = array_slice($others, 0, 2);
$more   = array_slice($others, 2, 3);
$quick  = array_slice($others, 5, 4);

$notices = getRows($conn, "SELECT title, created_at FROM announcements WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3") ?? [];
$cats    = getRows($conn, "SELECT name FROM categories ORDER BY name") ?? [];

$shown = [];
if ($main) {
    $shown[] = (int)$main['id'];
}
foreach (array_merge($second, $more, $quick) as $story) {
    $shown[] = (int)$story['id'];
}
$count = getRows($conn, "SELECT COUNT(*) AS total FROM articles WHERE status = 'approved'");
$moreLeft = $count && (int)$count[0]['total'] > count($shown);
?>

<div class="label-row">
    <span class="label">The stories shaping today</span>
    <span class="label-note">A closer look at what connects us on campus.</span>
</div>

<section class="top">

    <article class="main-story">
        <?php if ($main): ?>
            <?= picture($main, 'pic-wide') ?>
            <div class="label label-small"><?= safe($main['category']) ?> <i>&middot;</i> Featured</div>
            <h1 class="main-title"><a href="<?= storyLink($main['id']) ?>"><?= safe($main['title']) ?></a></h1>
            <p class="main-text"><?= safe($main['summary']) ?></p>
            <div class="small">By <?= safe($main['author']) ?> <i>&middot;</i> <?= showDate($main['published_at']) ?></div>
        <?php else: ?>
            <p class="main-text">No stories have been published yet.</p>
        <?php endif; ?>
    </article>

    <div class="middle">
        <?php foreach ($second as $story): ?>
            <article class="story">
                <?= picture($story, 'pic-box') ?>
                <div class="label label-small"><?= safe($story['category']) ?></div>
                <h3 class="story-title"><a href="<?= storyLink($story['id']) ?>"><?= safe($story['title']) ?></a></h3>
                <div class="small"><?= showDate($story['published_at']) ?></div>
            </article>
        <?php endforeach; ?>
    </div>

    <aside class="side">
        <section class="side-box">
            <h2 class="side-heading">Announcements <span class="live">Live</span></h2>
            <ul class="notice-list">
                <?php foreach ($notices as $notice): ?>
                    <li>
                        <span class="notice-date"><?= strtoupper(date('d M', strtotime($notice['created_at']))) ?></span>
                        <span class="notice-title"><?= safe($notice['title']) ?></span>
                    </li>
                <?php endforeach; ?>
                <?php if (!$notices): ?>
                    <li><span class="notice-title">No announcements right now.</span></li>
                <?php endif; ?>
            </ul>
        </section>

        <section class="side-box">
            <h2 class="side-heading">Categories</h2>
            <ul class="category-list">
                <?php foreach ($cats as $c): ?>
                    <li><a href="<?= BASE_URL ?>/category.php?name=<?= urlencode($c['name']) ?>"><span><?= safe($c['name']) ?></span><i>&gt;</i></a></li>
                <?php endforeach; ?>
            </ul>
        </section>
    </aside>

</section>

<?php if ($more): ?>
<section class="more">
    <div class="label">More news</div>
    <?php foreach ($more as $story) { echo newsRow($story); } ?>
</section>
<?php endif; ?>

<?php if ($quick): ?>
<section class="quick">
    <div class="label">Quick reads</div>
    <div class="quick-grid">
        <?php foreach ($quick as $story): ?>
            <article class="quick-item">
                <h3><a href="<?= storyLink($story['id']) ?>"><?= safe($story['title']) ?></a></h3>
                <p><?= safe($story['summary']) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="more" id="loadedBox" hidden>
    <div class="label">More stories</div>
    <div id="loadedList"></div>
</section>

<div class="load-wrap" id="loadWrap"<?= $moreLeft ? '' : ' hidden' ?>>
    <button type="button" id="loadButton" class="load-button">Load more stories</button>
</div>

<script>
var shown = <?= json_encode($shown) ?>;
var button = document.getElementById('loadButton');
var wrap = document.getElementById('loadWrap');
var box = document.getElementById('loadedBox');
var list = document.getElementById('loadedList');

button.addEventListener('click', function () {
    button.disabled = true;
    button.textContent = 'Loading...';
    fetch('<?= BASE_URL ?>/load_more.php?skip=' + shown.join(','))
        .then(function (response) { return response.json(); })
        .then(function (data) {
            list.insertAdjacentHTML('beforeend', data.html);
            box.hidden = false;
            shown = shown.concat(data.ids);
            if (data.more) {
                button.disabled = false;
                button.textContent = 'Load more stories';
            } else {
                wrap.hidden = true;
            }
        })
        .catch(function () {
            button.disabled = false;
            button.textContent = 'Could not load. Try again';
        });
});

document.addEventListener('click', function (event) {
    var save = event.target.closest('.save-button');
    if (!save) {
        return;
    }
    save.classList.toggle('saved');
    save.querySelector('.save-label').textContent = save.classList.contains('saved') ? 'Saved' : 'Save';
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>