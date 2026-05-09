<?php
require 'config/db.php';
$stmt = $pdo->query("
    SELECT p.*,
        (SELECT COUNT(*) FROM matches m 
         WHERE (m.player1_id = p.id OR m.player2_id = p.id OR m.p1_partner_id = p.id OR m.p2_partner_id = p.id) 
         AND m.status = 'completed') as total_matches,
        
        (SELECT COUNT(*) FROM matches m 
         WHERE ((m.winner_id = m.player1_id AND (m.player1_id = p.id OR m.p1_partner_id = p.id)) OR
                (m.winner_id = m.player2_id AND (m.player2_id = p.id OR m.p2_partner_id = p.id)))
         AND m.status = 'completed') as wins,
         
        (SELECT COUNT(*) FROM matches m 
         WHERE ((m.winner_id = m.player2_id AND (m.player1_id = p.id OR m.p1_partner_id = p.id)) OR
                (m.winner_id = m.player1_id AND (m.player2_id = p.id OR m.p2_partner_id = p.id)))
         AND m.status = 'completed') as losses
    FROM players p
    ORDER BY p.id ASC
");
$players = $stmt->fetchAll();

foreach ($players as &$p) {
    $p['points'] = ($p['wins'] * 2) - ($p['losses'] * 1);
    $p['win_rate'] = $p['total_matches'] > 0 ? round(($p['wins'] / $p['total_matches']) * 100) : 0;
}
unset($p); // Clean up reference

usort($players, function($a, $b) {
    if ($a['points'] !== $b['points']) return $b['points'] <=> $a['points'];
    return $b['wins'] <=> $a['wins'];
});

echo "TOTAL PLAYERS: " . count($players) . "\n";
foreach($players as $p) {
    echo "ID: " . $p['id'] . " | Name: " . $p['name'] . " | Pts: " . $p['points'] . "\n";
}
