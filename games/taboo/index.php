<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}
require_once dirname(__DIR__, 2) . '/db.php';
// Auto-create database table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS `head_guess_rooms` (
    `room_code` VARCHAR(10) PRIMARY KEY,
    `host_user_id` INT NOT NULL,
    `category_id` VARCHAR(50) DEFAULT NULL,
    `current_word` VARCHAR(255) DEFAULT NULL,
    `game_status` VARCHAR(50) DEFAULT 'setup',
    `score` INT DEFAULT 0,
    `seconds_remaining` INT DEFAULT 60,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$displayName = htmlspecialchars($_SESSION['real_name'] ?? $_SESSION['username'] ?? 'ผู้เล่น');
$avatarSrc = '../../assets/avatar/' . htmlspecialchars($_SESSION['avatar_img'] ?? 'dog.png');
$username = htmlspecialchars($_SESSION['username'] ?? '');
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>🚫 TABOO CHAMP</title>
    <link href="https://fonts.googleapis.com/css2?family=Chakra+Petch:wght@400;700&family=Outfit:wght@400;600;800&family=Prompt:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="style.css?v=<?=time();?>">
    <style>
        /* ─── Taboo Champ Custom Overrides ─── */
        :root {
            --tc-bg: #0d0d14;
            --tc-card: #16161f;
            --tc-border: rgba(255,255,255,0.09);
            --tc-purple: #a855f7;
            --tc-purple-dim: rgba(168,85,247,0.18);
            --tc-pink: #ec4899;
            --tc-pink-dim: rgba(236,72,153,0.18);
            --tc-cyan: #06b6d4;
            --tc-cyan-dim: rgba(6,182,212,0.18);
            --tc-green: #22c55e;
            --tc-green-dim: rgba(34,197,94,0.18);
            --tc-red: #ef4444;
            --tc-red-dim: rgba(239,68,68,0.18);
            --tc-gold: #f59e0b;
        }
        body { background: var(--tc-bg); font-family: 'Outfit', sans-serif; }
        body::before, body::after { display: none !important; }

        /* ─── Header ─── */
        .tc-header {
            position: fixed; top: 0; left: 50%; transform: translateX(-50%);
            width: 100%; max-width: 480px;
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 16px 12px;
            background: rgba(13,13,20,0.92); backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--tc-border);
            z-index: 100;
        }
        .tc-back { display: flex; align-items: center; gap: 6px; color: #aaa; text-decoration: none; font-size: 0.9rem; font-weight: 600; }
        .tc-back:hover { color: #fff; }
        .tc-title { font-size: 1rem; font-weight: 900; letter-spacing: 2px; background: linear-gradient(90deg, var(--tc-purple), var(--tc-pink)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .tc-theme-btn { width: 34px; height: 34px; border-radius: 50%; background: rgba(255,255,255,0.07); border: 1px solid var(--tc-border); display: flex; align-items: center; justify-content: center; color: #aaa; cursor: pointer; }

        /* ─── Screens ─── */
        .tc-screen { display: none; flex-direction: column; align-items: center; padding: 80px 16px 100px; min-height: 100vh; width: 100%; max-width: 480px; margin: 0 auto; }
        .tc-screen.active { display: flex; }

        /* ─── Lobby Entry (Screen 1) ─── */
        .tc-hero { text-align: center; padding: 12px 0 20px; }
        .tc-hero-icon { font-size: 3.5rem; margin-bottom: 8px; }
        .tc-hero-title { font-size: 1.6rem; font-weight: 900; color: #fff; margin-bottom: 4px; }
        .tc-hero-desc { font-size: 0.82rem; color: #888; line-height: 1.5; }

        /* Profile Card */
        .tc-profile-card {
            width: 100%; padding: 14px 16px; border-radius: 16px;
            background: var(--tc-card); border: 1px solid var(--tc-border);
            display: flex; align-items: center; gap: 12px; margin-bottom: 16px;
        }
        .tc-profile-card img { width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 2px solid var(--tc-cyan); flex-shrink: 0; }
        .tc-profile-name { font-weight: 800; font-size: 1rem; color: #fff; }
        .tc-profile-user { font-size: 0.75rem; color: var(--tc-cyan); margin-top: 1px; }

        /* Action Buttons */
        .tc-actions-row { display: flex; gap: 10px; width: 100%; margin-bottom: 12px; }
        .tc-btn { flex: 1; padding: 13px 10px; border-radius: 14px; font-weight: 800; font-size: 0.85rem; cursor: pointer; border: none; display: flex; align-items: center; justify-content: center; gap: 6px; transition: all 0.15s; }
        .tc-btn:active { transform: scale(0.96); }
        .tc-btn-purple { background: linear-gradient(135deg, #7c3aed, #a855f7); color: #fff; }
        .tc-btn-green { background: linear-gradient(135deg, #16a34a, #22c55e); color: #000; }
        .tc-btn-red { background: linear-gradient(135deg, #dc2626, #ef4444); color: #fff; }
        .tc-btn-gray { background: rgba(255,255,255,0.08); border: 1px solid var(--tc-border); color: #fff; }
        .tc-btn-pink { background: linear-gradient(135deg, #be185d, #ec4899); color: #fff; }
        .tc-btn-full { width: 100%; border-radius: 14px; padding: 15px; font-size: 1rem; }

        /* Room Code Input */
        .tc-join-row { display: flex; gap: 8px; width: 100%; }
        .tc-input { flex: 1; padding: 13px 14px; border-radius: 14px; background: var(--tc-card); border: 1px solid var(--tc-border); color: #fff; font-size: 1rem; font-weight: 700; text-align: center; font-family: 'Outfit', monospace; letter-spacing: 4px; }
        .tc-input:focus { outline: none; border-color: var(--tc-purple); }
        .tc-input::placeholder { letter-spacing: 1px; font-size: 0.8rem; font-weight: 400; color: #555; }

        /* ─── Waiting Lobby (Screen 2) ─── */
        .tc-room-header {
            width: 100%; padding: 14px 16px; border-radius: 16px;
            background: var(--tc-card); border: 1px solid var(--tc-border);
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;
        }
        .tc-room-code { font-size: 1.1rem; font-weight: 900; color: #fff; display: flex; align-items: center; gap: 6px; }
        .tc-room-code span { font-family: 'Chakra Petch', monospace; color: var(--tc-gold); font-size: 1.3rem; }

        /* Mode Selector */
        .tc-section-label { font-size: 0.78rem; font-weight: 700; color: #777; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; width: 100%; }
        .tc-seg { display: flex; background: var(--tc-card); border: 1px solid var(--tc-border); border-radius: 12px; overflow: hidden; width: 100%; margin-bottom: 12px; }
        .tc-seg-btn { flex: 1; padding: 10px 4px; font-size: 0.8rem; font-weight: 700; color: #777; background: none; border: none; cursor: pointer; transition: all 0.15s; }
        .tc-seg-btn.active { background: var(--tc-purple); color: #fff; border-radius: 10px; }

        /* Role Selector */
        .tc-roles-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; width: 100%; margin-bottom: 12px; }
        .tc-role-btn {
            padding: 12px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: 700;
            background: var(--tc-card); border: 1.5px solid var(--tc-border);
            color: #888; cursor: pointer; transition: all 0.15s; text-align: center;
        }
        .tc-role-btn.active-a { border-color: var(--tc-cyan); background: var(--tc-cyan-dim); color: var(--tc-cyan); }
        .tc-role-btn.active-b { border-color: var(--tc-pink); background: var(--tc-pink-dim); color: var(--tc-pink); }
        .tc-role-btn.active-spec { border-color: var(--tc-green); background: var(--tc-green-dim); color: var(--tc-green); }
        .tc-role-btn.active-gm { border-color: var(--tc-red); background: var(--tc-red-dim); color: var(--tc-red); }

        /* Players List */
        .tc-players-box {
            width: 100%; border-radius: 14px; background: var(--tc-card);
            border: 1px solid var(--tc-border); overflow: hidden; margin-bottom: 12px;
        }
        .tc-players-title { padding: 10px 14px; font-size: 0.78rem; font-weight: 700; color: #666; display: flex; align-items: center; gap: 6px; border-bottom: 1px solid var(--tc-border); }
        .tc-player-row { display: flex; align-items: center; gap: 10px; padding: 10px 14px; border-bottom: 1px solid rgba(255,255,255,0.04); }
        .tc-player-row:last-child { border-bottom: none; }
        .tc-player-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
        .tc-player-name { font-weight: 700; font-size: 0.85rem; color: #fff; flex: 1; }
        .tc-player-badge { padding: 3px 8px; border-radius: 20px; font-size: 0.68rem; font-weight: 800; }
        .badge-host { background: rgba(245,158,11,0.15); color: var(--tc-gold); border: 1px solid rgba(245,158,11,0.3); }
        .badge-ready { background: rgba(34,197,94,0.15); color: var(--tc-green); border: 1px solid rgba(34,197,94,0.3); }
        .badge-wait { background: rgba(255,159,10,0.12); color: #ff9f0a; border: 1px solid rgba(255,159,10,0.25); }

        /* ─── PLAY SCREEN ─── */
        .tc-play-screen {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: var(--tc-bg);
            display: none; flex-direction: column; align-items: center; justify-content: space-between;
            z-index: 200; padding: 0;
        }
        .tc-play-screen.active { display: flex; }

        /* Play HUD */
        .tc-play-hud {
            width: 100%; display: flex; align-items: center; justify-content: space-between;
            padding: 14px 16px; background: rgba(13,13,20,0.9); backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--tc-border); flex-shrink: 0;
        }
        .tc-hud-left { display: flex; align-items: center; gap: 8px; }
        .tc-hud-exit { padding: 8px 14px; border-radius: 10px; background: rgba(255,255,255,0.07); border: 1px solid var(--tc-border); color: #fff; font-size: 0.8rem; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 4px; }
        .tc-zoom-grp { display: flex; gap: 4px; }
        .tc-zoom-btn { padding: 6px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 800; border: 1px solid var(--tc-border); background: rgba(255,255,255,0.05); color: #fff; cursor: pointer; transition: all 0.15s; }
        .tc-zoom-btn.active { border-color: var(--tc-purple); background: var(--tc-purple-dim); color: #fff; }
        .tc-hud-timer { font-size: 0.85rem; font-weight: 700; color: #aaa; display: flex; align-items: center; gap: 4px; }
        .tc-hud-timer.urgent { color: var(--tc-red); animation: pulse-red 0.5s infinite; }
        @keyframes pulse-red { 0%,100% { opacity: 1; } 50% { opacity: 0.5; } }

        /* Word Card */
        .tc-word-area { flex: 1; display: flex; align-items: center; justify-content: center; width: 100%; padding: 24px 20px; }
        .tc-word-card {
            width: 100%; border-radius: 24px; background: var(--tc-card);
            border: 1.5px solid var(--tc-border);
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 32px 20px; min-height: 220px; text-align: center;
            transition: border-color 0.3s, background 0.3s;
        }
        .tc-word-card.state-correct { border-color: var(--tc-green); background: rgba(34,197,94,0.07); }
        .tc-word-card.state-caught { border-color: var(--tc-red); background: rgba(239,68,68,0.07); }
        .tc-word-cat { font-size: 0.72rem; font-weight: 700; color: var(--tc-purple); letter-spacing: 2px; text-transform: uppercase; margin-bottom: 16px; }
        .tc-word-text { font-size: 200px; font-weight: 900; color: #fff; line-height: 1.1; word-break: break-word; transition: font-size 0.2s; }

        /* Action Buttons Bottom */
        .tc-action-bar { width: 100%; padding: 16px; display: flex; gap: 12px; flex-shrink: 0; }
        .tc-action-pass { flex: 1; padding: 18px; border-radius: 16px; font-size: 0.9rem; font-weight: 800; background: rgba(239,68,68,0.12); border: 1.5px solid rgba(239,68,68,0.35); color: var(--tc-red); cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; }
        .tc-action-correct { flex: 1; padding: 18px; border-radius: 16px; font-size: 0.9rem; font-weight: 800; background: rgba(34,197,94,0.12); border: 1.5px solid rgba(34,197,94,0.35); color: var(--tc-green); cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; }

        /* ─── Section Divider ─── */
        .tc-divider { width: 100%; height: 1px; background: var(--tc-border); margin: 6px 0 14px; }

        /* ─── Countdown overlay ─── */
        .tc-countdown {
            position: fixed; inset: 0; z-index: 300; display: none;
            align-items: center; justify-content: center;
            background: rgba(0,0,0,0.92); backdrop-filter: blur(8px);
        }
        .tc-countdown.active { display: flex; }
        .tc-countdown-num { font-size: clamp(6rem, 30vw, 12rem); font-weight: 900; color: var(--tc-purple); animation: countIn 0.4s ease; }
        @keyframes countIn { from { transform: scale(1.4); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        /* ─── Misc ─── */
        .tc-info-tag { display: inline-flex; align-items: center; gap: 5px; padding: 4px 10px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; background: var(--tc-purple-dim); color: var(--tc-purple); border: 1px solid rgba(168,85,247,0.3); }
    </style>
</head>
<body class="theme-cyber">

<!-- HEADER -->
<header class="tc-header">
    <a href="../../index.php" class="tc-back">
        <ion-icon name="chevron-back-outline" style="font-size: 1.1rem;"></ion-icon>
        กลับหน้าหลัก
    </a>
    <div class="tc-title">TABOO CHAMP</div>
    <button class="tc-theme-btn" onclick="toggleTheme()" title="เปลี่ยนธีม">
        <ion-icon name="color-palette-outline" style="font-size: 1.1rem;" id="theme-icon"></ion-icon>
    </button>
</header>

<!-- ═══════════════════════════════════════════════
     SCREEN 1: LOBBY ENTRY (สร้าง/เข้าร่วมห้อง)
════════════════════════════════════════════════ -->
<div id="screen-entry" class="tc-screen active">
    <!-- Hero -->
    <div class="tc-hero">
        <div class="tc-hero-icon">🚫</div>
        <div class="tc-hero-title">ปาร์ตี้คำต้องห้าม</div>
        <div class="tc-hero-desc">เกมแอบจับผิดคำต้องห้ามแบ่งทีม 3v3 / 6v6 หรือเล่นบทบาทผู้คุมเกม</div>
    </div>

    <!-- Profile -->
    <div class="tc-profile-card">
        <img src="<?= $avatarSrc ?>" alt="avatar" onerror="this.src='../../assets/avatar/dog.png'">
        <div>
            <div class="tc-profile-name"><?= $displayName ?></div>
            <div class="tc-profile-user">@<?= $username ?></div>
        </div>
        <ion-icon name="checkmark-circle" style="color: var(--tc-green); font-size: 1.4rem; margin-left: auto; flex-shrink: 0;"></ion-icon>
    </div>

    <!-- Create / Join Buttons -->
    <div class="tc-actions-row">
        <button class="tc-btn tc-btn-purple" onclick="createRoom()">
            <ion-icon name="add-circle-outline"></ion-icon> สร้างห้อง
        </button>
    </div>
    <div class="tc-join-row" style="margin-bottom: 20px;">
        <input type="tel" id="join-code" class="tc-input" maxlength="4" placeholder="รหัส 4 หลัก">
        <button class="tc-btn tc-btn-green" onclick="joinRoom()" style="flex: 0; padding: 13px 20px; white-space: nowrap;">
            <ion-icon name="enter-outline"></ion-icon> เข้าร่วม
        </button>
    </div>

    <!-- Recent rooms (decoration) -->
    <div class="tc-section-label">เล่นได้ทันที</div>
    <button class="tc-btn tc-btn-pink tc-btn-full" onclick="quickPlay()" style="margin-bottom: 8px;">
        <ion-icon name="flash-outline" style="font-size: 1.1rem;"></ion-icon>
        เข้าร่วมห้องปาร์ตี้ 🚀
    </button>
</div>

<!-- ═══════════════════════════════════════════════
     SCREEN 2: WAITING LOBBY (รอเพื่อนเข้าห้อง)
════════════════════════════════════════════════ -->
<div id="screen-lobby" class="tc-screen">
    <!-- Room Code Header -->
    <div class="tc-room-header">
        <div class="tc-room-code">
            ห้อง: <span id="room-code-display">----</span>
            <ion-icon name="crown-outline" id="host-crown" style="color: var(--tc-gold); display: none;"></ion-icon>
        </div>
        <button class="tc-btn tc-btn-red" onclick="exitRoom()" style="flex: 0; padding: 8px 14px; font-size: 0.78rem; border-radius: 10px;">
            <ion-icon name="exit-outline"></ion-icon> ออกห้อง
        </button>
    </div>

    <!-- Mode -->
    <div class="tc-section-label">รูปแบบการเล่น:</div>
    <div class="tc-seg" id="mode-seg">
        <button class="tc-seg-btn active" onclick="setMode('solo')" id="mode-solo">Solo</button>
        <button class="tc-seg-btn" onclick="setMode('3v3')" id="mode-3v3">ทีม 3v3</button>
        <button class="tc-seg-btn" onclick="setMode('6v6')" id="mode-6v6">ทีม 6v6</button>
    </div>

    <!-- Time -->
    <div class="tc-section-label">เวลาในการแข่ง:</div>
    <div class="tc-seg" id="time-seg">
        <button class="tc-seg-btn active" onclick="setTime(120)" id="time-2m">2 นาที</button>
        <button class="tc-seg-btn" onclick="setTime(300)" id="time-5m">5 นาที</button>
        <button class="tc-seg-btn" onclick="setTime(600)" id="time-10m">10 นาที</button>
    </div>

    <!-- Role Selection (visible for non-host) -->
    <div id="role-section">
        <div class="tc-section-label">เลือกบทบาทของคุณ:</div>
        <div class="tc-roles-grid">
            <button class="tc-role-btn" id="role-a" onclick="setRole('a')">ทีม A</button>
            <button class="tc-role-btn" id="role-b" onclick="setRole('b')">ทีม B</button>
            <button class="tc-role-btn active-spec" id="role-spec" onclick="setRole('spec')">
                👁️ ผู้ชม
            </button>
            <button class="tc-role-btn active-gm" id="role-gm" onclick="setRole('gm')">
                🎮 ผู้คุมเกม
            </button>
        </div>
    </div>

    <!-- Players List -->
    <div class="tc-players-box">
        <div class="tc-players-title">
            <ion-icon name="people-outline" style="color: var(--tc-cyan);"></ion-icon>
            ผู้เข้าร่วมในห้อง:
        </div>
        <div id="players-list">
            <!-- Populated by JS -->
        </div>
    </div>

    <!-- Action Button -->
    <div id="lobby-action-area" style="width: 100%;">
        <!-- Host: Start Game button -->
        <button id="btn-start" class="tc-btn tc-btn-green tc-btn-full" onclick="startGame()" style="display: none;">
            <ion-icon name="play-circle-outline" style="font-size: 1.2rem;"></ion-icon>
            เริ่มเล่นเกม 🚀
        </button>
        <!-- Guest: Ready button -->
        <button id="btn-ready" class="tc-btn tc-btn-purple tc-btn-full" onclick="toggleReady()" style="display: none;">
            <ion-icon name="checkmark-done-circle-outline" style="font-size: 1.2rem;"></ion-icon>
            กดพร้อม 👍
        </button>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     COUNTDOWN OVERLAY
════════════════════════════════════════════════ -->
<div class="tc-countdown" id="screen-countdown">
    <div class="tc-countdown-num" id="countdown-num">3</div>
</div>

<!-- ═══════════════════════════════════════════════
     PLAY SCREEN (แสดงคำต้องห้ามขณะเล่น)
════════════════════════════════════════════════ -->
<div class="tc-play-screen" id="screen-play">
    <!-- HUD Top -->
    <div class="tc-play-hud">
        <div class="tc-hud-left">
            <button class="tc-hud-exit" onclick="exitPlay()">
                <ion-icon name="arrow-back-outline"></ion-icon> ออก
            </button>
        </div>
        <!-- Font Size Buttons -->
        <div class="tc-zoom-grp">
            <button class="tc-zoom-btn" id="zoom-100" onclick="setZoom(100)">100</button>
            <button class="tc-zoom-btn active" id="zoom-200" onclick="setZoom(200)">200</button>
            <button class="tc-zoom-btn" id="zoom-300" onclick="setZoom(300)">300</button>
        </div>
        <!-- Timer -->
        <div class="tc-hud-timer" id="play-timer">
            <ion-icon name="time-outline"></ion-icon>
            <span id="timer-val">--</span>
        </div>
    </div>

    <!-- Word Card -->
    <div class="tc-word-area">
        <div class="tc-word-card" id="play-card">
            <div class="tc-word-cat" id="play-cat">คำต้องห้าม</div>
            <div class="tc-word-text" id="play-word">กำลังโหลด...</div>
            <div style="font-size: 0.72rem; color: #555; margin-top: 16px;">แตะ 3 ครั้งเร็วๆ เพื่อออก</div>
        </div>
    </div>

    <!-- Action Bar -->
    <div class="tc-action-bar">
        <button class="tc-action-pass" id="btn-pass" onclick="triggerCaught()">
            <ion-icon name="alert-circle-outline" style="font-size: 1.2rem;"></ion-icon>
            โดนจับ! 🔴
        </button>
        <button class="tc-action-correct" id="btn-next" onclick="triggerNext()">
            <ion-icon name="arrow-forward-circle-outline" style="font-size: 1.2rem;"></ion-icon>
            คนถัดไป 🟢
        </button>
    </div>
</div>

<!-- ═══════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════ -->
<script>
// ═══════════════════════════════════════════════
// SESSION & CONSTANTS
// ═══════════════════════════════════════════════
const MY_NAME   = <?= json_encode($displayName) ?>;
const MY_AVATAR = <?= json_encode('../../assets/avatar/' . ($_SESSION['avatar_img'] ?? 'dog.png')) ?>;
const MY_USER   = <?= json_encode($username) ?>;

// Forbidden words pool
const WORDS = ["ส้มตำ","หมูกระทะ","ชาบู","ห้องน้ำ","โรงเรียน","คอมพิวเตอร์","โทรศัพท์","การนอน","ขอบคุณ","ขอโทษ","สวัสดี","รถยนต์","เครื่องบิน","ฟุตบอล","แมว","ทะเล","พัดลม","ตู้เย็น","แว่นตา","กระดาษ","กาแฟ","น้ำแข็ง","รองเท้า","หมวก","นาฬิกา","ดินสอ","กระเป๋า","แปรงสีฟัน","ช้าง","สิงโต","ฝน","หิมะ","ดวงอาทิตย์","ดวงจันทร์","ดาว","ภูเขา","แม่น้ำ","ทะเลสาบ","ป่า","ดอกไม้","ต้นไม้","ผีเสื้อ","นกแก้ว","ปลา","งู","กระต่าย","หมี","ลิง","ยีราฟ","เสือ"];

// State
let myRoom     = null;
let myIsHost   = false;
let myReady    = false;
let myRole     = 'spec';
let gameMode   = 'solo';
let gameTime   = 120;
let wordIdx    = 0;
let wordList   = [];
let timerSecs  = 120;
let timerInt   = null;
let lobbyInt   = null;
let tapCount   = 0;
let lastTap    = 0;
let wordZoom   = parseInt(localStorage.getItem('taboo_zoom') || '200');

// ═══════════════════════════════════════════════
// AUDIO
// ═══════════════════════════════════════════════
let audioCtx = null;
function initAudio() { if (!audioCtx) audioCtx = new (window.AudioContext||window.webkitAudioContext)(); if (audioCtx.state==='suspended') audioCtx.resume(); }
function playTone(freq, type='sine', dur=0.1, vol=0.06) {
    try { initAudio(); const o=audioCtx.createOscillator(), g=audioCtx.createGain(); o.type=type; o.frequency.setValueAtTime(freq, audioCtx.currentTime); g.gain.setValueAtTime(vol, audioCtx.currentTime); g.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime+dur); o.connect(g); g.connect(audioCtx.destination); o.start(); o.stop(audioCtx.currentTime+dur); } catch(e){}
}
function sndOk()   { playTone(523,  'sine', 0.08); setTimeout(()=>playTone(659,'sine',0.12), 60); }
function sndFail() { playTone(220, 'sawtooth', 0.2, 0.05); }
function sndTick() { playTone(1000,'triangle',0.05,0.03); }

// ═══════════════════════════════════════════════
// THEME
// ═══════════════════════════════════════════════
function toggleTheme() {
    document.body.classList.toggle('theme-cyber');
    localStorage.setItem('taboo-theme', document.body.classList.contains('theme-cyber') ? 'cyber' : 'dark');
    playTone(document.body.classList.contains('theme-cyber') ? 880 : 660, 'sine', 0.12);
}

// ═══════════════════════════════════════════════
// ROOM — Create / Join / Exit
// ═══════════════════════════════════════════════
function createRoom() {
    initAudio();
    const fd = new FormData();
    fd.append('player_name', MY_NAME);
    fd.append('avatar_icon', MY_AVATAR);
    fetch('api_room.php?action=create', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') {
                myRoom   = d.room_code;
                myIsHost = true;
                myReady  = true;
                showLobby();
                sndOk();
            } else { alert(d.message || 'ไม่สามารถสร้างห้องได้'); }
        }).catch(() => alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์'));
}

function joinRoom() {
    initAudio();
    const code = document.getElementById('join-code').value.trim();
    if (!code || code.length !== 4) { alert('กรุณากรอกรหัสห้อง 4 หลัก'); return; }
    const fd = new FormData();
    fd.append('player_name', MY_NAME);
    fd.append('avatar_icon', MY_AVATAR);
    fd.append('room_code', code);
    fetch('api_room.php?action=join', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') {
                myRoom   = code;
                myIsHost = false;
                myReady  = false;
                showLobby();
                sndOk();
            } else { alert(d.message || 'รหัสห้องไม่ถูกต้อง'); }
        }).catch(() => alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์'));
}

function quickPlay() {
    // Quick play: auto-generate room code prompt or show info
    const code = prompt('กรอกรหัสห้องที่ต้องการเข้าร่วม (4 หลัก):');
    if (code) {
        document.getElementById('join-code').value = code.trim();
        joinRoom();
    }
}

function exitRoom() {
    if (!myRoom) { showEntry(); return; }
    stopLobbyPoll();
    const fd = new FormData();
    fd.append('room_code', myRoom);
    fd.append('player_name', MY_NAME);
    fetch('api_room.php?action=exit', { method: 'POST', body: fd }).finally(() => {
        myRoom = null; myIsHost = false; myReady = false;
        showEntry();
    });
}

// ═══════════════════════════════════════════════
// LOBBY — Settings
// ═══════════════════════════════════════════════
function setMode(m) {
    gameMode = m;
    ['solo','3v3','6v6'].forEach(x => {
        const btn = document.getElementById('mode-' + x);
        if (btn) btn.classList.toggle('active', x === m);
    });
    playTone(440,'sine',0.06);
}

function setTime(s) {
    gameTime = s;
    timerSecs = s;
    const map = {120:'2m', 300:'5m', 600:'10m'};
    Object.entries(map).forEach(([sec, id]) => {
        const btn = document.getElementById('time-' + id);
        if (btn) btn.classList.toggle('active', parseInt(sec) === s);
    });
    playTone(440,'sine',0.06);
}

function setRole(r) {
    myRole = r;
    const classes = { a: 'active-a', b: 'active-b', spec: 'active-spec', gm: 'active-gm' };
    ['a','b','spec','gm'].forEach(x => {
        const btn = document.getElementById('role-' + x);
        if (!btn) return;
        btn.className = 'tc-role-btn';
        if (x === r) btn.classList.add(classes[x]);
    });
    // Highlight default already set for spec and gm
    playTone(440,'sine',0.06);
}

// ═══════════════════════════════════════════════
// LOBBY DISPLAY
// ═══════════════════════════════════════════════
function showEntry() {
    document.getElementById('screen-entry').classList.add('active');
    document.getElementById('screen-lobby').classList.remove('active');
}

function showLobby() {
    document.getElementById('screen-entry').classList.remove('active');
    document.getElementById('screen-lobby').classList.add('active');
    document.getElementById('room-code-display').textContent = myRoom;
    document.getElementById('host-crown').style.display = myIsHost ? 'inline' : 'none';
    // Show correct button
    document.getElementById('btn-start').style.display = myIsHost ? 'block' : 'none';
    document.getElementById('btn-ready').style.display = myIsHost ? 'none'  : 'block';
    // Add self to players list immediately
    renderPlayers([{ player_name: MY_NAME, avatar_icon: MY_AVATAR, is_host: myIsHost ? 1 : 0, is_ready: myIsHost ? 1 : 0 }]);
    startLobbyPoll();
}

function renderPlayers(players) {
    const list = document.getElementById('players-list');
    list.innerHTML = players.map(p => {
        const isUrl = p.avatar_icon && (p.avatar_icon.startsWith('http') || p.avatar_icon.startsWith('../../') || p.avatar_icon.startsWith('/'));
        const avatarHtml = isUrl
            ? `<img src="${p.avatar_icon}" class="tc-player-avatar" onerror="this.src='../../assets/avatar/dog.png'">`
            : `<div style="width:36px;height:36px;border-radius:50%;background:rgba(6,182,212,0.12);display:flex;align-items:center;justify-content:center;"><ion-icon name="${p.avatar_icon}" style="color:var(--tc-cyan);font-size:1rem;"></ion-icon></div>`;
        const badge = p.is_host == 1
            ? `<span class="tc-player-badge badge-host">👑 Host</span>`
            : p.is_ready == 1
                ? `<span class="tc-player-badge badge-ready">✅ พร้อม</span>`
                : `<span class="tc-player-badge badge-wait">⏳ รอ...</span>`;
        return `<div class="tc-player-row">${avatarHtml}<span class="tc-player-name">${p.player_name}</span>${badge}</div>`;
    }).join('');

    // Update start btn state for host
    if (myIsHost) {
        const nonHost = players.filter(p => !p.is_host);
        const allReady = nonHost.length > 0 && nonHost.every(p => p.is_ready == 1);
        const btn = document.getElementById('btn-start');
        if (btn) {
            btn.disabled = !allReady;
            btn.style.opacity = allReady ? '1' : '0.5';
            btn.innerHTML = allReady
                ? '<ion-icon name="play-circle-outline" style="font-size:1.2rem;"></ion-icon> เริ่มเล่นเกม 🚀'
                : `<ion-icon name="hourglass-outline" style="font-size:1.2rem;"></ion-icon> รอผู้เล่นพร้อม (${players.filter(p=>p.is_ready==1).length}/${players.length})`;
        }
    }
}

// ═══════════════════════════════════════════════
// LOBBY POLLING
// ═══════════════════════════════════════════════
function startLobbyPoll() {
    stopLobbyPoll();
    lobbyInt = setInterval(() => {
        if (!myRoom) return;
        fetch(`api_room.php?action=poll_taboo&room_code=${myRoom}`)
            .then(r => r.json())
            .then(d => {
                if (d.status !== 'success') { stopLobbyPoll(); showEntry(); return; }
                renderPlayers(d.players || []);
                if (d.game_status === 'playing') {
                    stopLobbyPoll();
                    startCountdown();
                }
            }).catch(() => {});
    }, 1500);
}

function stopLobbyPoll() {
    if (lobbyInt) { clearInterval(lobbyInt); lobbyInt = null; }
}

// ═══════════════════════════════════════════════
// READY
// ═══════════════════════════════════════════════
function toggleReady() {
    initAudio();
    const fd = new FormData();
    fd.append('room_code', myRoom);
    fd.append('player_name', MY_NAME);
    fetch('api_room.php?action=ready', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(d => {
            if (d.status === 'success') {
                myReady = !myReady;
                const btn = document.getElementById('btn-ready');
                if (btn) {
                    btn.style.background = myReady ? 'linear-gradient(135deg,#16a34a,#22c55e)' : '';
                    btn.style.color = myReady ? '#000' : '';
                    btn.innerHTML = myReady
                        ? '<ion-icon name="checkmark-done-circle-outline" style="font-size:1.2rem;"></ion-icon> พร้อมแล้ว ✅'
                        : '<ion-icon name="checkmark-done-circle-outline" style="font-size:1.2rem;"></ion-icon> กดพร้อม 👍';
                }
                sndOk();
            }
        });
}

// ═══════════════════════════════════════════════
// START GAME (Host)
// ═══════════════════════════════════════════════
function startGame() {
    initAudio();
    if (!myRoom) return;
    // Shuffle words
    wordList = [...WORDS].sort(() => Math.random() - 0.5);
    wordIdx = 0;

    // Fetch players and assign words
    fetch(`api_room.php?action=players&room_code=${myRoom}`)
        .then(r => r.json())
        .then(d => {
            if (d.status !== 'success') { alert('เกิดข้อผิดพลาด'); return; }
            const players = d.players;
            if (players.length < 2) { alert('ต้องมีอย่างน้อย 2 คน'); return; }
            const assignments = players.map((p, i) => ({ player_name: p.player_name, word: wordList[i % wordList.length] }));
            const fd = new FormData();
            fd.append('room_code', myRoom);
            fd.append('assignments', JSON.stringify(assignments));
            return fetch('api_room.php?action=start_taboo', { method: 'POST', body: fd });
        })
        .then(r => r ? r.json() : null)
        .then(d => {
            if (d && d.status === 'success') { startCountdown(); }
            else if (d) alert('ไม่สามารถเริ่มเกมได้');
        });
}

// ═══════════════════════════════════════════════
// COUNTDOWN → PLAY
// ═══════════════════════════════════════════════
function startCountdown() {
    stopLobbyPoll();
    const overlay = document.getElementById('screen-countdown');
    const numEl   = document.getElementById('countdown-num');
    let count = 3;
    overlay.classList.add('active');
    numEl.textContent = count;
    const cd = setInterval(() => {
        count--;
        if (count <= 0) {
            clearInterval(cd);
            overlay.classList.remove('active');
            showPlayScreen();
        } else {
            numEl.textContent = count;
            numEl.style.animation = 'none';
            setTimeout(() => numEl.style.animation = '', 10);
            sndTick();
        }
    }, 1000);
}

function showPlayScreen() {
    document.getElementById('screen-lobby').classList.remove('active');
    document.getElementById('screen-entry').classList.remove('active');
    const ps = document.getElementById('screen-play');
    ps.classList.add('active');

    // Shuffle words and load first
    wordList = [...WORDS].sort(() => Math.random() - 0.5);
    wordIdx = 0;
    loadWord();
    setZoom(wordZoom);

    // Start timer
    timerSecs = gameTime;
    updateTimerDisplay();
    if (timerInt) clearInterval(timerInt);
    timerInt = setInterval(() => {
        timerSecs--;
        updateTimerDisplay();
        if (timerSecs <= 10 && timerSecs > 5) { sndTick(); document.getElementById('play-timer').classList.add('urgent'); }
        if (timerSecs <= 5 && timerSecs > 0)  { sndFail(); }
        if (timerSecs <= 0) { clearInterval(timerInt); endGame(); }
    }, 1000);

    // Triple-tap to exit
    ps.addEventListener('click', handleTripleTap);
}

function updateTimerDisplay() {
    const m = Math.floor(timerSecs / 60);
    const s = timerSecs % 60;
    document.getElementById('timer-val').textContent = m > 0 ? `${m}:${s.toString().padStart(2,'0')}` : `${timerSecs}s`;
}

// ═══════════════════════════════════════════════
// WORD DISPLAY
// ═══════════════════════════════════════════════
function loadWord() {
    if (wordIdx >= wordList.length) {
        wordList = [...WORDS].sort(() => Math.random() - 0.5);
        wordIdx = 0;
    }
    const word = wordList[wordIdx];
    document.getElementById('play-word').textContent = word;
    document.getElementById('play-cat').textContent = 'คำต้องห้าม 🚫';
    const card = document.getElementById('play-card');
    card.className = 'tc-word-card';
}

function triggerCaught() {
    initAudio();
    sndFail();
    const card = document.getElementById('play-card');
    card.classList.add('state-caught');
    document.getElementById('play-word').textContent = 'โดนจับแล้ว! 🔴';
    wordIdx++;
    setTimeout(() => { loadWord(); }, 1000);
}

function triggerNext() {
    initAudio();
    sndOk();
    const card = document.getElementById('play-card');
    card.classList.add('state-correct');
    document.getElementById('play-word').textContent = 'รอดตัว! 🟢';
    wordIdx++;
    setTimeout(() => { loadWord(); }, 800);
}

function endGame() {
    if (timerInt) { clearInterval(timerInt); timerInt = null; }
    document.getElementById('play-word').textContent = 'หมดเวลา! ⏰';
    document.getElementById('play-cat').textContent  = 'เกมจบแล้ว';
    document.getElementById('btn-pass').style.display = 'none';
    document.getElementById('btn-next').innerHTML = '<ion-icon name="home-outline"></ion-icon> กลับหน้าหลัก';
    document.getElementById('btn-next').onclick = exitPlay;
    playTone(440,'sine',0.4,0.06);
    setTimeout(()=>playTone(370,'sine',0.4,0.06),200);
}

function exitPlay() {
    if (timerInt) { clearInterval(timerInt); timerInt = null; }
    stopLobbyPoll();
    myRoom = null; myIsHost = false; myReady = false;
    document.getElementById('screen-play').classList.remove('active');
    document.getElementById('btn-pass').style.display = '';
    document.getElementById('btn-next').innerHTML = '<ion-icon name="arrow-forward-circle-outline" style="font-size:1.2rem;"></ion-icon> คนถัดไป 🟢';
    document.getElementById('btn-next').onclick = triggerNext;
    document.getElementById('play-timer').classList.remove('urgent');
    showEntry();
}

// ═══════════════════════════════════════════════
// FONT ZOOM 100 / 200 / 300
// ═══════════════════════════════════════════════
function setZoom(size) {
    wordZoom = size;
    localStorage.setItem('taboo_zoom', size);
    [100, 200, 300].forEach(s => {
        const btn = document.getElementById('zoom-' + s);
        if (btn) btn.classList.toggle('active', s === size);
    });
    const el = document.getElementById('play-word');
    if (el) {
        el.style.fontSize = size + 'px';
        el.style.lineHeight = '1.1';
    }
}

// ═══════════════════════════════════════════════
// TRIPLE TAP TO EXIT
// ═══════════════════════════════════════════════
function handleTripleTap(e) {
    if (e.target.tagName === 'BUTTON' || e.target.closest('button')) return;
    const now = Date.now();
    if (now - lastTap < 700) {
        tapCount++;
        if (tapCount >= 2) { tapCount = 0; exitPlay(); }
    } else {
        tapCount = 1;
    }
    lastTap = now;
}

// ═══════════════════════════════════════════════
// INIT
// ═══════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('taboo-theme');
    if (saved === 'cyber' || !saved) document.body.classList.add('theme-cyber');
    else document.body.classList.remove('theme-cyber');

    // Apply saved zoom
    setZoom(wordZoom);
    
    // Ping to keep session alive
    setInterval(() => { fetch('../../api_ping.php').catch(() => {}); }, 15000);
});
</script>

</body>
</html>
