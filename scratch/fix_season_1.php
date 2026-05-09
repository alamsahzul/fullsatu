<?php
require 'config/db.php';
$seasonId = 1;
// Clean up
$pdo->prepare("DELETE FROM matches WHERE season_id = ? AND status != 'completed'")->execute([$seasonId]);
echo "Cleaned up non-completed matches for Season 1.\n";

// Regenerate using the logic from generate.php
$stmt = $pdo->prepare("SELECT * FROM seasons WHERE id = ?");
$stmt->execute([$seasonId]);
$season = $stmt->fetch();

$stmt = $pdo->prepare("SELECT player_id, partner_id, group_name FROM season_players WHERE season_id = ?");
$stmt->execute([$seasonId]);
$allParticipants = $stmt->fetchAll();

$groups = [];
foreach ($allParticipants as $p) {
    $groups[$p['group_name']][] = ['p1' => $p['player_id'], 'p2' => $p['partner_id']];
}

foreach ($groups as $groupName => $participants) {
    $teams = $participants;
    if (count($teams) < 2) continue;
    if (count($teams) % 2 != 0) $teams[] = null;

    $numTeams = count($teams);
    $numDays = $numTeams - 1;
    $halfSize = $numTeams / 2;

    $rounds = [];
    for ($day = 0; $day < $numDays; $day++) {
        for ($i = 0; $i < $halfSize; $i++) {
            $rounds[$day][] = [$teams[$i], $teams[$numTeams - 1 - $i]];
        }
        $teams = array_merge([$teams[0]], array_slice($teams, -1), array_slice($teams, 1, -1));
    }

    $isFull = ($season['league_type'] === 'full');
    foreach ($rounds as $dayIndex => $matches) {
        $roundNum = $dayIndex + 1;
        foreach ($matches as $m) {
            if ($m[0] === null || $m[1] === null) continue;
            
            // Insert First Leg
            $stmt = $pdo->prepare("INSERT INTO matches (season_id, player1_id, p1_partner_id, player2_id, p2_partner_id, group_name, round_number, status) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$seasonId, $m[0]['p1'], $m[0]['p2'], $m[1]['p1'], $m[1]['p2'], $groupName, $roundNum]);

            if ($isFull) {
                $stmt->execute([$seasonId, $m[1]['p1'], $m[1]['p2'], $m[0]['p1'], $m[0]['p2'], $groupName, $roundNum + 100]);
            }
        }
    }
}
echo "Regenerated matches for Season 1 (Type: {$season['league_type']}).\n";
