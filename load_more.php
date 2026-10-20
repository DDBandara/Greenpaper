<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
header('Content-Type: application/json; charset=utf-8');

$amount = 3;
$skip = array_values(array_filter(array_map('intval', explode(',', $_GET['skip'] ?? ''))));

$sql = "SELECT a.id, a.title, a.summary, a.image, a.published_at, c.name AS category
        FROM articles a
        JOIN categories c ON c.id = a.category_id
        WHERE a.status = 'approved'";
if ($skip) {
    $sql .= " AND a.id NOT IN (" . implode(',', $skip) . ")";
}
$sql .= " ORDER BY a.published_at DESC LIMIT " . ($amount + 1);

$rows = getRows($conn, $sql) ?? [];
$more = count($rows) > $amount;
$rows = array_slice($rows, 0, $amount);

$html = '';
$ids = [];
foreach ($rows as $row) {
    $html .= newsRow($row);
    $ids[] = (int)$row['id'];
}

echo json_encode(['html' => $html, 'ids' => $ids, 'more' => $more]);