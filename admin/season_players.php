<?php
require '../config/db.php';
require '../includes/functions.php';

$season = getCurrentSeason($pdo);
if (!$season) {
    header('Location: index.php'); exit;
}

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

$pageTitle = 'Pemain Musim Ini - Admin';
include 'includes/header.php';
?>

<div class="admin-header">
  <div>
    <h1>Peserta Musim <?= e($season['name']) ?></h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Pilih pemain yang akan berpartisipasi pada liga musim ini.</p>
  </div>
</div>

<?php if(isset($_GET['saved'])): ?>
<div class="alert" style="margin-bottom: 24px;">Daftar pemain musim berhasil disimpan.</div>
<?php endif; ?>

<div class="admin-card">
  <form method="post">
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
      <?php foreach($players as $p): ?>
      <label style="display: flex; align-items: center; gap: 10px; background: var(--color-bg-dark); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--color-border); cursor: pointer; transition: all 0.2s;">
        <input type="checkbox" name="players[]" value="<?= $p['id'] ?>" <?= in_array($p['id'], $selected) ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--color-primary);">
        <span style="font-weight: 600;"><?= e($p['name']) ?></span>
      </label>
      <?php endforeach; ?>
      <?php if(empty($players)): ?>
        <p style="color: var(--color-text-muted); grid-column: 1/-1;">Belum ada pemain terdaftar. Silakan <a href="players.php" style="color: var(--color-primary);">tambah pemain</a> terlebih dahulu.</p>
      <?php endif; ?>
    </div>
    
    <div style="display: flex; gap: 15px;">
      <button class="btn btn-primary">Simpan Daftar Peserta</button>
      <?php if(count($selected) >= 2): ?>
      <a href="generate.php" class="btn btn-outline">Lanjut Generate Jadwal →</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
