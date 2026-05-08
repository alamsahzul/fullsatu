<?php
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function base_url($path = '') {
    if (defined('BASE_URL')) {
        return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
    }
    return '/' . ltrim($path, '/');
}

function getCurrentSeason($pdo) {
    $stmt = $pdo->query("SELECT * FROM seasons ORDER BY id DESC LIMIT 1");
    return $stmt->fetch();
}

function calculateStandings($pdo, $seasonId) {
    $stmt = $pdo->prepare("SELECT p.id, p.name, p.photo FROM season_players sp JOIN players p ON p.id = sp.player_id WHERE sp.season_id = ? ORDER BY p.name ASC");
    $stmt->execute([$seasonId]);
    $players = $stmt->fetchAll();

    $table = [];
    foreach ($players as $p) {
        $table[$p['id']] = [
            'id' => $p['id'],
            'name' => $p['name'],
            'photo' => $p['photo'],
            'main' => 0,
            'w' => 0,
            'l' => 0,
            'pf' => 0,
            'pa' => 0,
            'diff' => 0,
            'pts' => 0,
        ];
    }

    $stmt = $pdo->prepare("SELECT * FROM matches WHERE season_id = ? AND status = 'completed'");
    $stmt->execute([$seasonId]);
    $matches = $stmt->fetchAll();

    foreach ($matches as $m) {
        $p1 = $m['player1_id'];
        $p2 = $m['player2_id'];
        if (!isset($table[$p1]) || !isset($table[$p2])) continue;

        $s1 = (int)$m['player1_score'];
        $s2 = (int)$m['player2_score'];
        $winner = (int)$m['winner_id'];

        $table[$p1]['main']++;
        $table[$p2]['main']++;
        $table[$p1]['pf'] += $s1;
        $table[$p1]['pa'] += $s2;
        $table[$p2]['pf'] += $s2;
        $table[$p2]['pa'] += $s1;

        if ($winner === $p1) {
            $table[$p1]['w']++;
            $table[$p1]['pts']++;
            $table[$p2]['l']++;
        } elseif ($winner === $p2) {
            $table[$p2]['w']++;
            $table[$p2]['pts']++;
            $table[$p1]['l']++;
        }
    }

    foreach ($table as &$row) {
        $row['diff'] = $row['pf'] - $row['pa'];
    }

    usort($table, function($a, $b) {
        if ($a['pts'] !== $b['pts']) return $b['pts'] <=> $a['pts'];
        if ($a['diff'] !== $b['diff']) return $b['diff'] <=> $a['diff'];
        if ($a['pf'] !== $b['pf']) return $b['pf'] <=> $a['pf'];
        return strcmp($a['name'], $b['name']);
    });

    return $table;
}

function generateRoundRobin($playerIds, $leagueType = 'half') {
    if (count($playerIds) % 2 === 1) {
        $playerIds[] = null;
    }

    $n = count($playerIds);
    $rounds = $n - 1;
    $half = $n / 2;
    $schedule = [];
    $players = array_values($playerIds);

    for ($round = 1; $round <= $rounds; $round++) {
        for ($i = 0; $i < $half; $i++) {
            $p1 = $players[$i];
            $p2 = $players[$n - 1 - $i];
            if ($p1 !== null && $p2 !== null) {
                $schedule[] = [
                    'round_number' => $round,
                    'leg_number' => 1,
                    'player1_id' => $p1,
                    'player2_id' => $p2,
                ];
            }
        }
        $fixed = array_shift($players);
        $last = array_pop($players);
        array_unshift($players, $fixed);
        array_splice($players, 1, 0, [$last]);
    }

    if ($leagueType === 'full') {
        $firstLeg = $schedule;
        foreach ($firstLeg as $m) {
            $schedule[] = [
                'round_number' => $m['round_number'] + $rounds,
                'leg_number' => 2,
                'player1_id' => $m['player2_id'],
                'player2_id' => $m['player1_id'],
            ];
        }
    }

    return $schedule;
}
function syncSeasonMatches($pdo, $seasonId) {
    // Get season info
    $stmt = $pdo->prepare("SELECT * FROM seasons WHERE id = ?");
    $stmt->execute([$seasonId]);
    $season = $stmt->fetch();
    if (!$season) return;

    // Get all current players in season
    $stmt = $pdo->prepare("SELECT player_id FROM season_players WHERE season_id = ?");
    $stmt->execute([$seasonId]);
    $players = array_column($stmt->fetchAll(), 'player_id');
    if (count($players) < 2) return;

    // Get current matches to avoid duplicates
    $stmt = $pdo->prepare("SELECT player1_id, player2_id, leg_number FROM matches WHERE season_id = ?");
    $stmt->execute([$seasonId]);
    $existing = $stmt->fetchAll();
    
    $existingPairs = [];
    foreach ($existing as $m) {
        $key = $m['player1_id'] . '-' . $m['player2_id'] . '-' . $m['leg_number'];
        $existingPairs[$key] = true;
    }

    // Find max round number to append new matches
    $stmt = $pdo->prepare("SELECT MAX(round_number) FROM matches WHERE season_id = ?");
    $stmt->execute([$seasonId]);
    $maxRound = (int)$stmt->fetchColumn();
    $newRound = $maxRound + 1;

    // Generate missing matches
    $newMatchesCount = 0;
    foreach ($players as $p1) {
        foreach ($players as $p2) {
            if ($p1 == $p2) continue;

            // Half league: only one match per pair (canonical order)
            if ($season['league_type'] === 'half') {
                $ids = [$p1, $p2];
                sort($ids);
                $key = $ids[0] . '-' . $ids[1] . '-1';
                if (!isset($existingPairs[$key])) {
                    $stmt = $pdo->prepare("INSERT INTO matches (season_id, round_number, leg_number, player1_id, player2_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$seasonId, $newRound, 1, $ids[0], $ids[1]]);
                    $existingPairs[$key] = true;
                    $newMatchesCount++;
                }
            } 
            // Full league: home and away
            else {
                // Check Leg 1 (p1 vs p2)
                $key1 = $p1 . '-' . $p2 . '-1';
                $key1_rev = $p2 . '-' . $p1 . '-1';
                if (!isset($existingPairs[$key1]) && !isset($existingPairs[$key1_rev])) {
                    $stmt = $pdo->prepare("INSERT INTO matches (season_id, round_number, leg_number, player1_id, player2_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$seasonId, $newRound, 1, $p1, $p2]);
                    $existingPairs[$key1] = true;
                    $newMatchesCount++;
                }

                // Check Leg 2 (p2 vs p1)
                $key2 = $p2 . '-' . $p1 . '-2';
                $key2_rev = $p1 . '-' . $p2 . '-2';
                if (!isset($existingPairs[$key2]) && !isset($existingPairs[$key2_rev])) {
                    $stmt = $pdo->prepare("INSERT INTO matches (season_id, round_number, leg_number, player1_id, player2_id) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$seasonId, $newRound, 2, $p2, $p1]);
                    $existingPairs[$key2] = true;
                    $newMatchesCount++;
                }
            }
        }
    }
    return $newMatchesCount;
}
function getAllSeasons($pdo) {
    $stmt = $pdo->query("SELECT * FROM seasons ORDER BY id DESC");
    return $stmt->fetchAll();
}

function getPlayerRank($name, $standings) {
    foreach ($standings as $i => $row) {
        if ($row['name'] === $name) return $i + 1;
    }
    return '-';
}
?>
