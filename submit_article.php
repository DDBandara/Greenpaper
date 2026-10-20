<?php
session_start();
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/upload.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: submit.php'); exit(); }
if (!isset($_SESSION['user_id']))          { header('Location: login.php');  exit(); }

function goBack(array $errors) {
    $_SESSION['submit_errors'] = $errors;
    $_SESSION['submit_old'] = [
        'title'    => $_POST['title']    ?? '',
        'summary'  => $_POST['summary']  ?? '',
        'body'     => $_POST['body']     ?? '',
        'category' => $_POST['category'] ?? '',
    ];
    header('Location: submit.php');
    exit();
}

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
    goBack(['The form expired. Please try again.']);
}

$isDraft    = (($_POST['action'] ?? 'submit') === 'draft');
$status     = $isDraft ? 'draft' : 'pending';
$title      = trim($_POST['title']   ?? '');
$summary    = trim($_POST['summary'] ?? '');
$body       = trim($_POST['body']    ?? '');
$categoryId = (int)($_POST['category'] ?? 0);
$authorId   = (int)$_SESSION['user_id'];

$errors = [];
if (mb_strlen($title) < 5 || mb_strlen($title) > 200) { $errors[] = 'The title must be between 5 and 200 characters.'; }
if (!$isDraft) {
    if (mb_strlen($summary) < 10 || mb_strlen($summary) > 300) { $errors[] = 'The summary must be between 10 and 300 characters.'; }
    if (mb_strlen($body) < 50)                                  { $errors[] = 'The article body is too short (at least 50 characters).'; }
}
if (mb_strlen($summary) > 300) { $errors[] = 'The summary must be 300 characters or fewer.'; }

$check = $conn->prepare('SELECT id FROM categories WHERE id = ?');
$check->bind_param('i', $categoryId);
$check->execute();
if ($check->get_result()->num_rows === 0) { $errors[] = 'Choose a category.'; }

if ($errors) { goBack($errors); }

$imgError = '';
$image = saveImage('image', $imgError);
if ($imgError !== '') { goBack([$imgError]); }

try {
    $stmt = $conn->prepare('INSERT INTO articles (title, summary, body, image, category_id, author_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssiis', $title, $summary, $body, $image, $categoryId, $authorId, $status);
    $stmt->execute();
} catch (mysqli_sql_exception $e) {
    removeImage($image);
    goBack([$isDraft ? 'Drafts are not switched on in the database yet. Ask the project lead.' : 'The article could not be saved. Please try again.']);
}

$_SESSION['submit_ok'] = $isDraft ? 'Draft saved. It is not visible to anyone else.' : 'Thank you! Your article was sent for approval.';
header('Location: submit.php');
exit();