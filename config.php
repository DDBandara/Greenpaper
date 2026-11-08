<?php
    if (!defined('BASE_URL')) {
        $docRoot = isset($_SERVER['DOCUMENT_ROOT']) ? rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/') : '';
        $appDir = rtrim(str_replace('\\', '/', __DIR__), '/');
        if ($docRoot !== '' && strpos($appDir, $docRoot) === 0) {
            $baseUrl = substr($appDir, strlen($docRoot));
        } else {
            $baseUrl = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        }
        define('BASE_URL', rtrim($baseUrl, '/'));
    }

    $host = "localhost";
    $user = "root";
    $password = "";
    $database = "newsdesk_db";

    $conn = mysqli_connect($host, $user, $password, $database);

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Store all text correctly (including Sinhala and emoji)
    mysqli_set_charset($conn, "utf8mb4");
?>