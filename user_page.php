<?php
session_start();
header("Cache-Control: no-store");   // stops the Back button showing this page after logout

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
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
<body style="background-color: white;">
    <div>
        <h1>Welcome,<span><?= htmlspecialchars($_SESSION['name']) ?></span> </h1>
        <p>This is a <span>user</span> page.</p>
        <button onclick="window.location.href='logout.php'">Logout</button>
    </div>
</body>
</html>