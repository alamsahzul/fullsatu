<?php
require 'includes/auth.php';
require '../config/db.php';
require '../includes/functions.php';

$seasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;
if ($seasonId === 0) {
    header('Location: seasons'); exit;
}

$stmt = $pdo->prepare("SELECT * FROM seasons WHERE id = ?");
$stmt->execute([$seasonId]);
$season = $stmt->fetch();

if (!$season) {
    header('Location: seasons'); exit;
}

$error = '';
$success = '';

// LOCK: Check if cup phase has started (brackets exist)
$cupCheck = $pdo->prepare("SELECT COUNT(*) FROM tournament_brackets WHERE season_id = ?");
$cupCheck->execute([$seasonId]);
$cupStarted = ($season['format'] === 'hybrid' && $cupCheck->fetchColumn() > 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($cupStarted) {
        $error = "Tournament sudah masuk Fase Cup! Peserta dan grup tidak bisa diubah lagi.";
    } else {
    // BULK ADD PARTICIPANTS
    if (isset($_POST['add_bulk'])) {
        $playerIds = $_POST['selected_players'] ?? [];
        $count = 0;
        foreach ($playerIds as $pid) {
            $pid = (int)$pid;
            // Check if already in season (as player or partner)
            $check = $pdo->prepare("SELECT COUNT(*) FROM season_players WHERE season_id=? AND (player_id=? OR partner_id=?)");
            $check->execute([$seasonId, $pid, $pid]);
            if ($check->fetchColumn() == 0) {
                $stmt = $pdo->prepare("INSERT INTO season_players (season_id, player_id, group_name) VALUES (?, ?, 'A')");
                $stmt->execute([$seasonId, $pid]);
                $count++;
            }
        }
        if ($count > 0) $success = "$count pemain berhasil ditambahkan ke tournament.";
        else $error = "Pemain yang dipilih sudah terdaftar semua.";
    }

    // PAIR UP (For Double)
    if (isset($_POST['pair_up'])) {
        $spId = (int)$_POST['sp_id'];
        $partnerId = (int)$_POST['partner_id'];
        
        $curr = $pdo->prepare("SELECT partner_id FROM season_players WHERE id = ?");
        $curr->execute([$spId]);
        $oldPartner = $curr->fetchColumn();
        
        if ($partnerId === 0) {
            $pdo->prepare("UPDATE season_players SET partner_id = NULL WHERE id = ?")->execute([$spId]);
            
            if ($oldPartner) {
                $pdo->prepare("INSERT INTO season_players (season_id, player_id, group_name) VALUES (?, ?, 'A')")->execute([$seasonId, $oldPartner]);
            }
            $success = "Pasangan berhasil dipisah.";
        } else {
            // Cek apakah partner yang dipilih sudah punya pasangan lain
            $check = $pdo->prepare("
                SELECT COUNT(*) FROM season_players 
                WHERE season_id=? 
                AND ((player_id=? AND partner_id IS NOT NULL) OR (partner_id=?)) 
                AND id != ?
            ");
            $check->execute([$seasonId, $partnerId, $partnerId, $spId]);
            
            if ($check->fetchColumn() > 0) {
                $error = "Partner yang dipilih sudah memiliki pasangan di tim lain.";
            } else {
                // Jika mengganti pasangan (bukan dari 0), pulihkan pasangan lama ke daftar jomblo
                if ($oldPartner && $oldPartner != $partnerId) {
                    $pdo->prepare("INSERT INTO season_players (season_id, player_id, group_name) VALUES (?, ?, 'A')")->execute([$seasonId, $oldPartner]);
                }
                
                // Hapus baris individual milik partner yang baru agar melebur
                $pdo->prepare("DELETE FROM season_players WHERE season_id=? AND player_id=? AND id != ?")->execute([$seasonId, $partnerId, $spId]);
                
                // Set pasangan baru
                $pdo->prepare("UPDATE season_players SET partner_id = ? WHERE id = ?")->execute([$partnerId, $spId]);
                $success = "Pasangan berhasil diperbarui.";
            }
        }
    }

    // Update Groups
    if (isset($_POST['update_groups']) && isset($_POST['group']) && is_array($_POST['group'])) {
        foreach ($_POST['group'] as $sp_id => $group) {
            $pdo->prepare("UPDATE season_players SET group_name=? WHERE id=? AND season_id=?")->execute([strtoupper($group), (int)$sp_id, $seasonId]);
        }
        $success = "Pembagian grup berhasil diperbarui.";
    }

    // Remove Participant
    if (isset($_POST['delete_sp_id'])) {
        $sp_id = (int)$_POST['delete_sp_id'];
        $pdo->prepare("DELETE FROM season_players WHERE id=? AND season_id=?")->execute([$sp_id, $seasonId]);
        $success = "Peserta berhasil dihapus.";
    }
    } // end !cupStarted check
}

// Get all master players
$players = $pdo->query("SELECT id, name FROM players ORDER BY name ASC")->fetchAll();

// Get active participants
$stmt = $pdo->prepare("SELECT sp.*, p1.name AS p1_name, p2.name AS p2_name, p1.photo AS photo1, p2.photo AS photo2 
                       FROM season_players sp 
                       JOIN players p1 ON p1.id = sp.player_id 
                       LEFT JOIN players p2 ON p2.id = sp.partner_id 
                       WHERE sp.season_id = ? 
                       ORDER BY sp.group_name ASC, p1_name ASC");
$stmt->execute([$seasonId]);
$activeParticipants = $stmt->fetchAll();

$registeredIds = [];
foreach ($activeParticipants as $ap) {
    if ($ap['player_id']) $registeredIds[] = $ap['player_id'];
    if ($ap['partner_id']) $registeredIds[] = $ap['partner_id'];
}

$availablePartners = [];
foreach ($activeParticipants as $ap) {
    if (empty($ap['partner_id'])) {
        $availablePartners[] = $ap;
    }
}

$pageTitle = 'Kelola Peserta - ' . $season['name'];
include 'includes/header.php';
?>

<div class="admin-header">
  <div>
    <h1>Kelola Peserta Tournament</h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Tournament: <strong><?= e($season['name']) ?></strong> | Kategori: <strong><?= strtoupper(e($season['category'])) ?></strong></p>
  </div>
  <a href="seasons" class="btn btn-outline">&larr; Kembali ke Daftar</a>
</div>

<?php if($error): ?><div class="alert" style="background: #ef4444; color: white; margin-bottom: 24px;"><?= e($error) ?></div><?php endif; ?>
<?php if($success): ?><div class="alert" style="margin-bottom: 24px;"><?= e($success) ?></div><?php endif; ?>
<?php if(isset($_GET['cup_locked'])): ?><div class="alert" style="background: #ef4444; color: white; margin-bottom: 24px;">⛔ Jadwal tidak bisa diperbarui — Tournament sudah masuk Fase Knockout!</div><?php endif; ?>

<?php if($cupStarted): ?>
<div style="background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.4); border-radius: 12px; padding: 16px 20px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
  <span style="font-size: 24px;">🔒</span>
  <div>
    <div style="font-weight: 800; color: var(--color-primary); font-size: 14px;">FASE KNOCKOUT SUDAH DIMULAI</div>
    <div style="font-size: 13px; color: var(--color-text-muted); margin-top: 2px;">Peserta, pasangan, dan pembagian grup tidak dapat diubah. Data fase grup ini hanya bisa dilihat sebagai referensi.</div>
  </div>
  <a href="cup?season_id=<?= $seasonId ?>" class="btn btn-primary" style="margin-left: auto; white-space: nowrap;">Ke Bagan Knockout →</a>
</div>
<?php endif; ?>

<div style="display: grid; grid-template-columns: 350px 1fr; gap: 30px; align-items: start;">
  
  <!-- LEFT: MASTER PLAYERS SEARCH & MULTISELECT -->
  <div class="admin-card" style="position: sticky; top: 100px; padding: 20px;">
    <h2 style="margin-top: 0; font-size: 16px; margin-bottom: 15px; color: var(--color-primary);">Cari & Tambah Pemain</h2>
    <input type="text" id="playerSearch" placeholder="Ketik nama pemain..." style="width: 100%; background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 10px; border-radius: 8px; margin-bottom: 15px;">
    
    <form method="post">
      <div id="playerList" style="max-height: 400px; overflow-y: auto; border: 1px solid var(--color-border); border-radius: 8px; background: rgba(0,0,0,0.2); padding: 5px; margin-bottom: 15px;">
        <?php foreach($players as $p): 
            $isReg = in_array($p['id'], $registeredIds);
        ?>
          <label class="player-item" style="display: flex; align-items: center; gap: 10px; padding: 8px; border-radius: 6px; cursor: <?= $isReg ? 'not-allowed' : 'pointer' ?>; transition: 0.2s; opacity: <?= $isReg ? '0.5' : '1' ?>; background: <?= $isReg ? 'rgba(0,0,0,0.2)' : 'transparent' ?>;">
             <input type="checkbox" name="selected_players[]" value="<?= $p['id'] ?>" class="player-checkbox" <?= $isReg ? 'checked disabled' : '' ?>>
             <span class="player-name" style="font-size: 14px; text-decoration: <?= $isReg ? 'line-through' : 'none' ?>;"><?= e($p['name']) ?></span>
             <?php if($isReg): ?><span style="font-size: 9px; background: var(--color-primary); color: #000; padding: 2px 6px; border-radius: 4px; font-weight: 800; margin-left: auto;">TERDAFTAR</span><?php endif; ?>
          </label>
        <?php endforeach; ?>
      </div>
      <button type="submit" name="add_bulk" class="btn btn-primary" style="width: 100%; padding: 12px; font-weight: 800;">TAMBAHKAN TERPILIH ( <span id="selectedCount">0</span> )</button>
    </form>
  </div>

  <!-- RIGHT: ACTIVE PARTICIPANTS & PAIRING -->
  <div class="admin-card">
    <form method="post">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 style="margin: 0; font-size: 18px;">Peserta Terdaftar (<?= count($activeParticipants) ?>)</h2>
        <div style="display: flex; gap: 10px;">
          <?php if($season['format'] === 'hybrid' && $season['group_count'] > 1): ?>
            <button type="submit" name="update_groups" class="btn btn-outline" style="font-size: 12px; padding: 8px 15px;">Update Grup</button>
          <?php endif; ?>
          
          <?php if($season['format'] !== 'cup'): ?>
            <a href="generate?season_id=<?= $seasonId ?>" class="btn btn-primary" style="font-size: 12px; padding: 8px 15px;">Update Jadwal →</a>
          <?php else: ?>
            <a href="cup?season_id=<?= $seasonId ?>" class="btn btn-primary" style="font-size: 12px; padding: 8px 15px;">Kelola Bagan Cup →</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="table-wrap">
        <table style="width: 100%; border-collapse: collapse;">
          <thead>
            <tr>
              <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Nama Peserta / Tim</th>
              <?php if($season['category'] === 'double'): ?>
                <th style="text-align: left; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Partner (Pasangan)</th>
              <?php endif; ?>
              <?php if($season['format'] === 'hybrid' && $season['group_count'] > 1): ?>
                <th style="text-align: center; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Grup</th>
              <?php endif; ?>
              <th style="text-align: right; padding: 12px; border-bottom: 1px solid var(--color-border); color: var(--color-text-muted);">Hapus</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($activeParticipants as $ap): ?>
            <tr>
              <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <div style="display: flex; align-items: center; gap: 10px;">
                   <div style="position: relative; width: <?= $ap['partner_id'] ? '50px' : '32px' ?>; height: 32px;">
                       <img src="<?= $ap['photo1'] ? base_url('assets/uploads/players/'.$ap['photo1']) : base_url('assets/img/player_avatar.png') ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid var(--color-border); position: absolute; left: 0; z-index: 2;">
                       <?php if($ap['partner_id']): ?>
                           <img src="<?= $ap['photo2'] ? base_url('assets/uploads/players/'.$ap['photo2']) : base_url('assets/img/player_avatar.png') ?>" style="width: 32px; height: 32px; border-radius: 50%; object-fit: cover; border: 1px solid var(--color-border); position: absolute; left: 18px; z-index: 1;">
                       <?php endif; ?>
                   </div>
                   <div style="font-weight: 700;">
                       <?= e($ap['p1_name']) ?>
                       <?php if($ap['partner_id']): ?>
                           <span style="color: var(--color-primary); font-size: 13px;"> & <?= e($ap['p2_name']) ?></span>
                       <?php endif; ?>
                   </div>
                </div>
              </td>
              
              <?php if($season['category'] === 'double'): ?>
              <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                <form method="post" style="display: flex; gap: 5px;">
                   <input type="hidden" name="sp_id" value="<?= $ap['id'] ?>">
                   <select name="partner_id" onchange="this.form.submit()" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: #fff; padding: 5px; border-radius: 6px; font-size: 12px; flex: 1;">
                      <option value="0">-- Pilih Partner --</option>
                      <?php 
                      if ($ap['partner_id']) {
                          echo '<option value="'.$ap['partner_id'].'" selected>'.e($ap['p2_name']).'</option>';
                      }
                      foreach($availablePartners as $other) {
                          if($other['player_id'] == $ap['player_id']) continue;
                          echo '<option value="'.$other['player_id'].'">'.e($other['p1_name']).'</option>';
                      }
                      ?>
                   </select>
                   <input type="hidden" name="pair_up" value="1">
                </form>
              </td>
              <?php endif; ?>

              <?php if($season['format'] === 'hybrid' && $season['group_count'] > 1): ?>
              <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: center;">
                <select name="group[<?= $ap['id'] ?>]" style="background: var(--color-bg-dark); border: 1px solid var(--color-border); color: white; padding: 4px 8px; border-radius: 6px; font-size: 13px;">
                  <?php 
                  $abc = range('A', 'Z');
                  for($i=0; $i<$season['group_count']; $i++): ?>
                    <option value="<?= $abc[$i] ?>" <?= $ap['group_name'] == $abc[$i] ? 'selected' : '' ?>>Grup <?= $abc[$i] ?></option>
                  <?php endfor; ?>
                </select>
              </td>
              <?php endif; ?>

              <td style="padding: 12px; border-bottom: 1px solid rgba(255,255,255,0.05); text-align: right;">
                <button type="submit" name="delete_sp_id" value="<?= $ap['id'] ?>" class="btn btn-outline" style="padding: 5px 10px; font-size: 11px; border-color: #ef4444; color: #ef4444;" onclick="return confirm('Hapus peserta ini?')">Hapus</button>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </form>
  </div>
</div>

<script>
// Player Search Logic
document.getElementById('playerSearch').addEventListener('input', function() {
    const filter = this.value.toLowerCase();
    const items = document.querySelectorAll('.player-item');
    items.forEach(item => {
        const name = item.querySelector('.player-name').innerText.toLowerCase();
        if (name.includes(filter)) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });
});

// Selection Count Logic
const checkboxes = document.querySelectorAll('.player-checkbox');
checkboxes.forEach(cb => {
    cb.addEventListener('change', () => {
        const checked = document.querySelectorAll('.player-checkbox:checked').length;
        document.getElementById('selectedCount').innerText = checked;
    });
});
</script>

<style>
.player-item:hover { background: rgba(255,255,255,0.05); }
.player-checkbox { width: 18px; height: 18px; cursor: pointer; accent-color: var(--color-primary); }
#playerList::-webkit-scrollbar { width: 6px; }
#playerList::-webkit-scrollbar-thumb { background: var(--color-border); border-radius: 10px; }
</style>

<?php include 'includes/footer.php'; ?>
