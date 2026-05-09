<?php
require 'config/db.php';
$seasonId = 1;
$sym = $pdo->prepare("
    SELECT m1.id as id1, m1.player1_id, m1.player2_id, m2.id as id2
    FROM matches m1
    JOIN matches m2 ON m1.player1_id = m2.player2_id AND m1.player2_id = m2.player1_id
    WHERE m1.season_id = ? AND m2.season_id = ? AND m1.id < m2.id
");
$sym->execute([$seasonId, $seasonId]);
print_r($sym->fetchAll(PDO::FETCH_ASSOC));
