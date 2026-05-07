<?php
require 'config/db.php';
require 'includes/functions.php';
$season = getCurrentSeason($pdo);
$pageTitle = 'Klasemen - FullSatu';
include 'includes/header.php';
?>
<section class="hero">
  <h1>Klasemen</h1>
  <p>FullSatu Single Man League - Season <?= e($season['name'] ?? '-') ?></p>
</section>
<?php if (!$season): ?>
  <div class="alert">Belum ada season. Buat season dari halaman admin.</div>
<?php else: $standings = calculateStandings($pdo, $season['id']); ?>
<div class="table-wrap">
<table>
  <thead>
    <tr><th class="num">#</th><th>Pemain</th><th class="num">Main</th><th class="num">W</th><th class="num">L</th><th class="num">PF</th><th class="num">PA</th><th class="num">Diff</th><th class="num">Pts</th></tr>
  </thead>
  <tbody>
    <?php foreach ($standings as $i => $row): ?>
    <tr class="<?= $i < 3 ? 'top' : '' ?>">
      <td class="num"><?= $i + 1 ?></td>
      <td><strong><?= e($row['name']) ?></strong></td>
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
