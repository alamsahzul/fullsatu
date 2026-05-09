<?php
require 'includes/auth.php';
require '../config/db.php';
require '../includes/functions.php';

$seasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;
if ($seasonId === 0) {
    header('Location: seasons'); exit;
}

$stmt = $pdo->prepare("SELECT * FROM seasons WHERE id = ?");
$stmt->execute([$seasonId]);
$season = $stmt->fetch();

if (!$season) {
    header('Location: seasons'); exit;
}

// LOCK: For hybrid, block schedule generation if cup phase has started
if ($season['format'] === 'hybrid') {
    $cupCheck = $pdo->prepare("SELECT COUNT(*) FROM tournament_brackets WHERE season_id = ?");
    $cupCheck->execute([$seasonId]);
    if ($cupCheck->fetchColumn() > 0) {
        header("Location: season_players?season_id=$seasonId&cup_locked=1"); exit;
    }
}

// Logic to generate schedule
// 1. Get participants grouped by their group_name
$stmt = $pdo->prepare("SELECT player_id, partner_id, group_name FROM season_players WHERE season_id = ?");
$stmt->execute([$seasonId]);
$allParticipants = $stmt->fetchAll();

$groups = [];
foreach ($allParticipants as $p) {
    // Store as object to keep partner info
    $groups[$p['group_name']][] = [
        'player_id' => $p['player_id'],
        'partner_id' => $p['partner_id']
    ];
}

// 2. Clear existing matches that are NOT completed
$pdo->prepare("DELETE FROM matches WHERE season_id = ? AND status != 'completed'")->execute([$seasonId]);

// 3. Generate Round Robin for each group
foreach ($groups as $groupName => $teamsList) {
    $teams = $teamsList;
    if (count($teams) < 2) continue;

    // If odd number of teams, add a 'bye' (null)
    if (count($teams) % 2 != 0) {
        $teams[] = null;
    }

    $numTeams = count($teams);
    $numDays = $numTeams - 1;
    $halfSize = $numTeams / 2;

    $rounds = [];
    for ($day = 0; $day < $numDays; $day++) {
        $rounds[$day] = [];
        for ($i = 0; $i < $halfSize; $i++) {
            $rounds[$day][] = [$teams[$i], $teams[$numTeams - 1 - $i]];
        }
        // Rotate teams (keep the first one fixed)
        $teams = array_merge([$teams[0]], array_slice($teams, -1), array_slice($teams, 1, -1));
    }

    // Insert into DB
    $isFull = ($season['league_type'] === 'full');
    foreach ($rounds as $dayIndex => $matches) {
        $roundNum = $dayIndex + 1;
        foreach ($matches as $m) {
            if ($m[0] === null || $m[1] === null) continue;
            
            $p1 = $m[0]['player_id'];
            $pa1 = $m[0]['partner_id'];
            $p2 = $m[1]['player_id'];
            $pa2 = $m[1]['partner_id'];

            // Check if this pair already has ANY match (completed or otherwise)
            // This is to prevent re-generating matches that are already completed
            $checkStmt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE season_id = ? 
                AND ((player1_id = ? AND player2_id = ?) OR (player1_id = ? AND player2_id = ?))");
            $checkStmt->execute([$seasonId, $p1, $p2, $p2, $p1]);
            $exists = $checkStmt->fetchColumn() > 0;

            if (!$exists) {
                // First Leg
                $stmt = $pdo->prepare("INSERT INTO matches (season_id, player1_id, p1_partner_id, player2_id, p2_partner_id, group_name, round_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
                $stmt->execute([$seasonId, $p1, $pa1, $p2, $pa2, $groupName, $roundNum]);
            }

            // Second Leg (if Full League)
            if ($isFull) {
                // For second leg, we also check if it exists (usually it won't if first leg didn't, but good for safety)
                $checkFull = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE season_id = ? AND player1_id = ? AND player2_id = ?");
                $checkFull->execute([$seasonId, $p2, $p1]);
                if ($checkFull->fetchColumn() == 0) {
                    $stmt = $pdo->prepare("INSERT INTO matches (season_id, player1_id, p1_partner_id, player2_id, p2_partner_id, group_name, round_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
                    $stmt->execute([$seasonId, $p2, $pa2, $p1, $pa1, $groupName, $roundNum + 100]); // Offset for second leg
                }
            }
        }
    }
}

// 4. Redirect directly to Input Skor (matches.php)
header("Location: matches?season_id=$seasonId&generated=1");
exit;
