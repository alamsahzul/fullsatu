<?php
require 'includes/auth.php';
require '../config/db.php';
require '../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare("SELECT m.*, p1.name AS p1_name, p2.name AS p2_name, s.category AS season_category
                       FROM matches m 
                       JOIN players p1 ON p1.id = m.player1_id 
                       JOIN players p2 ON p2.id = m.player2_id 
                       JOIN seasons s ON s.id = m.season_id
                       WHERE m.id = ?");
$stmt->execute([$id]);
$m = $stmt->fetch();

if (!$m) {
    header('Location: matches');
    exit;
}

$seasonId = $m['season_id'];
// Get all participants for partner selection from the correct season
$stmt = $pdo->prepare("SELECT p.id, p.name FROM season_players sp JOIN players p ON p.id = sp.player_id WHERE sp.season_id = ? ORDER BY p.name ASC");
$stmt->execute([$seasonId]);
$allParticipants = $stmt->fetchAll();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s1 = isset($_POST['player1_score']) ? (int)$_POST['player1_score'] : $m['player1_score'];
    $s2 = isset($_POST['player2_score']) ? (int)$_POST['player2_score'] : $m['player2_score'];
    $notes = $_POST['match_notes'] ?? '';
    $p1p = !empty($_POST['p1_partner_id']) ? (int)$_POST['p1_partner_id'] : null;
    $p2p = !empty($_POST['p2_partner_id']) ? (int)$_POST['p2_partner_id'] : null;
    
    if ($s1 === $s2 && ($s1 !== 0 || $s2 !== 0)) {
        $error = 'Skor tidak boleh seri.';
    } elseif (max($s1, $s2) > 0 && max($s1, $s2) < 11) {
        $error = 'Salah satu tim minimal harus mencapai 11 poin.';
    } else {
        $winner = null;
        if ($s1 > $s2) $winner = $m['player1_id'];
        elseif ($s2 > $s1) $winner = $m['player2_id'];
        
        $status = ($s1 > 0 || $s2 > 0) ? 'completed' : 'pending';
        $photoName = $m['match_photo'];

        // Handle Photo Upload
        if (isset($_FILES['match_photo']) && $_FILES['match_photo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['match_photo']['name'], PATHINFO_EXTENSION));
            $photoName = 'match_' . $id . '_' . time() . '.' . $ext;
            $target = '../assets/uploads/matches/' . $photoName;
            
            // Create directory if not exists
            if (!is_dir('../assets/uploads/matches/')) {
                mkdir('../assets/uploads/matches/', 0777, true);
            }

            if (move_uploaded_file($_FILES['match_photo']['tmp_name'], $target)) {
                if ($m['match_photo'] && file_exists('../assets/uploads/matches/' . $m['match_photo'])) {
                    unlink('../assets/uploads/matches/' . $m['match_photo']);
                }
            }
        }

        $stmt = $pdo->prepare("UPDATE matches SET player1_score=?, player2_score=?, winner_id=?, p1_partner_id=?, p2_partner_id=?, match_photo=?, match_notes=?, status=? WHERE id=?");
        $stmt->execute([$s1, $s2, $winner, $p1p, $p2p, $photoName, $notes, $status, $id]);
        
        header('Location: matches?season_id='.$seasonId.'&saved=1');
        exit;
    }
}

$pageTitle = 'Edit Dokumentasi Match - Admin';
include 'includes/header.php';
?>

<div class="admin-header">
  <div>
    <h1>Dokumentasi Pertandingan</h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Kategori: <span style="color:var(--color-primary); font-weight:700; text-transform:uppercase;"><?= e($m['season_category']) ?></span></p>
  </div>
  <a href="matches" class="btn btn-outline">&larr; Kembali</a>
</div>

<?php if($error): ?>
<div class="alert" style="background: #ef4444; color: white; margin-bottom: 24px;"><?= e($error) ?></div>
<?php endif; ?>

<div class="admin-card">
  <form method="post" enctype="multipart/form-data">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 30px;">
      
      <!-- SCORE & PARTNERS INPUT -->
      <div style="background: var(--color-bg-dark); padding: 30px; border-radius: 12px;">
        <h3 style="margin-bottom: 25px; font-size: 16px; color: var(--color-text-muted); text-align: center;">SKOR & KOMPOSISI TIM</h3>
        <div style="display: flex; align-items: flex-start; justify-content: center; gap: 20px;">
          
          <div style="flex: 1; text-align: center;">
            <div style="font-weight: 900; margin-bottom: 10px; color: var(--color-primary);"><?= e($m['p1_name']) ?></div>
            <?php if($m['season_category'] === 'double'): ?>
              <select name="p1_partner_id" style="width: 100%; background: #000; border: 1px solid var(--color-border); color: #fff; padding: 8px; border-radius: 6px; margin-bottom: 15px; font-size: 12px;">
                <option value="">+ Tambah Pasangan</option>
                <?php foreach($allParticipants as $p): if($p['id'] == $m['player1_id']) continue; ?>
                  <option value="<?= $p['id'] ?>" <?= $m['p1_partner_id'] == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
            <input type="number" name="player1_score" value="<?= e($m['player1_score'] ?? '') ?>" required style="width: 80px; font-size: 32px; font-weight: 900; background: #000; border: 1px solid var(--color-border); color: var(--color-primary); padding: 10px; border-radius: 8px; text-align: center;">
          </div>

          <div style="font-size: 24px; font-weight: 900; color: var(--color-text-muted); padding-top: 50px;">VS</div>

          <div style="flex: 1; text-align: center;">
            <div style="font-weight: 900; margin-bottom: 10px; color: var(--color-primary);"><?= e($m['p2_name']) ?></div>
            <?php if($m['season_category'] === 'double'): ?>
              <select name="p2_partner_id" style="width: 100%; background: #000; border: 1px solid var(--color-border); color: #fff; padding: 8px; border-radius: 6px; margin-bottom: 15px; font-size: 12px;">
                <option value="">+ Tambah Pasangan</option>
                <?php foreach($allParticipants as $p): if($p['id'] == $m['player2_id']) continue; ?>
                  <option value="<?= $p['id'] ?>" <?= $m['p2_partner_id'] == $p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                <?php endforeach; ?>
              </select>
            <?php endif; ?>
            <input type="number" name="player2_score" value="<?= e($m['player2_score'] ?? '') ?>" required style="width: 80px; font-size: 32px; font-weight: 900; background: #000; border: 1px solid var(--color-border); color: var(--color-primary); padding: 10px; border-radius: 8px; text-align: center;">
          </div>

        </div>
      </div>

      <!-- PHOTO UPLOAD -->
      <div>
        <h3 style="margin-bottom: 20px; font-size: 16px; color: var(--color-text-muted);">FOTO PERTANDINGAN</h3>
        <?php if($m['match_photo']): ?>
          <div style="margin-bottom: 15px;">
            <img src="<?= base_url('assets/uploads/matches/' . $m['match_photo']) ?>" style="width: 100%; max-height: 150px; object-fit: cover; border-radius: 8px; border: 1px solid var(--color-border);">
          </div>
        <?php endif; ?>
        <input type="file" name="match_photo" accept="image/*" style="width: 100%; background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 10px; border-radius: 8px;">
        <p style="font-size: 12px; color: var(--color-text-muted); margin-top: 8px;">Pilih foto bukti pertandingan (JPG/PNG).</p>
      </div>

    </div>

    <!-- NOTES AREA -->
    <div style="margin-bottom: 30px;">
      <h3 style="margin-bottom: 15px; font-size: 16px; color: var(--color-text-muted);">CATATAN / JALANNYA PERTANDINGAN</h3>
      <textarea name="match_notes" rows="5" style="width: 100%; background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 15px; border-radius: 8px; resize: vertical;"><?= e($m['match_notes'] ?? '') ?></textarea>
    </div>

    <button type="submit" class="btn btn-primary" style="width: 100%; padding: 18px; font-size: 18px;">SIMPAN PERTANDINGAN</button>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
