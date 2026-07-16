<?php
require 'db.php';
$stmt = $conn->query("SELECT * FROM users WHERE role = 'admin'");
$admins = [];
while ($row = $stmt->fetch_assoc()) {
    $admins[] = $row;
}
echo json_encode($admins, JSON_UNESCAPED_UNICODE);
?>
