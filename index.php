<?php
$pageTitle = 'Green Paper - Home';
require_once __DIR__ . '/includes/header.php';

// ================= FAKE DATA (replace later with database queries) =================
$breaking = "Final exam timetable released. Check your faculty notice board.";

$featured = [
    'id' => 1,
    'title' => 'NSBM students win the national robotics challenge',
    'category' => 'Events',
    'summary' => 'A team from the Faculty of Computing took first place after a two-day final, beating 24 universities.',
    'author' => 'Computing Society',
    'date' => '2026-10-02',
];

$topStories = [
    ['id' => 2, 'title' => 'Semester 2 exam timetable published', 'category' => 'Academic', 'date' => '2026-10-04'],
    ['id' => 3, 'title' => 'Inter-faculty cricket final this Friday', 'category' => 'Sports', 'date' => '2026-10-03'],
];

$announcements = [
    ['title' => 'Student portal maintenance', 'date' => '2026-10-06'],
    ['title' => 'Graduation rehearsal on 14 October', 'date' => '2026-10-05'],
    ['title' => 'Parking changes near Block B', 'date' => '2026-10-03'],
];

$categories = ['Academic', 'Events', 'Sports', 'General'];

$moreNews = [
    ['id' => 5, 'title' => 'Library opens late during exam weeks', 'category' => 'General', 'summary' => 'The library will stay open until midnight from 28 October, with extra study space on the second floor.', 'date' => '2026-09-30'],
    ['id' => 6, 'title' => 'New scholarship applications now open', 'category' => 'Academic', 'summary' => 'Merit scholarships are available for second-year students. Applications close on 20 October.', 'date' => '2026-09-28'],
    ['id' => 8, 'title' => 'Graduation rehearsal on 14 October', 'category' => 'General', 'summary' => 'All graduating students must attend one rehearsal. Bring your student ID to the main hall.', 'date' => '2026-10-05'],
];

$quickReads = [
    ['id' => 7,  'title' => 'Football trials for the university team', 'summary' => 'Open trials on Saturday morning.'],
    ['id' => 4,  'title' => 'Career fair returns with 40 employers',   'summary' => 'Bring your CV to the student desk.'],
    ['id' => 5,  'title' => 'Library opens late during exam weeks',    'summary' => 'Open until midnight from 28 October.'],
    ['id' => 9,  'title' => 'Parking changes near Block B',            'summary' => 'New rules start on Monday.'],
];
// ================= END OF FAKE DATA =================

function artUrl($id)   { return BASE_URL . '/article.php?id=' . (int)$id; }
function fmtDate($d)   { return date('d M Y', strtotime($d)); }
?>

<?php if ($breaking): ?>
    <div class="breaking">
        <strong>Breaking</strong>
        <span><?= e($breaking) ?></span>
    </div>
<?php endif; ?>

<div class="kicker-row">
    <span class="kicker">The stories shaping today</span>
    <span class="kicker-note">A closer look at what connects us on campus.</span>
</div>

<!-- ===== TOP GRID: featured / two stories / sidebar ===== -->
<section class="top-grid">

    <article class="lead">
        <a href="<?= artUrl($featured['id']) ?>" class="img-ph img-wide"><span>IMAGE</span></a>
        <div class="kicker kicker-meta"><?= e($featured['category']) ?> <i>&middot;</i> Featured</div>
        <h1 class="lead-title"><a href="<?= artUrl($featured['id']) ?>"><?= e($featured['title']) ?></a></h1>
        <p class="lead-summary"><?= e($featured['summary']) ?></p>
        <div class="meta">By <?= e($featured['author']) ?> <i>&middot;</i> <?= fmtDate($featured['date']) ?></div>
    </article>

    <div class="mid-col">
        <?php foreach ($topStories as $s): ?>
            <article class="story">
                <a href="<?= artUrl($s['id']) ?>" class="img-ph img-box"><span>IMAGE</span></a>
                <div class="kicker kicker-meta"><?= e($s['category']) ?></div>
                <h3 class="story-title"><a href="<?= artUrl($s['id']) ?>"><?= e($s['title']) ?></a></h3>
                <div class="meta"><?= fmtDate($s['date']) ?></div>
            </article>
        <?php endforeach; ?>
    </div>

    <aside class="sidebar">
        <section class="side-block">
            <h2 class="side-title">Announcements <span class="live">Live</span></h2>
            <ul class="ann-list">
                <?php foreach ($announcements as $a): ?>
                    <li>
                        <span class="ann-date"><?= strtoupper(date('d M', strtotime($a['date']))) ?></span>
                        <span class="ann-title"><?= e($a['title']) ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="side-block">
            <h2 class="side-title">Categories</h2>
            <ul class="cat-list">
                <?php foreach ($categories as $name): ?>
                    <li><a href="<?= BASE_URL ?>/category.php?name=<?= urlencode($name) ?>"><span><?= e($name) ?></span><i>&gt;</i></a></li>
                <?php endforeach; ?>
            </ul>
        </section>
    </aside>

</section>

<!-- ===== MORE NEWS ===== -->
<section class="more-news">
    <div class="kicker">More news</div>

    <?php foreach ($moreNews as $n): ?>
        <article class="row">
            <a href="<?= artUrl($n['id']) ?>" class="img-ph img-row"><span>IMAGE</span></a>
            <div class="row-body">
                <div class="kicker kicker-meta"><?= e($n['category']) ?></div>
                <h3 class="row-title"><a href="<?= artUrl($n['id']) ?>"><?= e($n['title']) ?></a></h3>
                <p class="row-summary"><?= e($n['summary']) ?></p>
                <div class="row-foot">
                    <span class="meta"><?= fmtDate($n['date']) ?></span>
                    <button type="button" class="save-btn" data-id="<?= (int)$n['id'] ?>">
                        <svg width="12" height="16" viewBox="0 0 12 16" aria-hidden="true"><path d="M1 1h10v14l-5-3.5L1 15z" fill="none" stroke="currentColor" stroke-width="1"/></svg>
                        <span class="save-label">Save</span>
                    </button>
                </div>
            </div>
        </article>
    <?php endforeach; ?>
</section>

<!-- ===== QUICK READS ===== -->
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

<script>
// Save button toggle (visual only for now - connect to database later)
document.querySelectorAll('.save-btn').forEach(function(btn){
    btn.addEventListener('click', function(){
        btn.classList.toggle('saved');
        btn.querySelector('.save-label').textContent = btn.classList.contains('saved') ? 'Saved' : 'Save';
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>