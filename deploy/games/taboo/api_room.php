<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . '/db.php';
session_start();

$action = $_GET['action'] ?? '';

// Auto-create taboo rooms and players tables if they don't exist
$conn->query("CREATE TABLE IF NOT EXISTS `taboo_rooms` (
    `room_code` VARCHAR(10) PRIMARY KEY,
    `host_user_id` INT NOT NULL,
    `team_mode` VARCHAR(20) DEFAULT '2vs2',
    `game_status` VARCHAR(50) DEFAULT 'setup',
    `timer_duration` INT DEFAULT 60,
    `timer_seconds` INT DEFAULT 60,
    `timer_running` TINYINT DEFAULT 0,
    `timer_sync_time` BIGINT DEFAULT 0,
    `current_word` VARCHAR(255) DEFAULT NULL,
    `current_forbidden` TEXT DEFAULT NULL,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$conn->query("CREATE TABLE IF NOT EXISTS `taboo_players` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `room_code` VARCHAR(10) NOT NULL,
    `player_name` VARCHAR(100) NOT NULL,
    `avatar_icon` VARCHAR(50) DEFAULT 'person-outline',
    `team` VARCHAR(10) DEFAULT 'A',
    `is_ready` TINYINT(1) DEFAULT 0,
    `is_host` TINYINT(1) DEFAULT 0,
    `joined_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `room_player` (`room_code`, `player_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

if ($action === 'create') {
    $player_name = trim($_POST['player_name'] ?? '');
    $avatar = trim($_POST['avatar_icon'] ?? 'person-outline');
    $team_mode = trim($_POST['team_mode'] ?? '2vs2');
    
    if (empty($player_name)) {
        echo json_encode(["status" => "error", "message" => "กรุณากรอกชื่อของคุณ"]);
        exit();
    }
    
    // Generate a unique 4-digit room code
    $room_code = '';
    for ($i = 0; $i < 10; $i++) {
        $temp_code = strval(rand(1000, 9999));
        $stmt = $conn->prepare("SELECT room_code FROM taboo_rooms WHERE room_code = ?");
        $stmt->bind_param("s", $temp_code);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows === 0) {
            $room_code = $temp_code;
            break;
        }
    }
    
    if (empty($room_code)) {
        echo json_encode(["status" => "error", "message" => "Could not generate room code"]);
        exit();
    }
    
    $userId = $_SESSION['user_id'] ?? 999;
    
    // Insert room
    $stmt = $conn->prepare("INSERT INTO taboo_rooms (room_code, host_user_id, team_mode, game_status, timer_duration, timer_seconds) VALUES (?, ?, ?, 'setup', 60, 60) ON DUPLICATE KEY UPDATE host_user_id = ?, team_mode = ?, game_status = 'setup'");
    $stmt->bind_param("sisis", $room_code, $userId, $team_mode, $userId, $team_mode);
    $stmt->execute();
    
    // Clear old players in this room code (if any)
    $stmt = $conn->prepare("DELETE FROM taboo_players WHERE room_code = ?");
    $stmt->bind_param("s", $room_code);
    $stmt->execute();
    
    // Insert host player (defaults to Team A)
    $stmt = $conn->prepare("INSERT INTO taboo_players (room_code, player_name, avatar_icon, team, is_ready, is_host) VALUES (?, ?, ?, 'A', 1, 1)");
    $stmt->bind_param("sss", $room_code, $player_name, $avatar);
    $stmt->execute();
    
    echo json_encode(["status" => "success", "room_code" => $room_code]);
    exit();
}

if ($action === 'join') {
    $code = trim($_POST['room_code'] ?? '');
    $player_name = trim($_POST['player_name'] ?? '');
    $avatar = trim($_POST['avatar_icon'] ?? 'person-outline');
    
    if (empty($code) || empty($player_name)) {
        echo json_encode(["status" => "error", "message" => "กรุณากรอกชื่อและรหัสห้อง"]);
        exit();
    }
    
    // Verify room and team mode limits
    $stmt = $conn->prepare("SELECT team_mode FROM taboo_rooms WHERE room_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "ไม่พบรหัสห้องนี้"]);
        exit();
    }
    
    $room = $res->fetch_assoc();
    $team_mode = $room['team_mode']; // 'solo', '2vs2', '3vs3', '6vs6'
    
    if ($team_mode === 'solo') {
        $max_per_team = 20; // Allow up to 20 players for solo mode
        $target_team = 'A'; // Everyone goes to Team A
        
        // Count total players
        $count_stmt = $conn->prepare("SELECT COUNT(*) as c FROM taboo_players WHERE room_code = ?");
        $count_stmt->bind_param("s", $code);
        $count_stmt->execute();
        $count_res = $count_stmt->get_result()->fetch_assoc();
        
        if (intval($count_res['c']) >= $max_per_team) {
            echo json_encode(["status" => "error", "message" => "ห้องนี้เต็มความจุแล้ว"]);
            exit();
        }
    } else {
        $max_per_team = 2;
        if ($team_mode === '3vs3') $max_per_team = 3;
        if ($team_mode === '6vs6') $max_per_team = 6;
        
        // Count players in each team
        $count_stmt = $conn->prepare("SELECT team, COUNT(*) as c FROM taboo_players WHERE room_code = ? GROUP BY team");
        $count_stmt->bind_param("s", $code);
        $count_stmt->execute();
        $count_res = $count_stmt->get_result();
        
        $team_counts = ['A' => 0, 'B' => 0];
        while($crow = $count_res->fetch_assoc()) {
            $team_counts[$crow['team']] = intval($crow['c']);
        }
        
        // Choose team with fewer players first
        $target_team = 'A';
        if ($team_counts['B'] < $team_counts['A']) {
            $target_team = 'B';
        }
        
        if ($team_counts[$target_team] >= $max_per_team) {
            echo json_encode(["status" => "error", "message" => "ห้องนี้เต็มความจุของโหมด $team_mode แล้ว"]);
            exit();
        }
    }
    
    // Insert player (unique name constraint inside room)
    try {
        $stmt = $conn->prepare("INSERT INTO taboo_players (room_code, player_name, avatar_icon, team, is_ready, is_host) VALUES (?, ?, ?, ?, 0, 0)");
        $stmt->bind_param("ssss", $code, $player_name, $avatar, $target_team);
        $stmt->execute();
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => "ชื่อผู้เล่นซ้ำในห้องนี้ กรุณาเปลี่ยนชื่ออื่น"]);
        exit();
    }
    
    echo json_encode(["status" => "success", "room_code" => $code]);
    exit();
}

if ($action === 'switch_team') {
    $code = $_POST['room_code'] ?? '';
    $player_name = $_POST['player_name'] ?? '';
    
    // Get current team and room mode
    $stmt = $conn->prepare("SELECT p.team, r.team_mode FROM taboo_players p JOIN taboo_rooms r ON p.room_code = r.room_code WHERE p.room_code = ? AND p.player_name = ?");
    $stmt->bind_param("ss", $code, $player_name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "ไม่พบข้อมูลผู้เล่น"]);
        exit();
    }
    
    $row = $res->fetch_assoc();
    $current_team = $row['team'];
    $team_mode = $row['team_mode'];
    
    $max_per_team = 2;
    if ($team_mode === '3vs3') $max_per_team = 3;
    if ($team_mode === '6vs6') $max_per_team = 6;
    
    $new_team = ($current_team === 'A') ? 'B' : 'A';
    
    // Check if new team is full
    $count_stmt = $conn->prepare("SELECT COUNT(*) as c FROM taboo_players WHERE room_code = ? AND team = ?");
    $count_stmt->bind_param("ss", $code, $new_team);
    $count_stmt->execute();
    $count_res = $count_stmt->get_result()->fetch_assoc();
    
    if (intval($count_res['c']) >= $max_per_team) {
        echo json_encode(["status" => "error", "message" => "ไม่สามารถเปลี่ยนได้เนื่องจาก ทีม $new_team เต็มแล้ว"]);
        exit();
    }
    
    $update_stmt = $conn->prepare("UPDATE taboo_players SET team = ? WHERE room_code = ? AND player_name = ?");
    $update_stmt->bind_param("sss", $new_team, $code, $player_name);
    $update_stmt->execute();
    
    echo json_encode(["status" => "success", "new_team" => $new_team]);
    exit();
}

if ($action === 'ready') {
    $code = $_POST['room_code'] ?? '';
    $player_name = $_POST['player_name'] ?? '';
    
    $stmt = $conn->prepare("UPDATE taboo_players SET is_ready = NOT is_ready WHERE room_code = ? AND player_name = ?");
    $stmt->bind_param("ss", $code, $player_name);
    $stmt->execute();
    
    echo json_encode(["status" => "success"]);
    exit();
}

if ($action === 'poll') {
    $code = $_GET['room_code'] ?? '';
    
    // Get room details
    $stmt = $conn->prepare("SELECT * FROM taboo_rooms WHERE room_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $room_res = $stmt->get_result();
    
    if ($room_res->num_rows === 0) {
        echo json_encode(["status" => "error", "message" => "ห้องนี้ปิดแล้ว"]);
        exit();
    }
    
    $room = $room_res->fetch_assoc();
    
    // Get player details
    $stmt = $conn->prepare("SELECT player_name, avatar_icon, team, is_ready, is_host FROM taboo_players WHERE room_code = ? ORDER BY id ASC");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $players = [];
    while ($row = $res->fetch_assoc()) {
        $players[] = $row;
    }
    
    echo json_encode([
        "status" => "success",
        "room" => [
            "room_code" => $room['room_code'],
            "team_mode" => $room['team_mode'],
            "game_status" => $room['game_status'],
            "timer_duration" => intval($room['timer_duration']),
            "timer_seconds" => intval($room['timer_seconds']),
            "timer_running" => intval($room['timer_running']),
            "timer_sync_time" => floatval($room['timer_sync_time']),
            "current_word" => $room['current_word'],
            "current_forbidden" => $room['current_forbidden'] ? explode(',', $room['current_forbidden']) : []
        ],
        "players" => $players
    ]);
    exit();
}

if ($action === 'timer_control') {
    $code = $_POST['room_code'] ?? '';
    $timer_action = $_POST['timer_action'] ?? ''; // 'start', 'pause', 'reset', 'set_duration'
    
    if ($timer_action === 'start') {
        $stmt = $conn->prepare("UPDATE taboo_rooms SET timer_running = 1, timer_sync_time = ? WHERE room_code = ?");
        $now_ms = round(microtime(true) * 1000);
        $stmt->bind_param("ds", $now_ms, $code);
        $stmt->execute();
    } elseif ($timer_action === 'pause') {
        $rem_sec = intval($_POST['remaining_seconds'] ?? 60);
        $stmt = $conn->prepare("UPDATE taboo_rooms SET timer_running = 0, timer_seconds = ? WHERE room_code = ?");
        $stmt->bind_param("is", $rem_sec, $code);
        $stmt->execute();
    } elseif ($timer_action === 'reset') {
        $stmt = $conn->prepare("UPDATE taboo_rooms SET timer_running = 0, timer_seconds = timer_duration WHERE room_code = ?");
        $stmt->bind_param("s", $code);
        $stmt->execute();
    } elseif ($timer_action === 'set_duration') {
        $dur = intval($_POST['duration'] ?? 60);
        $stmt = $conn->prepare("UPDATE taboo_rooms SET timer_duration = ?, timer_seconds = ? WHERE room_code = ?");
        $stmt->bind_param("iis", $dur, $dur, $code);
        $stmt->execute();
    }
    
    echo json_encode(["status" => "success"]);
    exit();
}

if ($action === 'start_game') {
    $code = $_POST['room_code'] ?? '';
    $word = $_POST['word'] ?? '';
    $forbidden = $_POST['forbidden'] ?? ''; // comma-separated
    
    $stmt = $conn->prepare("UPDATE taboo_rooms SET game_status = 'playing', current_word = ?, current_forbidden = ?, timer_running = 0, timer_seconds = timer_duration WHERE room_code = ?");
    $stmt->bind_param("sss", $word, $forbidden, $code);
    $stmt->execute();
    
    echo json_encode(["status" => "success"]);
    exit();
}

if ($action === 'next_word') {
    $code = $_POST['room_code'] ?? '';
    $word = $_POST['word'] ?? '';
    $forbidden = $_POST['forbidden'] ?? ''; // comma-separated
    
    $stmt = $conn->prepare("UPDATE taboo_rooms SET current_word = ?, current_forbidden = ? WHERE room_code = ?");
    $stmt->bind_param("sss", $word, $forbidden, $code);
    $stmt->execute();
    
    echo json_encode(["status" => "success"]);
    exit();
}

if ($action === 'end_game') {
    $code = $_POST['room_code'] ?? '';
    $stmt = $conn->prepare("UPDATE taboo_rooms SET game_status = 'ended', timer_running = 0 WHERE room_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    
    echo json_encode(["status" => "success"]);
    exit();
}

if ($action === 'restart') {
    $code = $_POST['room_code'] ?? '';
    $stmt = $conn->prepare("UPDATE taboo_rooms SET game_status = 'setup', current_word = NULL, current_forbidden = NULL, timer_running = 0, timer_seconds = timer_duration WHERE room_code = ?");
    $stmt->bind_param("s", $code);
    $stmt->execute();
    
    echo json_encode(["status" => "success"]);
    exit();
}

if ($action === 'exit') {
    $code = $_POST['room_code'] ?? '';
    $player_name = $_POST['player_name'] ?? '';
    
    // Check if host
    $is_host = 0;
    $stmt = $conn->prepare("SELECT is_host FROM taboo_players WHERE room_code = ? AND player_name = ?");
    $stmt->bind_param("ss", $code, $player_name);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        $is_host = $row['is_host'];
    }
    
    $stmt = $conn->prepare("DELETE FROM taboo_players WHERE room_code = ? AND player_name = ?");
    $stmt->bind_param("ss", $code, $player_name);
    $stmt->execute();
    
    if ($is_host == 1) {
        // Rotate host
        $stmt = $conn->prepare("SELECT player_name FROM taboo_players WHERE room_code = ? ORDER BY id ASC LIMIT 1");
        $stmt->bind_param("s", $code);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows > 0) {
            $new_host = $res->fetch_assoc();
            $new_host_name = $new_host['player_name'];
            
            $stmt = $conn->prepare("UPDATE taboo_players SET is_host = 1 WHERE room_code = ? AND player_name = ?");
            $stmt->bind_param("ss", $code, $new_host_name);
            $stmt->execute();
        } else {
            // Delete room
            $stmt = $conn->prepare("DELETE FROM taboo_rooms WHERE room_code = ?");
            $stmt->bind_param("s", $code);
            $stmt->execute();
        }
    }
    
    echo json_encode(["status" => "success"]);
    exit();
}

echo json_encode(["status" => "error", "message" => "Invalid action"]);
exit();
