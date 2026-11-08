<?php

if (!function_exists('safe')) {
    function safe($text) {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
}

function storyLink($id) {
    return BASE_URL . '/article.php?id=' . (int)$id;
}

function showDate($date) {
    return $date ? date('d M Y', strtotime($date)) : '';
}

function getRows($conn, $sql) {
    try {
        $result = $conn->query($sql);
    } catch (mysqli_sql_exception $error) {
        return null;
    }
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : null;
}

function toneFor($name) {
    return abs(crc32((string)$name)) % 5;
}

function initials($name) {
    $parts = preg_split('/\s+/u', trim((string)$name), -1, PREG_SPLIT_NO_EMPTY) ?: [];
    $out = '';
    foreach (array_slice($parts, 0, 2) as $part) {
        $out .= mb_strtoupper(mb_substr($part, 0, 1));
    }
    return $out !== '' ? $out : 'U';
}

function picture($story, $class) {
    $url = storyLink($story['id']);
    if (!empty($story['image'])) {
        return '<a href="' . $url . '" class="pic ' . $class . '"><img src="' . safe(BASE_URL . '/uploads/' . $story['image']) . '" alt="" loading="lazy" decoding="async" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover"></a>';
    }
    return '';
}

function newsRow($story) {
    ob_start(); ?>
    <article class="news-row">
        <?= picture($story, 'pic-row') ?>
        <div class="news-body">
            <div class="label label-small"><?= safe($story['category']) ?></div>
            <h3 class="news-title"><a href="<?= storyLink($story['id']) ?>"><?= safe($story['title']) ?></a></h3>
            <p class="news-text"><?= safe($story['summary']) ?></p>
            <div class="news-bottom">
                <span class="small"><?= showDate($story['published_at']) ?></span>
                <button type="button" class="save-button" data-id="<?= (int)$story['id'] ?>">
                    <svg width="12" height="16" viewBox="0 0 12 16"><path d="M1 1h10v14l-5-3.5L1 15z" fill="none" stroke="currentColor" stroke-width="1"/></svg>
                    <span class="save-label">Save</span>
                </button>
            </div>
        </div>
    </article>
    <?php
    return ob_get_clean();
    
}

function getNotices($conn, $limit = 3) {
    $limit = max(1, (int)$limit);
    return getRows($conn, "SELECT title, created_at FROM announcements WHERE is_active = 1 ORDER BY created_at DESC LIMIT $limit") ?? [];
}

function announcementsBox($notices, $extraClass = '') {
    ob_start(); ?>
    <section class="side-box<?= $extraClass ? ' ' . safe($extraClass) : '' ?>">
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
    <?php
    return ob_get_clean();
}