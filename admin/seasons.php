<?php
require '../config/db.php';
require '../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $stmt = $pdo->prepare("INSERT INTO seasons (name, league_type, status) VALUES (?, ?, 'active')");
    $stmt->execute([trim($_POST['name']), $_POST['league_type']]);
    header('Location: seasons.php'); exit;
}
$seasons = $pdo->query("SELECT * FROM seasons ORDER BY id DESC")->fetchAll();
$pageTitle = 'Admin Season';
include '../includes/header.php';
?>
<section class="hero"><h1>Admin Season</h1><p>Buat musim liga half atau full.</p></section>
<div class="card"><form method="post"><div class="grid"><div class="form-row"><label>Nama Season</label><input name="name" required placeholder="2026"></div><div class="form-row"><label>Tipe Liga</label><select name="league_type"><option value="half">Half League</option><option value="full">Full League</option></select></div></div><button class="btn">Buat Season</button> <a class="btn" href="season_players.php">Pilih Pemain</a></form></div>
<div class="table-wrap"><table><thead><tr><th>Season</th><th>Tipe</th><th>Status</th></tr></thead><tbody><?php foreach($seasons as $s): ?><tr><td><strong><?= e($s['name']) ?></strong></td><td><?= e($s['league_type']) ?></td><td><span class="badge"><?= e($s['status']) ?></span></td></tr><?php endforeach; ?></tbody></table></div>
<?php include '../includes/footer.php'; ?>
