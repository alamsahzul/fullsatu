<?php
require '../config/db.php';
require '../includes/functions.php';

$season = getCurrentSeason($pdo);
if (!$season) {
    header('Location: index.php'); exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("SELECT player_id FROM season_players WHERE season_id=? ORDER BY id ASC");
    $stmt->execute([$season['id']]);
    $playerIds = array_column($stmt->fetchAll(), 'player_id');
    
    if (count($playerIds) < 2) {
        $error = "Minimal 2 pemain dibutuhkan untuk men-generate jadwal.";
    } else {
        $pdo->prepare("DELETE FROM matches WHERE season_id=?")->execute([$season['id']]);
        $schedule = generateRoundRobin($playerIds, $season['league_type']);
        foreach ($schedule as $m) {
            $stmt = $pdo->prepare("INSERT INTO matches (season_id, round_number, leg_number, player1_id, player2_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$season['id'], $m['round_number'], $m['leg_number'], $m['player1_id'], $m['player2_id']]);
        }
        header('Location: matches.php?generated=1'); exit;
    }
}

$pageTitle = 'Generate Jadwal - Admin';
include 'includes/header.php';
?>

<div class="admin-header">
  <div>
    <h1>Generate Jadwal Pertandingan</h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Sistem akan otomatis membuat jadwal dengan format Round Robin.</p>
  </div>
</div>

<?php if(isset($error)): ?>
<div class="alert" style="background: #ef4444; color: white; margin-bottom: 24px;"><?= e($error) ?></div>
<?php endif; ?>

<div class="admin-card" style="max-width: 600px;">
  <div style="background: var(--color-bg-dark); border: 1px solid var(--color-border); padding: 20px; border-radius: 12px; margin-bottom: 24px;">
    <h3 style="margin-bottom: 10px; color: var(--color-primary);">Info Musim Ini</h3>
    <ul style="list-style: none; padding: 0; margin: 0; color: var(--color-text-main);">
      <li style="margin-bottom: 8px;"><strong>Nama Season:</strong> <?= e($season['name']) ?></li>
      <li style="margin-bottom: 8px; text-transform: capitalize;"><strong>Tipe Liga:</strong> <?= e($season['league_type']) ?> League</li>
      <?php
      $c = $pdo->prepare("SELECT COUNT(*) FROM season_players WHERE season_id=?");
      $c->execute([$season['id']]);
      $playerCount = $c->fetchColumn();
      ?>
      <li><strong>Total Peserta:</strong> <?= $playerCount ?> Pemain</li>
    </ul>
  </div>
  
  <p style="color: var(--color-text-muted); margin-bottom: 20px;">
    <strong>Peringatan:</strong> Menekan tombol di bawah ini akan menghapus semua jadwal dan skor pertandingan yang sudah ada di musim ini, lalu membuat jadwal yang benar-benar baru.
  </p>
  
  <form method="post">
    <button class="btn btn-primary" style="padding: 14px 24px; font-size: 16px; width: 100%; justify-content: center;" onclick="return confirm('Anda yakin ingin mere-generate semua jadwal untuk musim ini?')">
      Generate Jadwal Sekarang
    </button>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
