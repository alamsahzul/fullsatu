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

function getCurrentLigaSeason($pdo) {
    $stmt = $pdo->query("SELECT * FROM seasons WHERE format = 'league' ORDER BY id DESC LIMIT 1");
    return $stmt->fetch();
}

function getCurrentKnockoutSeason($pdo) {
    $stmt = $pdo->query("SELECT * FROM seasons WHERE format = 'cup' ORDER BY id DESC LIMIT 1");
    return $stmt->fetch();
}

function getCurrentHybridSeason($pdo) {
    $stmt = $pdo->query("SELECT * FROM seasons WHERE format = 'hybrid' ORDER BY id DESC LIMIT 1");
    return $stmt->fetch();
}

function getAllLigaSeasons($pdo) {
    return $pdo->query("SELECT * FROM seasons WHERE format = 'league' ORDER BY id DESC")->fetchAll();
}

function getAllKnockoutSeasons($pdo) {
    return $pdo->query("SELECT * FROM seasons WHERE format = 'cup' ORDER BY id DESC")->fetchAll();
}

function getAllHybridSeasons($pdo) {
    return $pdo->query("SELECT * FROM seasons WHERE format = 'hybrid' ORDER BY id DESC")->fetchAll();
}

function calculateStandings($pdo, $seasonId, $limitParticipantIds = null) {
    // Get season info
    $stmt = $pdo->prepare("SELECT format, category FROM seasons WHERE id = ?");
    $stmt->execute([$seasonId]);
    $season = $stmt->fetch();
    $isDouble = ($season['category'] === 'double');

    // Get participants
    $sql = "SELECT sp.*, p1.name AS p1_name, p1.photo AS photo1, p2.name AS p2_name, p2.photo AS photo2 
            FROM season_players sp 
            JOIN players p1 ON p1.id = sp.player_id 
            LEFT JOIN players p2 ON p2.id = sp.partner_id 
            WHERE sp.season_id = ?";
    $params = [$seasonId];
    if ($limitParticipantIds !== null && !empty($limitParticipantIds)) {
        $placeholders = implode(',', array_fill(0, count($limitParticipantIds), '?'));
        $sql .= " AND sp.id IN ($placeholders)";
        $params = array_merge($params, $limitParticipantIds);
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $participants = $stmt->fetchAll();

    $table = [];
    foreach ($participants as $p) {
        $table[$p['id']] = [
            'id' => $p['id'],
            'name' => $p['p1_name'],
            'photo' => $p['photo1'],
            'name2' => $p['p2_name'],
            'photo2' => $p['photo2'],
            'main' => 0, 'w' => 0, 'l' => 0, 'pf' => 0, 'pa' => 0, 'diff' => 0, 'pts' => 0,
        ];
    }

    // Get completed matches
    $stmt = $pdo->prepare("SELECT * FROM matches WHERE season_id = ? AND status = 'completed'");
    $stmt->execute([$seasonId]);
    $matches = $stmt->fetchAll();

    foreach ($matches as $m) {
        // We need to find which Participant (sp_id) this match belongs to
        // Match stores player1_id and p1_partner_id
        // So we look for sp entry where (player_id=p1 AND partner_id=p1p) OR (player_id=p1p AND partner_id=p1)
        
        $sp1_id = null;
        $sp2_id = null;
        
        foreach ($table as $sp_id => $row) {
            // Find participant 1
            $stmt_find = $pdo->prepare("SELECT id FROM season_players WHERE season_id=? AND player_id=? AND (partner_id=? OR (partner_id IS NULL AND ? IS NULL))");
            $stmt_find->execute([$seasonId, $m['player1_id'], $m['p1_partner_id'], $m['p1_partner_id']]);
            $sp1_id = $stmt_find->fetchColumn();

            $stmt_find->execute([$seasonId, $m['player2_id'], $m['p2_partner_id'], $m['p2_partner_id']]);
            $sp2_id = $stmt_find->fetchColumn();
            break; // Found
        }

        if (!$sp1_id || !$sp2_id) continue;
        if (!isset($table[$sp1_id]) || !isset($table[$sp2_id])) continue;

        $s1 = (int)$m['player1_score'];
        $s2 = (int)$m['player2_score'];

        $table[$sp1_id]['main']++;
        $table[$sp2_id]['main']++;
        $table[$sp1_id]['pf'] += $s1;
        $table[$sp1_id]['pa'] += $s2;
        $table[$sp2_id]['pf'] += $s2;
        $table[$sp2_id]['pa'] += $s1;

        if ($m['winner_id'] == $m['player1_id']) {
            $table[$sp1_id]['w']++;
            $table[$sp1_id]['pts']++;
            $table[$sp2_id]['l']++;
        } else {
            $table[$sp2_id]['w']++;
            $table[$sp2_id]['pts']++;
            $table[$sp1_id]['l']++;
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
    // Sort league/hybrid at the top, then newest first
    $stmt = $pdo->query("SELECT * FROM seasons ORDER BY CASE WHEN format = 'cup' THEN 1 ELSE 0 END ASC, id DESC");
    return $stmt->fetchAll();
}

function getPlayerRank($name, $standings) {
    foreach ($standings as $i => $row) {
        if ($row['name'] === $name) return $i + 1;
    }
    return '-';
}
?>
