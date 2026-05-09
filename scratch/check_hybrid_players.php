<?php
require 'config/db.php';
$sid = 9;
$stmt = $pdo->prepare("SELECT COUNT(*) FROM season_players WHERE season_id=?");
$stmt->execute([$sid]);
echo "Player Count: " . $stmt->fetchColumn() . "\n";

$stmt = $pdo->prepare("SELECT group_name, COUNT(*) as c FROM season_players WHERE season_id=? GROUP BY group_name");
$stmt->execute([$sid]);
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
