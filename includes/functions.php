<?php
// Shared helper functions used by index.php and load_more.php

if (!defined('BASE_URL')) {
    define('BASE_URL', '/newsdesk1.0');
}
if (!function_exists('e')) {
    function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
}

function artUrl($id) { return BASE_URL . '/article.php?id=' . (int)$id; }
function fmtDate($d) { return $d ? date('d M Y', strtotime($d)) : ''; }

// Runs a query and returns all rows, or null if the query failed (for example a missing column or table).
function tryRows($conn, $sql) {
    try { $res = $conn->query($sql); } catch (mysqli_sql_exception $e) { return null; }
    return $res ? $res->fetch_all(MYSQLI_ASSOC) : null;
}

// The picture link for a story: the uploaded image if there is one, otherwise the grey placeholder.
// Images are expected in the "uploads" folder of the project (uploads/photo.jpg).
function imgBlock($a, $cls) {
    $url = artUrl($a['id']);
    if (!empty($a['image'])) {
        return '<a href="' . $url . '" class="img-ph ' . $cls . '"><img src="' . e(BASE_URL . '/uploads/' . $a['image']) . '" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover"></a>';
    }
    return '<a href="' . $url . '" class="img-ph ' . $cls . '"><span>IMAGE</span></a>';
}

// One wide story row (the "More news" layout). Used for the first page load and for "Load more".
function renderRow($n) {
    ob_start(); ?>
    <article class="row">
        <?= imgBlock($n, 'img-row') ?>
        <div class="row-body">
            <div class="kicker kicker-meta"><?= e($n['category']) ?></div>
            <h3 class="row-title"><a href="<?= artUrl($n['id']) ?>"><?= e($n['title']) ?></a></h3>
            <p class="row-summary"><?= e($n['summary']) ?></p>
            <div class="row-foot">
                <span class="meta"><?= fmtDate($n['published_at']) ?></span>
                <button type="button" class="save-btn" data-id="<?= (int)$n['id'] ?>">
                    <svg width="12" height="16" viewBox="0 0 12 16" aria-hidden="true"><path d="M1 1h10v14l-5-3.5L1 15z" fill="none" stroke="currentColor" stroke-width="1"/></svg>
                    <span class="save-label">Save</span>
                </button>
            </div>
        </div>
    </article>
    <?php
    return ob_get_clean();
}