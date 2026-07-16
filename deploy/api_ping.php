<?php
session_start();
require 'db.php';

if (isset($_SESSION['user_id'])) {
    $stmt = $conn->prepare("UPDATE users SET last_seen = NOW() WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
}
?>
