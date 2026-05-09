-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Waktu pembuatan: 09 Bulan Mei 2026 pada 08.57
-- Versi server: 8.0.46
-- Versi PHP: 8.4.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `sportunsulbarac_pickelball_db`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `matches`
--

CREATE TABLE IF NOT EXISTS `matches` (
  `id` int NOT NULL,
  `season_id` int NOT NULL,
  `round_number` int NOT NULL,
  `leg_number` int NOT NULL DEFAULT '1',
  `player1_id` int NOT NULL,
  `player2_id` int NOT NULL,
  `player1_score` int DEFAULT NULL,
  `player2_score` int DEFAULT NULL,
  `winner_id` int DEFAULT NULL,
  `status` enum('scheduled','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'scheduled',
  `scheduled_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `match_photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `match_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `matches`
--

INSERT INTO `matches` (`id`, `season_id`, `round_number`, `leg_number`, `player1_id`, `player2_id`, `player1_score`, `player2_score`, `winner_id`, `status`, `scheduled_at`, `created_at`, `match_photo`, `match_notes`) VALUES
(1, 1, 1, 1, 2, 17, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(2, 1, 1, 1, 3, 16, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(3, 1, 1, 1, 4, 1, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(4, 1, 1, 1, 5, 15, 4, 11, 15, 'completed', NULL, '2026-05-07 08:54:41', 'match_4_1778212272.jpeg', '🔥 Pertandingan Panas: Coach Nursam vs Anto 🔥\r\n\r\nPertandingan kali ini benar-benar seru dan penuh tensi 😱⚔️\r\nCoach Nursam masih terlalu perkasa untuk Anto dengan permainan yang tenang dan penuh pengalaman 🎯🧠\r\n\r\nMeski Anto sempat memberikan perlawanan sengit di akhir-akhir pertandingan 💥🔥,\r\nnamun jam terbang dan pengalaman Coach Nursam memang belum bisa dibohongi 😎🏆\r\n\r\n👏 Selamat untuk Coach Nursam atas kemenangan kerennya! 🎉👑'),
(5, 1, 1, 1, 6, 14, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(6, 1, 1, 1, 7, 13, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(8, 1, 1, 1, 9, 12, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(9, 1, 1, 1, 10, 11, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(10, 1, 2, 1, 18, 17, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(11, 1, 2, 1, 2, 1, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(12, 1, 2, 1, 3, 15, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(13, 1, 2, 1, 4, 14, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(14, 1, 2, 1, 5, 13, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(16, 1, 2, 1, 7, 12, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(17, 1, 2, 1, 8, 11, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(18, 1, 2, 1, 9, 10, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(19, 1, 3, 1, 18, 16, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(20, 1, 3, 1, 17, 1, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(21, 1, 3, 1, 2, 14, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(22, 1, 3, 1, 3, 13, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(24, 1, 3, 1, 5, 12, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(25, 1, 3, 1, 6, 11, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(26, 1, 3, 1, 7, 10, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(27, 1, 3, 1, 8, 9, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(28, 1, 4, 1, 18, 1, 2, 11, 1, 'completed', NULL, '2026-05-07 08:54:41', 'match_28_1778212609.jpeg', '🔥 Match Pembuka: Opick vs Accank 🔥\r\n\r\nDi pertandingan pertama, Opick tampil gacor banget 😮💨⚡\r\nTanpa kasih celah sedikit pun buat Accank berkembang, Opick langsung tancap gas dari awal sampai akhir 🚀\r\n\r\nPermainannya mulus, rapi, dan penuh tekanan 😵💫🎯\r\nAccank dibuat kesulitan buat keluar dari ritme permainan sendiri 🥶\r\n\r\n🏆 Selamat buat Opick yang berhasil mengamankan kemenangan pertama dengan performa keren! 👏🔥'),
(29, 1, 4, 1, 16, 15, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(30, 1, 4, 1, 17, 14, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(32, 1, 4, 1, 3, 12, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(33, 1, 4, 1, 4, 11, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(34, 1, 4, 1, 5, 10, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(35, 1, 4, 1, 6, 9, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(36, 1, 4, 1, 7, 8, 5, 11, 8, 'completed', NULL, '2026-05-07 08:54:41', NULL, NULL),
(37, 1, 5, 1, 18, 15, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(38, 1, 5, 1, 1, 14, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(39, 1, 5, 1, 16, 13, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(41, 1, 5, 1, 2, 11, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(42, 1, 5, 1, 3, 10, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(43, 1, 5, 1, 4, 9, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(44, 1, 5, 1, 5, 8, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(45, 1, 5, 1, 6, 7, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(46, 1, 6, 1, 18, 14, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(47, 1, 6, 1, 15, 13, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(49, 1, 6, 1, 16, 12, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(50, 1, 6, 1, 17, 11, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(51, 1, 6, 1, 2, 9, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(52, 1, 6, 1, 3, 8, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(53, 1, 6, 1, 4, 7, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(54, 1, 6, 1, 5, 6, 11, 8, 5, 'completed', NULL, '2026-05-07 08:54:41', 'match_54_1778220374.jpeg', '😱🔥 KEJUTAN BESAR DI PERTANDINGAN! 🔥😱\r\n\r\nSecara mengejutkan, Anto berhasil mengakhiri perlawanan Appank dengan kemenangan meyakinkan 💥🏓\r\n\r\nDuel berlangsung sengit sejak awal ⚔️\r\nTapi Anto tampil lebih tenang dan efektif dalam memanfaatkan setiap peluang 🎯😎\r\n\r\nAppank sempat mencoba bangkit dan memberi tekanan 🔥\r\nNamun Anto tetap solid hingga akhirnya menutup pertandingan dengan skor 11-8 🏆👏\r\n\r\n🚨 Hasil yang cukup mengejutkan dan jadi bukti kalau Anto nggak bisa dipandang sebelah mata! 👑🔥'),
(55, 1, 7, 1, 18, 13, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(57, 1, 7, 1, 15, 12, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(58, 1, 7, 1, 1, 11, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(59, 1, 7, 1, 16, 10, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(60, 1, 7, 1, 17, 9, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(61, 1, 7, 1, 2, 7, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(62, 1, 7, 1, 3, 6, 6, 11, 6, 'completed', NULL, '2026-05-07 08:54:41', NULL, NULL),
(63, 1, 7, 1, 4, 5, 10, 11, 5, 'completed', NULL, '2026-05-07 08:54:41', NULL, NULL),
(65, 1, 8, 1, 13, 12, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(66, 1, 8, 1, 14, 11, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(67, 1, 8, 1, 15, 10, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(68, 1, 8, 1, 1, 9, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(69, 1, 8, 1, 16, 8, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(70, 1, 8, 1, 17, 7, 11, 8, 17, 'completed', NULL, '2026-05-07 08:54:41', NULL, NULL),
(71, 1, 8, 1, 2, 5, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(72, 1, 8, 1, 3, 4, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(73, 1, 9, 1, 18, 12, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(75, 1, 9, 1, 13, 10, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(76, 1, 9, 1, 14, 9, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(77, 1, 9, 1, 15, 8, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(78, 1, 9, 1, 1, 7, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(79, 1, 9, 1, 16, 6, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(80, 1, 9, 1, 17, 5, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(81, 1, 9, 1, 2, 3, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(82, 1, 10, 1, 18, 11, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(83, 1, 10, 1, 12, 10, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(85, 1, 10, 1, 13, 8, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(86, 1, 10, 1, 14, 7, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(87, 1, 10, 1, 15, 6, 11, 5, 15, 'completed', NULL, '2026-05-07 08:54:41', NULL, NULL),
(88, 1, 10, 1, 1, 5, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(89, 1, 10, 1, 16, 4, 11, 6, 16, 'completed', NULL, '2026-05-07 08:54:41', NULL, NULL),
(90, 1, 10, 1, 17, 3, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(91, 1, 11, 1, 18, 10, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(92, 1, 11, 1, 11, 9, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(93, 1, 11, 1, 12, 8, 8, 11, 8, 'completed', NULL, '2026-05-07 08:54:41', 'match_93_1778219140.jpeg', '😱🔥 EPIC COMEBACK ALERT! 🔥😱\r\nFarid sempat tampil menggila dan unggul telak 8-0 😳💨\r\nBanyak yang mengira pertandingan bakal selesai cepat…\r\nTapi Bakri punya cerita lain 😎⚡\r\nPelan tapi pasti, Bakri mulai bangkit dan membalikkan keadaan dengan comeback yang benar-benar gila 🤯🚀\r\nDari tertinggal jauh, akhirnya Bakri sukses menutup pertandingan dengan skor 11-8 🏆👏\r\n💥 Salah satu comeback paling epic sejauh ini!\r\nSelamat buat Bakri atas mental baja dan permainan luar biasanya 👑🔥'),
(95, 1, 11, 1, 13, 6, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(96, 1, 11, 1, 14, 5, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(97, 1, 11, 1, 15, 4, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(98, 1, 11, 1, 1, 3, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(99, 1, 11, 1, 16, 2, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(100, 1, 12, 1, 18, 9, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(101, 1, 12, 1, 10, 8, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(102, 1, 12, 1, 11, 7, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(103, 1, 12, 1, 12, 6, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(105, 1, 12, 1, 13, 4, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(106, 1, 12, 1, 14, 3, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(107, 1, 12, 1, 15, 2, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(108, 1, 12, 1, 16, 17, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(109, 1, 13, 1, 18, 8, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(110, 1, 13, 1, 9, 7, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(111, 1, 13, 1, 10, 6, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(112, 1, 13, 1, 11, 5, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(113, 1, 13, 1, 12, 4, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(115, 1, 13, 1, 13, 2, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(116, 1, 13, 1, 15, 17, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(117, 1, 13, 1, 1, 16, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(118, 1, 14, 1, 18, 7, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(119, 1, 14, 1, 8, 6, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(120, 1, 14, 1, 9, 5, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(121, 1, 14, 1, 10, 4, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(122, 1, 14, 1, 11, 3, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(123, 1, 14, 1, 12, 2, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(124, 1, 14, 1, 13, 17, 7, 11, 17, 'completed', NULL, '2026-05-07 08:54:41', NULL, NULL),
(125, 1, 14, 1, 14, 16, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(126, 1, 14, 1, 15, 1, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(127, 1, 15, 1, 18, 6, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(128, 1, 15, 1, 7, 5, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(129, 1, 15, 1, 8, 4, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(130, 1, 15, 1, 9, 3, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(131, 1, 15, 1, 10, 2, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(132, 1, 15, 1, 12, 17, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(134, 1, 15, 1, 13, 1, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(135, 1, 15, 1, 14, 15, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(136, 1, 16, 1, 18, 5, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(137, 1, 16, 1, 6, 4, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(138, 1, 16, 1, 7, 3, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(139, 1, 16, 1, 8, 2, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(140, 1, 16, 1, 10, 17, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(141, 1, 16, 1, 11, 16, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(142, 1, 16, 1, 12, 1, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(144, 1, 16, 1, 13, 14, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(145, 1, 17, 1, 18, 4, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(146, 1, 17, 1, 5, 3, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(147, 1, 17, 1, 6, 2, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(148, 1, 17, 1, 8, 17, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(149, 1, 17, 1, 9, 16, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(150, 1, 17, 1, 10, 1, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(151, 1, 17, 1, 11, 15, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(152, 1, 17, 1, 12, 14, 11, 8, 12, 'completed', NULL, '2026-05-07 08:54:41', NULL, NULL),
(154, 1, 18, 1, 18, 3, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(155, 1, 18, 1, 4, 2, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(156, 1, 18, 1, 6, 17, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(157, 1, 18, 1, 7, 16, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(158, 1, 18, 1, 8, 1, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(159, 1, 18, 1, 9, 15, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(160, 1, 18, 1, 10, 14, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(161, 1, 18, 1, 11, 13, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(163, 1, 19, 1, 18, 2, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(164, 1, 19, 1, 4, 17, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(165, 1, 19, 1, 5, 16, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(166, 1, 19, 1, 6, 1, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(167, 1, 19, 1, 7, 15, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(168, 1, 19, 1, 8, 14, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(169, 1, 19, 1, 9, 13, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL),
(171, 1, 19, 1, 11, 12, NULL, NULL, NULL, 'scheduled', NULL, '2026-05-07 08:54:41', NULL, NULL);

-- --------------------------------------------------------

--
-- Struktur dari tabel `players`
--

CREATE TABLE IF NOT EXISTS `players` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `players`
--

INSERT INTO `players` (`id`, `name`, `created_at`, `photo`) VALUES
(1, 'Opick', '2026-05-07 07:21:54', '5e6ccd5525085ec37c7036d2fddbb9a3.png'),
(2, 'Ade', '2026-05-07 07:21:54', '08e7ab0ec29ed69ef47f90b31034e25f.png'),
(3, 'Ahmad', '2026-05-07 07:21:54', '3ac37114ab45cecf170c2cb3520689c8.png'),
(4, 'Alam', '2026-05-07 07:21:54', '7a24e19c6984b2b15153b6a082509c2f.jpeg'),
(5, 'Anto', '2026-05-07 07:21:54', 'e4b3d319ec7b1674c0e6f89a0ad8df89.png'),
(6, 'Appank', '2026-05-07 07:21:54', 'cb785fdfe06b652ca00f1836a7d16b51.jpeg'),
(7, 'Badu', '2026-05-07 07:21:54', '3be59d49da62bd93cdac0127ae9d9c67.png'),
(8, 'Bakri', '2026-05-07 07:21:54', '726797372b2980560434771a37687d62.png'),
(9, 'Brewok', '2026-05-07 07:21:54', 'e66108ff60a9567efa26ea668c441c7c.png'),
(10, 'Erwin', '2026-05-07 07:21:54', '6cc45e54a02bf6bbb151b6aa4e793d60.png'),
(11, 'Fahyu', '2026-05-07 07:21:54', '21ba94d13deacf6a635cc39ee909b3b5.png'),
(12, 'Farid', '2026-05-07 07:21:54', 'd297ab18cb949d827eb6fd8921833852.png'),
(13, 'Ilham', '2026-05-07 07:21:54', 'b9f237cc23298f4231df4fefda229b2d.png'),
(14, 'Ippank', '2026-05-07 07:21:54', '9b6ae9df4ea8df5bd96fa9819c5cf337.png'),
(15, 'Nursam', '2026-05-07 07:21:54', '4bb6949070456b3e19d2a57855b40338.png'),
(16, 'Padi', '2026-05-07 07:21:54', 'e1783bd8e232795d8f79b6dbb3180a8c.png'),
(17, 'Rafli', '2026-05-07 07:21:54', 'b0edb235526a869dc1fdd1d7d85c6eef.png'),
(18, 'Accank', '2026-05-07 07:21:54', 'b9959d8eb70739a799bedbfb0748ad2e.png');

-- --------------------------------------------------------

--
-- Struktur dari tabel `seasons`
--

CREATE TABLE IF NOT EXISTS `seasons` (
  `id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `league_type` enum('half','full') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'half',
  `status` enum('draft','active','completed') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `seasons`
--

INSERT INTO `seasons` (`id`, `name`, `league_type`, `status`, `created_at`) VALUES
(1, '2026', 'half', 'active', '2026-05-07 07:21:54');

-- --------------------------------------------------------

--
-- Struktur dari tabel `season_players`
--

CREATE TABLE IF NOT EXISTS `season_players` (
  `id` int NOT NULL,
  `season_id` int NOT NULL,
  `player_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `season_players`
--

INSERT INTO `season_players` (`id`, `season_id`, `player_id`) VALUES
(17, 1, 1),
(2, 1, 2),
(3, 1, 3),
(4, 1, 4),
(5, 1, 5),
(6, 1, 6),
(7, 1, 7),
(8, 1, 8),
(9, 1, 9),
(10, 1, 10),
(11, 1, 11),
(12, 1, 12),
(14, 1, 13),
(15, 1, 14),
(16, 1, 15),
(18, 1, 16),
(19, 1, 17),
(1, 1, 18);
