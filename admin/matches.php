<?php
require 'includes/auth.php';
require '../config/db.php';
require '../includes/functions.php';

$seasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;
if ($seasonId === 0) {
    $current = getCurrentSeason($pdo);
    $seasonId = $current['id'] ?? 0;
}

$stmt = $pdo->prepare("SELECT * FROM seasons WHERE id = ?");
$stmt->execute([$seasonId]);
$season = $stmt->fetch();

if (!$season) {
    header('Location: seasons'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $matchId = (int)$_POST['match_id'];
    $s1 = (int)$_POST['player1_score'];
    $s2 = (int)$_POST['player2_score'];
    
    $stmt = $pdo->prepare("SELECT * FROM matches WHERE id=? AND season_id=?");
    $stmt->execute([$matchId, $seasonId]);
    $m = $stmt->fetch();
    
    if (!$m) $error = 'Match tidak ditemukan.';
    elseif ($s1 === $s2) $error = 'Skor tidak boleh seri.';
    elseif (max($s1, $s2) < 11) $error = 'Salah satu pemain minimal harus mencapai 11 poin.';
    else {
        $winner = $s1 > $s2 ? $m['player1_id'] : $m['player2_id'];
        $stmt = $pdo->prepare("UPDATE matches SET player1_score=?, player2_score=?, winner_id=?, status='completed' WHERE id=?");
        $stmt->execute([$s1, $s2, $winner, $matchId]);
        header('Location: matches?season_id='.$seasonId.'&saved=1'); exit;
    }
}

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$search2 = isset($_GET['q2']) ? trim($_GET['q2']) : '';

// Complex query to get team names and partner info for matches
$sql = "SELECT m.*, 
        p1a.name AS p1_name, p1b.name AS p1_partner,
        p2a.name AS p2_name, p2b.name AS p2_partner
        FROM matches m 
        JOIN players p1a ON p1a.id = m.player1_id
        LEFT JOIN season_players sp1 ON sp1.player_id = m.player1_id AND sp1.season_id = m.season_id
        LEFT JOIN players p1b ON p1b.id = sp1.partner_id
        JOIN players p2a ON p2a.id = m.player2_id
        LEFT JOIN season_players sp2 ON sp2.player_id = m.player2_id AND sp2.season_id = m.season_id
        LEFT JOIN players p2b ON p2b.id = sp2.partner_id
        WHERE m.season_id = ?";
$params = [$seasonId];

if ($search || $search2) {
    $sql .= " AND (
        ( (p1a.name LIKE ? OR p1b.name LIKE ?) AND (p2a.name LIKE ? OR p2b.name LIKE ?) )
        OR 
        ( (p1a.name LIKE ? OR p1b.name LIKE ?) AND (p2a.name LIKE ? OR p2b.name LIKE ?) )
    ) ";
    
    $s1 = "%$search%";
    $s2 = "%$search2%";
    
    if ($search && $search2) {
        $params[] = $s1; $params[] = $s1; $params[] = $s2; $params[] = $s2;
        $params[] = $s2; $params[] = $s2; $params[] = $s1; $params[] = $s1;
    } else {
        $q = $search ?: $search2;
        $sq = "%$q%";
        $params[] = $sq; $params[] = $sq; $params[] = "%%"; $params[] = "%%";
        $params[] = "%%"; $params[] = "%%"; $params[] = $sq; $params[] = $sq;
    }
}

$sql .= " ORDER BY m.group_name, m.id";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$allMatches = $stmt->fetchAll();

$groupedMatches = [];
foreach($allMatches as $m) {
    $groupedMatches[$m['group_name']][] = $m;
}

$pageTitle = 'Input Skor - ' . $season['name'];
include 'includes/header.php';
?>

<div class="admin-header">
  <div>
    <h1>Input Skor Pertandingan</h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Tournament: <strong><?= e($season['name']) ?></strong></p>
  </div>
  <a href="seasons" class="btn btn-outline">&larr; Kembali</a>
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

<div class="admin-card" style="margin-bottom: 30px;">
    <form method="get" style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
      <input type="hidden" name="season_id" value="<?= $seasonId ?>">
      <input type="text" name="q" value="<?= e($search) ?>" placeholder="Pemain 1..." style="flex: 1; min-width: 150px; background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 10px 15px; border-radius: 8px;">
      <div style="font-weight: 900; color: var(--color-text-muted); font-size: 12px;">VS</div>
      <input type="text" name="q2" value="<?= e($search2) ?>" placeholder="Pemain 2..." style="flex: 1; min-width: 150px; background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 10px 15px; border-radius: 8px;">
      <button class="btn btn-primary">Cari Match</button>
      <?php if($search || $search2): ?><a href="matches?season_id=<?= $seasonId ?>" class="btn btn-outline">Reset</a><?php endif; ?>
    </form>
</div>

<?php foreach($groupedMatches as $groupName => $matches): ?>
<div class="admin-card" style="margin-bottom: 40px; padding: 0; overflow: hidden;">
  <div style="background: var(--color-bg-light); padding: 15px 20px; border-bottom: 1px solid var(--color-border); display: flex; align-items: center; gap: 10px;">
     <div style="width: 30px; height: 30px; background: var(--color-primary); color: #000; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-weight: 900;"><?= e($groupName) ?></div>
     <h2 style="margin: 0; font-size: 16px; letter-spacing: 1px;">GROUP <?= e($groupName) ?></h2>
  </div>
  <div class="table-wrap" style="border: none;">
    <table style="width: 100%; border-collapse: collapse;">
      <thead>
        <tr>
          <th style="text-align: left; padding: 12px; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border);">Pemain / Tim</th>
          <th style="text-align: center; padding: 12px; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border);">Skor</th>
          <th style="text-align: right; padding: 12px; color: var(--color-text-muted); border-bottom: 1px solid var(--color-border);">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($matches as $m): ?>
        <tr>
          <td style="padding: 15px 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <div style="display: flex; align-items: center; gap: 10px;">
               <div style="flex: 1; text-align: right;">
                  <div style="font-weight: 700;"><?= e($m['p1_name']) ?></div>
                  <?php if($m['p1_partner']): ?><div style="font-size: 11px; color: var(--color-text-muted);">& <?= e($m['p1_partner']) ?></div><?php endif; ?>
               </div>
               <div style="color: var(--color-text-muted); font-size: 12px; font-weight: 800;">VS</div>
               <div style="flex: 1; text-align: left;">
                  <div style="font-weight: 700;"><?= e($m['p2_name']) ?></div>
                  <?php if($m['p2_partner']): ?><div style="font-size: 11px; color: var(--color-text-muted);">& <?= e($m['p2_partner']) ?></div><?php endif; ?>
               </div>
            </div>
          </td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center;">
             <form method="post" style="display: flex; gap: 5px; justify-content: center; align-items: center;">
                <input type="hidden" name="match_id" value="<?= $m['id'] ?>">
                <input type="number" name="player1_score" value="<?= $m['player1_score'] ?>" min="0" required style="width: 45px; background: #000; border: 1px solid var(--color-border); color: var(--color-primary); text-align: center; font-weight: 800; border-radius: 4px; padding: 5px;">
                <span style="color: var(--color-text-muted);">:</span>
                <input type="number" name="player2_score" value="<?= $m['player2_score'] ?>" min="0" required style="width: 45px; background: #000; border: 1px solid var(--color-border); color: var(--color-primary); text-align: center; font-weight: 800; border-radius: 4px; padding: 5px;">
                <button class="btn btn-primary" style="padding: 6px 12px; font-size: 11px; margin-left: 10px;">Simpan</button>
             </form>
          </td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: right;">
             <div style="display: flex; gap: 5px; justify-content: flex-end;">
               <a href="match_edit?id=<?= $m['id'] ?>" class="btn btn-outline" style="padding: 5px 10px; font-size: 11px;">📝 Dok</a>
               <?php if($m['status'] === 'completed'): ?>
                  <span style="color: #4ade80; font-size: 11px; font-weight: 800; display: flex; align-items: center; gap: 5px;">✅ SELESAI</span>
               <?php endif; ?>
             </div>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endforeach; ?>

<?php if(empty($groupedMatches)): ?>
  <div class="admin-card" style="text-align: center; padding: 50px;">
    <div style="font-size: 40px; margin-bottom: 15px;">📅</div>
    <h3 style="margin-bottom: 10px;">Belum ada jadwal pertandingan</h3>
    <p style="color: var(--color-text-muted);">Silakan masuk ke menu Kelola Peserta lalu klik tombol "Update Jadwal".</p>
  </div>
<?php endif; ?>

<?php include 'includes/footer.php'; ?>
