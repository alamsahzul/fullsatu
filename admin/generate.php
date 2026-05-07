<?php
require '../config/db.php';
require '../includes/functions.php';
$season = getCurrentSeason($pdo);
if (!$season) die('Buat season dulu.');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT player_id FROM season_players WHERE season_id=? ORDER BY id ASC");
    $stmt->execute([$season['id']]);
    $playerIds = array_column($stmt->fetchAll(), 'player_id');
    $pdo->prepare("DELETE FROM matches WHERE season_id=?")->execute([$season['id']]);
    $schedule = generateRoundRobin($playerIds, $season['league_type']);
    foreach ($schedule as $m) {
        $stmt = $pdo->prepare("INSERT INTO matches (season_id, round_number, leg_number, player1_id, player2_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$season['id'], $m['round_number'], $m['leg_number'], $m['player1_id'], $m['player2_id']]);
    }
    header('Location: matches.php?generated=1'); exit;
}
$pageTitle = 'Generate Jadwal';
include '../includes/header.php';
?>
<section class="hero"><h1>Generate Jadwal</h1><p>Season <?= e($season['name']) ?> - <?= e($season['league_type']) ?> league.</p></section>
<div class="card"><p>Generate jadwal akan menghapus jadwal lama di season ini dan membuat jadwal baru.</p><form method="post"><button class="btn">Generate Jadwal Sekarang</button></form></div>
<?php include '../includes/footer.php'; ?>
