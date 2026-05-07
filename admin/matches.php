<?php
require '../config/db.php';
require '../includes/functions.php';

$season = getCurrentSeason($pdo);
if (!$season) {
    header('Location: index.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matchId = (int)$_POST['match_id'];
    $s1 = (int)$_POST['player1_score'];
    $s2 = (int)$_POST['player2_score'];
    
    $stmt = $pdo->prepare("SELECT * FROM matches WHERE id=? AND season_id=?");
    $stmt->execute([$matchId, $season['id']]);
    $m = $stmt->fetch();
    
    if (!$m) $error = 'Match tidak ditemukan.';
    elseif ($s1 === $s2) $error = 'Skor tidak boleh seri.';
    elseif (max($s1, $s2) < 11) $error = 'Salah satu pemain minimal harus mencapai 11 poin.';
    else {
        $winner = $s1 > $s2 ? $m['player1_id'] : $m['player2_id'];
        $stmt = $pdo->prepare("UPDATE matches SET player1_score=?, player2_score=?, winner_id=?, status='completed' WHERE id=?");
        $stmt->execute([$s1, $s2, $winner, $matchId]);
        header('Location: matches.php?saved=1'); exit;
    }
}

$stmt = $pdo->prepare("SELECT m.*, p1.name AS player1, p2.name AS player2 
                       FROM matches m 
                       JOIN players p1 ON p1.id=m.player1_id 
                       JOIN players p2 ON p2.id=m.player2_id 
                       WHERE m.season_id=? 
                       ORDER BY m.round_number, m.id");
$stmt->execute([$season['id']]);
$matches = $stmt->fetchAll();

$pageTitle = 'Input Skor - Admin';
include 'includes/header.php';
?>

<div class="admin-header">
  <div>
    <h1>Input Skor Pertandingan</h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Satu pertandingan terdiri dari 1 game (hingga 11 poin). Pemenang harus selisih 2 poin jika terjadi deuce.</p>
  </div>
</div>

<?php if($error): ?>
<div class="alert" style="background: #ef4444; color: white; margin-bottom: 24px;"><?= e($error) ?></div>
<?php endif; ?>
<?php if(isset($_GET['saved'])): ?>
<div class="alert" style="margin-bottom: 24px;">Skor berhasil disimpan! Klasemen telah diperbarui.</div>
<?php endif; ?>
<?php if(isset($_GET['generated'])): ?>
<div class="alert" style="margin-bottom: 24px;">Jadwal baru berhasil dibuat. Silakan input skor.</div>
<?php endif; ?>

<div class="admin-card">
  <div class="table-wrap">
    <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
      <thead>
        <tr>
          <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Jadwal (Round/Leg)</th>
          <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Pemain</th>
          <th style="text-align: center; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Status/Skor</th>
          <th style="text-align: right; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Input Skor</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($matches as $m): ?>
        <tr>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <span style="background: rgba(255,255,255,0.1); padding: 4px 8px; border-radius: 4px; font-size: 12px; font-family: monospace;">
              R<?= e($m['round_number']) ?> / L<?= e($m['leg_number']) ?>
            </span>
          </td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <strong><?= e($m['player1']) ?></strong> <span style="color: var(--color-text-muted); font-style: italic; margin: 0 5px;">vs</span> <strong><?= e($m['player2']) ?></strong>
          </td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center; font-weight: 700; color: var(--color-primary);">
            <?= $m['status'] === 'completed' ? e($m['player1_score']).' - '.e($m['player2_score']) : '<span style="color:var(--color-text-muted); font-weight:400; font-size:12px;">Belum Main</span>' ?>
          </td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: right;">
            <form method="post" style="display:flex; gap:8px; justify-content: flex-end; align-items:center;">
              <input type="hidden" name="match_id" value="<?= $m['id'] ?>">
              <input type="number" name="player1_score" min="0" value="<?= e($m['player1_score'] ?? '') ?>" required style="width: 60px; background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 8px; border-radius: 6px; text-align: center;">
              <span style="color: var(--color-text-muted);">-</span>
              <input type="number" name="player2_score" min="0" value="<?= e($m['player2_score'] ?? '') ?>" required style="width: 60px; background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 8px; border-radius: 6px; text-align: center;">
              <button class="btn btn-primary" style="padding: 8px 16px;">Simpan</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($matches)): ?>
        <tr>
          <td colspan="4" style="text-align: center; padding: 20px; color: var(--color-text-muted);">Belum ada jadwal. Silakan <a href="generate.php" style="color: var(--color-primary);">generate jadwal</a> terlebih dahulu.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
