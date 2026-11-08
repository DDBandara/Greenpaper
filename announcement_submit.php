
<?php
session_start();


require_once __DIR__ . '/config.php';

function returnToForm(string $message): void
{
    $_SESSION['announcement_notice'] = $message;
    header('Location: ' . BASE_URL . '/submit_announcement.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . BASE_URL . '/submit_announcement.php');
    exit();
}

if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_URL . '/login.php');
    exit();
}

$sentToken = (string)($_POST['csrf'] ?? '');
$sessionToken = (string)($_SESSION['announcement_csrf'] ?? '');

if ($sessionToken === '' || !hash_equals($sessionToken, $sentToken)) {
    returnToForm('The form expired. Please try again.');
}

$title = trim((string)($_POST['title'] ?? ''));
$body = trim((string)($_POST['body'] ?? ''));

if ($title === '' || $body === '') {
    returnToForm('Please enter a title and description.');
}

if (mb_strlen($title) > 200 || mb_strlen($body) > 10000) {
    returnToForm('The title or description is too long.');
}

$userId = (int)$_SESSION['user_id'];

try {
    $stmt = $conn->prepare(
        'INSERT INTO announcements
            (title, body, is_active, created_by, created_at)
         VALUES (?, ?, 0, ?, NOW())'
    );
    $stmt->bind_param('ssi', $title, $body, $userId);
    $stmt->execute();
} catch (mysqli_sql_exception $error) {
    returnToForm('The announcement could not be submitted. Please try again.');
}

returnToForm('Your announcement was submitted for admin review.');