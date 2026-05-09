<?php
require 'config/db.php';

echo "Starting partner sync for matches...\n";

// Get all matches where partners might be missing
$stmt = $pdo->query("SELECT id, season_id, player1_id, player2_id FROM matches WHERE p1_partner_id IS NULL OR p2_partner_id IS NULL");
$matches = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($matches) . " matches to update.\n";

// Cache season_players to avoid repeated queries
$stmt = $pdo->query("SELECT season_id, player_id, partner_id FROM season_players");
$spData = $stmt->fetchAll(PDO::FETCH_ASSOC);
$partners = [];
foreach ($spData as $sp) {
    $partners[$sp['season_id']][$sp['player_id']] = $sp['partner_id'];
}

$updatedCount = 0;
foreach ($matches as $m) {
    $sid = $m['season_id'];
    $p1 = $m['player1_id'];
    $p2 = $m['player2_id'];
    
    $pa1 = $partners[$sid][$p1] ?? null;
    $pa2 = $partners[$sid][$p2] ?? null;
    
    if ($pa1 !== null || $pa2 !== null) {
        $upd = $pdo->prepare("UPDATE matches SET p1_partner_id = ?, p2_partner_id = ? WHERE id = ?");
        $upd->execute([$pa1, $pa2, $m['id']]);
        $updatedCount++;
    }
}

echo "Successfully updated $updatedCount matches with partner information.\n";
