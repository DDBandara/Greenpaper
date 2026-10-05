<?php
session_start();

// Already logged in? Send them to their page instead of showing the form.
if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header("Location: admin_page.php");
    } else {
        header("Location: user_page.php");
    }
    exit();
}

$errors = [
    'login'    => $_SESSION['login_error'] ?? '',
    'register' => $_SESSION['register_error'] ?? ''
];
$success    = $_SESSION['success'] ?? '';
$activeForm = $_SESSION['active_form'] ?? 'login';

// Remove only the one-time messages (do NOT wipe the whole session)
unset($_SESSION['login_error'], $_SESSION['register_error'], $_SESSION['success'], $_SESSION['active_form']);

function showError($error) {
    return $error ? "<p class='error-message'>" . htmlspecialchars($error) . "</p>" : "";
}

function showSuccess($message) {
    return $message ? "<p class='success-message'>" . htmlspecialchars($message) . "</p>" : "";
}

function isActiveForm($formName, $activeForm) {
    return $activeForm === $formName ? "active" : "";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login and Signup</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container1">
        <div class="form-box <?= isActiveForm('login', $activeForm) ?>" id="login-form">
            <form action="login_register.php" method="post">
                <h2>Login</h2>
                <?= showSuccess($success) ?>
                <?= showError($errors['login']) ?>
                <input type="email" name="email" placeholder="email" required>
                <input type="password" name="password" placeholder="password" required>
                <button type="submit" name="login">Login</button>
                <p>Don't have an account? <a href="#" onclick="showform('register-form'); return false;">Register</a></p>
            </form>
        </div>

        <div class="form-box <?= isActiveForm('register', $activeForm) ?>" id="register-form">
            <form action="login_register.php" method="post">
                <h2>Register</h2>
                <?= showError($errors['register']) ?>
                <input type="text" name="name" placeholder="name" required>
                <input type="email" name="email" placeholder="email" required>
                <input type="password" name="password" placeholder="password (at least 8 characters)" required>
                <button type="submit" name="register">Register</button>
                <p>Already have an account? <a href="#" onclick="showform('login-form'); return false;">Login</a></p>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>