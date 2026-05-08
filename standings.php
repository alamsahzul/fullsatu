<?php
require 'config/db.php';
require 'includes/functions.php';
$allSeasons = getAllSeasons($pdo);
$selectedSeasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;

// If no season selected or invalid, get current season
if ($selectedSeasonId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM seasons WHERE id = ?");
    $stmt->execute([$selectedSeasonId]);
    $season = $stmt->fetch();
} else {
    $season = getCurrentSeason($pdo);
    $selectedSeasonId = $season['id'] ?? 0;
}

$pageTitle = 'Klasemen - FullSatu';
include 'includes/header.php';
?>
<div style="padding-top: 100px;"></div>
<section class="hero-page">
  <h1>Klasemen Liga</h1>
  
  <!-- SEASON SWITCHER -->
  <div style="margin-top: 20px; display: flex; justify-content: center; gap: 10px; align-items: center;">
    <span style="color: var(--color-text-muted); font-size: 14px;">Pilih Musim:</span>
    <form method="get" id="seasonForm">
      <select name="season_id" onchange="document.getElementById('seasonForm').submit()" style="background: var(--color-bg-light); border: 1px solid var(--color-border); color: var(--color-primary); padding: 8px 15px; border-radius: 30px; font-weight: 700; cursor: pointer; min-width: 150px;">
        <?php foreach($allSeasons as $s): ?>
          <option value="<?= $s['id'] ?>" <?= $s['id'] == $selectedSeasonId ? 'selected' : '' ?>>
            <?= e($s['name']) ?> <?= $s['status'] == 'active' ? '(Active)' : '' ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
</section>
<?php if (!$season): ?>
  <div class="alert">Belum ada season. Buat season dari halaman admin.</div>
<?php else: $standings = calculateStandings($pdo, $season['id']); ?>
<div class="table-wrap" style="margin-bottom: 60px;">
<table>
  <thead>
    <tr><th class="num">#</th><th>Pemain</th><th class="num">Main</th><th class="num">W</th><th class="num">L</th><th class="num">PF</th><th class="num">PA</th><th class="num">Diff</th><th class="num">Pts</th></tr>
  </thead>
  <tbody>
    <?php foreach ($standings as $i => $row): ?>
    <tr class="<?= $i < 3 ? 'top' : '' ?>">
      <td class="num"><?= $i + 1 ?></td>
      <td>
          <a href="<?= base_url('player?id=' . $row['id']) ?>" style="display: flex; align-items: center; gap: 12px; color: white;">
            <?php if($row['photo']): ?>
              <img src="<?= base_url('assets/uploads/players/' . $row['photo']) ?>" alt="<?= e($row['name']) ?>" style="width: 32px; height: 32px; border-radius: 50%; border: 2px solid var(--color-border); object-fit: cover;">
            <?php else: ?>
              <img src="<?= base_url('assets/img/player_avatar.png') ?>" alt="Avatar" style="width: 32px; height: 32px; border-radius: 50%; border: 2px solid var(--color-border); object-fit: cover;">
            <?php endif; ?>
            <strong><?= e($row['name']) ?></strong>
          </a>
      </td>
      <td class="num"><?= $row['main'] ?></td>
      <td class="num"><?= $row['w'] ?></td>
      <td class="num"><?= $row['l'] ?></td>
      <td class="num"><?= $row['pf'] ?></td>
      <td class="num"><?= $row['pa'] ?></td>
      <td class="num"><?= $row['diff'] > 0 ? '+' : '' ?><?= $row['diff'] ?></td>
      <td class="num"><strong><?= $row['pts'] ?></strong></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
</div>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
