<?php
require 'config/db.php';

$s = $pdo->query("SELECT * FROM seasons WHERE format='cup' AND category='double' ORDER BY id DESC LIMIT 1")->fetch();
if(!$s) die('No cup double');
$seasonId = $s['id'];

// Get all participants
$stmt = $pdo->prepare("SELECT sp.*, p1.name AS p1_name, p2.name AS p2_name 
                       FROM season_players sp 
                       JOIN players p1 ON p1.id = sp.player_id 
                       LEFT JOIN players p2 ON p2.id = sp.partner_id 
                       WHERE sp.season_id = ?");
$stmt->execute([$seasonId]);
$participants = $stmt->fetchAll();

$pCount = count($participants);
function getNextPowerOfTwo($n) {
    if ($n < 2) return 2;
    $p = 1;
    while ($p < $n) $p *= 2;
    return $p;
}
$size = getNextPowerOfTwo($pCount);

$rounds = [];
if ($size >= 16) $rounds[] = 'Round of 16';
if ($size >= 8)  $rounds[] = 'Quarterfinal';
if ($size >= 4)  $rounds[] = 'Semifinal';
$rounds[] = 'Final';

$matchCount = $size / 2;
$bracketsData = []; 
for ($r = 0; $r < count($rounds); $r++) {
    $mCount = $matchCount / pow(2, $r);
    for ($i = 0; $i < $mCount; $i++) {
        // dummy insert
        $bracketsData[$rounds[$r]][$i] = rand(100, 999);
    }
}

$seedings = [
    2 => [[0,1]],
    4 => [[0,3], [1,2]],
    8 => [[0,7], [3,4], [1,6], [2,5]],
    16 => [[0,15], [7,8], [3,12], [4,11], [1,14], [6,9], [2,13], [5,10]]
];
$seeding = $seedings[$size];
$firstRound = $rounds[0];

echo "Simulating generation for size $size (Participants: $pCount)\n";
foreach ($seeding as $i => $pair) {
    $p1 = isset($participants[$pair[0]]) ? $participants[$pair[0]] : null;
    $p2 = isset($participants[$pair[1]]) ? $participants[$pair[1]] : null;
    
    $p1_id = $p1['player_id'] ?? null; $p1_partner = $p1['partner_id'] ?? null;
    $p2_id = $p2['player_id'] ?? null; $p2_partner = $p2['partner_id'] ?? null;
    
    echo "Match $i:\n";
    echo "  Team 1: Player=$p1_id, Partner=$p1_partner\n";
    echo "  Team 2: Player=$p2_id, Partner=$p2_partner\n";
}
