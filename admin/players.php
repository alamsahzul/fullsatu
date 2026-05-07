<?php
require '../config/db.php';
require '../includes/functions.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $stmt = $pdo->prepare("INSERT INTO players (name) VALUES (?)");
    $stmt->execute([trim($_POST['name'])]);
    header('Location: players.php'); exit;
}
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM players WHERE id=?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: players.php'); exit;
}
$players = $pdo->query("SELECT * FROM players ORDER BY name ASC")->fetchAll();
$pageTitle = 'Admin Pemain';
include '../includes/header.php';
?>
<section class="hero"><h1>Admin Pemain</h1><p>Tambah dan kelola pemain.</p></section>
<div class="card"><form method="post"><div class="form-row"><label>Nama Pemain</label><input name="name" required placeholder="Contoh: Harfan"></div><button class="btn">Tambah Pemain</button> <a class="btn" href="seasons.php">Kelola Season</a></form></div>
<div class="table-wrap"><table><thead><tr><th>Nama</th><th>Aksi</th></tr></thead><tbody><?php foreach($players as $p): ?><tr><td><strong><?= e($p['name']) ?></strong></td><td><a class="btn danger" onclick="return confirm('Hapus pemain?')" href="?delete=<?= $p['id'] ?>">Hapus</a></td></tr><?php endforeach; ?></tbody></table></div>
<?php include '../includes/footer.php'; ?>
