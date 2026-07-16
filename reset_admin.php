<?php
require 'db.php';
$pass = password_hash('1234', PASSWORD_BCRYPT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $pass);
$stmt->execute();
echo "Admin password reset to 1234";
?>
