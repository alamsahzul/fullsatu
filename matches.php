<?php
require 'config/db.php';
require 'includes/functions.php';
$season = getCurrentSeason($pdo);
$pageTitle = 'Jadwal - FullSatu';
include 'includes/header.php';
?>
<section class="hero"><h1>Jadwal & Hasil</h1><p>Daftar pertandingan season aktif.</p></section>
<?php if ($season):
$stmt = $pdo->prepare("SELECT m.*, p1.name AS player1, p2.name AS player2, w.name AS winner FROM matches m JOIN players p1 ON p1.id=m.player1_id JOIN players p2 ON p2.id=m.player2_id LEFT JOIN players w ON w.id=m.winner_id WHERE m.season_id=? ORDER BY m.round_number, m.id");
$stmt->execute([$season['id']]);
$matches = $stmt->fetchAll();
?>
<div class="table-wrap"><table><thead><tr><th>Round</th><th>Match</th><th>Skor</th><th>Status</th></tr></thead><tbody>
<?php foreach($matches as $m): ?>
<tr><td>R<?= e($m['round_number']) ?> / Leg <?= e($m['leg_number']) ?></td><td><strong><?= e($m['player1']) ?></strong> vs <strong><?= e($m['player2']) ?></strong></td><td><?= $m['status']==='completed' ? e($m['player1_score']).' - '.e($m['player2_score']) : '-' ?></td><td><span class="badge"><?= e($m['status']) ?></span></td></tr>
<?php endforeach; ?>
</tbody></table></div>
<?php endif; include 'includes/footer.php'; ?>
