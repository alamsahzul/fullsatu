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
  `player1_score` int DEFAULT NULL,
  `player2_score` int DEFAULT NULL,
  `winner_id` int DEFAULT NULL,
  `status` enum('scheduled','completed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'scheduled',
  `scheduled_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `season_id` (`season_id`),
  KEY `player1_id` (`player1_id`),
  KEY `player2_id` (`player2_id`),
  KEY `winner_id` (`winner_id`),
  CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`player1_id`) REFERENCES `players` (`id`),
  CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`player2_id`) REFERENCES `players` (`id`),
  CONSTRAINT `matches_ibfk_4` FOREIGN KEY (`winner_id`) REFERENCES `players` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table fullsatu_league.matches: ~0 rows (approximately)

-- Dumping structure for table fullsatu_league.players
CREATE TABLE IF NOT EXISTS `players` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table fullsatu_league.players: ~19 rows (approximately)
REPLACE INTO `players` (`id`, `name`, `created_at`) VALUES
	(1, 'Opick', '2026-05-07 07:21:54'),
	(2, 'Ade', '2026-05-07 07:21:54'),
	(3, 'Ahmad', '2026-05-07 07:21:54'),
	(4, 'Alam', '2026-05-07 07:21:54'),
	(5, 'Anto', '2026-05-07 07:21:54'),
	(6, 'Appank', '2026-05-07 07:21:54'),
	(7, 'Badu', '2026-05-07 07:21:54'),
	(8, 'Bakri', '2026-05-07 07:21:54'),
	(9, 'Brewok', '2026-05-07 07:21:54'),
	(10, 'Erwin', '2026-05-07 07:21:54'),
	(11, 'Fahyu', '2026-05-07 07:21:54'),
	(12, 'Farid', '2026-05-07 07:21:54'),
	(13, 'Ilham', '2026-05-07 07:21:54'),
	(14, 'Irfan', '2026-05-07 07:21:54'),
	(15, 'Nursam', '2026-05-07 07:21:54'),
	(16, 'Padi', '2026-05-07 07:21:54'),
	(17, 'Rafli', '2026-05-07 07:21:54'),
	(18, 'Accank', '2026-05-07 07:21:54'),
	(19, 'Harfan', '2026-05-07 07:21:54');

-- Dumping structure for table fullsatu_league.seasons
CREATE TABLE IF NOT EXISTS `seasons` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `league_type` enum('half','full') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'half',
  `status` enum('draft','active','completed') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table fullsatu_league.seasons: ~0 rows (approximately)
REPLACE INTO `seasons` (`id`, `name`, `league_type`, `status`, `created_at`) VALUES
	(1, '2026', 'half', 'active', '2026-05-07 07:21:54');

-- Dumping structure for table fullsatu_league.season_players
CREATE TABLE IF NOT EXISTS `season_players` (
  `id` int NOT NULL AUTO_INCREMENT,
  `season_id` int NOT NULL,
  `player_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_season_player` (`season_id`,`player_id`),
  KEY `player_id` (`player_id`),
  CONSTRAINT `season_players_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`) ON DELETE CASCADE,
  CONSTRAINT `season_players_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table fullsatu_league.season_players: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
