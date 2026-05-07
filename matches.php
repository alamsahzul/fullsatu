<?php
require 'config/db.php';
require 'includes/functions.php';
$season = getCurrentSeason($pdo);
$pageTitle = 'Jadwal & Hasil - FullSatu';
include 'includes/header.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$search2 = isset($_GET['q2']) ? trim($_GET['q2']) : '';
?>

<div style="padding-top: 100px;"></div>
<section class="hero-page">
  <h1>Jadwal & Hasil</h1>
  <p>Daftar pertandingan season aktif: <?= e($season['name'] ?? '-') ?></p>
</section>

<?php if ($season): ?>
<div class="container">
  <!-- SEARCH BAR -->
  <div style="margin-bottom: 30px; display: flex; justify-content: center;">
    <form method="get" action="" style="display: flex; gap: 10px; width: 100%; max-width: 600px; flex-wrap: wrap; justify-content: center;">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Pemain 1..." style="width: 180px; background: var(--color-bg-light); border: 1px solid var(--color-border); color: white; padding: 12px 20px; border-radius: 30px; font-family: inherit;">
      <div style="align-self: center; color: var(--color-text-muted); font-weight: 700;">VS</div>
      <input type="text" name="q2" value="<?= e($search2) ?>" placeholder="Pemain 2..." style="width: 180px; background: var(--color-bg-light); border: 1px solid var(--color-border); color: white; padding: 12px 20px; border-radius: 30px; font-family: inherit;">
      <button class="btn btn-primary" style="padding: 10px 25px; border-radius: 30px;">Cari</button>
      <?php if($search || $search2): ?>
        <a href="matches" class="btn btn-outline" style="padding: 10px 20px; border-radius: 30px; display: flex; align-items: center; justify-content: center;">Reset</a>
      <?php endif; ?>
    </form>
  </div>

  <?php
  $sql = "SELECT m.*, p1.name AS player1, p2.name AS player2, p1.photo AS photo1, p2.photo AS photo2, w.name AS winner 
          FROM matches m 
          JOIN players p1 ON p1.id=m.player1_id 
          JOIN players p2 ON p2.id=m.player2_id 
          LEFT JOIN players w ON w.id=m.winner_id 
          WHERE m.season_id = ? ";
  
  $params = [$season['id']];
  if ($search && $search2) {
      $sql .= " AND (
          (p1.name LIKE ? AND p2.name LIKE ?) 
          OR 
          (p1.name LIKE ? AND p2.name LIKE ?)
      ) ";
      $params[] = "%$search%";
      $params[] = "%$search2%";
      $params[] = "%$search2%";
      $params[] = "%$search%";
  } elseif ($search) {
      $sql .= " AND (p1.name LIKE ? OR p2.name LIKE ?) ";
      $params[] = "%$search%";
      $params[] = "%$search%";
  }
  
  $sql .= " ORDER BY m.round_number ASC, m.id ASC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $matches = $stmt->fetchAll();
  ?>

  <div class="table-wrap" style="margin-bottom: 60px;">
    <table>
      <thead>
        <tr>
          <th style="width: 60px;">#</th>
          <th>Pertandingan</th>
          <th class="num">Skor</th>
          <th style="text-align: center;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($matches as $i => $m): ?>
        <tr>
          <td>
            <div style="font-size: 14px; font-weight: 700; color: var(--color-primary);"><?= $i + 1 ?></div>
          </td>
          <td>
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
              <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 120px; justify-content: flex-end;">
                <span style="<?= $m['winner_id'] == $m['player1_id'] ? 'color: var(--color-primary); font-weight: 700;' : '' ?>"><?= e($m['player1']) ?></span>
                <?php if($m['photo1']): ?>
                  <img src="<?= base_url('assets/uploads/players/'.$m['photo1']) ?>" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                  <img src="<?= base_url('assets/img/player_avatar.png') ?>" style="width: 24px; height: 24px; border-radius: 50%; opacity: 0.3;">
                <?php endif; ?>
              </div>
              
              <div style="color: var(--color-text-muted); font-size: 12px; font-weight: 900;">VS</div>
              
              <div style="display: flex; align-items: center; gap: 8px; flex: 1; min-width: 120px;">
                <?php if($m['photo2']): ?>
                  <img src="<?= base_url('assets/uploads/players/'.$m['photo2']) ?>" style="width: 24px; height: 24px; border-radius: 50%; object-fit: cover;">
                <?php else: ?>
                  <img src="<?= base_url('assets/img/player_avatar.png') ?>" style="width: 24px; height: 24px; border-radius: 50%; opacity: 0.3;">
                <?php endif; ?>
                <span style="<?= $m['winner_id'] == $m['player2_id'] ? 'color: var(--color-primary); font-weight: 700;' : '' ?>"><?= e($m['player2']) ?></span>
              </div>
            </div>
          </td>
          <td class="num">
            <?php if($m['status'] === 'completed'): ?>
              <strong style="font-size: 18px; color: white;"><?= e($m['player1_score']) ?> - <?= e($m['player2_score']) ?></strong>
            <?php else: ?>
              <span style="color: var(--color-text-muted);">-</span>
            <?php endif; ?>
          </td>
          <td style="text-align: center;">
            <span class="badge" style="background: <?= $m['status'] === 'completed' ? 'rgba(34, 197, 94, 0.1)' : 'rgba(255,255,255,0.05)' ?>; color: <?= $m['status'] === 'completed' ? '#22c55e' : 'var(--color-text-muted)' ?>; border: 1px solid <?= $m['status'] === 'completed' ? 'rgba(34, 197, 94, 0.2)' : 'var(--color-border)' ?>;">
              <?= strtoupper(e($m['status'])) ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($matches)): ?>
          <tr>
            <td colspan="4" style="text-align: center; padding: 40px; color: var(--color-text-muted);">
              Tidak ada pertandingan ditemukan <?= $search ? 'untuk pencarian "'.e($search).'"' : '' ?>.
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php else: ?>
  <div class="container" style="text-align: center; padding: 100px 20px;">
    <div class="alert">Belum ada season aktif. Pantau terus untuk update berikutnya!</div>
  </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
