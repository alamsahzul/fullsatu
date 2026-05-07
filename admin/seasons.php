<?php
require 'includes/auth.php';
require '../config/db.php';
require '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $stmt = $pdo->prepare("INSERT INTO seasons (name, league_type, status) VALUES (?, ?, 'active')");
    $stmt->execute([trim($_POST['name']), $_POST['league_type']]);
    header('Location: seasons'); exit;
}
$seasons = $pdo->query("SELECT * FROM seasons ORDER BY id DESC")->fetchAll();

$pageTitle = 'Admin Season';
include 'includes/header.php';
?>

<div class="admin-header">
  <div>
    <h1>Kelola Musim (Season)</h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Buat musim liga baru dengan tipe Half atau Full League.</p>
  </div>
</div>

<div class="admin-card">
  <form method="post">
    <div class="admin-grid" style="gap: 20px; align-items: end;">
      <div class="form-row" style="margin-bottom: 0;">
        <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted);">Nama Season</label>
        <input name="name" required placeholder="Contoh: Season 1 - 2026" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 12px; border-radius: 8px; width: 100%;">
      </div>
      <div class="form-row" style="margin-bottom: 0;">
        <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted);">Tipe Liga</label>
        <select name="league_type" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 12px; border-radius: 8px; width: 100%;">
          <option value="half">Half League (1 Leg)</option>
          <option value="full">Full League (2 Leg)</option>
        </select>
      </div>
      <div style="margin-bottom: 0;">
        <button class="btn btn-primary" style="padding: 13px 24px;">Buat Season</button>
      </div>
    </div>
  </form>
</div>

<div class="admin-card">
  <div class="table-wrap">
    <table style="width: 100%; border-collapse: collapse;">
      <thead>
        <tr>
          <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Nama Season</th>
          <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Tipe</th>
          <th style="text-align: right; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Status</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($seasons as $s): ?>
        <tr>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);"><strong><?= e($s['name']) ?></strong></td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-transform: capitalize;"><?= e($s['league_type']) ?></td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: right;">
            <span style="background: var(--color-bg-dark); border: 1px solid <?= $s['status'] == 'active' ? 'var(--color-primary)' : 'var(--color-border)' ?>; color: <?= $s['status'] == 'active' ? 'var(--color-primary)' : 'var(--color-text-muted)' ?>; padding: 4px 12px; border-radius: 99px; font-size: 12px; text-transform: uppercase;">
              <?= e($s['status']) ?>
            </span>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($seasons)): ?>
        <tr>
          <td colspan="3" style="text-align: center; padding: 20px; color: var(--color-text-muted);">Belum ada season.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
