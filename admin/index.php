<?php
require 'includes/auth.php';
require '../config/db.php';
require '../includes/functions.php';

$pageTitle = 'Dashboard - Admin FullSatu';
include 'includes/header.php';

// Get some stats
$totalPlayers = $pdo->query("SELECT COUNT(*) FROM players")->fetchColumn();
$totalSeasons = $pdo->query("SELECT COUNT(*) FROM seasons")->fetchColumn();
$activeSeason = $pdo->query("SELECT * FROM seasons WHERE status='active' ORDER BY id DESC LIMIT 1")->fetch();

$activeSeasonName = $activeSeason ? $activeSeason['name'] : 'Belum Ada';
$matchesPlayed = 0;
if ($activeSeason) {
    $matchesPlayed = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE season_id=? AND status='completed'");
    $matchesPlayed->execute([$activeSeason['id']]);
    $matchesPlayed = $matchesPlayed->fetchColumn();
}
?>

<div class="admin-header">
  <div>
    <h1>Dashboard</h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Selamat datang di Panel Admin FullSatu Single Man League.</p>
  </div>
</div>

<div class="admin-grid mb-4" style="margin-bottom: 30px;">
  <div class="stat-card">
    <h3><?= $totalPlayers ?></h3>
    <p>Total Pemain</p>
  </div>
  <div class="stat-card">
    <h3><?= $totalSeasons ?></h3>
    <p>Total Musim</p>
  </div>
  <div class="stat-card">
    <h3><?= e($activeSeasonName) ?></h3>
    <p>Musim Aktif</p>
  </div>
  <div class="stat-card">
    <h3><?= $matchesPlayed ?></h3>
    <p>Match Selesai (Musim Aktif)</p>
  </div>
</div>

<div class="admin-grid">
  <div class="admin-card">
    <h2>1. Kelola Pemain & Musim</h2>
    <p style="color: var(--color-text-muted); margin-bottom: 15px;">Daftarkan pemain baru dan buat musim kompetisi.</p>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="players" class="btn btn-outline">Master Pemain</a>
      <a href="seasons" class="btn btn-outline">Buat Musim Baru</a>
    </div>
  </div>

  <div class="admin-card">
    <h2>2. Persiapan Jadwal</h2>
    <p style="color: var(--color-text-muted); margin-bottom: 15px;">Masukkan pemain ke dalam musim aktif, lalu generate jadwal pertandingan.</p>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
      <a href="season_players" class="btn btn-outline">Pemain Musim Ini</a>
      <a href="generate" class="btn btn-outline">Generate Jadwal</a>
    </div>
  </div>

  <div class="admin-card">
    <h2>3. Input Skor</h2>
    <p style="color: var(--color-text-muted); margin-bottom: 15px;">Catat hasil pertandingan. Klasemen akan otomatis diperbarui.</p>
    <a href="matches" class="btn btn-primary">Input Skor Pertandingan</a>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
