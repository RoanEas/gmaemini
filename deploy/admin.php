<?php 
session_start();
include 'db.php'; 

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    echo "<script>alert('⛔ เฉพาะแอดมินเท่านั้น'); window.location.href='index.php';</script>";
    exit();
}

// 📈 เพิ่มระบบอัปเดตคะแนนผู้เล่นใหม่
if (isset($_POST['update_score'])) {
    $user_id = $_POST['user_id'];
    $new_score = $_POST['score'];
    
    $stmt = $conn->prepare("UPDATE users SET score = ? WHERE id = ?");
    $stmt->bind_param("ii", $new_score, $user_id);
    $stmt->execute();
    header("Location: admin.php");
    exit();
}

// (ระบบเดิม: เพิ่มเกม / ลบเกม / สลับสถานะ)
if (isset($_POST['add_game'])) {
    $name = $_POST['game_name']; $desc = $_POST['description']; $url = $_POST['game_url']; $img = $_POST['image_url'];
    $stmt = $conn->prepare("INSERT INTO games (game_name, description, game_url, image_url) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $desc, $url, $img); $stmt->execute();
    header("Location: admin.php"); exit();
}
if (isset($_GET['delete'])) {
    $id = $_GET['delete']; $stmt = $conn->prepare("DELETE FROM games WHERE id = ?"); $stmt->bind_param("i", $id); $stmt->execute();
    header("Location: admin.php"); exit();
}
if (isset($_GET['toggle_status'])) {
    $id = $_GET['toggle_status']; $current_status = $_GET['current']; $new_status = ($current_status == 'active') ? 'maintenance' : 'active';
    $stmt = $conn->prepare("UPDATE games SET status = ? WHERE id = ?"); $stmt->bind_param("si", $new_status, $id); $stmt->execute();
    header("Location: admin.php"); exit();
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Center - 🛸</title>
    <link href="style.css" rel="stylesheet" type="text/css">
    <script src="assets/js/layout_manager.js?v=<?=time();?>"></script>
</head>
<body>

<nav class="nav-minimal">
    <div class="nav-logo"><a href="admin.php">⚙️ CONTROL CENTER</a></div>
    <div><a href="index.php" class="nav-link" style="background: #10b981;">ดูหน้าเว็บ (View Site)</a></div>
</nav>

<style>
.tab-btn {
    padding: 12px 20px;
    border-radius: 10px;
    border: 1px solid var(--card-border);
    background: rgba(255,255,255,0.05);
    color: var(--text-muted);
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
}
.tab-btn.active {
    background: var(--accent-purple);
    color: #fff;
    border-color: var(--accent-purple);
}
</style>

<div class="container" style="max-width:1300px;">
    
    <div class="admin-tabs-nav" style="display: flex; gap: 10px; margin-bottom: 30px;">
        <button class="tab-btn active" onclick="switchAdminTab('tab-games', this)">🎮 จัดการเกม</button>
        <button class="tab-btn" onclick="switchAdminTab('tab-scores', this)">🏆 จัดการคะแนน</button>
        <button class="tab-btn" onclick="switchAdminTab('tab-online', this)">🟢 เช็คชื่อคนออนไลน์</button>
    </div>

    <!-- TAB 1: GAMES -->
    <div id="tab-games" class="admin-tab">
        <div class="admin-layout" style="margin-bottom: 50px;">
            <div class="form-box">
                <h2>➕ เพิ่มภารกิจใหม่</h2>
                <form action="admin.php" method="POST">
                    <div class="input-group"><label>ชื่อเกม</label><input type="text" name="game_name" required></div>
                    <div class="input-group"><label>รายละเอียด</label><textarea name="description" rows="2" required></textarea></div>
                    <div class="input-group"><label>ลิงก์เกม</label><input type="text" name="game_url" required></div>
                    <div class="input-group"><label>ลิงก์รูปภาพปก</label><input type="url" name="image_url" value="https://images.unsplash.com/photo-1614728263952-84ea256f9679"></div>
                    <button type="submit" name="add_game" class="btn-submit">บันทึกเกม</button>
                </form>
            </div>

            <div>
                <table class="minimal-table">
                    <thead><tr><th>รูปปก</th><th>ชื่อเกม</th><th>สถานะ</th><th style="text-align:right;">การจัดการ</th></tr></thead>
                    <tbody>
                        <?php
                        $sql = "SELECT * FROM games ORDER BY id DESC"; $result = $conn->query($sql);
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                ?>
                                <tr>
                                    <td><img src="<?php echo htmlspecialchars($row['image_url']); ?>" style="width:60px; height:40px; object-fit:cover; border-radius:5px;"></td>
                                    <td><strong style="color:#fff;"><?php echo htmlspecialchars($row['game_name']); ?></strong></td>
                                    <td><?php echo ($row['status'] == 'active') ? '<span class="badge-active">● ออนไลน์</span>' : '<span class="badge-maintenance">● ปิดปรับปรุง</span>'; ?></td>
                                    <td style="text-align:right;"><a href="admin.php?toggle_status=<?php echo $row['id']; ?>&current=<?php echo $row['status']; ?>" class="btn-action">สลับสถานะ</a><a href="admin.php?delete=<?php echo $row['id']; ?>" class="btn-action" style="color:#f87171;" onclick="return confirm('ลบเกมนี้?')">ลบ</a></td>
                                </tr>
                            <?php } } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: SCORES -->
    <div id="tab-scores" class="admin-tab" style="display:none;">
        <div class="form-box" style="max-width: 100%;">
            <h2 style="color: #facc15;">🏆 ระบบอัปเดตคะแนน & จัดอันดับที่ 1 ของผู้เล่น</h2>
            <table class="minimal-table" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อผู้ใช้งาน</th>
                        <th>อีเมลนักศึกษา</th>
                        <th width="250">คะแนนปัจจุบัน</th>
                        <th style="text-align: right;">ดำเนินการ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // ดึงข้อมูลผู้ใช้ทุกคนที่เป็น member (เรียงจากคะแนนสูงสุดลงมา)
                    $user_sql = "SELECT * FROM users WHERE role = 'member' ORDER BY score DESC";
                    $user_res = $conn->query($user_sql);
                    $u_rank = 1;

                    if ($user_res && $user_res->num_rows > 0) {
                        while($u_row = $user_res->fetch_assoc()) {
                            ?>
                            <tr>
                                <td>#<?php echo $u_row['id']; ?></td>
                                <td>
                                    <strong style="color: #fff;">
                                        <?php echo ($u_rank == 1) ? '👑 ' : ''; ?>
                                        <?php echo htmlspecialchars($u_row['username']); ?>
                                    </strong>
                                    <?php echo ($u_rank == 1) ? ' <span style="color:#facc15; font-size:0.8rem;">(ที่ 1 ของชมรม)</span>' : ''; ?>
                                </td>
                                <td style="color: #94a3b8;"><?php echo htmlspecialchars($u_row['email']); ?></td>
                                
                                <form action="admin.php" method="POST">
                                    <td>
                                        <input type="number" name="score" value="<?php echo $u_row['score']; ?>" 
                                               style="width:120px; padding:6px; background:#0f051d; border:1px solid #4c1d95; border-radius:5px; color:#4ade80; font-weight:bold; text-align:center;">
                                        <input type="hidden" name="user_id" value="<?php echo $u_row['id']; ?>">
                                    </td>
                                    <td style="text-align: right;">
                                        <button type="submit" name="update_score" class="btn-action" style="background:#22c55e; color:#fff; border:none;">
                                            💾 อัปเดตคะแนน
                                        </button>
                                    </td>
                                </form>
                            </tr>
                            <?php
                            $u_rank++;
                        }
                    } else {
                        echo '<tr><td colspan="5" style="text-align:center; color:#94a3b8;">ยังไม่มีผู้เล่นเข้ามาในระบบ</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB 3: ONLINE REAL-TIME -->
    <div id="tab-online" class="admin-tab" style="display:none;">
        <div class="form-box" style="max-width: 100%;">
            <h2 style="color: #3b82f6;">🟢 เช็คชื่อคนกำลังออนไลน์ (Live)</h2>
            <p style="color: var(--text-muted); font-size: 0.85rem; margin-top: 5px;">* ตารางนี้จะอัปเดตอัตโนมัติทุก 5 วินาที แสดงรายชื่อคนที่กำลังเปิดหน้าเว็บหรือเล่นเกมอยู่ในขณะนี้</p>
            <table class="minimal-table" style="margin-top: 20px;">
                <thead>
                    <tr>
                        <th width="80">โปรไฟล์</th>
                        <th>ชื่อผู้เล่น</th>
                        <th style="text-align: right;">สถานะล่าสุด</th>
                    </tr>
                </thead>
                <tbody id="online-users-tbody">
                    <tr><td colspan="3" style="text-align:center; color:#94a3b8;">กำลังโหลดข้อมูล...</td></tr>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script>
function switchAdminTab(tabId, btn) {
    document.querySelectorAll('.admin-tab').forEach(t => t.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';
    
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.style.background = 'rgba(255,255,255,0.05)';
        b.style.color = 'var(--text-muted)';
        b.style.borderColor = 'var(--card-border)';
    });
    btn.style.background = 'var(--accent-purple)';
    btn.style.color = '#fff';
    btn.style.borderColor = 'var(--accent-purple)';
}

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