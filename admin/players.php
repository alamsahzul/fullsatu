<?php
require 'includes/auth.php';
require '../config/db.php';
require '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $photoName = null;

    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['photo']['tmp_name'];
        $fileName = $_FILES['photo']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $uploadFileDir = '../assets/uploads/players/';
            $dest_path = $uploadFileDir . $newFileName;

            if(move_uploaded_file($fileTmpPath, $dest_path)) {
                $photoName = $newFileName;
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO players (name, photo) VALUES (?, ?)");
    $stmt->execute([$name, $photoName]);
    header('Location: players'); exit;
}
$error = '';
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // Check if player has any matches
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE player1_id = ? OR player2_id = ?");
    $stmt->execute([$id, $id]);
    if ($stmt->fetchColumn() > 0) {
        $error = 'Pemain tidak bisa dihapus dari Master List karena sudah memiliki riwayat pertandingan.';
    } else {
        $stmt = $pdo->prepare("SELECT photo FROM players WHERE id=?");
        $stmt->execute([$id]);
        $p = $stmt->fetch();
        if ($p && $p['photo']) {
            $photoPath = '../assets/uploads/players/' . $p['photo'];
            if (file_exists($photoPath)) unlink($photoPath);
        }

        $stmt = $pdo->prepare("DELETE FROM players WHERE id=?");
        $stmt->execute([$id]);
        header('Location: players'); exit;
    }
}
$players = $pdo->query("SELECT * FROM players ORDER BY name ASC")->fetchAll();

$pageTitle = 'Admin Pemain';
include 'includes/header.php';
?>

<div class="admin-header">
  <div>
    <h1>Kelola Pemain</h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Tambah dan hapus daftar pemain liga.</p>
  </div>
</div>

<?php if($error): ?>
  <div class="alert" style="background: #ef4444; color: white; margin-bottom: 24px;"><?= e($error) ?></div>
<?php endif; ?>

<div class="admin-card">
  <form method="post" enctype="multipart/form-data" style="max-width: 400px;">
    <div class="form-row">
      <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted);">Nama Pemain</label>
      <input name="name" required placeholder="Contoh: Harfan" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 12px; border-radius: 8px; width: 100%;">
    </div>
    <div class="form-row" style="margin-top: 15px;">
      <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted);">Foto Pemain (Opsional)</label>
      <input type="file" name="photo" accept="image/*" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 10px; border-radius: 8px; width: 100%;">
    </div>
    <div style="margin-top: 20px;">
      <button class="btn btn-primary">Tambah Pemain</button>
    </div>
  </form>
</div>

<div class="admin-card">
  <div class="table-wrap">
    <table style="width: 100%; border-collapse: collapse;">
      <thead>
        <tr>
          <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Foto</th>
          <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Nama Pemain</th>
          <th style="text-align: right; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($players as $p): ?>
        <tr>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
            <?php if($p['photo']): ?>
              <img src="<?= base_url('assets/uploads/players/' . $p['photo']) ?>" alt="<?= e($p['name']) ?>" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
            <?php else: ?>
              <img src="<?= base_url('assets/img/player_avatar.png') ?>" alt="Default" style="width: 40px; height: 40px; border-radius: 50%; opacity: 0.5;">
            <?php endif; ?>
          </td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);"><strong><?= e($p['name']) ?></strong></td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: right; white-space: nowrap;">
            <a class="btn btn-outline" style="padding: 6px 12px; font-size: 12px; margin-right: 5px;" href="player_edit?id=<?= $p['id'] ?>">Edit</a>
            <a class="btn btn-outline" style="color: #ef4444; border-color: rgba(239,68,68,0.3); padding: 6px 12px; font-size: 12px;" onclick="return confirm('Yakin ingin menghapus pemain ini?')" href="?delete=<?= $p['id'] ?>">Hapus</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($players)): ?>
        <tr>
          <td colspan="2" style="text-align: center; padding: 20px; color: var(--color-text-muted);">Belum ada pemain.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
