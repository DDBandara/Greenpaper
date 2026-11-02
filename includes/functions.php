<style>
.main-story,
.story,
.news-row {
  box-sizing: border-box;
  background: rgba(217, 217, 217, 0.58);
  border: 1px solid white;
  box-shadow: 12px 17px 51px rgba(0, 0, 0, 0.22);
  backdrop-filter: blur(6px);
  border-radius: 0;
  cursor: pointer;
  transition: transform 0.5s ease, border-color 0.5s ease;
  padding: 20px;
}

.main-story:hover,
.story:hover,
.news-row:hover {
  border-color: black;
  transform: scale(1.05);
}

.main-story:active,
.story:active,
.news-row:active {
  transform: scale(0.95) rotate(1.7deg);
}
</style>
<style>

.news-row {
  box-sizing: border-box;
  background: rgba(217, 217, 217, 0.58);
  border: 1px solid white;
  box-shadow: 12px 17px 51px rgba(0, 0, 0, 0.22);
  backdrop-filter: blur(3px);
  border-radius: 0;
  cursor: pointer;
  transition: transform 0.5s ease, border-color 0.5s ease;
  padding: 50px;
  

}
.more .news-row {
  padding: 28px;
  margin-bottom: 20px;
}


.news-row:hover {
  border-color: black;
  transform: scale(0.9);
}

.news-row:active {
  transform: scale(0.95) rotate(1.7deg);
}
</style>
<?php
if (!defined('BASE_URL')) {
    define('BASE_URL', '/newsdesk1.0');
}

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

function picture($story, $class) {
    $url = storyLink($story['id']);
    if (!empty($story['image'])) {
        return '<a href="' . $url . '" class="pic ' . $class . '"><img src="' . safe(BASE_URL . '/uploads/' . $story['image']) . '" alt="" style="position:absolute;inset:0;width:100%;height:100%;object-fit:cover"></a>';
    }
    return '<a href="' . $url . '" class="pic ' . $class . '"><span>IMAGE</span></a>';
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

