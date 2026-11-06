<?php
session_start();
header('Cache-Control: no-store');

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/header.php';

if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

$userId = (int) $_SESSION['user_id'];
$userName = (string) ($_SESSION['name'] ?? 'Reader');
$userEmail = (string) ($_SESSION['email'] ?? '');
$userRole = (string) ($_SESSION['role'] ?? 'user');
$pageError = '';
$categories = [];
$articleCategories = [];
$articles = [];
$stats = ['published' => 0, 'in_progress' => 0, 'total_views' => 0];

try {
    
    $stmt = $conn->prepare(
        'SELECT c.id, c.name
         FROM subscriptions s
         JOIN categories c ON c.id = s.category_id
         WHERE s.user_id = ?
         ORDER BY c.name'
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    
    $stmt = $conn->prepare(
        "SELECT
            COALESCE(SUM(status = 'approved'), 0) AS published,
            COALESCE(SUM(status IN ('pending', 'draft')), 0) AS in_progress,
            COALESCE(SUM(CASE WHEN status = 'approved' THEN views ELSE 0 END), 0) AS total_views
         FROM articles
         WHERE author_id = ?"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $stats = $stmt->get_result()->fetch_assoc() ?: $stats;
    $stmt->close();

    
    $stmt = $conn->prepare(
        "SELECT c.name, COALESCE(SUM(a.views), 0) AS views
         FROM categories c
         LEFT JOIN articles a
           ON a.category_id = c.id
          AND a.author_id = ?
          AND a.status = 'approved'
         GROUP BY c.id, c.name
         ORDER BY views DESC, c.name"
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $articleCategories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    
    $stmt = $conn->prepare(
        'SELECT a.id, a.title, a.status, a.created_at, a.published_at, a.views,
                c.name AS category
         FROM articles a
         LEFT JOIN categories c ON c.id = a.category_id
         WHERE a.author_id = ?
         ORDER BY
            CASE a.status
                WHEN \'pending\' THEN 0
                WHEN \'draft\' THEN 1
                WHEN \'approved\' THEN 2
                WHEN \'rejected\' THEN 3
                ELSE 4
            END,
            a.created_at DESC'
    );
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $articles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
} catch (mysqli_sql_exception $error) {
    $pageError = 'Some profile information could not be loaded. Please refresh the page or contact the site administrator.';
}

$publishedCount = (int) ($stats['published'] ?? 0);
$inProgressCount = (int) ($stats['in_progress'] ?? 0);
$totalViews = (int) ($stats['total_views'] ?? 0);
$averageViews = $publishedCount > 0 ? (int) round($totalViews / $publishedCount) : 0;
$maxCategoryViews = 1;
foreach ($articleCategories as $categoryRow) {
    $maxCategoryViews = max($maxCategoryViews, (int) $categoryRow['views']);
}

$nameParts = preg_split('/\s+/u', trim($userName), -1, PREG_SPLIT_NO_EMPTY) ?: [];
$initials = '';
foreach (array_slice($nameParts, 0, 2) as $part) {
    preg_match('/^./us', $part, $firstCharacter);
    $initials .= strtoupper($firstCharacter[0] ?? '');
}
if ($initials === '') {
    $initials = 'U';
}

$statusLabels = [
    'approved' => 'Published',
    'pending' => 'Pending review',
    'draft' => 'Draft',
    'rejected' => 'Rejected',
];
$statusClasses = [
    'approved' => 'bg-green-100 text-green-800',
    'pending' => 'bg-amber-100 text-amber-800',
    'draft' => 'bg-gray-100 text-gray-700',
    'rejected' => 'bg-red-100 text-red-800',
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= safe($userName) ?> - Green Paper</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,500;0,6..72,600;1,6..72,400&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            cream: '#f7f5ee', green: '#1b5e20', 'green-dark': '#124116',
                            card: '#ffffff', border: '#e5e2d9', dark: '#1c1c1c', muted: '#666666'
                        }
                    },
                    fontFamily: {
                        serif: ['Newsreader', 'Georgia', 'serif'],
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        };
    </script>
    <style>
        body { background: #f7f5ee; color: #1c1c1c; font-family: Inter, sans-serif; }
        .font-serif-title { font-family: Newsreader, Georgia, serif; }
    </style>
</head>
<body class="min-h-screen pb-16">
    <main class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
        <header class="mb-8">
            <div class="mb-2 flex flex-col items-start justify-between gap-2 sm:flex-row sm:items-center">
                <span class="text-xs font-semibold uppercase tracking-wider text-brand-green">Profile / Author management</span>
                <span class="text-xs font-medium text-brand-muted"><?= safe(date('l, j F Y')) ?></span>
            </div>
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <h1 class="font-serif-title text-4xl font-normal tracking-tight text-brand-dark sm:text-5xl">Author Profile</h1>
                    <p class="mt-2 text-sm text-brand-muted">Your articles, readership, and selected categories.</p>
                </div>
                <a href="<?= safe(BASE_URL) ?>/logout.php" class="border border-brand-dark px-4 py-2 text-xs font-semibold uppercase tracking-wider text-brand-dark hover:bg-brand-dark hover:text-white">Log out</a>
            </div>
        </header>

        <?php if ($pageError !== ''): ?>
            <div class="mb-6 border border-red-200 bg-red-50 p-4 text-sm text-red-800" role="alert"><?= safe($pageError) ?></div>
        <?php endif; ?>

        <section class="mb-8 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4" aria-label="Author statistics">
            <article class="flex flex-col justify-between border border-brand-border bg-white p-6 shadow-sm">
                <div><div class="font-serif-title mb-1 text-4xl text-brand-dark"><?= $publishedCount ?></div><div class="mb-1 text-xs font-bold uppercase tracking-wider text-brand-muted">Published articles</div></div>
                <div class="text-xs text-brand-muted">Articles approved for publication</div>
            </article>
            <article class="flex flex-col justify-between border border-brand-border bg-white p-6 shadow-sm">
                <div><div class="font-serif-title mb-1 text-4xl text-brand-dark"><?= $inProgressCount ?></div><div class="mb-1 text-xs font-bold uppercase tracking-wider text-brand-muted">Drafts &amp; pending</div></div>
                <div class="text-xs text-brand-muted">Awaiting review or saved as drafts</div>
            </article>
            <article class="flex flex-col justify-between border border-brand-border bg-white p-6 shadow-sm">
                <div><div class="font-serif-title mb-1 text-4xl text-brand-dark"><?= number_format($totalViews) ?></div><div class="mb-1 text-xs font-bold uppercase tracking-wider text-brand-muted">Article views</div></div>
                <div class="text-xs text-brand-muted"><?= number_format($averageViews) ?> average views per published article</div>
            </article>
            <article class="flex flex-col justify-between border border-brand-border bg-white p-6 shadow-sm">
                <div>
                    <div class="font-serif-title mb-2 mt-2 text-2xl text-brand-dark"><?= $userRole === 'admin' ? 'Administrator' : 'Contributor' ?></div>
                    <div class="mb-1 text-xs font-bold uppercase tracking-wider text-brand-muted">Account type</div>
                </div>
                <div class="text-xs text-brand-muted">Profile details come from your account</div>
            </article>
        </section>

        <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
            <div class="space-y-8 lg:col-span-2">
                <section class="border border-brand-border bg-white p-6 shadow-sm" aria-labelledby="profile-heading">
                    <div class="flex flex-col items-start justify-between gap-4 border-b border-brand-border pb-6 sm:flex-row sm:items-center">
                        <div class="flex items-center gap-4">
                            <div class="flex h-16 w-16 flex-shrink-0 items-center justify-center rounded-full bg-brand-green font-serif-title text-2xl font-semibold text-white" aria-hidden="true"><?= safe($initials) ?></div>
                            <div>
                                <h2 id="profile-heading" class="font-serif-title text-2xl font-medium text-brand-dark"><?= safe($userName) ?></h2>
                                <p class="text-xs text-brand-muted"><?= safe(ucfirst($userRole)) ?> · Green Paper</p>
                                <?php if ($userEmail !== ''): ?><p class="mt-1 text-xs text-brand-muted"><?= safe($userEmail) ?></p><?php endif; ?>
                            </div>
                        </div>
                        <a href="<?= safe(BASE_URL) ?>/submit.php" class="border border-brand-dark px-4 py-2 text-xs font-semibold uppercase tracking-wider text-brand-dark hover:bg-brand-dark hover:text-white">Write an article</a>
                    </div>
                    <div class="grid grid-cols-1 gap-6 pt-6 text-sm sm:grid-cols-2">
                        <div>
                            <span class="mb-2 block text-xs font-bold uppercase tracking-wider text-brand-muted">Your selected categories</span>
                            <?php if ($categories): ?>
                                <div class="flex flex-wrap gap-2">
                                    <?php foreach ($categories as $category): ?>
                                        <span class="border border-brand-border bg-brand-cream px-3 py-1 text-xs text-brand-dark"><?= safe($category['name']) ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-brand-muted">You have not selected any categories yet.</p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <span class="mb-2 block text-xs font-bold uppercase tracking-wider text-brand-muted">Account</span>
                            <p class="text-brand-dark">Signed in as <?= safe($userName) ?></p>
                            <p class="mt-1 text-xs text-brand-muted">Use your account settings to update personal details.</p>
                        </div>
                    </div>
                </section>

                <section class="border border-brand-border bg-white shadow-sm" aria-labelledby="articles-heading">
                    <div class="flex flex-col justify-between gap-4 border-b border-brand-border p-6 sm:flex-row sm:items-center">
                        <div>
                            <h2 id="articles-heading" class="font-serif-title text-2xl font-medium text-brand-dark">My Articles</h2>
                            <p class="mt-1 text-xs text-brand-muted">Your submitted articles and their current review status.</p>
                        </div>
                        <div class="flex flex-col gap-2 sm:flex-row">
                            <input type="search" id="article-search" placeholder="Search title..." class="w-full border border-brand-border bg-brand-cream/30 px-3 py-2 text-xs focus:border-brand-green focus:outline-none sm:w-44" aria-label="Search your articles">
                            <select id="status-filter" class="border border-brand-border bg-brand-cream/30 px-3 py-2 text-xs text-brand-dark focus:border-brand-green focus:outline-none" aria-label="Filter articles by status">
                                <option value="all">All statuses</option>
                                <option value="approved">Published</option>
                                <option value="pending">Pending review</option>
                                <option value="draft">Draft</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                    </div>
                    <div id="articles-container" class="divide-y divide-brand-border">
                        <?php foreach ($articles as $article):
                            $status = (string) $article['status'];
                            $label = $statusLabels[$status] ?? ucfirst($status);
                            $badgeClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-700';
                            $date = $status === 'approved' && !empty($article['published_at']) ? $article['published_at'] : $article['created_at'];
                        ?>
                            <article class="article-row flex flex-col items-start justify-between gap-4 p-4 transition-colors hover:bg-brand-cream/30 sm:flex-row sm:items-center sm:p-6" data-title="<?= safe(mb_strtolower((string) $article['title'])) ?>" data-status="<?= safe($status) ?>">
                                <div class="max-w-xl space-y-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="border border-brand-border bg-brand-cream px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-brand-green"><?= safe($article['category'] ?? 'Uncategorized') ?></span>
                                        <span class="rounded-full px-2 py-0.5 text-[10px] font-semibold <?= safe($badgeClass) ?>"><?= safe($label) ?></span>
                                        <span class="text-xs text-brand-muted"><?= safe(date('d M Y', strtotime((string) $date))) ?></span>
                                    </div>
                                    <h3 class="font-serif-title text-lg font-medium leading-snug text-brand-dark"><?= safe($article['title']) ?></h3>
                                    <p class="text-xs text-brand-muted"><?= number_format((int) $article['views']) ?> views</p>
                                </div>
                                <?php if ($status === 'approved'): ?>
                                    <a href="<?= safe(BASE_URL) ?>/article.php?id=<?= (int) $article['id'] ?>" class="self-end border border-brand-border bg-white px-3 py-1.5 text-xs font-semibold text-brand-dark hover:border-brand-green hover:text-brand-green sm:self-auto">View article</a>
                                <?php endif; ?>
                            </article>
                        <?php endforeach; ?>
                    </div>
                    <div id="empty-state" class="hidden p-8 text-center text-sm text-brand-muted">No articles match your search or filter.</div>
                    <?php if (!$articles): ?>
                        <p class="p-8 text-center text-sm text-brand-muted">You haven’t submitted any articles yet. <a class="font-semibold text-brand-green underline" href="<?= safe(BASE_URL) ?>/submit.php">Write your first article</a>.</p>
                    <?php endif; ?>
                </section>
            </div>

            <aside class="space-y-8">
                <section class="border border-brand-border bg-white p-6 shadow-sm" aria-labelledby="category-views-heading">
                    <h2 id="category-views-heading" class="font-serif-title mb-6 text-xl font-medium text-brand-dark">Views by category</h2>
                    <?php if ($articleCategories): ?>
                        <div class="space-y-4">
                            <?php foreach ($articleCategories as $categoryRow):
                                $categoryViews = (int) $categoryRow['views'];
                                $width = $categoryViews > 0 ? max(3, (int) round(($categoryViews / $maxCategoryViews) * 100)) : 0;
                            ?>
                                <div>
                                    <div class="mb-1 flex justify-between text-xs font-medium"><span><?= safe($categoryRow['name']) ?></span><span class="font-bold"><?= number_format($categoryViews) ?></span></div>
                                    <div class="h-3 w-full overflow-hidden bg-brand-cream"><div class="h-full bg-brand-green" style="width: <?= $width ?>%"></div></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-sm text-brand-muted">Category readership will appear here when your articles are published.</p>
                    <?php endif; ?>
                </section>

                <section class="border border-brand-border bg-white shadow-sm" aria-labelledby="quick-actions-heading">
                    <div class="border-b border-brand-border p-6"><h2 id="quick-actions-heading" class="font-serif-title text-xl font-medium text-brand-dark">Quick actions</h2></div>
                    <div class="divide-y divide-brand-border text-sm">
                        <a href="<?= safe(BASE_URL) ?>/submit.php" class="group flex items-center justify-between p-4 hover:bg-brand-cream/50"><span class="font-medium text-brand-dark">Write a new article</span><span class="transition-transform group-hover:translate-x-1">&rarr;</span></a>
                        <a href="<?= safe(BASE_URL) ?>/index.php" class="group flex items-center justify-between p-4 hover:bg-brand-cream/50"><span class="font-medium text-brand-dark">Browse latest news</span><span class="transition-transform group-hover:translate-x-1">&rarr;</span></a>
                        <a href="<?= safe(BASE_URL) ?>/logout.php" class="group flex items-center justify-between p-4 hover:bg-brand-cream/50"><span class="font-medium text-brand-dark">Log out</span><span class="transition-transform group-hover:translate-x-1">&rarr;</span></a>
                    </div>
                </section>
            </aside>
        </div>
    </main>

    <script>
        const searchInput = document.getElementById('article-search');
        const statusFilter = document.getElementById('status-filter');
        const articleRows = [...document.querySelectorAll('.article-row')];
        const emptyState = document.getElementById('empty-state');

        function filterArticles() {
            const query = searchInput.value.trim().toLocaleLowerCase();
            const selectedStatus = statusFilter.value;
            let visible = 0;

            articleRows.forEach((row) => {
                const matchesTitle = row.dataset.title.includes(query);
                const matchesStatus = selectedStatus === 'all' || row.dataset.status === selectedStatus;
                const show = matchesTitle && matchesStatus;
                row.classList.toggle('hidden', !show);
                if (show) visible += 1;
            });

            emptyState.classList.toggle('hidden', visible !== 0 || articleRows.length === 0);
        }

        searchInput.addEventListener('input', filterArticles);
        statusFilter.addEventListener('change', filterArticles);
    </script>
</body>
</html>
