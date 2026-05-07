<?php
function e($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function getCurrentSeason($pdo) {
    $stmt = $pdo->query("SELECT * FROM seasons ORDER BY id DESC LIMIT 1");
    return $stmt->fetch();
}

function calculateStandings($pdo, $seasonId) {
    $stmt = $pdo->prepare("SELECT p.id, p.name FROM season_players sp JOIN players p ON p.id = sp.player_id WHERE sp.season_id = ? ORDER BY p.name ASC");
    $stmt->execute([$seasonId]);
    $players = $stmt->fetchAll();

    $table = [];
    foreach ($players as $p) {
        $table[$p['id']] = [
            'id' => $p['id'],
            'name' => $p['name'],
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
?>
