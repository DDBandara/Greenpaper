<?php
session_start();
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    header('Location: index.php');
    exit();
}

$userId = (int)$_SESSION['user_id'];
$articleId = (int)($_POST['article'] ?? 0);

$stmt = $conn->prepare("SELECT 1 FROM articles WHERE id = ? AND status = 'approved'");
$stmt->bind_param('i', $articleId);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    header('Location: index.php');
    exit();
}

$stmt = $conn->prepare('SELECT 1 FROM bookmarks WHERE user_id = ? AND article_id = ?');
$stmt->bind_param('ii', $userId, $articleId);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    $stmt = $conn->prepare('DELETE FROM bookmarks WHERE user_id = ? AND article_id = ?');
} else {
    $stmt = $conn->prepare('INSERT INTO bookmarks (user_id, article_id) VALUES (?, ?)');
}
$stmt->bind_param('ii', $userId, $articleId);
$stmt->execute();

header('Location: article.php?id=' . $articleId);
exit();
