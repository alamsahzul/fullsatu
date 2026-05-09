<?php
require 'config/db.php';
$stmt = $pdo->query("SELECT id, name FROM seasons WHERE format='hybrid' ORDER BY id DESC LIMIT 1");
$season = $stmt->fetch(PDO::FETCH_ASSOC);
if ($season) {
    echo "Checking Season: {$season['name']} (ID: {$season['id']})\n";
    $stmt = $pdo->prepare("SELECT id, player1_id, p1_partner_id, player2_id, p2_partner_id FROM matches WHERE season_id = ? LIMIT 10");
    $stmt->execute([$season['id']]);
    print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
} else {
    echo "No Hybrid season found.\n";
}
