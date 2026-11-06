<?php
require_once __DIR__ . '/../includes/admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !csrfOk()) {
    http_response_code(403);
    exit('Invalid request. Return to the announcements page and try again.');
}

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$action = $_POST['action'] ?? '';

if (!$id || $id < 1 || !in_array($action, ['approve', 'delete'], true)) {
    http_response_code(400);
    exit('Invalid announcement action.');
}

if ($action === 'approve') {
    $stmt = $conn->prepare(
        'UPDATE announcements SET is_active = 1 WHERE id = ? AND is_active = 0'
    );
} else {
    $stmt = $conn->prepare(
        'DELETE FROM announcements WHERE id = ?'
    );
}

$stmt->bind_param('i', $id);
$stmt->execute();
$stmt->close();

header('Location: ' . BASE_URL . '/submit_announcement.php');
exit();