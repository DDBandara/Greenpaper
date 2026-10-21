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
$categoryId = (int)($_POST['category'] ?? 0);

$stmt = $conn->prepare('SELECT name FROM categories WHERE id = ?');
$stmt->bind_param('i', $categoryId);
$stmt->execute();
$category = $stmt->get_result()->fetch_assoc();
if (!$category) {
    header('Location: index.php');
    exit();
}

$stmt = $conn->prepare('SELECT 1 FROM subscriptions WHERE user_id = ? AND category_id = ?');
$stmt->bind_param('ii', $userId, $categoryId);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    $stmt = $conn->prepare('DELETE FROM subscriptions WHERE user_id = ? AND category_id = ?');
} else {
    $stmt = $conn->prepare('INSERT INTO subscriptions (user_id, category_id) VALUES (?, ?)');
}
$stmt->bind_param('ii', $userId, $categoryId);
$stmt->execute();

header('Location: category.php?name=' . urlencode($category['name']));
exit();