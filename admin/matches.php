<?php
require '../config/db.php';
require '../includes/functions.php';
$season = getCurrentSeason($pdo);
if (!$season) die('Buat season dulu.');
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matchId = (int)$_POST['match_id'];
    $s1 = (int)$_POST['player1_score'];
    $s2 = (int)$_POST['player2_score'];
    $stmt = $pdo->prepare("SELECT * FROM matches WHERE id=? AND season_id=?");
    $stmt->execute([$matchId, $season['id']]);
    $m = $stmt->fetch();
    if (!$m) $error = 'Match tidak ditemukan.';
    elseif ($s1 === $s2) $error = 'Skor tidak boleh sama.';
    elseif (max($s1, $s2) < 11) $error = 'Salah satu skor minimal 11.';
    else {
        $winner = $s1 > $s2 ? $m['player1_id'] : $m['player2_id'];
        $stmt = $pdo->prepare("UPDATE matches SET player1_score=?, player2_score=?, winner_id=?, status='completed' WHERE id=?");
        $stmt->execute([$s1, $s2, $winner, $matchId]);
        header('Location: matches.php?saved=1'); exit;
    }
}
$stmt = $pdo->prepare("SELECT m.*, p1.name AS player1, p2.name AS player2 FROM matches m JOIN players p1 ON p1.id=m.player1_id JOIN players p2 ON p2.id=m.player2_id WHERE m.season_id=? ORDER BY m.round_number, m.id");
$stmt->execute([$season['id']]);
$matches = $stmt->fetchAll();
$pageTitle = 'Input Skor';
include '../includes/header.php';
?>
<section class="hero"><h1>Input Skor</h1><p>1 pertandingan = 1 game sampai 11 poin.</p></section>
<?php if($error): ?><div class="alert"><?= e($error) ?></div><?php endif; ?><?php if(isset($_GET['saved'])): ?><div class="alert">Skor tersimpan.</div><?php endif; ?><?php if(isset($_GET['generated'])): ?><div class="alert">Jadwal berhasil dibuat.</div><?php endif; ?>
<div class="table-wrap"><table><thead><tr><th>Round</th><th>Match</th><th>Skor</th><th>Aksi</th></tr></thead><tbody><?php foreach($matches as $m): ?><tr><td>R<?= e($m['round_number']) ?> / L<?= e($m['leg_number']) ?></td><td><strong><?= e($m['player1']) ?></strong> vs <strong><?= e($m['player2']) ?></strong></td><td><?= $m['status']==='completed' ? e($m['player1_score']).' - '.e($m['player2_score']) : '-' ?></td><td><form method="post" style="display:flex;gap:8px;align-items:center"><input type="hidden" name="match_id" value="<?= $m['id'] ?>"><input class="score-input" type="number" name="player1_score" min="0" value="<?= e($m['player1_score'] ?? '') ?>" required><input class="score-input" type="number" name="player2_score" min="0" value="<?= e($m['player2_score'] ?? '') ?>" required><button class="btn">Simpan</button></form></td></tr><?php endforeach; ?></tbody></table></div>
<?php include '../includes/footer.php'; ?>
