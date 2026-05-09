<?php
require 'includes/auth.php';
require '../config/db.php';
require '../includes/functions.php';

$error = '';
$success = '';

// Handle deletion
if (isset($_GET['delete_id'])) {
    $did = (int)$_GET['delete_id'];
    
    // Safety check: count completed matches
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE season_id = ? AND status = 'completed'");
    $stmt->execute([$did]);
    if ($stmt->fetchColumn() > 0) {
        $error = "Musim tidak bisa dihapus karena sudah memiliki riwayat hasil pertandingan.";
    } else {
        $pdo->prepare("DELETE FROM seasons WHERE id = ?")->execute([$did]);
        $success = "Musim berhasil dihapus.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $name = trim($_POST['name']);
    $category = $_POST['category'];
    $format = $_POST['format'];
    $group_count = ($format === 'cup') ? 0 : (int)$_POST['group_count'];
    $league_type = ($format === 'cup') ? 'half' : $_POST['league_type'];
    $qualifiers_per_group = ($format === 'hybrid') ? max(1, (int)$_POST['qualifiers_per_group']) : 2;
    
    $stmt = $pdo->prepare("INSERT INTO seasons (name, league_type, category, format, group_count, qualifiers_per_group, status) VALUES (?, ?, ?, ?, ?, ?, 'active')");
    $stmt->execute([$name, $league_type, $category, $format, $group_count, $qualifiers_per_group]);
    header('Location: seasons?saved=1'); exit;
}
$seasons = $pdo->query("SELECT * FROM seasons ORDER BY id DESC")->fetchAll();

$pageTitle = 'Admin Tournament';
include 'includes/header.php';
?>

<div class="admin-header">
  <div>
    <h1>Kelola Tournament</h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Konfigurasi liga, cup, atau hybrid dengan jumlah grup fleksibel.</p>
  </div>
</div>

<?php if($error): ?><div class="alert" style="background: #ef4444; color: white; margin-bottom: 20px;"><?= e($error) ?></div><?php endif; ?>
<?php if($success || isset($_GET['saved'])): ?><div class="alert" style="margin-bottom: 20px;"><?= $success ?: 'Tournament berhasil disimpan.' ?></div><?php endif; ?>

<div class="admin-card">
  <form method="post">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; align-items: end;">
      <div class="form-row" style="margin-bottom: 0;">
        <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted);">Nama Tournament</label>
        <input name="name" required placeholder="Contoh: Tournament Merdeka 2026" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 12px; border-radius: 8px; width: 100%;">
      </div>
      <div class="form-row" style="margin-bottom: 0;">
        <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted);">Kategori</label>
        <select name="category" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 12px; border-radius: 8px; width: 100%;">
          <option value="single">Single (Perorangan)</option>
          <option value="double">Double (Ganda)</option>
        </select>
      </div>
      <div class="form-row" style="margin-bottom: 0;">
        <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted);">Format</label>
        <select name="format" id="formatSelect" onchange="toggleFormatFields()" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 12px; border-radius: 8px; width: 100%;">
          <option value="league">Liga</option>
          <option value="cup">Knockout</option>
          <option value="hybrid">Hybrid</option>
        </select>
      </div>
      <div class="form-row" id="groupCountRow" style="margin-bottom: 0;">
        <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted);">Jumlah Grup</label>
        <input type="number" name="group_count" id="groupCountInput" value="1" min="0" max="10" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 12px; border-radius: 8px; width: 100%;">
      </div>
      <div class="form-row" id="leagueTypeRow" style="margin-bottom: 0;">
        <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted);">Tipe Pertemuan</label>
        <select name="league_type" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 12px; border-radius: 8px; width: 100%;">
          <option value="half">1x Pertemuan (Half League)</option>
          <option value="full">2x Pertemuan (Full League)</option>
        </select>
      </div>
      <div class="form-row" id="qualifiersRow" style="margin-bottom: 0;">
        <label style="display: block; margin-bottom: 8px; color: var(--color-text-muted);">Lolos per Grup (ke Cup)</label>
        <input type="number" name="qualifiers_per_group" id="qualifiersInput" value="2" min="1" max="8" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 12px; border-radius: 8px; width: 100%;">
      </div>
      <div style="margin-bottom: 0;">
        <button type="submit" id="submitBtn" class="btn btn-primary" style="width: 100%; padding: 13px 24px;">Buat Tournament</button>
      </div>
    </div>
  </form>
</div>

<script>
function toggleFormatFields() {
    const format = document.getElementById('formatSelect').value;
    const groupRow = document.getElementById('groupCountRow');
    const groupInput = document.getElementById('groupCountInput');
    const leagueRow = document.getElementById('leagueTypeRow');
    const qualifiersRow = document.getElementById('qualifiersRow');
    const submitBtn = document.getElementById('submitBtn');
    
    if (format === 'cup') {
        groupRow.style.display = 'none';
        leagueRow.style.display = 'none';
        qualifiersRow.style.display = 'none';
        groupInput.value = 0;
        groupInput.min = 0;
        submitBtn.innerText = 'Buat Knockout Tournament';
    } else if (format === 'league') {
        groupRow.style.display = 'none';
        leagueRow.style.display = 'block';
        qualifiersRow.style.display = 'none';
        groupInput.value = 1;
        groupInput.min = 1;
        submitBtn.innerText = 'Buat Liga Tournament';
    } else {
        groupRow.style.display = 'block';
        leagueRow.style.display = 'block';
        qualifiersRow.style.display = 'block';
        if(groupInput.value <= 1) groupInput.value = 2;
        groupInput.min = 2;
        submitBtn.innerText = 'Buat Hybrid Tournament';
    }
}
// Run once on load
toggleFormatFields();
</script>

<div class="admin-card">
  <div class="table-wrap">
    <table style="width: 100%; border-collapse: collapse;">
      <thead>
        <tr>
          <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Nama Tournament</th>
          <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Tipe / Kategori</th>
          <th style="text-align: right; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Aksi</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($seasons as $s): ?>
        <tr>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);"><strong><?= e($s['name']) ?></strong></td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
             <div style="text-transform: capitalize; font-size: 13px; font-weight: 700; color: var(--color-primary);"><?= e($s['format']) ?></div>
             <div style="font-size: 11px; color: var(--color-text-muted);">
                <?= e($s['category'] ?? 'single') ?> | <?= e($s['group_count']) ?> Grup | <?= e($s['league_type']) ?>
             </div>
          </td>
          <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: right; display: flex; gap: 8px; justify-content: flex-end; align-items: center; flex-wrap: wrap;">
            <a href="season_players?season_id=<?= $s['id'] ?>" class="btn btn-outline" style="padding: 5px 12px; font-size: 11px;">Kelola Peserta</a>
            
            <?php if($s['format'] !== 'cup'): ?>
              <a href="matches?season_id=<?= $s['id'] ?>" class="btn btn-outline" style="padding: 5px 12px; font-size: 11px; border-color: #fbbf24; color: #fbbf24;">Input Skor</a>
            <?php endif; ?>

            <?php if($s['format'] !== 'league'): ?>
              <a href="cup?season_id=<?= $s['id'] ?>" class="btn btn-outline" style="padding: 5px 12px; font-size: 11px; border-color: var(--color-primary); color: var(--color-primary);">Kelola Knockout</a>
            <?php endif; ?>

            <a href="?delete_id=<?= $s['id'] ?>" class="btn btn-outline" style="padding: 5px 12px; font-size: 11px; border-color: #ef4444; color: #ef4444;" onclick="return confirm('Yakin ingin menghapus tournament ini? Semua jadwal yang belum ada skornya akan ikut terhapus.')">Hapus</a>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($seasons)): ?>
        <tr>
          <td colspan="3" style="text-align: center; padding: 20px; color: var(--color-text-muted);">Belum ada tournament.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'includes/footer.php'; ?>
