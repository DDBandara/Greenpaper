<?php
session_start();
header("Cache-Control: no-store");   // stops the Back button showing this page after logout

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config.php';

// Get only the categories THIS user chose (joined through the subscriptions table)
$categories = [];
$stmt = $conn->prepare(
    "SELECT c.id, c.name
     FROM subscriptions s
     JOIN categories c ON c.id = s.category_id
     WHERE s.user_id = ?
     ORDER BY c.name"
);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="page">
        <h1>Welcome,<span><?= htmlspecialchars($_SESSION['name']) ?></span> </h1>
        <p>This is a <span>user</span> page.</p>

        <h2>Your categories</h2>
        <?php if (count($categories) > 0): ?>
            <ul class="category-list">
                <?php foreach ($categories as $category): ?>
                    <li><?= htmlspecialchars($category['name']) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p>You haven't chosen any categories yet.</p>
        <?php endif; ?>

        <button onclick="window.location.href='logout.php'">Logout</button>
    </div>
</body>
</html>