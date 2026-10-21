<?php
session_start();
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$articleId = (int)($_POST['article'] ?? 0);
$back = 'article.php?id=' . $articleId . '#comments';

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    $_SESSION['comment_error'] = 'The form expired. Please try again.';
    header('Location: ' . $back);
    exit();
}

$text = trim($_POST['comment'] ?? '');
if ($text === '' || mb_strlen($text) > 1000) {
    $_SESSION['comment_error'] = 'A comment must be between 1 and 1000 characters.';
    header('Location: ' . $back);
    exit();
}

$stmt = $conn->prepare("SELECT 1 FROM articles WHERE id = ? AND status = 'approved'");
$stmt->bind_param('i', $articleId);
$stmt->execute();
if ($stmt->get_result()->num_rows === 0) {
    header('Location: index.php');
    exit();
}

try {
    $userId = (int)$_SESSION['user_id'];
    $stmt = $conn->prepare('INSERT INTO comments (article_id, user_id, body) VALUES (?, ?, ?)');
    $stmt->bind_param('iis', $articleId, $userId, $text);
    $stmt->execute();
    $_SESSION['comment_ok'] = 'Your comment was posted.';
} catch (mysqli_sql_exception $error) {
    $_SESSION['comment_error'] = 'The comment could not be saved. Please try again.';
}

header('Location: ' . $back);
exit();
