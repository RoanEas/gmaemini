<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<tr><td colspan='3' style='text-align:center;'>Unauthorized</td></tr>";
    exit();
}

// Fetch users seen in the last 1 minute
$sql = "SELECT username, real_name, avatar_img, last_seen FROM users WHERE last_seen >= NOW() - INTERVAL 1 MINUTE ORDER BY last_seen DESC";
$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $name = htmlspecialchars($row['real_name'] ?? $row['username']);
        $avatar = htmlspecialchars($row['avatar_img']);
        $time = date('H:i:s', strtotime($row['last_seen']));
        echo "<tr>";
        echo "<td><img src='assets/avatar/{$avatar}' style='width:40px; height:40px; border-radius:50%; object-fit:cover; border: 2px solid #3b82f6;' onerror=\"this.src='https://api.dicebear.com/7.x/bottts/svg?seed=1'\"></td>";
        echo "<td><strong style='color:#fff;'>{$name}</strong> <span style='font-size:0.75rem; color:#10b981; margin-left:8px;'>● Online</span></td>";
        echo "<td style='color:#94a3b8; text-align:right;'>ใช้งานล่าสุด: {$time}</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='3' style='text-align:center; color:#94a3b8; padding: 30px;'>ขณะนี้ไม่มีผู้เล่นออนไลน์</td></tr>";
}
?>
