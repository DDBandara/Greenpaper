<?php
session_start();
require_once 'config.php';

// Only accept form submissions. Opening this file directly goes back to the form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

// ===== REGISTER =====
if (isset($_POST['register'])) {
    $name          = trim($_POST['name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $plainPassword = $_POST['password'] ?? '';
    $role          = 'user';   // Never take the role from the form. Make admins in phpMyAdmin.
    $selected      = $_POST['categories'] ?? [];   // the ticked category ids

    if ($name === '' || $email === '' || $plainPassword === '') {
        $_SESSION['register_error'] = "Please fill in all fields.";
        $_SESSION['active_form']    = "register";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['register_error'] = "Enter a valid email address.";
        $_SESSION['active_form']    = "register";
    } elseif (strlen($plainPassword) < 8) {
        $_SESSION['register_error'] = "Password must be at least 8 characters.";
        $_SESSION['active_form']    = "register";
    } else {
        // Is the email already used?
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $checkEmail = $stmt->get_result();

        if ($checkEmail->num_rows > 0) {
            $_SESSION['register_error'] = "Email is already registered.";
            $_SESSION['active_form']    = "register";
        } else {
            $password = password_hash($plainPassword, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $name, $email, $password, $role);
            $stmt->execute();
            $newUserId = $stmt->insert_id;   // the id MySQL just gave this new user

            // Save the chosen categories (only ids that really exist in the categories table)
            if (is_array($selected) && count($selected) > 0) {
                $validIds = [];
                $res = $conn->query("SELECT id FROM categories");
                while ($r = $res->fetch_assoc()) {
                    $validIds[] = (int)$r['id'];
                }

                $subStmt = $conn->prepare("INSERT IGNORE INTO subscriptions (user_id, category_id) VALUES (?, ?)");
                foreach (array_unique($selected) as $catId) {
                    $catId = (int)$catId;
                    if (in_array($catId, $validIds, true)) {
                        $subStmt->bind_param("ii", $newUserId, $catId);
                        $subStmt->execute();
                    }
                }
            }

            $_SESSION['success']     = "Registered! Please log in.";
            $_SESSION['active_form'] = "login";
        }
    }

    header("Location: login.php");
    exit();
}

// ===== LOGIN =====
if (isset($_POST['login'])) {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user["password"])) {
            session_regenerate_id(true);   // new session id after login (security)

            $_SESSION["user_id"] = $user["id"];
            $_SESSION["name"]    = $user["name"];
            $_SESSION["email"]   = $user["email"];
            $_SESSION["role"]    = $user["role"];

            if ($user['role'] === 'admin') {
                header("Location: admin_page.php");
            } else {
                header("Location: index.php");
            }
            exit();
        }
    }

    // Wrong email or wrong password: same message for both
    $_SESSION['login_error'] = "Invalid email or password.";
    $_SESSION['active_form'] = "login";
    header("Location: login.php");
    exit();
}

// Neither button was pressed
header("Location: login.php");
exit();
?>