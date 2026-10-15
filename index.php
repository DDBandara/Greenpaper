<?php
$pageTitle = 'NSBM NewsDesk - Home';
require_once DIR . '/includes/header.php';

// ================= FAKE DATA (replace later with database queries) =================
// Each article needs these fields: id, title, category, summary, date.
// When the database is ready, replace each array below with a query that returns the same fields.
$breaking = "Final exam timetable released. Check your faculty notice board.";

$featured = [
    'id' => 1,
    'title' => 'NSBM students win the national robotics challenge',
    'category' => 'Events',
    'summary' => 'A team from the Faculty of Computing took first place after a two-day final, beating 24 universities.',
    'date' => '2026-10-02',
];

$articles = [
    ['id' => 2, 'title' => 'Semester 2 exam timetable published', 'category' => 'Academic', 'summary' => 'Exams begin on 3 November. Download the timetable from the student portal.', 'date' => '2026-10-04'],
    ['id' => 3, 'title' => 'Inter-faculty cricket final this Friday', 'category' => 'Sports', 'summary' => 'Business and Computing meet in the final at the main ground at 3 pm.', 'date' => '2026-10-03'],
    ['id' => 4, 'title' => 'Career fair returns with 40 employers', 'category' => 'Events', 'summary' => 'Bring your CV. Registration is open at the student services desk.', 'date' => '2026-10-01'],
    ['id' => 5, 'title' => 'Library opens late during exam weeks', 'category' => 'General', 'summary' => 'The library will stay open until midnight from 28 October.', 'date' => '2026-09-30'],
    ['id' => 6, 'title' => 'New scholarship applications now open', 'category' => 'Academic', 'summary' => 'Merit scholarships are available for second-year students. Apply by 20 October.', 'date' => '2026-09-28'],
    ['id' => 7, 'title' => 'Football trials for the university team', 'category' => 'Sports', 'summary' => 'Open trials on Saturday morning. All faculties are welcome.', 'date' => '2026-09-27'],
];

$announcements = [
    ['title' => 'Student portal maintenance', 'date' => '2026-10-06'],
    ['title' => 'Graduation rehearsal on 14 October', 'date' => '2026-10-05'],
    ['title' => 'Parking changes near Block B', 'date' => '2026-10-03'],
];

$categories = ['Academic', 'Events', 'Sports', 'General'];
// ================= END OF FAKE DATA =================

// Turns "Sports" into "cat-sports" so each category gets its own colour in news.css
function catClass($name) {
    return 'cat-' . strtolower($name);
}
?>

<?php if ($breaking): ?>
    <div class="breaking">
        <strong>Breaking</strong>
        <span><?= e($breaking) ?></span>
    </div>
<?php endif; ?>

<section class="featured <?= catClass($featured['category']) ?>">
    <div class="featured-body">
        <span class="tag"><?= e($featured['category']) ?></span>
        <h1><a href="article.php?id=<?= (int)$featured['id'] ?>"><?= e($featured['title']) ?></a></h1>
        <p><?= e($featured['summary']) ?></p>
        <span class="meta"><?= date('d M Y', strtotime($featured['date'])) ?></span>
    </div>
</section>

<div class="layout">
    <section class="main-col">
        <h2 class="section-title">Latest news</h2>
        <div class="card-grid">
            <?php foreach ($articles as $article): ?>
                <article class="card">
                    <div class="card-thumb <?= catClass($article['category']) ?>"></div>
                    <div class="card-body">
                        <span class="tag <?= catClass($article['category']) ?>"><?= e($article['category']) ?></span>
                        <h3><a href="article.php?id=<?= (int)$article['id'] ?>"><?= e($article['title']) ?></a></h3>
                        <p><?= e($article['summary']) ?></p>
                        <span class="meta"><?= date('d M Y', strtotime($article['date'])) ?></span>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </section>

    <aside class="sidebar">
        <section class="panel announcements">
            <h2 class="section-title">Announcements</h2>
            <ul>
                <?php foreach ($announcements as $a): ?>
                    <li>
                        <strong><?= e($a['title']) ?></strong>
                        <span class="meta"><?= date('d M Y', strtotime($a['date'])) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="panel category-panel">
            <h2 class="section-title">Categories</h2>
            <ul class="category-links">
                <?php foreach ($categories as $name): ?>
                    <li><a class="<?= catClass($name) ?>" href="category.php?name=<?= urlencode($name) ?>"><?= e($name) ?></a></li>
                <?php endforeach; ?>
            </ul>
        </section>
    </aside>
</div>

<?php require_once DIR . '/includes/footer.php'; ?>
        