-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table fullsatu_league.matches
CREATE TABLE IF NOT EXISTS `matches` (
  `id` int NOT NULL AUTO_INCREMENT,
  `season_id` int NOT NULL,
  `round_number` int NOT NULL,
  `leg_number` int NOT NULL DEFAULT '1',
  `player1_id` int NOT NULL,
  `player2_id` int NOT NULL,
  `group_name` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'A',
  `player1_score` int DEFAULT NULL,
  `player2_score` int DEFAULT NULL,
  `winner_id` int DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `scheduled_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `match_photo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `match_notes` text COLLATE utf8mb4_general_ci,
  `p1_partner_id` int DEFAULT NULL,
  `p2_partner_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `season_id` (`season_id`),
  KEY `player1_id` (`player1_id`),
  KEY `player2_id` (`player2_id`),
  KEY `winner_id` (`winner_id`),
  KEY `fk_p1_partner` (`p1_partner_id`),
  KEY `fk_p2_partner` (`p2_partner_id`),
  CONSTRAINT `fk_p1_partner` FOREIGN KEY (`p1_partner_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_p2_partner` FOREIGN KEY (`p2_partner_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`player1_id`) REFERENCES `players` (`id`),
  CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`player2_id`) REFERENCES `players` (`id`),
  CONSTRAINT `matches_ibfk_4` FOREIGN KEY (`winner_id`) REFERENCES `players` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=172 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table fullsatu_league.matches: ~153 rows (approximately)
REPLACE INTO `matches` (`id`, `season_id`, `round_number`, `leg_number`, `player1_id`, `player2_id`, `group_name`, `player1_score`, `player2_score`, `winner_id`, `status`, `scheduled_at`, `created_at`, `match_photo`, `match_notes`, `p1_partner_id`, `p2_partner_id`) VALUES
	(1, 1, 1, 1, 2, 17, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(2, 1, 1, 1, 3, 16, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(3, 1, 1, 1, 4, 1, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(4, 1, 1, 1, 5, 15, 'A', 4, 11, 15, 'completed', NULL, '2026-05-07 00:54:41', 'match_4_1778212272.jpeg', '🔥 Pertandingan Panas: Coach Nursam vs Anto 🔥\r\n\r\nPertandingan kali ini benar-benar seru dan penuh tensi 😱⚔️\r\nCoach Nursam masih terlalu perkasa untuk Anto dengan permainan yang tenang dan penuh pengalaman 🎯🧠\r\n\r\nMeski Anto sempat memberikan perlawanan sengit di akhir-akhir pertandingan 💥🔥,\r\nnamun jam terbang dan pengalaman Coach Nursam memang belum bisa dibohongi 😎🏆\r\n\r\n👏 Selamat untuk Coach Nursam atas kemenangan kerennya! 🎉👑', NULL, NULL),
	(5, 1, 1, 1, 6, 14, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(6, 1, 1, 1, 7, 13, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(8, 1, 1, 1, 9, 12, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(9, 1, 1, 1, 10, 11, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(10, 1, 2, 1, 18, 17, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(11, 1, 2, 1, 2, 1, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(12, 1, 2, 1, 3, 15, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(13, 1, 2, 1, 4, 14, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(14, 1, 2, 1, 5, 13, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(16, 1, 2, 1, 7, 12, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(17, 1, 2, 1, 8, 11, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(18, 1, 2, 1, 9, 10, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(19, 1, 3, 1, 18, 16, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(20, 1, 3, 1, 17, 1, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(21, 1, 3, 1, 2, 14, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(22, 1, 3, 1, 3, 13, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(24, 1, 3, 1, 5, 12, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(25, 1, 3, 1, 6, 11, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(26, 1, 3, 1, 7, 10, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(27, 1, 3, 1, 8, 9, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(28, 1, 4, 1, 18, 1, 'A', 2, 11, 1, 'completed', NULL, '2026-05-07 00:54:41', 'match_28_1778212609.jpeg', '🔥 Match Pembuka: Opick vs Accank 🔥\r\n\r\nDi pertandingan pertama, Opick tampil gacor banget 😮💨⚡\r\nTanpa kasih celah sedikit pun buat Accank berkembang, Opick langsung tancap gas dari awal sampai akhir 🚀\r\n\r\nPermainannya mulus, rapi, dan penuh tekanan 😵💫🎯\r\nAccank dibuat kesulitan buat keluar dari ritme permainan sendiri 🥶\r\n\r\n🏆 Selamat buat Opick yang berhasil mengamankan kemenangan pertama dengan performa keren! 👏🔥', NULL, NULL),
	(29, 1, 4, 1, 16, 15, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(30, 1, 4, 1, 17, 14, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(32, 1, 4, 1, 3, 12, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(33, 1, 4, 1, 4, 11, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(34, 1, 4, 1, 5, 10, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(35, 1, 4, 1, 6, 9, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(36, 1, 4, 1, 7, 8, 'A', 5, 11, 8, 'completed', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(37, 1, 5, 1, 18, 15, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(38, 1, 5, 1, 1, 14, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(39, 1, 5, 1, 16, 13, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(41, 1, 5, 1, 2, 11, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(42, 1, 5, 1, 3, 10, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(43, 1, 5, 1, 4, 9, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(44, 1, 5, 1, 5, 8, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(45, 1, 5, 1, 6, 7, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(46, 1, 6, 1, 18, 14, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(47, 1, 6, 1, 15, 13, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(49, 1, 6, 1, 16, 12, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(50, 1, 6, 1, 17, 11, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(51, 1, 6, 1, 2, 9, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(52, 1, 6, 1, 3, 8, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(53, 1, 6, 1, 4, 7, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(54, 1, 6, 1, 5, 6, 'A', 11, 8, 5, 'completed', NULL, '2026-05-07 00:54:41', 'match_54_1778220374.jpeg', '😱🔥 KEJUTAN BESAR DI PERTANDINGAN! 🔥😱\r\n\r\nSecara mengejutkan, Anto berhasil mengakhiri perlawanan Appank dengan kemenangan meyakinkan 💥🏓\r\n\r\nDuel berlangsung sengit sejak awal ⚔️\r\nTapi Anto tampil lebih tenang dan efektif dalam memanfaatkan setiap peluang 🎯😎\r\n\r\nAppank sempat mencoba bangkit dan memberi tekanan 🔥\r\nNamun Anto tetap solid hingga akhirnya menutup pertandingan dengan skor 11-8 🏆👏\r\n\r\n🚨 Hasil yang cukup mengejutkan dan jadi bukti kalau Anto nggak bisa dipandang sebelah mata! 👑🔥', NULL, NULL),
	(55, 1, 7, 1, 18, 13, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(57, 1, 7, 1, 15, 12, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(58, 1, 7, 1, 1, 11, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(59, 1, 7, 1, 16, 10, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(60, 1, 7, 1, 17, 9, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(61, 1, 7, 1, 2, 7, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(62, 1, 7, 1, 3, 6, 'A', 6, 11, 6, 'completed', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(63, 1, 7, 1, 4, 5, 'A', 10, 11, 5, 'completed', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(65, 1, 8, 1, 13, 12, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(66, 1, 8, 1, 14, 11, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(67, 1, 8, 1, 15, 10, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(68, 1, 8, 1, 1, 9, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(69, 1, 8, 1, 16, 8, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(70, 1, 8, 1, 17, 7, 'A', 11, 8, 17, 'completed', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(71, 1, 8, 1, 2, 5, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(72, 1, 8, 1, 3, 4, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(73, 1, 9, 1, 18, 12, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(75, 1, 9, 1, 13, 10, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(76, 1, 9, 1, 14, 9, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(77, 1, 9, 1, 15, 8, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(78, 1, 9, 1, 1, 7, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(79, 1, 9, 1, 16, 6, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(80, 1, 9, 1, 17, 5, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(81, 1, 9, 1, 2, 3, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(82, 1, 10, 1, 18, 11, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(83, 1, 10, 1, 12, 10, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(85, 1, 10, 1, 13, 8, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(86, 1, 10, 1, 14, 7, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(87, 1, 10, 1, 15, 6, 'A', 11, 5, 15, 'completed', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(88, 1, 10, 1, 1, 5, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(89, 1, 10, 1, 16, 4, 'A', 11, 6, 16, 'completed', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(90, 1, 10, 1, 17, 3, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(91, 1, 11, 1, 18, 10, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(92, 1, 11, 1, 11, 9, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(93, 1, 11, 1, 12, 8, 'A', 8, 11, 8, 'completed', NULL, '2026-05-07 00:54:41', 'match_93_1778219140.jpeg', '😱🔥 EPIC COMEBACK ALERT! 🔥😱\r\nFarid sempat tampil menggila dan unggul telak 8-0 😳💨\r\nBanyak yang mengira pertandingan bakal selesai cepat…\r\nTapi Bakri punya cerita lain 😎⚡\r\nPelan tapi pasti, Bakri mulai bangkit dan membalikkan keadaan dengan comeback yang benar-benar gila 🤯🚀\r\nDari tertinggal jauh, akhirnya Bakri sukses menutup pertandingan dengan skor 11-8 🏆👏\r\n💥 Salah satu comeback paling epic sejauh ini!\r\nSelamat buat Bakri atas mental baja dan permainan luar biasanya 👑🔥', NULL, NULL),
	(95, 1, 11, 1, 13, 6, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(96, 1, 11, 1, 14, 5, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(97, 1, 11, 1, 15, 4, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(98, 1, 11, 1, 1, 3, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(99, 1, 11, 1, 16, 2, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(100, 1, 12, 1, 18, 9, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(101, 1, 12, 1, 10, 8, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(102, 1, 12, 1, 11, 7, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(103, 1, 12, 1, 12, 6, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(105, 1, 12, 1, 13, 4, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(106, 1, 12, 1, 14, 3, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(107, 1, 12, 1, 15, 2, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(108, 1, 12, 1, 16, 17, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(109, 1, 13, 1, 18, 8, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(110, 1, 13, 1, 9, 7, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(111, 1, 13, 1, 10, 6, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(112, 1, 13, 1, 11, 5, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(113, 1, 13, 1, 12, 4, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(115, 1, 13, 1, 13, 2, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(116, 1, 13, 1, 15, 17, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(117, 1, 13, 1, 1, 16, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(118, 1, 14, 1, 18, 7, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(119, 1, 14, 1, 8, 6, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(120, 1, 14, 1, 9, 5, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(121, 1, 14, 1, 10, 4, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(122, 1, 14, 1, 11, 3, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(123, 1, 14, 1, 12, 2, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(124, 1, 14, 1, 13, 17, 'A', 7, 11, 17, 'completed', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(125, 1, 14, 1, 14, 16, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(126, 1, 14, 1, 15, 1, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(127, 1, 15, 1, 18, 6, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(128, 1, 15, 1, 7, 5, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(129, 1, 15, 1, 8, 4, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(130, 1, 15, 1, 9, 3, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(131, 1, 15, 1, 10, 2, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(132, 1, 15, 1, 12, 17, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(134, 1, 15, 1, 13, 1, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(135, 1, 15, 1, 14, 15, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(136, 1, 16, 1, 18, 5, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(137, 1, 16, 1, 6, 4, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(138, 1, 16, 1, 7, 3, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(139, 1, 16, 1, 8, 2, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(140, 1, 16, 1, 10, 17, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(141, 1, 16, 1, 11, 16, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(142, 1, 16, 1, 12, 1, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(144, 1, 16, 1, 13, 14, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(145, 1, 17, 1, 18, 4, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(146, 1, 17, 1, 5, 3, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(147, 1, 17, 1, 6, 2, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(148, 1, 17, 1, 8, 17, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(149, 1, 17, 1, 9, 16, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(150, 1, 17, 1, 10, 1, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(151, 1, 17, 1, 11, 15, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(152, 1, 17, 1, 12, 14, 'A', 11, 8, 12, 'completed', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(154, 1, 18, 1, 18, 3, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(155, 1, 18, 1, 4, 2, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(156, 1, 18, 1, 6, 17, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(157, 1, 18, 1, 7, 16, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(158, 1, 18, 1, 8, 1, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(159, 1, 18, 1, 9, 15, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(160, 1, 18, 1, 10, 14, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(161, 1, 18, 1, 11, 13, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(163, 1, 19, 1, 18, 2, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(164, 1, 19, 1, 4, 17, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(165, 1, 19, 1, 5, 16, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(166, 1, 19, 1, 6, 1, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(167, 1, 19, 1, 7, 15, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(168, 1, 19, 1, 8, 14, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(169, 1, 19, 1, 9, 13, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL),
	(171, 1, 19, 1, 11, 12, 'A', NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 00:54:41', NULL, NULL, NULL, NULL);

-- Dumping structure for table fullsatu_league.players
CREATE TABLE IF NOT EXISTS `players` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `photo` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table fullsatu_league.players: ~18 rows (approximately)
REPLACE INTO `players` (`id`, `name`, `created_at`, `photo`) VALUES
	(1, 'Opick', '2026-05-06 23:21:54', '5e6ccd5525085ec37c7036d2fddbb9a3.png'),
	(2, 'Ade', '2026-05-06 23:21:54', '08e7ab0ec29ed69ef47f90b31034e25f.png'),
	(3, 'Ahmad', '2026-05-06 23:21:54', '3ac37114ab45cecf170c2cb3520689c8.png'),
	(4, 'Alam', '2026-05-06 23:21:54', '7a24e19c6984b2b15153b6a082509c2f.jpeg'),
	(5, 'Anto', '2026-05-06 23:21:54', 'e4b3d319ec7b1674c0e6f89a0ad8df89.png'),
	(6, 'Appank', '2026-05-06 23:21:54', 'cb785fdfe06b652ca00f1836a7d16b51.jpeg'),
	(7, 'Badu', '2026-05-06 23:21:54', '3be59d49da62bd93cdac0127ae9d9c67.png'),
	(8, 'Bakri', '2026-05-06 23:21:54', '726797372b2980560434771a37687d62.png'),
	(9, 'Brewok', '2026-05-06 23:21:54', 'e66108ff60a9567efa26ea668c441c7c.png'),
	(10, 'Erwin', '2026-05-06 23:21:54', '6cc45e54a02bf6bbb151b6aa4e793d60.png'),
	(11, 'Fahyu', '2026-05-06 23:21:54', '21ba94d13deacf6a635cc39ee909b3b5.png'),
	(12, 'Farid', '2026-05-06 23:21:54', 'd297ab18cb949d827eb6fd8921833852.png'),
	(13, 'Ilham', '2026-05-06 23:21:54', 'b9f237cc23298f4231df4fefda229b2d.png'),
	(14, 'Ippank', '2026-05-06 23:21:54', '9b6ae9df4ea8df5bd96fa9819c5cf337.png'),
	(15, 'Nursam', '2026-05-06 23:21:54', '4bb6949070456b3e19d2a57855b40338.png'),
	(16, 'Padi', '2026-05-06 23:21:54', 'e1783bd8e232795d8f79b6dbb3180a8c.png'),
	(17, 'Rafli', '2026-05-06 23:21:54', 'b0edb235526a869dc1fdd1d7d85c6eef.png'),
	(18, 'Accank', '2026-05-06 23:21:54', 'b9959d8eb70739a799bedbfb0748ad2e.png');

-- Dumping structure for table fullsatu_league.seasons
CREATE TABLE IF NOT EXISTS `seasons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `league_type` enum('half','full') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'half',
  `status` enum('draft','active','completed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `category` enum('single','double') COLLATE utf8mb4_general_ci DEFAULT 'single',
  `format` enum('league','cup','hybrid') COLLATE utf8mb4_general_ci DEFAULT 'league',
  `group_count` int DEFAULT '1',
  `qualifiers_per_group` int DEFAULT '2',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table fullsatu_league.seasons: ~0 rows (approximately)
REPLACE INTO `seasons` (`id`, `name`, `league_type`, `status`, `created_at`, `category`, `format`, `group_count`, `qualifiers_per_group`) VALUES
	(1, '2026', 'half', 'active', '2026-05-06 23:21:54', 'single', 'league', 1, 2);

-- Dumping structure for table fullsatu_league.season_players
CREATE TABLE IF NOT EXISTS `season_players` (
  `id` int NOT NULL AUTO_INCREMENT,
  `season_id` int NOT NULL,
  `player_id` int NOT NULL,
  `group_name` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'A',
  `partner_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_season_player` (`season_id`,`player_id`),
  KEY `player_id` (`player_id`),
  KEY `fk_sp_partner` (`partner_id`),
  CONSTRAINT `fk_sp_partner` FOREIGN KEY (`partner_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  CONSTRAINT `season_players_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `season_players_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table fullsatu_league.season_players: ~18 rows (approximately)
REPLACE INTO `season_players` (`id`, `season_id`, `player_id`, `group_name`, `partner_id`) VALUES
	(1, 1, 18, 'A', NULL),
	(2, 1, 2, 'A', NULL),
	(3, 1, 3, 'A', NULL),
	(4, 1, 4, 'A', NULL),
	(5, 1, 5, 'A', NULL),
	(6, 1, 6, 'A', NULL),
	(7, 1, 7, 'A', NULL),
	(8, 1, 8, 'A', NULL),
	(9, 1, 9, 'A', NULL),
	(10, 1, 10, 'A', NULL),
	(11, 1, 11, 'A', NULL),
	(12, 1, 12, 'A', NULL),
	(14, 1, 13, 'A', NULL),
	(15, 1, 14, 'A', NULL),
	(16, 1, 15, 'A', NULL),
	(17, 1, 1, 'A', NULL),
	(18, 1, 16, 'A', NULL),
	(19, 1, 17, 'A', NULL);

-- Dumping structure for table fullsatu_league.tournament_brackets
CREATE TABLE IF NOT EXISTS `tournament_brackets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `season_id` int NOT NULL,
  `round_name` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `player1_id` int DEFAULT NULL,
  `player2_id` int DEFAULT NULL,
  `player1_score` int DEFAULT NULL,
  `player2_score` int DEFAULT NULL,
  `winner_id` int DEFAULT NULL,
  `next_match_id` int DEFAULT NULL,
  `position_index` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `p1_partner_id` int DEFAULT NULL,
  `p2_partner_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `season_id` (`season_id`),
  KEY `player1_id` (`player1_id`),
  KEY `player2_id` (`player2_id`),
  KEY `winner_id` (`winner_id`),
  KEY `fk_b_p1_partner` (`p1_partner_id`),
  KEY `fk_b_p2_partner` (`p2_partner_id`),
  CONSTRAINT `fk_b_p1_partner` FOREIGN KEY (`p1_partner_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_b_p2_partner` FOREIGN KEY (`p2_partner_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tournament_brackets_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tournament_brackets_ibfk_2` FOREIGN KEY (`player1_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tournament_brackets_ibfk_3` FOREIGN KEY (`player2_id`) REFERENCES `players` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tournament_brackets_ibfk_4` FOREIGN KEY (`winner_id`) REFERENCES `players` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table fullsatu_league.tournament_brackets: ~3 rows (approximately)
REPLACE INTO `tournament_brackets` (`id`, `season_id`, `round_name`, `player1_id`, `player2_id`, `player1_score`, `player2_score`, `winner_id`, `next_match_id`, `position_index`, `created_at`, `p1_partner_id`, `p2_partner_id`) VALUES
	(119, 9, 'Semifinal', 4, 2, 11, 10, 4, NULL, 0, '2026-05-08 13:29:39', 15, 3),
	(120, 9, 'Semifinal', 12, 7, 11, 10, 12, NULL, 1, '2026-05-08 13:29:39', 14, 11),
	(121, 9, 'Final', 4, 12, 11, 10, 4, NULL, 0, '2026-05-08 13:29:39', 15, 14);

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
