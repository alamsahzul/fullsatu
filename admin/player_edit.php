<?php
require 'includes/auth.php';
require '../config/db.php';
require '../includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$stmt = $pdo->prepare("SELECT * FROM players WHERE id = ?");
$stmt->execute([$id]);
$player = $stmt->fetch();

if (!$player) {
    header('Location: players');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $photoName = $player['photo'];

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
                // Delete old photo if exists
                if ($player['photo']) {
                    $oldPhotoPath = $uploadFileDir . $player['photo'];
                    if (file_exists($oldPhotoPath)) unlink($oldPhotoPath);
                }
                $photoName = $newFileName;
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE players SET name = ?, photo = ? WHERE id = ?");
    $stmt->execute([$name, $photoName, $id]);
    header('Location: players');
    exit;
}

$pageTitle = 'Edit Pemain - ' . $player['name'];
include 'includes/header.php';
?>

<div class="admin-header">
  <div>
    <h1>Edit Pemain</h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Perbarui informasi pemain terpilih.</p>
  </div>
</div>

<div class="admin-card">
  <form method="post" enctype="multipart/form-data" style="max-width: 400px;">
    <div class="form-row" style="margin-bottom: 20px; text-align: center;">
      <div style="margin-bottom: 10px; color: var(--color-text-muted); font-size: 14px;">Foto Saat Ini</div>
      <?php if($player['photo']): ?>
        <img src="<?= base_url('assets/uploads/players/' . $player['photo']) ?>" alt="<?= e($player['name']) ?>" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 3px solid var(--color-primary);">
      <?php else: ?>
        <img src="<?= base_url('assets/img/player_avatar.png') ?>" alt="Default" style="width: 100px; height: 100px; border-radius: 50%; border: 3px solid var(--color-border); opacity: 0.5;">
      <?php endif; ?>
    </div>

    <div class="form-row">
      <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted);">Nama Pemain</label>
      <input name="name" required value="<?= e($player['name']) ?>" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 12px; border-radius: 8px; width: 100%;">
    </div>
    
    <div class="form-row" style="margin-top: 15px;">
      <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted);">Ganti Foto (Opsional)</label>
      <input type="file" name="photo" accept="image/*" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 10px; border-radius: 8px; width: 100%;">
      <p style="font-size: 11px; color: var(--color-text-muted); margin-top: 5px;">Biarkan kosong jika tidak ingin mengganti foto.</p>
    </div>

    <div style="margin-top: 30px; display: flex; gap: 10px;">
      <button class="btn btn-primary">Simpan Perubahan</button>
      <a href="players" class="btn btn-outline">Batal</a>
    </div>
  </form>
</div>

<?php include 'includes/footer.php'; ?>
