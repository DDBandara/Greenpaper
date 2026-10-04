<?php
session_start();
if (!isset($_SESSION['is_admin'])) {
    header('Location: index.php');
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div>
        <h1>Welcome,<span><?= $_SESSION['name'] ?></span> </h1>
        <p>This is an <span>admin</span> page.</p>
        <button>Logout</button>
    </div>
</body>
</html>