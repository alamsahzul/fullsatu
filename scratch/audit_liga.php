<?php
require 'config/db.php';
$stmt = $pdo->query("SELECT id, name, format, league_type FROM seasons WHERE name LIKE '%2026%'");
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$row['id']} | Name: {$row['name']} | Format: {$row['format']} | Type: {$row['league_type']}\n";
    $c = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE season_id = ?");
    $c->execute([$row['id']]);
    echo "Match Count: " . $c->fetchColumn() . "\n";
    
    // Check for duplicates (A vs B and B vs A)
    $dupes = $pdo->prepare("
        SELECT m1.player1_id, m1.player2_id, COUNT(*) as count 
        FROM matches m1 
        WHERE m1.season_id = ? 
        GROUP BY m1.player1_id, m1.player2_id 
        HAVING count > 1
    ");
    $dupes->execute([$row['id']]);
    $dRows = $dupes->fetchAll();
    if($dRows) {
        echo "Duplicates found!\n";
        print_r($dRows);
    }
    
    // Check for symmetric matches (A vs B AND B vs A)
    $sym = $pdo->prepare("
        SELECT m1.player1_id, m1.player2_id
        FROM matches m1
        JOIN matches m2 ON m1.player1_id = m2.player2_id AND m1.player2_id = m2.player1_id
        WHERE m1.season_id = ? AND m2.season_id = ? AND m1.id < m2.id
    ");
    $sym->execute([$row['id'], $row['id']]);
    $sRows = $sym->fetchAll();
    if($sRows) {
        echo "Symmetric matches (Home/Away) found: " . count($sRows) . "\n";
    }
}
