<?php
require 'config/db.php';
$seasonId = 9;
// Find pending matches where a completed match already exists for the same pair
$stmt = $pdo->prepare("
    SELECT m1.id as pending_id
    FROM matches m1
    JOIN matches m2 ON (
        (m1.player1_id = m2.player1_id AND m1.player2_id = m2.player2_id)
        OR
        (m1.player1_id = m2.player2_id AND m1.player2_id = m2.player1_id)
    )
    WHERE m1.season_id = ? AND m2.season_id = ? 
    AND m1.status = 'pending' AND m2.status = 'completed'
");
$stmt->execute([$seasonId, $seasonId]);
$toDelete = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($toDelete as $row) {
    $pdo->prepare("DELETE FROM matches WHERE id = ?")->execute([$row['pending_id']]);
}
echo "Cleaned up " . count($toDelete) . " redundant pending matches for Season 9.\n";
