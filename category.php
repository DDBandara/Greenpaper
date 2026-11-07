<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$name = trim($_GET['name'] ?? '');
$stmt = $conn->prepare('SELECT id, name FROM categories WHERE name = ?');
$stmt->bind_param('s', $name);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();

if (!$category) {
    http_response_code(404);
    $pageTitle = 'Category not found';
    require_once __DIR__ . '/includes/header.php';
    echo '<p class="main-text" style="padding:40px 0">That category does not exist.</p>';
    require_once __DIR__ . '/includes/footer.php';
    exit();
}

$descriptions = [
    'Academic' => 'Exams, timetables, scholarships and everything about studying at NSBM.',
    'Events'   => 'Competitions, fairs, clubs and life on campus.',
    'Sports'   => 'Teams, trials, fixtures and results.',
    'General'  => 'Notices, facilities and everything else.',
];
$description = $descriptions[$category['name']] ?? 'Stories in this category.';

$perPage = 5;
$page = max(1, (int)($_GET['page'] ?? 1));

$stmt = $conn->prepare("SELECT COUNT(*) AS total FROM articles WHERE status = 'approved' AND category_id = ?");
$stmt->bind_param('i', $category['id']);
$stmt->execute();
$total = (int)$stmt->get_result()->fetch_assoc()['total'];
$pages = max(1, (int)ceil($total / $perPage));
$page = min($page, $pages);
$offset = ($page - 1) * $perPage;

$stmt = $conn->prepare("SELECT a.id, a.title, a.summary, a.image, a.published_at, c.name AS category
                        FROM articles a
                        JOIN categories c ON c.id = a.category_id
                        WHERE a.status = 'approved' AND a.category_id = ?
                        ORDER BY a.published_at DESC
                        LIMIT ? OFFSET ?");
$stmt->bind_param('iii', $category['id'], $perPage, $offset);
$stmt->execute();
$stories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT id, title, published_at, views
                        FROM articles
                        WHERE status = 'approved' AND category_id = ?
                        ORDER BY views DESC, published_at DESC
                        LIMIT 4");
$stmt->bind_param('i', $category['id']);
$stmt->execute();
$trending = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

if (count($trending) < 2) {
    $stmt = $conn->prepare("SELECT id, title, published_at, views
                            FROM articles
                            WHERE status = 'approved'
                            ORDER BY views DESC, published_at DESC
                            LIMIT 4");
    $stmt->execute();
    $trending = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $trendingTitle = 'Trending Stories';
} else {
    $trendingTitle = 'Trending in ' . $category['name'];
}

$others = getRows($conn, "SELECT c.name, COUNT(a.id) AS total
                          FROM categories c
                          LEFT JOIN articles a ON a.category_id = c.id AND a.status = 'approved'
                          GROUP BY c.id, c.name
                          ORDER BY c.name") ?? [];

$loggedIn = !empty($_SESSION['user_id']);
$following = false;
if ($loggedIn) {
    $stmt = $conn->prepare('SELECT 1 FROM subscriptions WHERE user_id = ? AND category_id = ?');
    $stmt->bind_param('ii', $_SESSION['user_id'], $category['id']);
    $stmt->execute();
    $following = $stmt->get_result()->num_rows > 0;
}

$notices = getNotices($conn);
$from = $total ? $offset + 1 : 0;
$to = min($offset + $perPage, $total);
$pageUrl = BASE_URL . '/category.php?name=' . urlencode($category['name']) . '&page=';

$pageTitle = $category['name'] . ' - Green Paper';
require_once __DIR__ . '/includes/header.php';
?>
<link rel="stylesheet" href="<?= BASE_URL ?>/category.css">

<div class="category-head">
    <span class="label">Category</span>
    <h1><?= safe($category['name']) ?></h1>
    <p><?= safe($description) ?></p>
</div>

<div class="category-layout">

    <div class="category-main">
        <?php if ($stories): ?>
            <?php foreach ($stories as $story) { echo newsRow($story); } ?>

            <div class="pager">
                <span>Showing <?= $from ?> to <?= $to ?> of <?= $total ?> article<?= $total === 1 ? '' : 's' ?></span>
                <div class="pager-links">
                    <?php if ($page > 1): ?>
                        <a href="<?= $pageUrl . ($page - 1) ?>">&lsaquo;</a>
                    <?php else: ?>
                        <span class="off">&lsaquo;</span>
                    <?php endif; ?>

                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="current"><?= $i ?></span>
                        <?php else: ?>
                            <a href="<?= $pageUrl . $i ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>

                    <?php if ($page < $pages): ?>
                        <a href="<?= $pageUrl . ($page + 1) ?>">&rsaquo;</a>
                    <?php else: ?>
                        <span class="off">&rsaquo;</span>
                    <?php endif; ?>
                </div>
            </div>
        <?php else: ?>
            <p class="main-text">No articles in this category yet.</p>
        <?php endif; ?>
    </div>

    <aside class="category-side">
        <div class="side-count"><?= $total ?> article<?= $total === 1 ? '' : 's' ?></div>

        <?php if ($loggedIn): ?>
            <form action="<?= BASE_URL ?>/follow.php" method="post">
                <input type="hidden" name="csrf" value="<?= safe($_SESSION['csrf']) ?>">
                <input type="hidden" name="category" value="<?= (int)$category['id'] ?>">
                <button type="submit" class="follow-btn<?= $following ? ' following' : '' ?>">
                    <?= $following ? 'Following' : '+ Follow this category' ?>
                </button>
            </form>
        <?php else: ?>
            <a class="follow-btn" href="<?= BASE_URL ?>/login.php">+ Follow this category</a>
        <?php endif; ?>

        <?php if (!empty($trending)): ?>
            <div class="trending-box">
                <h3 class="side-heading"><?= safe($trendingTitle) ?></h3>
                <ol class="trending-list">
                    <?php foreach ($trending as $item): ?>
                        <li>
                            <a href="<?= storyLink($item['id']) ?>"><?= safe($item['title']) ?></a>
                            <span class="small"><?= showDate($item['published_at']) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </div>
        <?php endif; ?>

        <?= announcementsBox($notices, 'category-announcements') ?>
        <div class="other-box">
            <h3>Other categories</h3>
            <ul>
                <?php foreach ($others as $other): ?>
                    <?php if ($other['name'] === $category['name']) { continue; } ?>
                    <li>
                        <a href="<?= BASE_URL ?>/category.php?name=<?= urlencode($other['name']) ?>">
                            <span><?= safe($other['name']) ?></span>
                            <span class="other-count"><?= (int)$other['total'] ?> article<?= (int)$other['total'] === 1 ? '' : 's' ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </aside>

</div>

<script>
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