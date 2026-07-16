<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../index.php");
    exit();
}
require_once dirname(__DIR__, 2) . '/db.php';
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
$avatarSrc   = '../../assets/avatar/' . htmlspecialchars($_SESSION['avatar_img'] ?? 'dog.png');
$username    = htmlspecialchars($_SESSION['username'] ?? '');
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
<!-- Layout Manager: adds .pc-mode or .mobile-mode to <body> -->
<script src="../../assets/js/layout_manager.js?v=<?=time();?>"></script>
<style>
/* ══════════════════════════════════════════
   DESIGN TOKENS
══════════════════════════════════════════ */
:root {
    --bg:      #0d0d14;
    --card:    #16161f;
    --border:  rgba(255,255,255,0.09);
    --purple:  #a855f7;
    --purple-d:rgba(168,85,247,0.18);
    --pink:    #ec4899;
    --pink-d:  rgba(236,72,153,0.18);
    --cyan:    #06b6d4;
    --cyan-d:  rgba(6,182,212,0.18);
    --green:   #22c55e;
    --green-d: rgba(34,197,94,0.18);
    --red:     #ef4444;
    --red-d:   rgba(239,68,68,0.18);
    --gold:    #f59e0b;
    --text:    #fff;
    --muted:   #888;
    --font:    'Outfit', sans-serif;
}
*{box-sizing:border-box;margin:0;padding:0;}
body{
    background:var(--bg);color:var(--text);font-family:var(--font);
    min-height:100vh;overflow-x:hidden;
}

/* ══════════════════════════════════════════
   HEADER
══════════════════════════════════════════ */
.tc-header{
    position:fixed;top:0;left:0;right:0;
    display:flex;align-items:center;justify-content:space-between;
    padding:14px 20px;
    background:rgba(13,13,20,0.95);backdrop-filter:blur(12px);
    border-bottom:1px solid var(--border);z-index:300;
}
.tc-back{display:flex;align-items:center;gap:6px;color:#aaa;text-decoration:none;font-size:0.85rem;font-weight:600;}
.tc-back:hover{color:#fff;}
.tc-logo{font-size:1rem;font-weight:900;letter-spacing:2px;
    background:linear-gradient(90deg,var(--purple),var(--pink));
    -webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.tc-hdr-right{display:flex;align-items:center;gap:8px;}
.tc-icon-btn{width:34px;height:34px;border-radius:50%;background:rgba(255,255,255,0.07);
    border:1px solid var(--border);display:flex;align-items:center;justify-content:center;
    color:#aaa;cursor:pointer;transition:.15s;}
.tc-icon-btn:hover{color:#fff;background:rgba(255,255,255,0.12);}

/* ══════════════════════════════════════════
   SHARED COMPONENTS
══════════════════════════════════════════ */
.tc-btn{display:flex;align-items:center;justify-content:center;gap:6px;
    padding:13px 16px;border-radius:14px;font-weight:800;font-size:0.85rem;
    cursor:pointer;border:none;transition:.15s;text-align:center;}
.tc-btn:active{transform:scale(.96);}
.btn-purple{background:linear-gradient(135deg,#7c3aed,#a855f7);color:#fff;}
.btn-green {background:linear-gradient(135deg,#16a34a,#22c55e);color:#000;}
.btn-red   {background:linear-gradient(135deg,#dc2626,#ef4444);color:#fff;}
.btn-gray  {background:rgba(255,255,255,0.08);border:1px solid var(--border);color:#fff;}
.btn-pink  {background:linear-gradient(135deg,#be185d,#ec4899);color:#fff;}
.btn-full  {width:100%;border-radius:14px;padding:15px;font-size:1rem;}
.btn-sm    {padding:8px 14px;font-size:0.78rem;border-radius:10px;}

.tc-input{width:100%;padding:13px 14px;border-radius:14px;background:var(--card);
    border:1px solid var(--border);color:#fff;font-size:1rem;font-weight:700;
    text-align:center;font-family:'Chakra Petch',monospace;letter-spacing:4px;}
.tc-input:focus{outline:none;border-color:var(--purple);}
.tc-input::placeholder{letter-spacing:1px;font-size:.8rem;font-weight:400;color:#555;}

.tc-seg{display:flex;background:var(--card);border:1px solid var(--border);
    border-radius:12px;overflow:hidden;width:100%;margin-bottom:12px;}
.tc-seg-btn{flex:1;padding:10px 4px;font-size:.8rem;font-weight:700;color:#777;
    background:none;border:none;cursor:pointer;transition:.15s;}
.tc-seg-btn.active{background:var(--purple);color:#fff;border-radius:10px;}

.tc-section-label{font-size:.78rem;font-weight:700;color:#666;
    text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;width:100%;}

.roles-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;width:100%;margin-bottom:12px;}
.role-btn{padding:12px 8px;border-radius:12px;font-size:.8rem;font-weight:700;
    background:var(--card);border:1.5px solid var(--border);
    color:#888;cursor:pointer;transition:.15s;text-align:center;}
.role-btn.ra{border-color:var(--cyan);  background:var(--cyan-d);  color:var(--cyan);}
.role-btn.rb{border-color:var(--pink);  background:var(--pink-d);  color:var(--pink);}
.role-btn.rs{border-color:var(--green); background:var(--green-d); color:var(--green);}
.role-btn.rg{border-color:var(--red);   background:var(--red-d);   color:var(--red);}

.profile-row{display:flex;align-items:center;gap:12px;width:100%;
    padding:14px 16px;border-radius:16px;background:var(--card);
    border:1px solid var(--border);margin-bottom:16px;}
.profile-row img{width:48px;height:48px;border-radius:50%;object-fit:cover;
    border:2px solid var(--cyan);flex-shrink:0;}
.pname{font-weight:800;font-size:1rem;color:#fff;}
.puser{font-size:.75rem;color:var(--cyan);margin-top:1px;}

.players-box{width:100%;border-radius:14px;background:var(--card);
    border:1px solid var(--border);overflow:hidden;margin-bottom:12px;}
.players-title{padding:10px 14px;font-size:.78rem;font-weight:700;color:#666;
    display:flex;align-items:center;gap:6px;border-bottom:1px solid var(--border);}
.player-row{display:flex;align-items:center;gap:10px;padding:10px 14px;
    border-bottom:1px solid rgba(255,255,255,0.04);}
.player-row:last-child{border-bottom:none;}
.player-avatar{width:36px;height:36px;border-radius:50%;object-fit:cover;flex-shrink:0;}
.player-name{font-weight:700;font-size:.85rem;color:#fff;flex:1;}
.badge{padding:3px 8px;border-radius:20px;font-size:.68rem;font-weight:800;}
.badge-host{background:rgba(245,158,11,.15);color:var(--gold);border:1px solid rgba(245,158,11,.3);}
.badge-ready{background:rgba(34,197,94,.15);color:var(--green);border:1px solid rgba(34,197,94,.3);}
.badge-wait{background:rgba(255,159,10,.12);color:#ff9f0a;border:1px solid rgba(255,159,10,.25);}

.room-header{display:flex;align-items:center;justify-content:space-between;
    width:100%;padding:14px 16px;border-radius:16px;background:var(--card);
    border:1px solid var(--border);margin-bottom:12px;}
.room-code{font-size:1.1rem;font-weight:900;color:#fff;display:flex;align-items:center;gap:6px;}
.room-code span{font-family:'Chakra Petch',monospace;color:var(--gold);font-size:1.3rem;}

/* ══════════════════════════════════════════
   PLAY SCREEN (shared)
══════════════════════════════════════════ */
.play-hud{width:100%;display:flex;align-items:center;justify-content:space-between;
    padding:12px 16px;background:rgba(13,13,20,.95);backdrop-filter:blur(12px);
    border-bottom:1px solid var(--border);flex-shrink:0;}
.zoom-grp{display:flex;gap:4px;}
.zoom-btn{padding:6px 10px;border-radius:8px;font-size:.75rem;font-weight:800;
    border:1px solid var(--border);background:rgba(255,255,255,.05);color:#fff;cursor:pointer;transition:.15s;}
.zoom-btn.active{border-color:var(--purple);background:var(--purple-d);}
.hud-timer{font-size:.85rem;font-weight:700;color:#aaa;display:flex;align-items:center;gap:4px;}
.hud-timer.urgent{color:var(--red);animation:pulse .5s infinite;}
@keyframes pulse{0%,100%{opacity:1;}50%{opacity:.5;}}
.hud-exit{padding:8px 14px;border-radius:10px;background:rgba(255,255,255,.07);
    border:1px solid var(--border);color:#fff;font-size:.8rem;font-weight:700;cursor:pointer;
    display:flex;align-items:center;gap:4px;}

.word-card{border-radius:24px;background:var(--card);border:1.5px solid var(--border);
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    padding:32px 20px;text-align:center;transition:.3s;width:100%;}
.word-card.st-correct{border-color:var(--green);background:rgba(34,197,94,.07);}
.word-card.st-caught {border-color:var(--red);  background:rgba(239,68,68,.07);}
.word-cat{font-size:.72rem;font-weight:700;color:var(--purple);letter-spacing:2px;
    text-transform:uppercase;margin-bottom:16px;}
.word-text{font-size:200px;font-weight:900;color:#fff;line-height:1.1;
    word-break:break-word;transition:font-size .2s;}

.action-bar{width:100%;display:flex;gap:12px;padding:16px;flex-shrink:0;}
.act-pass   {flex:1;padding:18px;border-radius:16px;font-size:.9rem;font-weight:800;
    background:rgba(239,68,68,.12);border:1.5px solid rgba(239,68,68,.35);
    color:var(--red);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;}
.act-correct{flex:1;padding:18px;border-radius:16px;font-size:.9rem;font-weight:800;
    background:rgba(34,197,94,.12);border:1.5px solid rgba(34,197,94,.35);
    color:var(--green);cursor:pointer;display:flex;align-items:center;justify-content:center;gap:6px;}

/* Countdown */
.tc-countdown{position:fixed;inset:0;z-index:400;display:none;
    align-items:center;justify-content:center;
    background:rgba(0,0,0,.92);backdrop-filter:blur(8px);}
.tc-countdown.active{display:flex;}
.cd-num{font-size:clamp(6rem,30vw,12rem);font-weight:900;color:var(--purple);
    animation:cdIn .4s ease;}
@keyframes cdIn{from{transform:scale(1.4);opacity:0;}to{transform:scale(1);opacity:1;}}


/* ══════════════════════════════════════════
   MOBILE LAYOUT  (.mobile-mode)
══════════════════════════════════════════ */
body.mobile-mode{display:block;}
body.mobile-mode .tc-header{left:0;right:0;}

/* Screens */
body.mobile-mode .m-screen{
    display:none;flex-direction:column;align-items:center;
    padding:76px 16px 24px;min-height:100vh;
    width:100%;max-width:480px;margin:0 auto;
}
body.mobile-mode .m-screen.active{display:flex;}

/* Play screen full-fixed on mobile */
body.mobile-mode .play-screen{
    display:none;position:fixed;inset:0;z-index:200;
    flex-direction:column;align-items:center;
    background:var(--bg);
}
body.mobile-mode .play-screen.active{display:flex;}
body.mobile-mode .word-area{flex:1;display:flex;align-items:center;justify-content:center;
    width:100%;padding:20px;}
body.mobile-mode .word-card{min-height:200px;}

/* PC: hide mobile screens */
body.pc-mode .m-screen,
body.pc-mode .play-screen{display:none !important;}


/* ══════════════════════════════════════════
   PC LAYOUT  (.pc-mode)
══════════════════════════════════════════ */
body.pc-mode{display:flex;justify-content:center;align-items:flex-start;}

/* Two-column wrapper */
body.pc-mode .pc-wrap{
    display:grid;
    grid-template-columns: 400px 1fr;
    gap:0;
    width:100%;max-width:1200px;
    min-height:100vh;
    padding-top:60px; /* header height */
}

/* Left sidebar */
body.pc-mode .pc-sidebar{
    border-right:1px solid var(--border);
    background:rgba(13,13,20,.5);
    padding:24px 20px;
    display:flex;flex-direction:column;gap:0;
    min-height:calc(100vh - 60px);
    overflow-y:auto;
}

/* Right: game / play area */
body.pc-mode .pc-main{
    display:flex;flex-direction:column;
    padding:24px 32px;
    overflow-y:auto;
    min-height:calc(100vh - 60px);
}

/* PC screens */
body.pc-mode .pc-screen{display:none;}
body.pc-mode .pc-screen.active{display:flex;flex-direction:column;gap:12px;width:100%;}

/* PC play area */
body.pc-mode .pc-play{
    display:none;flex-direction:column;height:calc(100vh - 60px);
}
body.pc-mode .pc-play.active{display:flex;}
body.pc-mode .pc-word-area{
    flex:1;display:flex;align-items:center;justify-content:center;
    padding:20px 0;
}
body.pc-mode .pc-word-area .word-card{
    max-width:700px;min-height:280px;padding:48px 40px;
}
body.pc-mode .pc-action-bar{
    display:flex;gap:16px;padding:16px 0;
}
body.pc-mode .pc-action-bar .act-pass,
body.pc-mode .pc-action-bar .act-correct{padding:22px;font-size:1rem;}

/* PC: hide mobile layers */
body.pc-mode .m-screen,
body.pc-mode .play-screen{display:none !important;}
</style>
</head>
<body>

<!-- ════════════════════════════════════════
     HEADER (shared)
════════════════════════════════════════ -->
<header class="tc-header">
    <a href="../../index.php" class="tc-back">
        <ion-icon name="chevron-back-outline" style="font-size:1.1rem;"></ion-icon>
        กลับหน้าหลัก
    </a>
    <div class="tc-logo">TABOO CHAMP</div>
    <div class="tc-hdr-right">
        <button class="tc-icon-btn" onclick="toggleTheme()" title="เปลี่ยนธีม">
            <ion-icon name="color-palette-outline" style="font-size:1.1rem;" id="theme-icon"></ion-icon>
        </button>
    </div>
</header>

<!-- COUNTDOWN (shared, shown on top of everything) -->
<div class="tc-countdown" id="screen-countdown">
    <div class="cd-num" id="cd-num">3</div>
</div>


<!-- ════════════════════════════════════════════════════════
     MOBILE LAYOUT
════════════════════════════════════════════════════════ -->

<!-- Mobile: Screen Entry -->
<div id="m-entry" class="m-screen active">
    <div style="text-align:center;padding:16px 0 20px;">
        <div style="font-size:3.2rem;">🚫</div>
        <div style="font-size:1.5rem;font-weight:900;color:#fff;margin-top:6px;">ปาร์ตี้คำต้องห้าม</div>
        <div style="font-size:.82rem;color:#888;margin-top:4px;line-height:1.5;">เกมแอบจับผิดคำต้องห้ามแบ่งทีม 3v3 / 6v6<br>หรือเล่นบทบาทผู้คุมเกม</div>
    </div>
    <div class="profile-row">
        <img src="<?= $avatarSrc ?>" alt="avatar" onerror="this.src='../../assets/avatar/dog.png'">
        <div><div class="pname"><?= $displayName ?></div><div class="puser">@<?= $username ?></div></div>
        <ion-icon name="checkmark-circle" style="color:var(--green);font-size:1.4rem;margin-left:auto;flex-shrink:0;"></ion-icon>
    </div>
    <button class="tc-btn btn-purple btn-full" onclick="createRoom()" style="margin-bottom:10px;">
        <ion-icon name="add-circle-outline"></ion-icon> สร้างห้อง
    </button>
    <div style="display:flex;gap:8px;width:100%;margin-bottom:20px;">
        <input type="tel" id="m-join-code" class="tc-input" maxlength="4" placeholder="รหัส 4 หลัก">
        <button class="tc-btn btn-green" onclick="joinRoom('m')" style="flex:0;padding:13px 18px;white-space:nowrap;">
            <ion-icon name="enter-outline"></ion-icon> เข้าร่วม
        </button>
    </div>
    <div class="tc-section-label">เล่นได้ทันที</div>
    <button class="tc-btn btn-pink btn-full" onclick="quickPlay()">
        <ion-icon name="flash-outline" style="font-size:1.1rem;"></ion-icon> เข้าร่วมห้องปาร์ตี้ 🚀
    </button>
</div>

<!-- Mobile: Screen Lobby -->
<div id="m-lobby" class="m-screen">
    <div class="room-header">
        <div class="room-code">ห้อง: <span id="m-room-code">----</span>
            <ion-icon name="crown-outline" id="m-host-crown" style="color:var(--gold);display:none;"></ion-icon>
        </div>
        <button class="tc-btn btn-red btn-sm" onclick="exitRoom()">
            <ion-icon name="exit-outline"></ion-icon> ออกห้อง
        </button>
    </div>
    <div class="tc-section-label">รูปแบบการเล่น:</div>
    <div class="tc-seg">
        <button class="tc-seg-btn active" onclick="setMode('solo')"  id="m-mode-solo">Solo</button>
        <button class="tc-seg-btn"        onclick="setMode('3v3')"   id="m-mode-3v3">ทีม 3v3</button>
        <button class="tc-seg-btn"        onclick="setMode('6v6')"   id="m-mode-6v6">ทีม 6v6</button>
    </div>
    <div class="tc-section-label">เวลาในการแข่ง:</div>
    <div class="tc-seg">
        <button class="tc-seg-btn active" onclick="setTime(120)" id="m-time-2m">2 นาที</button>
        <button class="tc-seg-btn"        onclick="setTime(300)" id="m-time-5m">5 นาที</button>
        <button class="tc-seg-btn"        onclick="setTime(600)" id="m-time-10m">10 นาที</button>
    </div>
    <div class="tc-section-label">เลือกบทบาทของคุณ:</div>
    <div class="roles-grid">
        <button class="role-btn"    id="m-role-a"    onclick="setRole('a')">ทีม A</button>
        <button class="role-btn"    id="m-role-b"    onclick="setRole('b')">ทีม B</button>
        <button class="role-btn rs" id="m-role-spec" onclick="setRole('spec')">👁️ ผู้ชม</button>
        <button class="role-btn rg" id="m-role-gm"   onclick="setRole('gm')">🎮 ผู้คุมเกม</button>
    </div>
    <div class="players-box">
        <div class="players-title"><ion-icon name="people-outline" style="color:var(--cyan);"></ion-icon> ผู้เข้าร่วมในห้อง:</div>
        <div id="m-players-list"></div>
    </div>
    <div style="width:100%;">
        <button id="m-btn-start" class="tc-btn btn-green btn-full" onclick="startGame()" style="display:none;">
            <ion-icon name="play-circle-outline" style="font-size:1.2rem;"></ion-icon> เริ่มเล่นเกม 🚀
        </button>
        <button id="m-btn-ready" class="tc-btn btn-purple btn-full" onclick="toggleReady()" style="display:none;">
            <ion-icon name="checkmark-done-circle-outline" style="font-size:1.2rem;"></ion-icon> กดพร้อม 👍
        </button>
    </div>
</div>

<!-- Mobile: Play Screen -->
<div class="play-screen" id="m-play">
    <div class="play-hud">
        <button class="hud-exit" onclick="exitPlay()">
            <ion-icon name="arrow-back-outline"></ion-icon> ออก
        </button>
        <div class="zoom-grp">
            <button class="zoom-btn"        id="m-zoom-100" onclick="setZoom(100)">100</button>
            <button class="zoom-btn active" id="m-zoom-200" onclick="setZoom(200)">200</button>
            <button class="zoom-btn"        id="m-zoom-300" onclick="setZoom(300)">300</button>
        </div>
        <div class="hud-timer" id="m-timer">
            <ion-icon name="time-outline"></ion-icon><span id="m-timer-val">--</span>
        </div>
    </div>
    <div class="word-area">
        <div class="word-card" id="m-card">
            <div class="word-cat" id="m-cat">คำต้องห้าม</div>
            <div class="word-text" id="m-word">กำลังโหลด...</div>
            <div style="font-size:.7rem;color:#444;margin-top:14px;">แตะ 3 ครั้งเร็วๆ เพื่อออก</div>
        </div>
    </div>
    <div class="action-bar">
        <button class="act-pass"    id="m-btn-pass"    onclick="triggerCaught()">
            <ion-icon name="alert-circle-outline" style="font-size:1.2rem;"></ion-icon> โดนจับ! 🔴
        </button>
        <button class="act-correct" id="m-btn-correct" onclick="triggerNext()">
            <ion-icon name="arrow-forward-circle-outline" style="font-size:1.2rem;"></ion-icon> คนถัดไป 🟢
        </button>
    </div>
</div>


<!-- ════════════════════════════════════════════════════════
     PC LAYOUT (Two-column)
════════════════════════════════════════════════════════ -->
<div class="pc-wrap">

    <!-- LEFT SIDEBAR -->
    <aside class="pc-sidebar">
        <!-- Profile -->
        <div class="profile-row" style="margin-bottom:20px;">
            <img src="<?= $avatarSrc ?>" alt="avatar" onerror="this.src='../../assets/avatar/dog.png'">
            <div><div class="pname"><?= $displayName ?></div><div class="puser">@<?= $username ?></div></div>
            <ion-icon name="checkmark-circle" style="color:var(--green);font-size:1.4rem;margin-left:auto;flex-shrink:0;"></ion-icon>
        </div>

        <!-- SIDEBAR: ENTRY state -->
        <div id="pc-sidebar-entry">
            <div style="font-size:.9rem;font-weight:800;color:#fff;margin-bottom:12px;">🚫 ปาร์ตี้คำต้องห้าม</div>
            <div style="font-size:.78rem;color:#666;margin-bottom:16px;line-height:1.6;">
                เกมแอบจับผิดคำต้องห้ามแบ่งทีม 3v3 / 6v6<br>หรือเล่นบทบาทผู้คุมเกม
            </div>
            <button class="tc-btn btn-purple btn-full" onclick="createRoom()" style="margin-bottom:10px;">
                <ion-icon name="add-circle-outline"></ion-icon> สร้างห้องใหม่
            </button>
            <div style="display:flex;gap:8px;width:100%;margin-bottom:8px;">
                <input type="tel" id="pc-join-code" class="tc-input" maxlength="4" placeholder="รหัส 4 หลัก">
                <button class="tc-btn btn-green" onclick="joinRoom('pc')" style="flex:0;padding:13px 16px;white-space:nowrap;">เข้าร่วม</button>
            </div>
            <button class="tc-btn btn-pink btn-full" onclick="quickPlay()">
                <ion-icon name="flash-outline"></ion-icon> Quick Join 🚀
            </button>
        </div>

        <!-- SIDEBAR: LOBBY state -->
        <div id="pc-sidebar-lobby" style="display:none;">
            <div class="room-header" style="margin-bottom:14px;">
                <div class="room-code">ห้อง: <span id="pc-room-code">----</span>
                    <ion-icon name="crown-outline" id="pc-host-crown" style="color:var(--gold);display:none;"></ion-icon>
                </div>
                <button class="tc-btn btn-red btn-sm" onclick="exitRoom()">
                    <ion-icon name="exit-outline"></ion-icon> ออก
                </button>
            </div>

            <div class="tc-section-label">รูปแบบการเล่น:</div>
            <div class="tc-seg">
                <button class="tc-seg-btn active" onclick="setMode('solo')"  id="pc-mode-solo">Solo</button>
                <button class="tc-seg-btn"        onclick="setMode('3v3')"   id="pc-mode-3v3">ทีม 3v3</button>
                <button class="tc-seg-btn"        onclick="setMode('6v6')"   id="pc-mode-6v6">ทีม 6v6</button>
            </div>

            <div class="tc-section-label">เวลาในการแข่ง:</div>
            <div class="tc-seg">
                <button class="tc-seg-btn active" onclick="setTime(120)" id="pc-time-2m">2 นาที</button>
                <button class="tc-seg-btn"        onclick="setTime(300)" id="pc-time-5m">5 นาที</button>
                <button class="tc-seg-btn"        onclick="setTime(600)" id="pc-time-10m">10 นาที</button>
            </div>

            <div class="tc-section-label">เลือกบทบาทของคุณ:</div>
            <div class="roles-grid">
                <button class="role-btn"    id="pc-role-a"    onclick="setRole('a')">ทีม A</button>
                <button class="role-btn"    id="pc-role-b"    onclick="setRole('b')">ทีม B</button>
                <button class="role-btn rs" id="pc-role-spec" onclick="setRole('spec')">👁️ ผู้ชม</button>
                <button class="role-btn rg" id="pc-role-gm"   onclick="setRole('gm')">🎮 ผู้คุมเกม</button>
            </div>

            <div class="players-box">
                <div class="players-title"><ion-icon name="people-outline" style="color:var(--cyan);"></ion-icon> ผู้เข้าร่วมในห้อง:</div>
                <div id="pc-players-list"></div>
            </div>

            <div id="pc-lobby-actions" style="width:100%;margin-top:4px;">
                <button id="pc-btn-start" class="tc-btn btn-green btn-full" onclick="startGame()" style="display:none;">
                    <ion-icon name="play-circle-outline" style="font-size:1.2rem;"></ion-icon> เริ่มเล่นเกม 🚀
                </button>
                <button id="pc-btn-ready" class="tc-btn btn-purple btn-full" onclick="toggleReady()" style="display:none;">
                    <ion-icon name="checkmark-done-circle-outline" style="font-size:1.2rem;"></ion-icon> กดพร้อม 👍
                </button>
            </div>
        </div>
    </aside>

    <!-- RIGHT: MAIN AREA -->
    <main class="pc-main">

        <!-- PC: Entry placeholder -->
        <div id="pc-main-entry" class="pc-screen active">
            <div style="text-align:center;padding:40px 0 20px;">
                <div style="font-size:5rem;margin-bottom:16px;">🚫</div>
                <div style="font-size:2rem;font-weight:900;background:linear-gradient(90deg,var(--purple),var(--pink));-webkit-background-clip:text;-webkit-text-fill-color:transparent;">TABOO CHAMP</div>
                <div style="font-size:.95rem;color:#666;margin-top:10px;line-height:1.7;">
                    สร้างห้องหรือเข้าร่วมห้องจากแผงซ้าย<br>
                    เมื่อเกมเริ่ม พื้นที่นี้จะแสดงคำต้องห้ามและปุ่มควบคุม
                </div>
            </div>
            <!-- Word categories info -->
            <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-top:24px;">
                <?php
                $cats = [
                    ['🏠','ชีวิตประจำวัน','ส้มตำ, รถยนต์, ห้องน้ำ...'],
                    ['💻','เทคโนโลยี','คอมพิวเตอร์, ไวไฟ, แอป...'],
                    ['🐾','สัตว์โลก','แมว, ช้าง, สิงโต...'],
                    ['🍕','ของกิน','หมูกระทะ, พิซซ่า, ชาบู...'],
                ];
                foreach ($cats as $c) {
                    echo "<div style='padding:16px;border-radius:14px;background:var(--card);border:1px solid var(--border);'>";
                    echo "<div style='font-size:1.8rem;margin-bottom:6px;'>{$c[0]}</div>";
                    echo "<div style='font-weight:800;font-size:.85rem;color:#fff;margin-bottom:4px;'>{$c[1]}</div>";
                    echo "<div style='font-size:.72rem;color:#555;'>{$c[2]}</div>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>

        <!-- PC: Play area -->
        <div id="pc-play" class="pc-play">
            <div class="play-hud" style="border-radius:14px;margin-bottom:12px;border:1px solid var(--border);">
                <button class="hud-exit" onclick="exitPlay()">
                    <ion-icon name="arrow-back-outline"></ion-icon> ออก / จบเกม
                </button>
                <div class="zoom-grp">
                    <button class="zoom-btn"        id="pc-zoom-100" onclick="setZoom(100)">100</button>
                    <button class="zoom-btn active" id="pc-zoom-200" onclick="setZoom(200)">200</button>
                    <button class="zoom-btn"        id="pc-zoom-300" onclick="setZoom(300)">300</button>
                </div>
                <div class="hud-timer" id="pc-timer">
                    <ion-icon name="time-outline"></ion-icon><span id="pc-timer-val">--</span>
                </div>
            </div>

            <div class="pc-word-area">
                <div class="word-card" id="pc-card">
                    <div class="word-cat" id="pc-cat">คำต้องห้าม</div>
                    <div class="word-text" id="pc-word">กำลังโหลด...</div>
                    <div style="font-size:.72rem;color:#444;margin-top:16px;">คลิกปุ่มด้านล่าง หรือ กด ← → บนคีย์บอร์ด</div>
                </div>
            </div>

            <div class="pc-action-bar">
                <button class="act-pass"    onclick="triggerCaught()">
                    <ion-icon name="alert-circle-outline" style="font-size:1.3rem;"></ion-icon> โดนจับ! 🔴
                </button>
                <button class="act-correct" onclick="triggerNext()">
                    <ion-icon name="arrow-forward-circle-outline" style="font-size:1.3rem;"></ion-icon> คนถัดไป 🟢
                </button>
            </div>
        </div>

    </main>
</div><!-- end pc-wrap -->


<!-- ════════════════════════════════════════════════════════
     JAVASCRIPT
════════════════════════════════════════════════════════ -->
<script>
// ─── Session ─────────────────────────────────
const MY_NAME   = <?= json_encode($displayName) ?>;
const MY_AVATAR = <?= json_encode('../../assets/avatar/' . ($_SESSION['avatar_img'] ?? 'dog.png')) ?>;
const MY_USER   = <?= json_encode($username) ?>;

// Word Pool
const WORDS = ["ส้มตำ","หมูกระทะ","ชาบู","ห้องน้ำ","โรงเรียน","คอมพิวเตอร์","โทรศัพท์","การนอน","ขอบคุณ","ขอโทษ","สวัสดี","รถยนต์","เครื่องบิน","ฟุตบอล","แมว","ทะเล","พัดลม","ตู้เย็น","แว่นตา","กระดาษ","กาแฟ","น้ำแข็ง","รองเท้า","หมวก","นาฬิกา","ดินสอ","กระเป๋า","แปรงสีฟัน","ช้าง","สิงโต","ฝน","หิมะ","ดวงอาทิตย์","ดวงจันทร์","ดาว","ภูเขา","แม่น้ำ","ทะเลสาบ","ป่า","ดอกไม้","ต้นไม้","ผีเสื้อ","นกแก้ว","ปลา","งู","กระต่าย","หมี","ลิง","ยีราฟ","เสือ"];

// ─── State ───────────────────────────────────
let myRoom    = null;
let myIsHost  = false;
let myReady   = false;
let myRole    = 'spec';
let gameMode  = 'solo';
let gameTime  = 120;
let wordIdx   = 0;
let wordList  = [];
let timerSecs = 120;
let timerInt  = null;
let lobbyInt  = null;
let wordZoom  = parseInt(localStorage.getItem('taboo_zoom') || '200');
let tapCount  = 0;
let lastTap   = 0;

// ─── Device detection ────────────────────────
function isPc() { return document.body.classList.contains('pc-mode'); }

// ─── Audio ───────────────────────────────────
let audioCtx = null;
function initAudio(){ if(!audioCtx) audioCtx=new(window.AudioContext||window.webkitAudioContext)(); if(audioCtx.state==='suspended') audioCtx.resume(); }
function playTone(f,t='sine',d=.1,v=.06){
    try{ initAudio(); const o=audioCtx.createOscillator(),g=audioCtx.createGain();
    o.type=t; o.frequency.setValueAtTime(f,audioCtx.currentTime);
    g.gain.setValueAtTime(v,audioCtx.currentTime);
    g.gain.exponentialRampToValueAtTime(.001,audioCtx.currentTime+d);
    o.connect(g); g.connect(audioCtx.destination); o.start(); o.stop(audioCtx.currentTime+d); }catch(e){}
}
function sndOk()  { playTone(523,'sine',.08); setTimeout(()=>playTone(659,'sine',.12),60); }
function sndFail(){ playTone(220,'sawtooth',.2,.05); }
function sndTick(){ playTone(1000,'triangle',.05,.03); }

// ─── Theme ───────────────────────────────────
function toggleTheme(){
    document.body.classList.toggle('theme-cyber');
    localStorage.setItem('taboo-theme', document.body.classList.contains('theme-cyber')?'cyber':'dark');
    playTone(document.body.classList.contains('theme-cyber')?880:660,'sine',.12);
}

// ─── Show/hide screens ───────────────────────
function showEntry(){
    if(isPc()){
        document.getElementById('pc-sidebar-entry').style.display='block';
        document.getElementById('pc-sidebar-lobby').style.display='none';
        document.getElementById('pc-main-entry').classList.add('active');
        document.getElementById('pc-play').classList.remove('active');
    } else {
        document.getElementById('m-entry').classList.add('active');
        document.getElementById('m-lobby').classList.remove('active');
        document.getElementById('m-play').classList.remove('active');
    }
}

function showLobbyUI(){
    document.getElementById(`${isPc()?'pc':'m'}-room-code`).textContent = myRoom;
    const crown = document.getElementById(`${isPc()?'pc':'m'}-host-crown`);
    if(crown) crown.style.display = myIsHost ? 'inline':'none';

    const startBtn = document.getElementById(`${isPc()?'pc':'m'}-btn-start`);
    const readyBtn = document.getElementById(`${isPc()?'pc':'m'}-btn-ready`);
    if(startBtn) startBtn.style.display = myIsHost ? 'block':'none';
    if(readyBtn) readyBtn.style.display = myIsHost ? 'none':'block';

    if(isPc()){
        document.getElementById('pc-sidebar-entry').style.display='none';
        document.getElementById('pc-sidebar-lobby').style.display='block';
        document.getElementById('pc-main-entry').classList.add('active');
        document.getElementById('pc-play').classList.remove('active');
    } else {
        document.getElementById('m-entry').classList.remove('active');
        document.getElementById('m-lobby').classList.add('active');
        document.getElementById('m-play').classList.remove('active');
    }
    renderPlayers([{player_name:MY_NAME,avatar_icon:MY_AVATAR,is_host:myIsHost?1:0,is_ready:myIsHost?1:0}]);
    startLobbyPoll();
}

function showPlayUI(){
    if(isPc()){
        document.getElementById('pc-main-entry').classList.remove('active');
        document.getElementById('pc-play').classList.add('active');
    } else {
        document.getElementById('m-lobby').classList.remove('active');
        document.getElementById('m-entry').classList.remove('active');
        document.getElementById('m-play').classList.add('active');
        document.getElementById('m-play').style.display='flex';
    }
}

// ─── Players render ──────────────────────────
function renderPlayers(players){
    const prefix = isPc() ? 'pc' : 'm';
    const list = document.getElementById(`${prefix}-players-list`);
    if(!list) return;
    list.innerHTML = players.map(p=>{
        const isUrl = p.avatar_icon&&(p.avatar_icon.startsWith('http')||p.avatar_icon.startsWith('../../')||p.avatar_icon.startsWith('/'));
        const av = isUrl
            ? `<img src="${p.avatar_icon}" class="player-avatar" onerror="this.src='../../assets/avatar/dog.png'">`
            : `<div style="width:36px;height:36px;border-radius:50%;background:rgba(6,182,212,.12);display:flex;align-items:center;justify-content:center;"><ion-icon name="${p.avatar_icon}" style="color:var(--cyan);font-size:1rem;"></ion-icon></div>`;
        const badge = p.is_host==1
            ? `<span class="badge badge-host">👑 Host</span>`
            : p.is_ready==1
                ? `<span class="badge badge-ready">✅ พร้อม</span>`
                : `<span class="badge badge-wait">⏳ รอ...</span>`;
        return `<div class="player-row">${av}<span class="player-name">${p.player_name}</span>${badge}</div>`;
    }).join('');

    // Update start btn
    if(myIsHost){
        const nonHost = players.filter(p=>!p.is_host);
        const allReady = nonHost.length>0 && nonHost.every(p=>p.is_ready==1);
        const btn = document.getElementById(`${prefix}-btn-start`);
        if(btn){
            btn.disabled = !allReady;
            btn.style.opacity = allReady?'1':'0.5';
            btn.innerHTML = allReady
                ? '<ion-icon name="play-circle-outline" style="font-size:1.2rem;"></ion-icon> เริ่มเล่นเกม 🚀'
                : `<ion-icon name="hourglass-outline" style="font-size:1.2rem;"></ion-icon> รอผู้เล่น (${players.filter(p=>p.is_ready==1).length}/${players.length})`;
        }
    }
}

// ─── Room Create / Join ───────────────────────
function createRoom(){
    initAudio();
    const fd=new FormData(); fd.append('player_name',MY_NAME); fd.append('avatar_icon',MY_AVATAR);
    fetch('api_room.php?action=create',{method:'POST',body:fd})
        .then(r=>r.json()).then(d=>{
            if(d.status==='success'){ myRoom=d.room_code; myIsHost=true; myReady=true; showLobbyUI(); sndOk(); }
            else alert(d.message||'ไม่สามารถสร้างห้องได้');
        }).catch(()=>alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์'));
}

function joinRoom(src){
    initAudio();
    const id = src==='pc' ? 'pc-join-code' : 'm-join-code';
    const code = document.getElementById(id).value.trim();
    if(!code||code.length!==4){ alert('กรุณากรอกรหัสห้อง 4 หลัก'); return; }
    const fd=new FormData(); fd.append('player_name',MY_NAME); fd.append('avatar_icon',MY_AVATAR); fd.append('room_code',code);
    fetch('api_room.php?action=join',{method:'POST',body:fd})
        .then(r=>r.json()).then(d=>{
            if(d.status==='success'){ myRoom=code; myIsHost=false; myReady=false; showLobbyUI(); sndOk(); }
            else alert(d.message||'รหัสห้องไม่ถูกต้อง');
        }).catch(()=>alert('ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์'));
}

function quickPlay(){
    const code=prompt('กรอกรหัสห้อง (4 หลัก):');
    if(!code) return;
    const idEl = isPc() ? 'pc-join-code' : 'm-join-code';
    document.getElementById(idEl).value = code.trim();
    joinRoom(isPc()?'pc':'m');
}

function exitRoom(){
    stopLobbyPoll();
    if(!myRoom){ showEntry(); return; }
    const fd=new FormData(); fd.append('room_code',myRoom); fd.append('player_name',MY_NAME);
    fetch('api_room.php?action=exit',{method:'POST',body:fd}).finally(()=>{ myRoom=null; myIsHost=false; myReady=false; showEntry(); });
}

// ─── Mode / Time / Role ───────────────────────
function setMode(m){
    gameMode=m;
    ['solo','3v3','6v6'].forEach(x=>{
        ['m','pc'].forEach(p=>{ const b=document.getElementById(`${p}-mode-${x}`); if(b) b.classList.toggle('active',x===m); });
    });
    playTone(440,'sine',.06);
}

function setTime(s){
    gameTime=s; timerSecs=s;
    const map={120:'2m',300:'5m',600:'10m'};
    Object.entries(map).forEach(([sec,id])=>{
        ['m','pc'].forEach(p=>{ const b=document.getElementById(`${p}-time-${id}`); if(b) b.classList.toggle('active',parseInt(sec)===s); });
    });
    playTone(440,'sine',.06);
}

function setRole(r){
    myRole=r;
    const cl={a:'ra',b:'rb',spec:'rs',gm:'rg'};
    ['a','b','spec','gm'].forEach(x=>{
        ['m','pc'].forEach(p=>{ const b=document.getElementById(`${p}-role-${x}`); if(!b) return; b.className='role-btn'; if(x===r) b.classList.add(cl[x]); });
    });
    playTone(440,'sine',.06);
}

// ─── Ready ───────────────────────────────────
function toggleReady(){
    initAudio();
    const fd=new FormData(); fd.append('room_code',myRoom); fd.append('player_name',MY_NAME);
    fetch('api_room.php?action=ready',{method:'POST',body:fd}).then(r=>r.json()).then(d=>{
        if(d.status==='success'){
            myReady=!myReady;
            ['m','pc'].forEach(p=>{
                const btn=document.getElementById(`${p}-btn-ready`);
                if(btn){
                    btn.style.background = myReady?'linear-gradient(135deg,#16a34a,#22c55e)':'';
                    btn.style.color = myReady?'#000':'';
                    btn.innerHTML = myReady
                        ? '<ion-icon name="checkmark-done-circle-outline" style="font-size:1.2rem;"></ion-icon> พร้อมแล้ว ✅'
                        : '<ion-icon name="checkmark-done-circle-outline" style="font-size:1.2rem;"></ion-icon> กดพร้อม 👍';
                }
            });
            sndOk();
        }
    });
}

// ─── Lobby Poll ───────────────────────────────
function startLobbyPoll(){
    stopLobbyPoll();
    lobbyInt=setInterval(()=>{
        if(!myRoom) return;
        fetch(`api_room.php?action=poll_taboo&room_code=${myRoom}`)
            .then(r=>r.json()).then(d=>{
                if(d.status!=='success'){ stopLobbyPoll(); showEntry(); return; }
                renderPlayers(d.players||[]);
                if(d.game_status==='playing'){ stopLobbyPoll(); startCountdown(); }
            }).catch(()=>{});
    },1500);
}
function stopLobbyPoll(){ if(lobbyInt){ clearInterval(lobbyInt); lobbyInt=null; } }

// ─── Start Game ───────────────────────────────
function startGame(){
    initAudio();
    if(!myRoom) return;
    wordList=[...WORDS].sort(()=>Math.random()-.5); wordIdx=0;
    fetch(`api_room.php?action=players&room_code=${myRoom}`)
        .then(r=>r.json()).then(d=>{
            if(d.status!=='success'){ alert('เกิดข้อผิดพลาด'); return; }
            if(d.players.length<2){ alert('ต้องมีอย่างน้อย 2 คน'); return; }
            const assignments=d.players.map((p,i)=>({player_name:p.player_name,word:wordList[i%wordList.length]}));
            const fd=new FormData(); fd.append('room_code',myRoom); fd.append('assignments',JSON.stringify(assignments));
            return fetch('api_room.php?action=start_taboo',{method:'POST',body:fd});
        }).then(r=>r?r.json():null).then(d=>{ if(d&&d.status==='success') startCountdown(); else if(d) alert('ไม่สามารถเริ่มเกมได้'); });
}

// ─── Countdown → Play ────────────────────────
function startCountdown(){
    stopLobbyPoll();
    const ov=document.getElementById('screen-countdown');
    const nl=document.getElementById('cd-num');
    let c=3; ov.classList.add('active'); nl.textContent=c;
    const cd=setInterval(()=>{
        c--; if(c<=0){ clearInterval(cd); ov.classList.remove('active'); beginPlay(); }
        else { nl.textContent=c; nl.style.animation='none'; setTimeout(()=>nl.style.animation='',10); sndTick(); }
    },1000);
}

function beginPlay(){
    wordList=[...WORDS].sort(()=>Math.random()-.5); wordIdx=0;
    showPlayUI();
    loadWord();
    setZoom(wordZoom);
    timerSecs=gameTime;
    updateTimer();
    if(timerInt) clearInterval(timerInt);
    timerInt=setInterval(()=>{
        timerSecs--;
        updateTimer();
        const tm = isPc() ? document.getElementById('pc-timer') : document.getElementById('m-timer');
        if(timerSecs<=10&&timerSecs>5){ sndTick(); if(tm) tm.classList.add('urgent'); }
        if(timerSecs<=5&&timerSecs>0){ sndFail(); }
        if(timerSecs<=0){ clearInterval(timerInt); endGame(); }
    },1000);

    // Keyboard shortcuts for PC
    document.removeEventListener('keydown', handleKey);
    document.addEventListener('keydown', handleKey);

    // Triple-tap to exit on mobile
    const ps=document.getElementById('m-play');
    if(ps){ ps.removeEventListener('click',handleTripleTap); ps.addEventListener('click',handleTripleTap); }
}

function handleKey(e){
    if(e.key==='ArrowRight'||e.key===' ') triggerNext();
    if(e.key==='ArrowLeft') triggerCaught();
}

function updateTimer(){
    const m=Math.floor(timerSecs/60), s=timerSecs%60;
    const val = m>0 ? `${m}:${s.toString().padStart(2,'0')}` : `${timerSecs}s`;
    ['m-timer-val','pc-timer-val'].forEach(id=>{ const el=document.getElementById(id); if(el) el.textContent=val; });
}

// ─── Word Load ────────────────────────────────
function loadWord(){
    if(wordIdx>=wordList.length){ wordList=[...WORDS].sort(()=>Math.random()-.5); wordIdx=0; }
    const w=wordList[wordIdx];
    ['m','pc'].forEach(p=>{
        const wEl=document.getElementById(`${p}-word`); if(wEl) wEl.textContent=w;
        const cEl=document.getElementById(`${p}-cat`);  if(cEl) cEl.textContent='คำต้องห้าม 🚫';
        const card=document.getElementById(`${p}-card`); if(card) card.className='word-card';
    });
    setZoom(wordZoom);
}

function triggerCaught(){
    initAudio(); sndFail();
    ['m','pc'].forEach(p=>{
        const card=document.getElementById(`${p}-card`); if(card) card.classList.add('st-caught');
        const wEl=document.getElementById(`${p}-word`); if(wEl) wEl.textContent='โดนจับแล้ว! 🔴';
    });
    wordIdx++;
    setTimeout(()=>loadWord(),900);
}

function triggerNext(){
    initAudio(); sndOk();
    ['m','pc'].forEach(p=>{
        const card=document.getElementById(`${p}-card`); if(card) card.classList.add('st-correct');
        const wEl=document.getElementById(`${p}-word`); if(wEl) wEl.textContent='รอดตัว! 🟢';
    });
    wordIdx++;
    setTimeout(()=>loadWord(),700);
}

function endGame(){
    if(timerInt){ clearInterval(timerInt); timerInt=null; }
    ['m','pc'].forEach(p=>{
        const wEl=document.getElementById(`${p}-word`); if(wEl) wEl.textContent='หมดเวลา! ⏰';
        const cEl=document.getElementById(`${p}-cat`);  if(cEl) cEl.textContent='เกมจบแล้ว';
    });
    // Reuse correct button to go back
    document.querySelectorAll('.act-correct').forEach(btn=>{
        btn.innerHTML='<ion-icon name="home-outline"></ion-icon> กลับหน้าหลัก';
        btn.onclick=exitPlay;
    });
    document.querySelectorAll('.act-pass').forEach(btn=>btn.style.display='none');
    playTone(440,'sine',.4,.06); setTimeout(()=>playTone(370,'sine',.4,.06),200);
}

function exitPlay(){
    if(timerInt){ clearInterval(timerInt); timerInt=null; }
    stopLobbyPoll();
    document.removeEventListener('keydown',handleKey);
    myRoom=null; myIsHost=false; myReady=false;
    // Reset buttons
    document.querySelectorAll('.act-correct').forEach(btn=>{
        btn.innerHTML='<ion-icon name="arrow-forward-circle-outline" style="font-size:1.2rem;"></ion-icon> คนถัดไป 🟢';
        btn.onclick=triggerNext; btn.style.display='';
    });
    document.querySelectorAll('.act-pass').forEach(btn=>btn.style.display='');
    ['m-timer','pc-timer'].forEach(id=>{ const el=document.getElementById(id); if(el) el.classList.remove('urgent'); });
    if(isPc()){
        document.getElementById('pc-play').classList.remove('active');
    } else {
        const ps=document.getElementById('m-play');
        if(ps){ ps.classList.remove('active'); ps.style.display='none'; }
    }
    showEntry();
}

// ─── Font Zoom ────────────────────────────────
function setZoom(size){
    wordZoom=size;
    localStorage.setItem('taboo_zoom',size);
    // Update active state for all zoom buttons (both mobile and PC)
    [100,200,300].forEach(s=>{
        ['m','pc'].forEach(p=>{
            const btn=document.getElementById(`${p}-zoom-${s}`);
            if(btn) btn.classList.toggle('active', s===size);
        });
    });
    // Apply font size to all word elements
    ['m-word','pc-word'].forEach(id=>{
        const el=document.getElementById(id);
        if(el){ el.style.fontSize=size+'px'; el.style.lineHeight='1.1'; }
    });
}

// ─── Triple Tap (mobile exit) ─────────────────
function handleTripleTap(e){
    if(e.target.tagName==='BUTTON'||e.target.closest('button')) return;
    const now=Date.now();
    if(now-lastTap<700){ tapCount++; if(tapCount>=2){ tapCount=0; exitPlay(); } }
    else tapCount=1;
    lastTap=now;
}

// ─── Init ─────────────────────────────────────
document.addEventListener('DOMContentLoaded',()=>{
    const saved=localStorage.getItem('taboo-theme');
    if(saved==='cyber'||!saved) document.body.classList.add('theme-cyber');
    else document.body.classList.remove('theme-cyber');
    setZoom(wordZoom);
    // Ping session
    setInterval(()=>fetch('../../api_ping.php').catch(()=>{}),15000);
});
</script>

</body>
</html>
