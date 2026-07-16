<?php
$host = "localhost";
$username = "root"; // ปรับตามของวิทยาลัย (ถ้ามี)
$password = "";     // ปรับตามของวิทยาลัย (ถ้ามี)
$dbname = "club_game_db";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Auto-add last_seen column if it doesn't exist
$check_col = $conn->query("SHOW COLUMNS FROM users LIKE 'last_seen'");
if ($check_col->num_rows == 0) {
    $conn->query("ALTER TABLE users ADD COLUMN last_seen TIMESTAMP NULL DEFAULT NULL");
}
?>