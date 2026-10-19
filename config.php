<?php
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