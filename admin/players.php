<?php
require '../config/db.php';
require '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $stmt = $pdo->prepare("INSERT INTO players (name) VALUES (?)");
    $stmt->execute([trim($_POST['name'])]);
    header('Location: players.php'); exit;
}
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM players WHERE id=?");
    $stmt->execute([(int)$_GET['delete']]);
    header('Location: players.php'); exit;
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

<div class="admin-card">
  <form method="post" style="max-width: 400px;">
    <div class="form-row">
      <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted);">Nama Pemain</label>
      <input name="name" required placeholder="Contoh: Harfan" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 12px; border-radius: 8px; width: 100%;">
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
          <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Nama Pemain</th>
          <th style="text-align: right; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($players as $p): ?>
        <tr>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);"><strong><?= e($p['name']) ?></strong></td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: right;">
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
