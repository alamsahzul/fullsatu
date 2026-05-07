<?php
require 'includes/auth.php';
require '../config/db.php';
require '../includes/functions.php';

$season = getCurrentSeason($pdo);
if (!$season) {
    header('Location: index'); exit;
}

$error = '';
$success = '';

// Handle removal
if (isset($_GET['remove'])) {
    $pid = (int)$_GET['remove'];
    
    // Check if has completed matches in this season
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE season_id=? AND status='completed' AND (player1_id=? OR player2_id=?)");
    $stmt->execute([$season['id'], $pid, $pid]);
    
    if ($stmt->fetchColumn() > 0) {
        $error = "Pemain tidak bisa dikeluarkan karena sudah memiliki riwayat skor pertandingan di musim ini.";
    } else {
        $pdo->prepare("DELETE FROM season_players WHERE season_id=? AND player_id=?")->execute([$season['id'], $pid]);
        // Delete scheduled matches involving this player in this season
        $pdo->prepare("DELETE FROM matches WHERE season_id=? AND status='scheduled' AND (player1_id=? OR player2_id=?)")->execute([$season['id'], $pid, $pid]);
        $success = "Pemain berhasil dikeluarkan dan jadwal kosongnya telah dibersihkan.";
    }
}

// Handle adding
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['players'])) {
    foreach ($_POST['players'] as $pid) {
        $pdo->prepare("INSERT IGNORE INTO season_players (season_id, player_id) VALUES (?, ?)")->execute([$season['id'], (int)$pid]);
    }
    
    // Automatically sync matches for new players
    $addedCount = syncSeasonMatches($pdo, $season['id']);
    if ($addedCount > 0) {
        $success = "Pemain berhasil ditambahkan dan " . $addedCount . " pertandingan baru telah dibuat otomatis.";
    } else {
        $success = "Pemain baru berhasil ditambahkan ke musim ini.";
    }
}

// Get current participants
$stmt = $pdo->prepare("SELECT p.* FROM season_players sp JOIN players p ON p.id = sp.player_id WHERE sp.season_id=? ORDER BY p.name ASC");
$stmt->execute([$season['id']]);
$activePlayers = $stmt->fetchAll();

// Get available players (not in this season)
$activeIds = array_column($activePlayers, 'id');
if (count($activeIds) > 0) {
    $placeholders = implode(',', array_fill(0, count($activeIds), '?'));
    $stmt = $pdo->prepare("SELECT * FROM players WHERE id NOT IN ($placeholders) ORDER BY name ASC");
    $stmt->execute($activeIds);
} else {
    $stmt = $pdo->query("SELECT * FROM players ORDER BY name ASC");
}
$availablePlayers = $stmt->fetchAll();

$pageTitle = 'Manajemen Pemain Musim';
include 'includes/header.php';
?>

<div class="admin-header">
  <div>
    <h1>Manajemen Peserta Musim <?= e($season['name']) ?></h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Kelola siapa saja yang bertanding di musim ini.</p>
  </div>
</div>

<?php if($error): ?>
  <div class="alert" style="background: #ef4444; color: white; margin-bottom: 24px;"><?= e($error) ?></div>
<?php endif; ?>

<?php if($success || isset($_GET['saved'])): ?>
  <div class="alert" style="margin-bottom: 24px;"><?= $success ?: 'Perubahan berhasil disimpan.' ?></div>
<?php endif; ?>

<!-- PESERTA AKTIF SECTION -->
<div class="admin-card">
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
    <h2 style="margin: 0; font-size: 20px;">Peserta Aktif (<?= count($activePlayers) ?>)</h2>
    <?php if(count($activePlayers) >= 2): ?>
      <a href="generate" class="btn btn-primary" style="font-size: 13px;">Update/Generate Jadwal →</a>
    <?php endif; ?>
  </div>

  <div class="table-wrap">
    <table style="width: 100%; border-collapse: collapse;">
      <thead>
        <tr>
          <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Foto</th>
          <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Nama Pemain</th>
          <th style="text-align: right; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($activePlayers as $p): ?>
        <tr>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <?php if($p['photo']): ?>
              <img src="<?= base_url('assets/uploads/players/' . $p['photo']) ?>" alt="<?= e($p['name']) ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover;">
            <?php else: ?>
              <img src="<?= base_url('assets/img/player_avatar.png') ?>" alt="Default" style="width: 32px; height: 32px; border-radius: 50%; opacity: 0.5;">
            <?php endif; ?>
          </td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);"><strong><?= e($p['name']) ?></strong></td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: right;">
            <a class="btn btn-outline" style="color: #ef4444; border-color: rgba(239,68,68,0.3); padding: 6px 12px; font-size: 11px;" onclick="return confirm('Keluarkan pemain ini dari musim?')" href="?remove=<?= $p['id'] ?>">Keluarkan</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($activePlayers)): ?>
        <tr>
          <td colspan="3" style="text-align: center; padding: 30px; color: var(--color-text-muted);">Belum ada pemain yang didaftarkan untuk musim ini.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- TAMBAH PESERTA SECTION -->
<div class="admin-card" style="margin-top: 40px;">
  <h2 style="margin-bottom: 20px; font-size: 20px;">Tambah Peserta Baru</h2>
  <form method="post">
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px; margin-bottom: 30px;">
      <?php foreach($availablePlayers as $p): ?>
      <label style="display: flex; align-items: center; gap: 10px; background: var(--color-bg-dark); padding: 12px 16px; border-radius: 8px; border: 1px solid var(--color-border); cursor: pointer; transition: all 0.2s;">
        <input type="checkbox" name="players[]" value="<?= $p['id'] ?>" style="width: 18px; height: 18px; accent-color: var(--color-primary);">
        <span style="font-weight: 600;"><?= e($p['name']) ?></span>
      </label>
      <?php endforeach; ?>
      <?php if(empty($availablePlayers)): ?>
        <p style="color: var(--color-text-muted); grid-column: 1/-1;">Semua pemain sudah didaftarkan ke musim ini, atau <a href="players" style="color: var(--color-primary);">Master Pemain</a> masih kosong.</p>
      <?php endif; ?>
    </div>
    
    <?php if(!empty($availablePlayers)): ?>
    <button class="btn btn-primary">Daftarkan Pemain Terpilih</button>
    <?php endif; ?>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
