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

$pageTitle = 'Jadwal & Hasil - FullSatu';
include 'includes/header.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$search2 = isset($_GET['q2']) ? trim($_GET['q2']) : '';
?>

<div style="padding-top: 100px;"></div>
<section class="hero-page">
  <h1>Jadwal & Hasil</h1>
  
  <!-- SEASON SWITCHER -->
  <div style="margin-top: 20px; display: flex; justify-content: center; gap: 10px; align-items: center;">
    <span style="color: var(--color-text-muted); font-size: 14px;">Pilih Musim:</span>
    <form method="get" id="seasonMatchForm">
      <select name="season_id" onchange="document.getElementById('seasonMatchForm').submit()" style="background: var(--color-bg-light); border: 1px solid var(--color-border); color: var(--color-primary); padding: 8px 15px; border-radius: 30px; font-weight: 700; cursor: pointer; min-width: 150px;">
        <?php foreach($allSeasons as $s): ?>
          <option value="<?= $s['id'] ?>" <?= $s['id'] == $selectedSeasonId ? 'selected' : '' ?>>
            <?= e($s['name']) ?> <?= $s['status'] == 'active' ? '(Active)' : '' ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
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
  
  // Show 'completed' matches first, then 'scheduled'
  $sql .= " ORDER BY CASE WHEN m.status = 'completed' THEN 0 ELSE 1 END, m.round_number ASC, m.id ASC";
  $stmt = $pdo->prepare($sql);
  $stmt->execute($params);
  $matches = $stmt->fetchAll();
  ?>

  <div class="table-wrap" style="margin-bottom: 60px;">
    <table>
      <thead>
        <tr>
          <th style="width: 60px;">#</th>
          <th>Pertandingan & Hasil</th>
          <th style="text-align: center;">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($matches as $i => $m): ?>
        <tr>
          <td style="<?= ($m['match_photo'] || $m['match_notes']) ? 'padding-bottom: 5px;' : '' ?>">
            <div style="font-size: 14px; font-weight: 700; color: var(--color-primary);"><?= $i + 1 ?></div>
          </td>
          <td style="<?= ($m['match_photo'] || $m['match_notes']) ? 'padding-bottom: 5px;' : '' ?>">
            <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap; justify-content: center; padding: 10px 0;">
              
              <!-- Player 1 -->
              <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 150px; justify-content: flex-end;">
                <a href="<?= base_url('player?id=' . $m['player1_id']) ?>" style="text-align: right; color: inherit;">
                   <div style="<?= $m['winner_id'] == $m['player1_id'] ? 'color: var(--color-primary); font-weight: 700;' : 'color: white;' ?>"><?= e($m['player1']) ?></div>
                   <?php if($m['status'] === 'completed'): ?>
                     <div style="font-size: 24px; font-weight: 900; color: var(--color-primary);"><?= e($m['player1_score']) ?></div>
                   <?php endif; ?>
                </a>
                <a href="<?= base_url('player?id=' . $m['player1_id']) ?>">
                  <?php if($m['photo1']): ?>
                    <img src="<?= base_url('assets/uploads/players/'.$m['photo1']) ?>" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid <?= $m['winner_id'] == $m['player1_id'] ? 'var(--color-primary)' : 'transparent' ?>;">
                  <?php else: ?>
                    <img src="<?= base_url('assets/img/player_avatar.png') ?>" style="width: 45px; height: 45px; border-radius: 50%; opacity: 0.3;">
                  <?php endif; ?>
                </a>
              </div>
              
              <div style="color: var(--color-text-muted); font-size: 14px; font-weight: 900; background: rgba(255,255,255,0.05); padding: 5px 10px; border-radius: 8px;">VS</div>
              
              <!-- Player 2 -->
              <div style="display: flex; align-items: center; gap: 12px; flex: 1; min-width: 150px; justify-content: flex-start;">
                <a href="<?= base_url('player?id=' . $m['player2_id']) ?>">
                  <?php if($m['photo2']): ?>
                    <img src="<?= base_url('assets/uploads/players/'.$m['photo2']) ?>" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid <?= $m['winner_id'] == $m['player2_id'] ? 'var(--color-primary)' : 'transparent' ?>;">
                  <?php else: ?>
                    <img src="<?= base_url('assets/img/player_avatar.png') ?>" style="width: 45px; height: 45px; border-radius: 50%; opacity: 0.3;">
                  <?php endif; ?>
                </a>
                <a href="<?= base_url('player?id=' . $m['player2_id']) ?>" style="text-align: left; color: inherit;">
                   <div style="<?= $m['winner_id'] == $m['player2_id'] ? 'color: var(--color-primary); font-weight: 700;' : 'color: white;' ?>"><?= e($m['player2']) ?></div>
                   <?php if($m['status'] === 'completed'): ?>
                     <div style="font-size: 24px; font-weight: 900; color: var(--color-primary);"><?= e($m['player2_score']) ?></div>
                   <?php endif; ?>
                </a>
              </div>

            </div>
          </td>
          <td style="text-align: center; vertical-align: middle; <?= ($m['match_photo'] || $m['match_notes']) ? 'padding-bottom: 5px;' : '' ?>">
            <?php if($m['status'] === 'completed'): ?>
              <span class="badge" style="background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); padding: 6px 15px; border-radius: 30px; font-size: 11px; font-weight: 800; letter-spacing: 1px; display: inline-flex; align-items: center; gap: 5px;">
                <span style="font-size: 14px;">✓</span> COMPLETED
              </span>
            <?php else: ?>
              <span class="badge" style="background: rgba(255, 255, 255, 0.05); color: var(--color-text-muted); border: 1px solid rgba(255, 255, 255, 0.1); padding: 6px 15px; border-radius: 30px; font-size: 11px; font-weight: 800; letter-spacing: 1px; display: inline-flex; align-items: center; gap: 5px;">
                <span style="font-size: 14px;">🕒</span> SCHEDULED
              </span>
            <?php endif; ?>
          </td>
        </tr>
        
        <?php if($m['match_photo'] || $m['match_notes']): ?>
        <tr class="documentation-row">
          <td colspan="3" style="padding: 0 20px 30px 75px; border-top: none;">
            <div style="background: linear-gradient(135deg, rgba(255,255,255,0.05) 0%, rgba(255,255,255,0.01) 100%); border: 1px solid rgba(255,255,255,0.1); border-radius: 20px; padding: 30px; display: flex; gap: 30px; flex-wrap: wrap; box-shadow: 0 10px 30px rgba(0,0,0,0.2); position: relative; overflow: hidden;">
              
              <!-- Subtle Background Icon -->
              <div style="position: absolute; right: -20px; bottom: -20px; font-size: 120px; opacity: 0.03; color: white; transform: rotate(-15deg); pointer-events: none;">🏆</div>

              <?php if($m['match_photo']): ?>
                <div style="flex: 0 0 280px; position: relative;">
                  <a href="<?= base_url('assets/uploads/matches/' . $m['match_photo']) ?>" target="_blank" style="display: block; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 20px rgba(0,0,0,0.4); transform: rotate(-2deg); transition: transform 0.3s ease;">
                    <img src="<?= base_url('assets/uploads/matches/' . $m['match_photo']) ?>" style="width: 100%; height: 180px; object-fit: cover; display: block;" onmouseover="this.parentElement.style.transform='rotate(0deg)'" onmouseout="this.parentElement.style.transform='rotate(-2deg)'">
                  </a>
                </div>
              <?php endif; ?>

              <div style="flex: 1; min-width: 300px;">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                   <div style="width: 30px; height: 2px; background: var(--color-primary);"></div>
                   <h4 style="color: var(--color-primary); font-size: 14px; text-transform: uppercase; letter-spacing: 2px; font-weight: 900;">JALANNYA PERTANDINGAN</h4>
                </div>
                
                <div style="position: relative; padding-left: 0;">
                  <p style="color: #e2e8f0; font-size: 16px; line-height: 1.8; font-style: italic; font-family: 'Inter', sans-serif;">
                    <span style="font-size: 40px; color: var(--color-primary); position: absolute; left: -10px; top: -15px; opacity: 0.2; font-family: serif;">"</span>
                    <?= nl2br(e($m['match_notes'] ?? 'Tidak ada catatan pertandingan.')) ?>
                    <span style="font-size: 40px; color: var(--color-primary); opacity: 0.2; font-family: serif; vertical-align: bottom; line-height: 0;">"</span>
                  </p>
                </div>

                <div style="margin-top: 20px; display: flex; align-items: center; gap: 15px;">
                   <div style="font-size: 12px; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 1px;">Match Highlight</div>
                   <div style="flex: 1; height: 1px; background: rgba(255,255,255,0.05);"></div>
                </div>
              </div>

            </div>
          </td>
        </tr>
        <?php endif; ?>

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
