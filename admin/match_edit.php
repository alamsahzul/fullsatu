<?php
require 'includes/auth.php';
require '../config/db.php';
require '../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$season = getCurrentSeason($pdo);

$stmt = $pdo->prepare("SELECT m.*, p1.name AS p1_name, p2.name AS p2_name 
                       FROM matches m 
                       JOIN players p1 ON p1.id = m.player1_id 
                       JOIN players p2 ON p2.id = m.player2_id 
                       WHERE m.id = ? AND m.season_id = ?");
$stmt->execute([$id, $season['id'] ?? 0]);
$m = $stmt->fetch();

if (!$m) {
    header('Location: matches');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $s1 = (int)$_POST['player1_score'];
    $s2 = (int)$_POST['player2_score'];
    $notes = $_POST['match_notes'] ?? '';
    
    if ($s1 === $s2) {
        $error = 'Skor tidak boleh seri.';
    } elseif (max($s1, $s2) < 11) {
        $error = 'Salah satu pemain minimal harus mencapai 11 poin.';
    } else {
        $winner = $s1 > $s2 ? $m['player1_id'] : $m['player2_id'];
        $photoName = $m['match_photo'];

        // Handle Photo Upload
        if (isset($_FILES['match_photo']) && $_FILES['match_photo']['error'] === UPLOAD_ERR_OK) {
            $ext = pathinfo($_FILES['match_photo']['name'], PATHINFO_EXTENSION);
            $photoName = 'match_' . $id . '_' . time() . '.' . $ext;
            $target = '../assets/uploads/matches/' . $photoName;
            
            if (move_uploaded_file($_FILES['match_photo']['tmp_path'] ?? $_FILES['match_photo']['tmp_name'], $target)) {
                // Delete old photo if exists
                if ($m['match_photo'] && file_exists('../assets/uploads/matches/' . $m['match_photo'])) {
                    unlink('../assets/uploads/matches/' . $m['match_photo']);
                }
            }
        }

        $stmt = $pdo->prepare("UPDATE matches SET player1_score=?, player2_score=?, winner_id=?, match_photo=?, match_notes=?, status='completed' WHERE id=?");
        $stmt->execute([$s1, $s2, $winner, $photoName, $notes, $id]);
        
        header('Location: matches?saved=1');
        exit;
    }
}

$pageTitle = 'Edit Dokumentasi Match - Admin';
include 'includes/header.php';
?>

<div class="admin-header">
  <div>
    <h1>Dokumentasi Pertandingan</h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Lengkapi skor, foto, dan catatan pertandingan.</p>
  </div>
  <a href="matches" class="btn btn-outline">&larr; Kembali</a>
</div>

<?php if($error): ?>
<div class="alert" style="background: #ef4444; color: white; margin-bottom: 24px;"><?= e($error) ?></div>
<?php endif; ?>

<div class="admin-card">
  <form method="post" enctype="multipart/form-data">
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px; margin-bottom: 30px;">
      
      <!-- SCORE INPUT -->
      <div style="background: var(--color-bg-dark); padding: 30px; border-radius: 12px; text-align: center;">
        <h3 style="margin-bottom: 20px; font-size: 16px; color: var(--color-text-muted);">INPUT SKOR</h3>
        <div style="display: flex; align-items: center; justify-content: center; gap: 20px;">
          <div style="flex: 1;">
            <div style="font-weight: 700; margin-bottom: 10px;"><?= e($m['p1_name']) ?></div>
            <input type="number" name="player1_score" value="<?= e($m['player1_score'] ?? '') ?>" required style="width: 80px; font-size: 32px; font-weight: 900; background: #000; border: 1px solid var(--color-border); color: var(--color-primary); padding: 10px; border-radius: 8px; text-align: center;">
          </div>
          <div style="font-size: 24px; font-weight: 900; color: var(--color-text-muted); padding-top: 25px;">VS</div>
          <div style="flex: 1;">
            <div style="font-weight: 700; margin-bottom: 10px;"><?= e($m['p2_name']) ?></div>
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
