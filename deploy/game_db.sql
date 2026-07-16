-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 16, 2026 at 06:20 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `game_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

CREATE TABLE `games` (
  `id` int(11) NOT NULL,
  `game_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `game_url` varchar(255) NOT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `status` varchar(50) DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`id`, `game_name`, `description`, `game_url`, `image_url`, `status`) VALUES
(1, 'สุ่มภารกิจจับคู่', 'สับกองไพ่ทายปริศนาใบหน้ารุ่นพี่ ปวส.', 'games/senior_roulette/index.php', 'https://images.unsplash.com/photo-1614728263952-84ea256f9679', 'active'),
(2, 'สมรภูมิทายเพลง', 'ฟังเสียงท่อนฮุกออโต้จำกัดเวลาทายชื่อเพลง', 'games/senior_roulette/game_music.php', 'https://images.unsplash.com/photo-1511671782779-c97d3d27a1d4', 'active'),
(3, 'ทายภาพอุปกรณ์', 'วิเคราะห์ภาพฮาร์ดแวร์ ทดสอบความไว', 'games/hardware_quiz/index.php', 'https://images.unsplash.com/photo-1518770660439-4636190af475', 'active'),
(4, 'กาชาคัดออก', 'ตู้สไลด์สายพานสุ่มไฟกระพริบ 3 ใบสุดท้าย', 'games/gacha_v2.php', 'https://images.unsplash.com/photo-1607604276583-eef5d076aa5f', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `head_guess_players`
--

CREATE TABLE `head_guess_players` (
  `id` int(11) NOT NULL,
  `room_code` varchar(10) NOT NULL,
  `player_name` varchar(100) NOT NULL,
  `avatar_icon` varchar(50) DEFAULT 'person-outline',
  `is_ready` tinyint(1) DEFAULT 0,
  `is_host` tinyint(1) DEFAULT 0,
  `current_word` varchar(255) DEFAULT NULL,
  `is_caught` tinyint(1) DEFAULT 0,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `head_guess_players`
--

INSERT INTO `head_guess_players` (`id`, `room_code`, `player_name`, `avatar_icon`, `is_ready`, `is_host`, `current_word`, `is_caught`, `joined_at`) VALUES
(1, '1498', 'Administrator', '../../assets/avatar/dog.png', 1, 1, 'กลัว', 0, '2026-07-08 02:48:31'),
(3, '4709', 'KKKK', '../../assets/avatar/dog.png', 1, 1, NULL, 0, '2026-07-08 02:49:01'),
(4, '4709', 'Administrator', '../../assets/avatar/dog.png', 0, 0, NULL, 0, '2026-07-08 02:49:39'),
(10, '5975', 'KKKK', '../../assets/avatar/dog.png', 1, 1, NULL, 0, '2026-07-08 02:59:53'),
(11, '5975', 'Administrator', '../../assets/avatar/dog.png', 0, 0, NULL, 0, '2026-07-08 03:00:08'),
(12, '9420', 'Administrator', '../../assets/avatar/dog.png', 1, 1, 'ยืน', 0, '2026-07-08 03:00:39'),
(14, '6592', 'KKKK', '../../assets/avatar/dog.png', 1, 1, NULL, 0, '2026-07-08 03:00:56'),
(15, '6592', 'Administrator', '../../assets/avatar/dog.png', 0, 0, NULL, 0, '2026-07-08 03:01:06');

-- --------------------------------------------------------

--
-- Table structure for table `head_guess_rooms`
--

CREATE TABLE `head_guess_rooms` (
  `room_code` varchar(10) NOT NULL,
  `host_user_id` int(11) NOT NULL,
  `category_id` varchar(50) DEFAULT NULL,
  `current_word` varchar(255) DEFAULT NULL,
  `game_status` varchar(50) DEFAULT 'setup',
  `score` int(11) DEFAULT 0,
  `seconds_remaining` int(11) DEFAULT 60,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `head_guess_rooms`
--

INSERT INTO `head_guess_rooms` (`room_code`, `host_user_id`, `category_id`, `current_word`, `game_status`, `score`, `seconds_remaining`, `updated_at`) VALUES
('1498', 1, 'forbidden_words', NULL, 'playing', 0, 60, '2026-07-08 02:48:41'),
('4709', 2, 'forbidden_words', NULL, 'setup', 0, 60, '2026-07-08 02:49:49'),
('5975', 2, 'forbidden_words', NULL, 'setup', 0, 60, '2026-07-08 03:00:13'),
('6592', 2, 'forbidden_words', NULL, 'setup', 0, 60, '2026-07-08 03:01:14'),
('9420', 1, 'forbidden_words', NULL, 'playing', 0, 60, '2026-07-08 03:00:47');

-- --------------------------------------------------------

--
-- Table structure for table `lightning_questions`
--

CREATE TABLE `lightning_questions` (
  `id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lightning_questions`
--

INSERT INTO `lightning_questions` (`id`, `question_text`, `created_at`) VALUES
(1, 'สายแลน UTP ย่อมาจากอะไร?', '2026-07-16 03:59:26'),
(2, 'CPU ย่อมาจากอะไร?', '2026-07-16 03:59:26'),
(3, 'RAM คือหน่วยความจำหลักของเครื่องคอมพิวเตอร์ ใช่หรือไม่?', '2026-07-16 03:59:26'),
(4, '1 Byte มีค่าเท่ากับกี่ Bit?', '2026-07-16 03:59:26'),
(5, 'โปรโตคอล HTTP และ HTTPS แตกต่างกันที่เรื่องใด?', '2026-07-16 03:59:26'),
(6, 'อุปกรณ์ที่ทำหน้าที่แปลงสัญญาณดิจิทัลเป็นอนาล็อกเพื่อรับส่งข้อมูล เรียกว่าอะไร?', '2026-07-16 03:59:26'),
(7, 'ที่อยู่อีเมลประกอบด้วยเครื่องหมายใดเป็นหลัก?', '2026-07-16 03:59:26');

-- --------------------------------------------------------

--
-- Table structure for table `lightning_quiz_state`
--

CREATE TABLE `lightning_quiz_state` (
  `id` int(11) NOT NULL,
  `current_question_id` int(11) DEFAULT 0,
  `current_level` int(11) DEFAULT 0,
  `timer_duration` int(11) DEFAULT 60,
  `timer_seconds` int(11) DEFAULT 60,
  `timer_running` tinyint(4) DEFAULT 0,
  `timer_sync_time` bigint(20) DEFAULT 0,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `lightning_quiz_state`
--

INSERT INTO `lightning_quiz_state` (`id`, `current_question_id`, `current_level`, `timer_duration`, `timer_seconds`, `timer_running`, `timer_sync_time`, `updated_at`) VALUES
(1, 0, 2, 60, 60, 0, 1784174727019, '2026-07-16 04:06:10');

-- --------------------------------------------------------

--
-- Table structure for table `taboo_players`
--

CREATE TABLE `taboo_players` (
  `id` int(11) NOT NULL,
  `room_code` varchar(10) NOT NULL,
  `player_name` varchar(100) NOT NULL,
  `avatar_icon` varchar(50) DEFAULT 'person-outline',
  `team` varchar(10) DEFAULT 'A',
  `is_ready` tinyint(1) DEFAULT 0,
  `is_host` tinyint(1) DEFAULT 0,
  `joined_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `taboo_players`
--

INSERT INTO `taboo_players` (`id`, `room_code`, `player_name`, `avatar_icon`, `team`, `is_ready`, `is_host`, `joined_at`) VALUES
(1, '1334', 'System Admin', 'dog.png', 'A', 1, 1, '2026-07-16 03:52:20'),
(2, '4396', 'System Admin', 'dog.png', 'A', 1, 1, '2026-07-16 03:52:28'),
(3, '8865', 'System Admin', 'dog.png', 'A', 1, 1, '2026-07-16 03:55:12'),
(4, '6628', 'ธีระชัย', 'dog.png', 'A', 1, 1, '2026-07-16 04:07:32');

-- --------------------------------------------------------

--
-- Table structure for table `taboo_rooms`
--

CREATE TABLE `taboo_rooms` (
  `room_code` varchar(10) NOT NULL,
  `host_user_id` int(11) NOT NULL,
  `team_mode` varchar(20) DEFAULT '2vs2',
  `game_status` varchar(50) DEFAULT 'setup',
  `timer_duration` int(11) DEFAULT 60,
  `timer_seconds` int(11) DEFAULT 60,
  `timer_running` tinyint(4) DEFAULT 0,
  `timer_sync_time` bigint(20) DEFAULT 0,
  `current_word` varchar(255) DEFAULT NULL,
  `current_forbidden` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `taboo_rooms`
--

INSERT INTO `taboo_rooms` (`room_code`, `host_user_id`, `team_mode`, `game_status`, `timer_duration`, `timer_seconds`, `timer_running`, `timer_sync_time`, `current_word`, `current_forbidden`, `updated_at`) VALUES
('1334', 1, '2vs2', 'setup', 60, 60, 0, 0, NULL, NULL, '2026-07-16 03:52:20'),
('4396', 1, '2vs2', 'setup', 60, 60, 0, 0, NULL, NULL, '2026-07-16 03:52:28'),
('6628', 11, 'solo', 'ended', 60, 60, 0, 0, 'โทรศัพท์', 'มือถือ,โทร,ไลน์,สมาร์ทโฟน,ติดต่อ', '2026-07-16 04:09:48'),
('8865', 1, 'solo', 'setup', 60, 60, 0, 0, NULL, NULL, '2026-07-16 03:55:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `real_name` varchar(255) DEFAULT NULL,
  `avatar_img` varchar(255) DEFAULT NULL,
  `is_avatar_created` tinyint(1) DEFAULT 0,
  `score` int(11) DEFAULT 0,
  `role` varchar(50) DEFAULT 'member',
  `last_seen` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `real_name`, `avatar_img`, `is_avatar_created`, `score`, `role`, `last_seen`) VALUES
(1, 'admin', 'admin@clubgame.com', '$2y$10$Q1pL8QOPr4gAxKrvW1Gn9OamsHnK4gw0aaXN8Dv0pS9t1z9oyG.LS', 'Administrator', 'dog.png', 1, 0, 'admin', '2026-07-16 03:43:05'),
(2, 'k', 'k@gmail.com', '$2y$10$r4/FakVT8izAD86T4/L4ZOD8jqlRe.15juupeubvP.RUHfvp2xVE2', 'KKKK', 'dog.png', 1, 0, 'member', NULL),
(3, 'agent_17841722149868', 'agent_17841722149868@gmaemini.local', '$2y$10$bgbj2E8p5zSKpCCKTMjb7OdbG1UWMKCrEZMmbwZyvJrxO6HVSUqdO', 'ธีระชัย ', 'cat.png', 1, 0, 'member', NULL),
(4, 'agent_17841722207283', 'agent_17841722207283@gmaemini.local', '$2y$10$X0HlkcaY.2WutdRAhrqW0O2txSScKpnKkKITABZqfBfO/lpS0oTzC', 'ธีระชัย', 'dog.png', 1, 0, 'member', NULL),
(5, 'agent_17841722253541', 'agent_17841722253541@gmaemini.local', '$2y$10$WL2M1Hgl0x7JVMJHOcrckOp7qMTPm0BRvhkpnd44qoW3SQzWtNimW', 'ธีระชัย', 'dog.png', 1, 0, 'member', NULL),
(6, 'agent_17841722479326', 'agent_17841722479326@gmaemini.local', '$2y$10$yUq2ToeLvoirycpGV.vGc.xZosogKFxUTOGI2TjspcEesJP6gsM6a', 'ธีระชัย', 'dog.png', 1, 0, 'member', NULL),
(7, 'agent_17841722682074', 'agent_17841722682074@gmaemini.local', '$2y$10$FG9ioHzHwIH6Ali8gAGesepxM0ztR9xkEUW4iJztdIJrDSrp/Ck4W', 'ธีระชัย', 'dog.png', 1, 0, 'member', NULL),
(8, 'agent_17841723429119', 'agent_17841723429119@gmaemini.local', '$2y$10$ym1xgeVDs/IzFEoUCDjBVegy10KKuR2.f/upkodsIXmM6/d2xSgDm', 'ธีระชัย', 'bear.png', 1, 0, 'member', NULL),
(9, 'agent_17841724767383', 'agent_17841724767383@gmaemini.local', '$2y$10$h9w3yeb6CkSXoe/ivVnLj.MII9vDXXtaQS6q7h6MRL86mqpQusRdq', 'admin', 'dog.png', 1, 0, 'member', NULL),
(10, 'agent_17841727266464', 'agent_17841727266464@gmaemini.local', '$2y$10$Ux1LD8fUAwTnzCEwT.HQReBW3QCtDUNI7oAHMBdZaSehy69vM/dIi', 'admin', 'dog.png', 1, 0, 'member', NULL),
(11, 'agent_17841748439763', 'agent_17841748439763@gmaemini.local', '$2y$10$S5sIrLgPblEw8ag/qfq6euJJIcabThiuxM6FYP9y885MVN0/umpby', 'ธีระชัย', 'dog.png', 1, 0, 'member', NULL),
(12, 'agent_17841749928598', 'agent_17841749928598@gmaemini.local', '$2y$10$nZnXVDS.Dr0cyJ.SFffnbeCxH5YgC9eInLW1Illb6k3.bv41SI7yG', 'admin', 'dog.png', 1, 0, 'member', '2026-07-16 04:14:31');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `games`
--
ALTER TABLE `games`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `head_guess_players`
--
ALTER TABLE `head_guess_players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_player` (`room_code`,`player_name`);

--
-- Indexes for table `head_guess_rooms`
--
ALTER TABLE `head_guess_rooms`
  ADD PRIMARY KEY (`room_code`);

--
-- Indexes for table `lightning_questions`
--
ALTER TABLE `lightning_questions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lightning_quiz_state`
--
ALTER TABLE `lightning_quiz_state`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `taboo_players`
--
ALTER TABLE `taboo_players`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `room_player` (`room_code`,`player_name`);

--
-- Indexes for table `taboo_rooms`
--
ALTER TABLE `taboo_rooms`
  ADD PRIMARY KEY (`room_code`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `games`
--
ALTER TABLE `games`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `head_guess_players`
--
ALTER TABLE `head_guess_players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `lightning_questions`
--
ALTER TABLE `lightning_questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `taboo_players`
--
ALTER TABLE `taboo_players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
