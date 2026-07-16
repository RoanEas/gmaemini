<?php 
session_start();
include 'db.php'; 

// อ่านข้อมูลโครงสร้างอวตารจากไฟล์ JSON
$jsonData = file_get_contents('data/avatar_items.json');
$avatarData = json_decode($jsonData, true);

// ================= ระบบบันทึกข้อมูลตัวละครและชื่อสายลับครั้งแรก =================
if (isset($_POST['start_agent'])) {
    $real_name = $_POST['real_name'];
    $avatar_img = $_POST['selected_avatar']; 
    
    // ตรวจสอบการ Login ของ Admin
    if (strtolower(trim($real_name)) === 'admin' && !empty($_POST['admin_password'])) {
        $admin_pass = $_POST['admin_password'];
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = 'admin' LIMIT 1");
        $stmt->execute();
        $res = $stmt->get_result();
        if ($res->num_rows > 0) {
            $admin_user = $res->fetch_assoc();
            if (password_verify($admin_pass, $admin_user['password']) || $admin_pass === $admin_user['password'] || md5($admin_pass) === $admin_user['password']) {
                $_SESSION['user_id'] = $admin_user['id'];
                $_SESSION['username'] = $admin_user['username'];
                $_SESSION['role'] = 'admin';
                $_SESSION['real_name'] = 'System Admin';
                $_SESSION['avatar_img'] = $avatar_img;
                $_SESSION['avatar_status'] = 1; 
                $_SESSION['score'] = $admin_user['score'];
                header("Location: index.php");
                exit();
            } else {
                echo "<script>alert('รหัสผ่านผู้ดูแลระบบไม่ถูกต้อง');</script>";
            }
        } else {
            echo "<script>alert('ไม่พบบัญชีแอดมินในระบบ');</script>";
        }
    }
    
    // Create an anonymous user
    $temp_username = 'agent_' . time() . rand(1000, 9999);
    $temp_email = $temp_username . '@gmaemini.local';
    $password = password_hash((string)rand(100000,999999), PASSWORD_BCRYPT);
    
    $stmt = $conn->prepare("INSERT INTO users (username, email, password, real_name, avatar_img, is_avatar_created, role, score) VALUES (?, ?, ?, ?, ?, 1, 'member', 0)");
    $stmt->bind_param("sssss", $temp_username, $temp_email, $password, $real_name, $avatar_img);
    $stmt->execute();
    
    $user_id = $conn->insert_id;
    
    $_SESSION['user_id'] = $user_id;
    $_SESSION['username'] = $temp_username;
    $_SESSION['role'] = 'member';
    $_SESSION['real_name'] = $real_name;
    $_SESSION['avatar_img'] = $avatar_img;
    $_SESSION['avatar_status'] = 1; 
    $_SESSION['score'] = 0;
    
    header("Location: index.php");
    exit();
}

if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ATBASH & CAESAR GAME 🚀</title>
    <link href="style.css?v=<?=time();?>" rel="stylesheet" type="text/css">
    <!-- Layout Manager for PC/Mobile Separation -->
    <script src="assets/js/layout_manager.js?v=<?=time();?>"></script>
    <!-- Ionicons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</head>
<body>

<!-- Background Geometric Elements -->
<div class="bg-shapes">
    <div class="shape circle"></div>
    <div class="shape triangle"></div>
    <div class="shape square"></div>
    <div class="shape cross"></div>
</div>

<?php if (!isset($_SESSION['user_id'])): ?>
<div class="game-overlay">
    <div class="agent-card">
        <div class="main-preview-box">
            <img id="current-agent-view" src="assets/avatar/dog.png" alt="Preview">
        </div>
        <form action="index.php" method="POST">
            <input type="hidden" name="selected_avatar" id="selected_avatar" value="dog.png">
            <div class="avatar-grid">
                <?php if(isset($avatarData['avatars'])): ?>
                    <?php foreach($avatarData['avatars'] as $index => $avatar): ?>
                        <div class="avatar-option <?php echo ($index === 0) ? 'selected' : ''; ?>" 
                             onclick="selectAgent('<?php echo $avatar['file']; ?>', '<?php echo $avatar['img_url']; ?>', this)">
                            <img src="<?php echo $avatar['img_url']; ?>" onerror="this.src='https://api.dicebear.com/7.x/bottts/svg?seed=<?php echo $avatar['id']; ?>'">
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div style="position: relative; width: 100%;">
                <input type="text" name="real_name" id="real_name_input" class="agent-input" placeholder="ใส่ชื่อ นามสกุลสายลับ" required oninput="checkAdminLogin(this.value)">
                <ion-icon name="person-circle-outline" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 1.5rem; color: var(--text-muted); pointer-events: none;"></ion-icon>
            </div>
            
            <!-- Hidden Admin Password Field -->
            <div id="admin_password_container" style="display: none; position: relative; width: 100%; margin-top: 10px; animation: fadeIn 0.3s ease;">
                <input type="password" name="admin_password" class="agent-input" placeholder="รหัสผ่านผู้ดูแลระบบ (Admin Password)" style="border-color: #facc15; box-shadow: 0 0 10px rgba(250, 204, 21, 0.2);">
                <ion-icon name="key-outline" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); font-size: 1.5rem; color: #facc15; pointer-events: none;"></ion-icon>
            </div>
            
            <button type="submit" name="start_agent" class="btn-submit">เริ่มต้นภารกิจ</button>
        </form>
    </div>
</div>

<script>
function checkAdminLogin(val) {
    const adminContainer = document.getElementById('admin_password_container');
    if (val.trim().toLowerCase() === 'admin') {
        adminContainer.style.display = 'block';
    } else {
        adminContainer.style.display = 'none';
        adminContainer.querySelector('input').value = '';
    }
}
</script>
<?php endif; ?>


<!-- 🛸 TOP HEADER -->
<header class="top-header">
    <a href="index.php" class="brand-logo">
        <ion-icon name="rocket"></ion-icon>
        MISSION CONTROL
    </a>

    <?php if(isset($_SESSION['user_id'])): ?>
        <a href="<?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? 'dashboard.php' : '#'; ?>" class="user-profile-pill" style="text-decoration: none; <?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') ? '' : 'cursor: default;'; ?>">
            <img src="assets/avatar/<?php echo htmlspecialchars($_SESSION['avatar_img'] ?? 'dog.png'); ?>" 
                 onerror="this.src='https://api.dicebear.com/7.x/bottts/svg?seed=1'" class="user-avatar" alt="Avatar">
            <div class="user-info">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['real_name'] ?? $_SESSION['username']); ?></span>
                <span class="user-score"><?php echo number_format($_SESSION['score'] ?? 0); ?> PTS</span>
            </div>
        </a>
    <?php else: ?>
        <div class="user-profile-pill">
            <ion-icon name="person-circle-outline" style="font-size:24px; color:var(--text-muted);"></ion-icon>
            <span class="visitor-badge">VISITOR</span>
        </div>
    <?php endif; ?>
</header>


<!-- 🎮 MAIN CONTENT AREA -->
<main class="main-container">

    <!-- TAB 1: GAMES -->
    <div id="tab-home" class="tab-content active">
        <div class="arcade-grid">
            
            <a href="games/senior_roulette/index.php" class="game-card card-blue">
                <ion-icon name="shuffle" class="game-icon"></ion-icon>
                <div class="game-title">สุ่มภารกิจจับคู่</div>
                <div class="game-desc">สับกองไพ่ทายปริศนาใบหน้ารุ่นพี่ ปวส.</div>
                <div class="play-pill">PLAY</div>
            </a>

            <a href="games/senior_roulette/game_music.php" class="game-card card-green">
                <ion-icon name="musical-notes" class="game-icon"></ion-icon>
                <div class="game-title">สมรภูมิทายเพลง</div>
                <div class="game-desc">ฟังเสียงท่อนฮุกออโต้จำกัดเวลาทายชื่อเพลง</div>
                <div class="play-pill">PLAY</div>
            </a>

            <a href="games/hardware_quiz/index.php" class="game-card card-orange">
                <ion-icon name="hardware-chip" class="game-icon"></ion-icon>
                <div class="game-title">ทายภาพอุปกรณ์</div>
                <div class="game-desc">วิเคราะห์ภาพฮาร์ดแวร์ ทดสอบความไว</div>
                <div class="play-pill">PLAY</div>
            </a>

            <a href="games/gacha_v2.php" class="game-card card-pink">
                <ion-icon name="dice" class="game-icon"></ion-icon>
                <div class="game-title">กาชาคัดออก</div>
                <div class="game-desc">ตู้สไลด์สายพานสุ่มไฟกระพริบ 3 ใบสุดท้าย</div>
                <div class="play-pill">PLAY</div>
            </a>

            <!-- Heads Up Cyber -->
            <a href="games/head_guess/index.php" class="game-card card-purple">
                <ion-icon name="phone-portrait" class="game-icon"></ion-icon>
                <div class="game-title">ทายคำบนหัว</div>
                <div class="game-desc">ถือโทรศัพท์ทาบหน้าผาก ใบ้คำสุดมันส์กับเพื่อนๆ</div>
                <div class="play-pill">PLAY</div>
            </a>

            <!-- Taboo Party -->
            <a href="games/taboo/index.php" class="game-card card-cyan">
                <ion-icon name="ban" class="game-icon" style="color:var(--neon-pink); filter: drop-shadow(0 0 12px rgba(236,72,153,0.4));"></ion-icon>
                <div class="game-title">เกมคำห้ามพูด</div>
                <div class="game-desc">ห้ามพูดคำเหล่านี้เด็ดขาด! สร้างทีมแข่งความสามารถในการใบ้</div>
                <div class="play-pill">PLAY</div>
            </a>

            <!-- Lightning Quiz (Admin Only) -->
            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="games/lightning_quiz/index.php" class="game-card card-green">
                    <ion-icon name="flash" class="game-icon" style="color:var(--neon-yellow); filter: drop-shadow(0 0 12px rgba(250,204,21,0.4));"></ion-icon>
                    <div class="game-title" style="color:var(--neon-green);">ปริศนาฟ้าแลบ</div>
                    <div class="game-desc">ไต่หอคอยสายฟ้า 10 ระดับ ตอบคำถามความไวแสง (แอดมินควบคุม)</div>
                    <div class="play-pill" style="background:var(--neon-green); color:#000;">ADMIN CONTROL</div>
                </a>
            <?php else: ?>
                <div class="game-card card-orange" style="opacity: 0.4; cursor: not-allowed;" title="เฉพาะผู้ดูแลระบบ (Admin) เท่านั้น">
                    <ion-icon name="lock-closed" class="game-icon" style="color:var(--text-muted);"></ion-icon>
                    <div class="game-title">ปริศนาฟ้าแลบ</div>
                    <div class="game-desc">ไต่หอคอยสายฟ้า 10 ระดับ (เปิดให้เล่นเฉพาะแอดมินเป็นโฮสต์)</div>
                    <div class="play-pill" style="background: rgba(255,255,255,0.05); color: var(--text-muted); cursor: not-allowed;">LOCKED</div>
                </div>
            <?php endif; ?>

        </div>
    </div>


    <!-- TAB 2: LEADERBOARD -->
    <div id="tab-leaderboard" class="tab-content">
        <div class="auth-panel">
            <h2>🏆 LEADERBOARD</h2>
            <table class="leaderboard-table">
                <thead><tr><th>RANK</th><th>AGENT</th><th>SCORE</th></tr></thead>
                <tbody>
                    <?php
                    $rank_sql = "SELECT username, real_name, score, avatar_img FROM users WHERE is_avatar_created = 1 ORDER BY score DESC LIMIT 5";
                    $rank_res = $conn->query($rank_sql); $rank = 1;
                    if ($rank_res && $rank_res->num_rows > 0) {
                        while($user_row = $rank_res->fetch_assoc()) {
                            ?>
                            <tr>
                                <td>#<?php echo $rank; ?></td>
                                <td>
                                    <img src="assets/avatar/<?php echo $user_row['avatar_img']; ?>" class="rank-avatar" onerror="this.src='https://api.dicebear.com/7.x/bottts/svg?seed=1'">
                                    <span style="margin-left:8px; font-weight:500; color:var(--text-main);"><?php echo htmlspecialchars($user_row['real_name']); ?></span>
                                </td>
                                <td><?php echo number_format($user_row['score']); ?> PTS</td>
                            </tr>
                    <?php $rank++; } } else { echo '<tr><td colspan="3" style="text-align:center;">No data available</td></tr>'; } ?>
                </tbody>
            </table>
        </div>
    </div>


</main>


<!-- 📱 BOTTOM FLOATING DOCK -->
<div class="bottom-dock-wrapper">
    <nav class="bottom-dock" id="bottom-dock">
        <div class="dock-slider" id="dock-slider"></div>
        
        <button class="dock-btn active" data-tab="tab-home" onclick="switchTab('tab-home', this)">
            <ion-icon name="home"></ion-icon>
            <span>HOME</span>
        </button>

        <button class="dock-btn" data-tab="tab-leaderboard" onclick="switchTab('tab-leaderboard', this)">
            <ion-icon name="trophy"></ion-icon>
            <span>RANK</span>
        </button>


        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <a href="admin.php" class="dock-btn" style="text-decoration: none; color: #facc15;">
                <ion-icon name="settings"></ion-icon>
                <span>ADMIN</span>
            </a>
        <?php endif; ?>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="index.php?logout=1" class="dock-btn" style="text-decoration: none; color: var(--neon-pink);">
                <ion-icon name="log-out-outline"></ion-icon>
                <span>LOGOUT</span>
            </a>
        <?php endif; ?>
    </nav>
</div>


<script>
// ═══════════════════════════════════════════════════════════════
//  AVATAR SELECTION
// ═══════════════════════════════════════════════════════════════
function selectAgent(fileName, imgUrl, element) {
    document.getElementById('selected_avatar').value = fileName;
    const viewImg = document.getElementById('current-agent-view');
    viewImg.src = imgUrl;
    viewImg.onerror = function() { this.src = element.querySelector('img').src; };
    
    document.querySelectorAll('.avatar-option').forEach(opt => opt.classList.remove('selected'));
    element.classList.add('selected');
}

// ═══════════════════════════════════════════════════════════════
//  ANIMATED DOCK SLIDER
// ═══════════════════════════════════════════════════════════════
function moveSlider(button) {
    const slider = document.getElementById('dock-slider');
    if (!slider || !button) return;
    
    const dock = document.getElementById('bottom-dock');
    const dockRect = dock.getBoundingClientRect();
    const btnRect = button.getBoundingClientRect();
    
    const offsetLeft = btnRect.left - dockRect.left;
    
    slider.style.width = btnRect.width + 'px';
    slider.style.transform = `translateX(${offsetLeft - 12}px)`; // -12px due to dock padding
}

function switchTab(tabId, button) {
    document.querySelectorAll('.tab-content').forEach(c => {
        c.classList.remove('active');
        c.style.animation = 'none'; // reset animation
    });
    
    document.querySelectorAll('.dock-btn').forEach(b => b.classList.remove('active'));
    
    const target = document.getElementById(tabId);
    target.classList.add('active');
    
    // Force reflow
    target.offsetHeight;
    target.style.animation = 'fadeIn 0.4s ease forwards';
    
    if (button) {
        button.classList.add('active');
        moveSlider(button);
    }
}

// Init slider position
window.addEventListener('load', () => {
    const activeBtn = document.querySelector('.dock-btn.active');
    if (activeBtn) {
        setTimeout(() => moveSlider(activeBtn), 150);
    }
});

window.addEventListener('resize', () => {
    const activeBtn = document.querySelector('.dock-btn.active');
    if (activeBtn) moveSlider(activeBtn);
// Auto Ping to keep online status
setInterval(() => {
    fetch('api_ping.php').catch(() => {});
}, 15000);
</script>
</body>
</html>