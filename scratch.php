<?php
require 'config/db.php';

$s = $pdo->query("SELECT * FROM seasons WHERE format='cup' AND category='double' ORDER BY id DESC LIMIT 1")->fetch();
if(!$s) die('No cup double');
echo "Season: " . $s['name'] . "\n";

$sp = $pdo->query("SELECT sp.*, p1.name as p1n, p2.name as p2n FROM season_players sp JOIN players p1 ON p1.id=sp.player_id LEFT JOIN players p2 ON p2.id=sp.partner_id WHERE sp.season_id=" . $s['id'])->fetchAll(PDO::FETCH_ASSOC);
echo "Season Players:\n";
print_r($sp);

$tb = $pdo->query("SELECT b.*, p1.name as p1n, p2.name as p2n FROM tournament_brackets b LEFT JOIN players p1 ON p1.id=b.player1_id LEFT JOIN players p2 ON p2.id=b.player2_id WHERE b.season_id=" . $s['id'])->fetchAll(PDO::FETCH_ASSOC);
echo "Tournament Brackets:\n";
print_r($tb);
