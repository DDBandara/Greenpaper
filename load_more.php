<?php
// Called by the "Load more" button on the home page. Returns the next stories as JSON.
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

$perClick = 3;    // how many stories each click adds

// Ids of the stories already on the page (whole numbers only, so it is safe to put in the query)
$exclude = array_values(array_filter(array_map('intval', explode(',', $_GET['exclude'] ?? ''))));

$sql = "SELECT a.id, a.title, a.summary, a.image, a.published_at, c.name AS category
        FROM articles a
        JOIN categories c ON c.id = a.category_id
        WHERE a.status = 'approved'";
if ($exclude) {
    $sql .= " AND a.id NOT IN (" . implode(',', $exclude) . ")";
}
$sql .= " ORDER BY a.published_at DESC LIMIT " . ($perClick + 1);    // one extra row tells us if there is more

$rows    = tryRows($conn, $sql) ?? [];
$hasMore = count($rows) > $perClick;
$rows    = array_slice($rows, 0, $perClick);

$html = '';
$ids  = [];
foreach ($rows as $r) {
    $html .= renderRow($r);
    $ids[] = (int)$r['id'];
}

echo json_encode(['html' => $html, 'ids' => $ids, 'hasMore' => $hasMore]);