<?php
session_start();
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}
$username = isset($_SESSION['username']) ? $_SESSION['username'] : "admin";
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ระบบเช็คชื่อสายลับออนไลน์ (Live)</title>
    <link href="dashboard-style.css" rel="stylesheet" type="text/css">
    <script src="assets/js/layout_manager.js?v=<?=time();?>"></script>
    <style>
        .minimal-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #0f051d;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--card-border);
        }
        .minimal-table th, .minimal-table td {
            padding: 16px;
            text-align: left;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .minimal-table th {
            background: rgba(255,255,255,0.02);
            color: #94a3b8;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .minimal-table tr:hover td {
            background: rgba(255,255,255,0.02);
        }
    </style>
</head>
<body>

<div class="dashboard-wrapper">

    <header class="dash-header">
        <div class="welcome-zone">
            <span class="badge">ระบบจัดการหลังบ้าน (Admin Only)</span>
            <h1>หน้าต่างเช็คชื่อคนออนไลน์ (Live)</h1>
            <p>รายชื่อจะถูกอัปเดตอัตโนมัติทุกๆ 5 วินาที แสดงเฉพาะผู้เล่นที่กำลังเปิดหน้าเว็บอยู่ในขณะนี้</p>
        </div>
    </header>

    <main style="margin-top: 30px;">
        <table class="minimal-table">
            <thead>
                <tr>
                    <th width="80">โปรไฟล์</th>
                    <th>ชื่อผู้เล่น</th>
                    <th style="text-align: right;">สถานะล่าสุด</th>
                </tr>
            </thead>
            <tbody id="online-users-tbody">
                <tr><td colspan="3" style="text-align:center; color:#94a3b8; padding: 40px;">กำลังโหลดข้อมูล...</td></tr>
            </tbody>
        </table>
    </main>

    <nav class="floating-tabbar">
        <a href="index.php" class="tab-item">กลับหน้าแรก</a>
        <a href="admin.php" class="tab-item" style="color: #facc15;">จัดการระบบ (ตั้งค่า)</a>
        <a href="logout.php" class="tab-item" style="color: var(--neon-pink);">ออกจากระบบ</a>
    </nav>

</div>

<script>
function fetchOnlineUsers() {
    fetch('api_admin_online.php')
        .then(r => r.text())
        .then(html => {
            const tbody = document.getElementById('online-users-tbody');
            if(tbody) tbody.innerHTML = html;
        })
        .catch(e => console.error(e));
}

// Initial fetch and poll every 5 seconds
fetchOnlineUsers();
setInterval(fetchOnlineUsers, 5000);
</script>

</body>
</html>