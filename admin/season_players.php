<?php
require '../config/db.php';
require '../includes/functions.php';
$season = getCurrentSeason($pdo);
if (!$season) die('Buat season dulu.');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pdo->prepare("DELETE FROM season_players WHERE season_id=?")->execute([$season['id']]);
    foreach ($_POST['players'] ?? [] as $playerId) {
        $pdo->prepare("INSERT IGNORE INTO season_players (season_id, player_id) VALUES (?, ?)")->execute([$season['id'], (int)$playerId]);
    }
    header('Location: season_players.php?saved=1'); exit;
}
$players = $pdo->query("SELECT * FROM players ORDER BY name ASC")->fetchAll();
$stmt = $pdo->prepare("SELECT player_id FROM season_players WHERE season_id=?");
$stmt->execute([$season['id']]);
$selected = array_column($stmt->fetchAll(), 'player_id');
$pageTitle = 'Pemain Season';
include '../includes/header.php';
?>
<section class="hero"><h1>Pemain Season <?= e($season['name']) ?></h1><p>Pilih pemain yang ikut liga.</p></section>
<?php if(isset($_GET['saved'])): ?><div class="alert">Pemain season tersimpan.</div><?php endif; ?>
<form method="post" class="card"><div class="grid"><?php foreach($players as $p): ?><label><input type="checkbox" name="players[]" value="<?= $p['id'] ?>" <?= in_array($p['id'], $selected) ? 'checked' : '' ?>> <?= e($p['name']) ?></label><?php endforeach; ?></div><br><button class="btn">Simpan Pemain Season</button> <a class="btn" href="generate.php">Generate Jadwal</a></form>
<?php include '../includes/footer.php'; ?>
