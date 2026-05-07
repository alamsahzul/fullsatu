CREATE DATABASE IF NOT EXISTS fullsatu_league CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE fullsatu_league;

CREATE TABLE players (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE seasons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  league_type ENUM('half','full') NOT NULL DEFAULT 'half',
  status ENUM('draft','active','completed') NOT NULL DEFAULT 'draft',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE season_players (
  id INT AUTO_INCREMENT PRIMARY KEY,
  season_id INT NOT NULL,
  player_id INT NOT NULL,
  UNIQUE KEY unique_season_player (season_id, player_id),
  FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE,
  FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
);

CREATE TABLE matches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  season_id INT NOT NULL,
  round_number INT NOT NULL,
  leg_number INT NOT NULL DEFAULT 1,
  player1_id INT NOT NULL,
  player2_id INT NOT NULL,
  player1_score INT DEFAULT NULL,
  player2_score INT DEFAULT NULL,
  winner_id INT DEFAULT NULL,
  status ENUM('scheduled','completed') NOT NULL DEFAULT 'scheduled',
  scheduled_at DATETIME DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (season_id) REFERENCES seasons(id) ON DELETE CASCADE,
  FOREIGN KEY (player1_id) REFERENCES players(id),
  FOREIGN KEY (player2_id) REFERENCES players(id),
  FOREIGN KEY (winner_id) REFERENCES players(id)
);

INSERT INTO seasons (name, league_type, status) VALUES ('2026', 'half', 'active');
INSERT INTO players (name) VALUES
('Opick'),('Ade'),('Ahmad'),('Alam'),('Anto'),('Appank'),('Badu'),('Bakri'),('Brewok'),('Erwin'),('Fahyu'),('Farid'),('Ilham'),('Irfan'),('Nursam'),('Padi'),('Rafli'),('Accank'),('Harfan');
