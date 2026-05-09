<?php
require 'config/db.php';
$stmt = $pdo->query("SELECT id, name, league_type FROM seasons WHERE format='hybrid'");
while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "Season: {$row['name']} (ID: {$row['id']}) | Type: {$row['league_type']}\n";
    $c = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE season_id = ?");
    $c->execute([$row['id']]);
    echo "Match Count: " . $c->fetchColumn() . "\n";
}
