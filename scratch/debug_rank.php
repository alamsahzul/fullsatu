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
foreach($players as $p) {
    echo $p['id'] . " - " . $p['name'] . "\n";
}
