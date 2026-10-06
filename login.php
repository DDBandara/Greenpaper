<?php
session_start();
require_once 'config.php';

// Already logged in? Send them to their page instead of showing the form.
if (isset($_SESSION['user_id'])) {
    if (($_SESSION['role'] ?? '') === 'admin') {
        header("Location: admin_page.php");
    } else {
        header("Location: index.php");
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

// Get the categories for the register form
$categories = [];
$result = $conn->query("SELECT id, name FROM categories ORDER BY name");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}

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
    <style>
        /* The global "input" style in style.css would stretch checkboxes, so reset them here */
        .field-label { text-align: left; font-weight: 500; margin-bottom: 8px; }
        .category-options { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 20px; }
        .category-options label { display: flex; align-items: center; gap: 6px; background: #eef2fb; padding: 8px 14px; border-radius: 20px; font-size: 15px; cursor: pointer; }
        .category-options input[type="checkbox"] { width: auto; margin: 0; padding: 0; }
    </style>
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

                <p class="field-label">Choose the categories you want to follow:</p>
                <div class="category-options">
                    <?php foreach ($categories as $category): ?>
                        <label>
                            <input type="checkbox" name="categories[]" value="<?= (int)$category['id'] ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>

                <button type="submit" name="register">Register</button>
                <p>Already have an account? <a href="#" onclick="showform('login-form'); return false;">Login</a></p>
            </form>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>