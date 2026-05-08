<?php
require 'includes/auth.php';
require '../config/db.php';
require '../includes/functions.php';

$season = getCurrentSeason($pdo);
if (!$season) {
    header('Location: index'); exit;
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
        header('Location: matches?saved=1'); exit;
    }
}

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$search2 = isset($_GET['q2']) ? trim($_GET['q2']) : '';

$sql = "SELECT m.*, p1.name AS player1, p2.name AS player2 
        FROM matches m 
        JOIN players p1 ON p1.id=m.player1_id 
        JOIN players p2 ON p2.id=m.player2_id 
        WHERE m.season_id=?";
$params = [$season['id']];

if ($search && $search2) {
    $sql .= " AND (
        (p1.name LIKE ? AND p2.name LIKE ?) 
        OR 
        (p1.name LIKE ? AND p2.name LIKE ?)
    ) ";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search2 . '%';
    $params[] = '%' . $search2 . '%';
    $params[] = '%' . $search . '%';
} elseif ($search) {
    $sql .= " AND (p1.name LIKE ? OR p2.name LIKE ?) ";
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}

$sql .= " ORDER BY m.round_number, m.id";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$matches = $stmt->fetchAll();

$pageTitle = 'Input Skor - Admin';
include 'includes/header.php';
?>

<div class="admin-header">
  <div>
    <h1>Input Skor Pertandingan</h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Satu pertandingan terdiri dari 1 game. Pemenang adalah yang pertama kali mencapai skor 11.</p>
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
  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
    <h2 style="margin: 0; font-size: 20px;">Daftar Pertandingan</h2>
    <form method="get" style="display: flex; gap: 10px; flex-wrap: wrap;">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Pemain 1..." style="width: 150px; background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 8px 15px; border-radius: 8px; font-size: 14px;">
      <div style="align-self: center; color: var(--color-text-muted);">vs</div>
      <input type="text" name="q2" value="<?= e($search2) ?>" placeholder="Pemain 2..." style="width: 150px; background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 8px 15px; border-radius: 8px; font-size: 14px;">
      <button class="btn btn-primary" style="padding: 8px 15px; font-size: 14px;">Cari Match</button>
      <?php if($search || $search2): ?>
        <a href="matches" class="btn btn-outline" style="padding: 8px 15px; font-size: 14px;">Reset</a>
      <?php endif; ?>
    </form>
  </div>
  <div class="table-wrap">
    <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
      <thead>
        <tr>
          <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">#</th>
          <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Pemain</th>
          <th style="text-align: center; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Status/Skor</th>
          <th style="text-align: right; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Input Skor</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($matches as $i => $m): ?>
        <tr>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <span style="background: rgba(255,255,255,0.1); padding: 4px 8px; border-radius: 4px; font-size: 12px; font-family: monospace;">
              <?= $i + 1 ?>
            </span>
          </td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <strong><?= e($m['player1']) ?></strong> <span style="color: var(--color-text-muted); font-style: italic; margin: 0 5px;">vs</span> <strong><?= e($m['player2']) ?></strong>
          </td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center; font-weight: 700; color: var(--color-primary);">
            <?= $m['status'] === 'completed' ? e($m['player1_score']).' - '.e($m['player2_score']) : '<span style="color:var(--color-text-muted); font-weight:400; font-size:12px;">Belum Main</span>' ?>
          </td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: right;">
            <div style="display:flex; gap:8px; justify-content: flex-end; align-items:center;">
              <form method="post" style="display:flex; gap:8px; align-items:center;">
                <input type="hidden" name="match_id" value="<?= $m['id'] ?>">
                <input type="number" name="player1_score" min="0" value="<?= e($m['player1_score'] ?? '') ?>" required style="width: 50px; background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 5px; border-radius: 6px; text-align: center;">
                <span style="color: var(--color-text-muted);">-</span>
                <input type="number" name="player2_score" min="0" value="<?= e($m['player2_score'] ?? '') ?>" required style="width: 50px; background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 5px; border-radius: 6px; text-align: center;">
                <button class="btn btn-primary" style="padding: 5px 12px; font-size: 12px;">Simpan</button>
              </form>
              <a href="match_edit?id=<?= $m['id'] ?>" class="btn btn-outline" style="padding: 5px 12px; font-size: 12px; display: flex; align-items: center; gap: 5px;">
                <span>📝</span> Dok
              </a>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($matches)): ?>
        <tr>
          <td colspan="4" style="text-align: center; padding: 20px; color: var(--color-text-muted);">Belum ada jadwal. Silakan <a href="generate" style="color: var(--color-primary);">generate jadwal</a> terlebih dahulu.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
