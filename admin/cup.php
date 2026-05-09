<?php
require 'includes/auth.php';
require '../config/db.php';
require '../includes/functions.php';

$seasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;
if ($seasonId === 0) {
    $current = getCurrentCupSeason($pdo);
    $seasonId = $current['id'] ?? 0;
}

if ($seasonId === 0) {
    die("Belum ada tournament aktif. Buat tournament dulu.");
}

$stmt = $pdo->prepare("SELECT * FROM seasons WHERE id = ?");
$stmt->execute([$seasonId]);
$season = $stmt->fetch();

$error = '';
$success = '';

// Helper to get next power of 2
function getNextPowerOfTwo($n) {
    if ($n <= 2) return 2;
    if ($n <= 4) return 4;
    if ($n <= 8) return 8;
    return 16;
}

// ACTION: Initialize Tournament (Auto Size)
if (isset($_POST['init_cup'])) {
    // Safety Check: Don't allow init if there are already scores or winners
    $checkScores = $pdo->prepare("SELECT COUNT(*) FROM tournament_brackets WHERE season_id = ? AND (player1_score > 0 OR player2_score > 0 OR winner_id IS NOT NULL)");
    $checkScores->execute([$seasonId]);
    if ($checkScores->fetchColumn() > 0) {
        $error = "Gagal: Bagan tidak bisa dibuat ulang karena sudah ada hasil pertandingan yang tercatat.";
    } else {
        $stmt_p = $pdo->prepare("SELECT sp.id, sp.player_id, sp.partner_id FROM season_players sp WHERE sp.season_id = ?");
        $stmt_p->execute([$seasonId]);
        $allParticipants = $stmt_p->fetchAll();
    
    if ($season['format'] === 'hybrid') {
        // BLOCK if any group match is still pending
        $pendingCheck = $pdo->prepare("SELECT COUNT(*) FROM matches WHERE season_id = ? AND status = 'pending'");
        $pendingCheck->execute([$seasonId]);
        if ($pendingCheck->fetchColumn() > 0) {
            $error = "Fase Grup belum selesai! Selesaikan semua pertandingan grup sebelum membuat bagan Knockout.";
        } else {
            $qualN = max(1, (int)($season['qualifiers_per_group'] ?? 2));

            // Calculate standings per group
            $spStmt = $pdo->prepare("SELECT sp.id as sp_id, sp.player_id, sp.partner_id, sp.group_name,
                COALESCE(SUM(CASE WHEN m.player1_id = sp.player_id THEN m.player1_score
                                  WHEN m.player2_id = sp.player_id THEN m.player2_score ELSE 0 END), 0) AS scored,
                COALESCE(SUM(CASE WHEN m.player1_id = sp.player_id THEN m.player2_score
                                  WHEN m.player2_id = sp.player_id THEN m.player1_score ELSE 0 END), 0) AS conceded,
                COALESCE(SUM(CASE WHEN (m.player1_id = sp.player_id AND m.player1_score > m.player2_score)
                                    OR (m.player2_id = sp.player_id AND m.player2_score > m.player1_score) THEN 3
                                  WHEN m.player1_score = m.player2_score THEN 1 ELSE 0 END), 0) AS points
            FROM season_players sp
            LEFT JOIN matches m ON m.season_id = sp.season_id
                AND (m.player1_id = sp.player_id OR m.player2_id = sp.player_id)
                AND m.status = 'completed'
            WHERE sp.season_id = ?
            GROUP BY sp.id
            ORDER BY sp.group_name ASC, points DESC, (scored - conceded) DESC");
            $spStmt->execute([$seasonId]);
            $allStandings = $spStmt->fetchAll();

            // Group them by group_name
            $groupedStandings = [];
            foreach ($allStandings as $row) {
                $groupedStandings[$row['group_name']][] = $row;
            }

            // Pick Top N per group
            $qualifiersByGroup = [];
            foreach ($groupedStandings as $grp => $rows) {
                $qualifiersByGroup[$grp] = array_slice($rows, 0, $qualN);
            }

            // Cross-group seeding: Rank 1 vs Rank 2, interleaved by group
            // e.g. with 2 groups, qualN=2: [A1, B1, A2, B2] in seeding order
            $participants = [];
            $groupKeys = array_keys($qualifiersByGroup);
            for ($rank = 0; $rank < $qualN; $rank++) {
                foreach ($groupKeys as $grp) {
                    if (isset($qualifiersByGroup[$grp][$rank])) {
                        $q = $qualifiersByGroup[$grp][$rank];
                        // Match back to season_players record
                        foreach ($allParticipants as $ap) {
                            if ($ap['id'] == $q['sp_id']) {
                                $participants[] = $ap;
                                break;
                            }
                        }
                    }
                }
            }

            // Now feed into normal bracket logic below
        }
    } else {
        $participants = $allParticipants;
    }

    if (empty($error)) {
    $pCount = count($participants);
    if ($pCount < 2) {
        $error = "Peserta minimal 2 untuk membuat bagan Cup.";
    } else {
        $size = getNextPowerOfTwo($pCount);
        $pdo->prepare("DELETE FROM tournament_brackets WHERE season_id = ?")->execute([$seasonId]);
        
        $rounds = [];
        if ($size >= 16) $rounds[] = 'Round of 16';
        if ($size >= 8)  $rounds[] = 'Quarterfinal';
        if ($size >= 4)  $rounds[] = 'Semifinal';
        $rounds[] = 'Final';

        // 1. Generate all empty placeholders first
        $matchCount = $size / 2;
        $bracketsData = []; 
        for ($r = 0; $r < count($rounds); $r++) {
            $mCount = $matchCount / pow(2, $r);
            for ($i = 0; $i < $mCount; $i++) {
                $stmt = $pdo->prepare("INSERT INTO tournament_brackets (season_id, round_name, position_index) VALUES (?, ?, ?)");
                $stmt->execute([$seasonId, $rounds[$r], $i]);
                $bracketsData[$rounds[$r]][$i] = $pdo->lastInsertId();
            }
        }

        // 2. Standard Seeding Logic for Round 1
        $seedings = [
            2 => [[0,1]],
            4 => [[0,3], [1,2]],
            8 => [[0,7], [3,4], [1,6], [2,5]],
            16 => [[0,15], [7,8], [3,12], [4,11], [1,14], [6,9], [2,13], [5,10]]
        ];
        $seeding = $seedings[$size];

        // 3. Fill Round 1 & Handle BYEs
        $firstRound = $rounds[0];
        foreach ($seeding as $i => $pair) {
            $p1 = isset($participants[$pair[0]]) ? $participants[$pair[0]] : null;
            $p2 = isset($participants[$pair[1]]) ? $participants[$pair[1]] : null;
            
            $p1_id = $p1['player_id'] ?? null; $p1_partner = $p1['partner_id'] ?? null;
            $p2_id = $p2['player_id'] ?? null; $p2_partner = $p2['partner_id'] ?? null;
            
            $winner_id = null;
            // Handle BYE logic (NULL indicates BYE if winner is already set)
            if ($p1_id && !$p2_id) {
                $p2_id = null;
                $winner_id = $p1_id;
            } elseif (!$p1_id && $p2_id) {
                $p1_id = null;
                $winner_id = $p2_id;
            } elseif (!$p1_id && !$p2_id) {
                // Both are empty
                $p1_id = null; $p2_id = null;
            }

            $bid = $bracketsData[$firstRound][$i];
            $stmt = $pdo->prepare("UPDATE tournament_brackets SET player1_id=?, p1_partner_id=?, player2_id=?, p2_partner_id=?, winner_id=? WHERE id=?");
            $stmt->execute([$p1_id, $p1_partner, $p2_id, $p2_partner, $winner_id, $bid]);

            // Auto-advance BYE winner to next round
            if ($winner_id && count($rounds) > 1) {
                $next_round = $rounds[1];
                $next_pos = floor($i / 2);
                $next_bid = $bracketsData[$next_round][$next_pos];
                $is_p2 = ($i % 2 != 0);
                
                $w_partner = ($winner_id == $p1_id) ? $p1_partner : $p2_partner;
                
                if ($is_p2) {
                    $pdo->prepare("UPDATE tournament_brackets SET player2_id=?, p2_partner_id=? WHERE id=?")->execute([$winner_id, $w_partner, $next_bid]);
                } else {
                    $pdo->prepare("UPDATE tournament_brackets SET player1_id=?, p1_partner_id=? WHERE id=?")->execute([$winner_id, $w_partner, $next_bid]);
                }
            }
        }
        $success = "Bagan Cup otomatis dibuat dengan sistem unggulan dan BYE.";
        }
    } // end empty error check
    }
}

// ACTION: Save Score & Advance
if (isset($_POST['save_score'])) {
    $bid = (int)$_POST['bracket_id'];
    $s1 = (int)$_POST['s1'];
    $s2 = (int)$_POST['s2'];
    $winner_id = null;
    
    $stmt = $pdo->prepare("SELECT * FROM tournament_brackets WHERE id = ?");
    $stmt->execute([$bid]);
    $b = $stmt->fetch();
    
    if ($b) {
        if ($s1 > $s2) $winner_id = $b['player1_id'];
        elseif ($s2 > $s1) $winner_id = $b['player2_id'];
        
        $stmt = $pdo->prepare("UPDATE tournament_brackets SET player1_score = ?, player2_score = ?, winner_id = ? WHERE id = ?");
        $stmt->execute([$s1, $s2, $winner_id, $bid]);
        
        if ($winner_id) {
            $rounds = ['Round of 16', 'Quarterfinal', 'Semifinal', 'Final'];
            $currKey = array_search($b['round_name'], $rounds);
            if ($currKey !== false && isset($rounds[$currKey+1])) {
                $next_round = $rounds[$currKey+1];
                $next_pos = floor($b['position_index'] / 2);
                $is_p2 = ($b['position_index'] % 2 != 0);
                $p_id = ($winner_id == $b['player1_id']) ? $b['p1_partner_id'] : $b['p2_partner_id'];
                if ($is_p2) {
                    $stmt = $pdo->prepare("UPDATE tournament_brackets SET player2_id = ?, p2_partner_id = ? WHERE season_id = ? AND round_name = ? AND position_index = ?");
                } else {
                    $stmt = $pdo->prepare("UPDATE tournament_brackets SET player1_id = ?, p1_partner_id = ? WHERE season_id = ? AND round_name = ? AND position_index = ?");
                }
                $stmt->execute([$winner_id, $p_id, $seasonId, $next_round, $next_pos]);
            }
        }
        $success = "Skor diperbarui dan pemenang berhasil lanjut!";
    } else {
        $error = "Pertandingan tidak valid atau tidak ditemukan.";
    }
}

// ACTION: Manual Assign
if (isset($_POST['assign_player'])) {
    $bid = (int)$_POST['bracket_id'];
    $slot = $_POST['slot'];
    $sp_id = (int)$_POST['participant_sp_id'];
    $stmt_sp = $pdo->prepare("SELECT player_id, partner_id FROM season_players WHERE id = ?");
    $stmt_sp->execute([$sp_id]);
    $sp = $stmt_sp->fetch();
    if ($sp) {
        $col_p = ($slot === 'p1') ? 'player1_id' : 'player2_id';
        $col_partner = ($slot === 'p1') ? 'p1_partner_id' : 'p2_partner_id';
        $stmt = $pdo->prepare("UPDATE tournament_brackets SET $col_p = ?, $col_partner = ? WHERE id = ?");
        $stmt->execute([$sp['player_id'], $sp['partner_id'], $bid]);
        $success = "Peserta dipasang manual.";
    }
}

// Fetch brackets
$stmt = $pdo->prepare("SELECT b.*, 
                       p1.name AS p1_name, p1p.name AS p1p_name,
                       p2.name AS p2_name, p2p.name AS p2p_name
                       FROM tournament_brackets b 
                       LEFT JOIN players p1 ON p1.id = b.player1_id 
                       LEFT JOIN players p1p ON p1p.id = b.p1_partner_id
                       LEFT JOIN players p2 ON p2.id = b.player2_id 
                       LEFT JOIN players p2p ON p2p.id = b.p2_partner_id
                       WHERE b.season_id = ? 
                       ORDER BY FIELD(round_name, 'Round of 16', 'Quarterfinal', 'Semifinal', 'Final'), position_index");
$stmt->execute([$seasonId]);
$brackets = $stmt->fetchAll();

// Participants for dropdown
$stmt_p = $pdo->prepare("SELECT sp.id, p1.name AS p1_name, p2.name AS p2_name 
                         FROM season_players sp 
                         JOIN players p1 ON p1.id = sp.player_id 
                         LEFT JOIN players p2 ON p2.id = sp.partner_id 
                         WHERE sp.season_id = ? ORDER BY p1_name ASC");
$stmt_p->execute([$seasonId]);
$registeredParticipants = $stmt_p->fetchAll();

$pageTitle = 'Kelola Cup - Admin';

// For Hybrid: fetch group standings to display as reference
$groupStandings = [];
if ($season['format'] === 'hybrid') {
    $qualN = max(1, (int)($season['qualifiers_per_group'] ?? 2));
    $gsStmt = $pdo->prepare("SELECT sp.id as sp_id, sp.player_id, sp.partner_id, sp.group_name,
        p1.name AS p1_name, p2.name AS p2_name,
        COALESCE(COUNT(m.id), 0) AS played,
        COALESCE(SUM(CASE WHEN (m.player1_id = sp.player_id AND m.player1_score > m.player2_score)
                            OR (m.player2_id = sp.player_id AND m.player2_score > m.player1_score) THEN 1 ELSE 0 END), 0) AS wins,
        COALESCE(SUM(CASE WHEN (m.player1_id = sp.player_id AND m.player1_score < m.player2_score)
                            OR (m.player2_id = sp.player_id AND m.player2_score < m.player1_score) THEN 1 ELSE 0 END), 0) AS losses,
        COALESCE(SUM(CASE WHEN m.player1_id = sp.player_id THEN m.player1_score
                          WHEN m.player2_id = sp.player_id THEN m.player2_score ELSE 0 END), 0) AS scored,
        COALESCE(SUM(CASE WHEN m.player1_id = sp.player_id THEN m.player2_score
                          WHEN m.player2_id = sp.player_id THEN m.player1_score ELSE 0 END), 0) AS conceded,
        COALESCE(SUM(CASE WHEN (m.player1_id = sp.player_id AND m.player1_score > m.player2_score)
                            OR (m.player2_id = sp.player_id AND m.player2_score > m.player1_score) THEN 1 ELSE 0 END), 0) AS points
    FROM season_players sp
    JOIN players p1 ON p1.id = sp.player_id
    LEFT JOIN players p2 ON p2.id = sp.partner_id
    LEFT JOIN matches m ON m.season_id = sp.season_id
        AND (m.player1_id = sp.player_id OR m.player2_id = sp.player_id)
        AND m.status = 'completed'
    WHERE sp.season_id = ?
    GROUP BY sp.id
    ORDER BY sp.group_name ASC, points DESC, (scored - conceded) DESC");
    $gsStmt->execute([$seasonId]);
    $allGsRows = $gsStmt->fetchAll();
    foreach ($allGsRows as $row) {
        $groupStandings[$row['group_name']][] = $row;
    }
}

include 'includes/header.php';
?>

<div class="admin-header">
  <div>
    <h1>Kelola Tournament Knockout</h1>
    <p style="color: var(--color-text-muted); margin-top: 5px;">Tournament: <?= e($season['name']) ?> (<?= count($registeredParticipants) ?> Peserta)</p>
  </div>
  <form method="post" onsubmit="return confirm('Sistem akan membuat bagan otomatis sesuai jumlah peserta terdaftar. Lanjutkan?')">
    <button type="submit" name="init_cup" class="btn btn-primary">Generate Bagan Otomatis</button>
  </form>
</div>

<?php if($error): ?><div class="alert" style="background: #ef4444; color: white; margin-bottom: 24px;"><?= e($error) ?></div><?php endif; ?>
<?php if($success): ?><div class="alert" style="margin-bottom: 24px;"><?= e($success) ?></div><?php endif; ?>

<?php if($season['format'] === 'hybrid' && !empty($groupStandings)): ?>
<details class="admin-card" style="margin-bottom: 24px; cursor: pointer;" open>
  <summary style="padding: 15px 20px; font-size: 15px; font-weight: 800; color: var(--color-primary); letter-spacing: 1px; list-style: none; display: flex; justify-content: space-between; align-items: center;">
    📊 HASIL FASE GRUP
    <span style="font-size: 11px; color: var(--color-text-muted); font-weight: 400;">Klik untuk sembunyikan/tampilkan</span>
  </summary>
  <div style="padding: 0 20px 20px; display: flex; flex-wrap: wrap; gap: 20px;">
  <?php 
  $qualN = max(1, (int)($season['qualifiers_per_group'] ?? 2));
  foreach ($groupStandings as $grp => $rows): ?>
    <div style="flex: 1; min-width: 280px;">
      <h3 style="font-size: 13px; letter-spacing: 2px; color: var(--color-text-muted); margin-bottom: 10px; text-transform: uppercase;">Grup <?= e($grp) ?></h3>
      <table style="width: 100%; border-collapse: collapse; font-size: 12px;">
        <thead>
          <tr style="border-bottom: 1px solid var(--color-border);">
            <th style="text-align: left; padding: 6px 8px; color: var(--color-text-muted);">#</th>
            <th style="text-align: left; padding: 6px 8px; color: var(--color-text-muted);">Tim</th>
            <th style="text-align: center; padding: 6px 8px; color: var(--color-text-muted);">M</th>
            <th style="text-align: center; padding: 6px 8px; color: var(--color-text-muted);">M</th>
            <th style="text-align: center; padding: 6px 8px; color: var(--color-text-muted);">K</th>
            <th style="text-align: center; padding: 6px 8px; color: var(--color-text-muted);">Poin</th>
            <th style="text-align: center; padding: 6px 8px; color: var(--color-text-muted);">Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $ri => $row): 
            $lolos = ($ri < $qualN);
          ?>
          <tr style="border-bottom: 1px solid rgba(255,255,255,0.04); background: <?= $lolos ? 'rgba(251,191,36,0.06)' : 'transparent' ?>;">
            <td style="padding: 8px; font-weight: 800; color: var(--color-text-muted);"><?= $ri+1 ?></td>
            <td style="padding: 8px; font-weight: 700;">
              <?= e($row['p1_name']) ?>
              <?php if($row['p2_name']): ?><div style="font-size: 10px; color: var(--color-text-muted);">& <?= e($row['p2_name']) ?></div><?php endif; ?>
            </td>
            <td style="padding: 8px; text-align: center;"><?= $row['played'] ?></td>
            <td style="padding: 8px; text-align: center; color: #4ade80;"><?= $row['wins'] ?></td>
            <td style="padding: 8px; text-align: center; color: #f87171;"><?= $row['losses'] ?></td>
            <td style="padding: 8px; text-align: center; font-weight: 900; color: var(--color-primary);"><?= $row['points'] ?></td>
            <td style="padding: 8px; text-align: center;">
              <?php if($lolos): ?>
                <span style="background: var(--color-primary); color: #000; font-size: 9px; font-weight: 900; padding: 2px 7px; border-radius: 4px;">LOLOS</span>
              <?php else: ?>
                <span style="color: var(--color-text-muted); font-size: 10px;">Gugur</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endforeach; ?>
  </div>
</details>
<?php endif; ?>

<div style="display: flex; flex-wrap: nowrap; overflow-x: auto; gap: 30px; padding-bottom: 20px;">
  <?php 
  $groupedBrackets = [];
  foreach($brackets as $b) $groupedBrackets[$b['round_name']][] = $b;
  
  foreach ($groupedBrackets as $roundName => $roundBrackets): ?>
  <div class="admin-card" style="padding: 20px; min-width: 350px; display: flex; flex-direction: column;">
    <h2 style="color: var(--color-primary); margin-bottom: 25px; font-size: 16px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 2px solid rgba(255,255,255,0.05); padding-bottom: 10px;"><?= $roundName ?></h2>
    <div style="display: flex; flex-direction: column; justify-content: space-around; flex: 1; gap: 20px;">
      <?php foreach ($roundBrackets as $b): ?>
      <div style="background: rgba(0,0,0,0.2); border: 1px solid var(--color-border); padding: 20px; border-radius: 12px;">
          
          <form method="post">
          <input type="hidden" name="bracket_id" value="<?= $b['id'] ?>">

          <!-- Team 1 -->
          <div style="margin-bottom: 15px;">
             <?php if(!$b['player1_id'] && $b['winner_id'] == $b['player2_id'] && $b['player2_id']): ?>
                <div style="font-weight: 900; color: var(--color-text-muted); font-size: 16px; letter-spacing: 2px; text-align: center; padding: 10px 0; background: rgba(0,0,0,0.3); border-radius: 8px;">BYE</div>
             <?php elseif($b['player1_id']): ?>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                   <div style="flex: 1;">
                      <div style="font-weight: 700; font-size: 14px;"><?= e($b['p1_name']) ?></div>
                      <?php if($b['p1p_name']): ?><div style="font-size: 11px; color: var(--color-text-muted);">& <?= e($b['p1p_name']) ?></div><?php endif; ?>
                   </div>
                   <?php if(!(!$b['player2_id'] && $b['winner_id'] == $b['player1_id'])): ?>
                   <input type="number" name="s1" value="<?= e($b['player1_score'] ?? 0) ?>" min="0" max="30" style="width: 60px; height: 35px; background: #000; border: 1px solid var(--color-border); color: var(--color-primary); text-align: center; font-weight: 900; font-size: 16px; border-radius: 6px;">
                   <?php endif; ?>
                </div>
             <?php else: ?>
                <div style="display: flex; gap: 5px;">
                   <select name="participant_sp_id_p1" style="flex: 1; font-size: 11px; background: #000; color: #fff; border: 1px solid var(--color-border); padding: 5px;">
                      <option value="">Pilih Peserta 1...</option>
                      <?php foreach($registeredParticipants as $rp): ?>
                        <option value="<?= $rp['id'] ?>"><?= e($rp['p1_name']) ?> <?= $rp['p2_name'] ? '& '.e($rp['p2_name']) : '' ?></option>
                      <?php endforeach; ?>
                   </select>
                   <button type="submit" name="assign_player" onclick="this.form.slot.value='p1'; this.form.participant_sp_id.value=this.form.participant_sp_id_p1.value;" class="btn btn-primary" style="padding: 2px 8px; font-size: 10px;">Set</button>
                </div>
             <?php endif; ?>
          </div>

          <div style="height: 1px; background: rgba(255,255,255,0.05); margin: 15px 0; position: relative;">
             <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #1a1a1a; padding: 0 10px; font-size: 10px; color: var(--color-text-muted);">VS</span>
          </div>

          <!-- Team 2 -->
          <div style="margin-bottom: 15px;">
             <?php if(!$b['player2_id'] && $b['winner_id'] == $b['player1_id'] && $b['player1_id']): ?>
                <div style="font-weight: 900; color: var(--color-text-muted); font-size: 16px; letter-spacing: 2px; text-align: center; padding: 10px 0; background: rgba(0,0,0,0.3); border-radius: 8px;">BYE</div>
             <?php elseif($b['player2_id']): ?>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                   <div style="flex: 1;">
                      <div style="font-weight: 700; font-size: 14px;"><?= e($b['p2_name']) ?></div>
                      <?php if($b['p2p_name']): ?><div style="font-size: 11px; color: var(--color-text-muted);">& <?= e($b['p2p_name']) ?></div><?php endif; ?>
                   </div>
                   <?php if(!(!$b['player1_id'] && $b['winner_id'] == $b['player2_id'])): ?>
                   <input type="number" name="s2" value="<?= e($b['player2_score'] ?? 0) ?>" min="0" max="30" style="width: 60px; height: 35px; background: #000; border: 1px solid var(--color-border); color: var(--color-primary); text-align: center; font-weight: 900; font-size: 16px; border-radius: 6px;">
                   <?php endif; ?>
                </div>
             <?php else: ?>
                <div style="display: flex; gap: 5px;">
                   <select name="participant_sp_id_p2" style="flex: 1; font-size: 11px; background: #000; color: #fff; border: 1px solid var(--color-border); padding: 5px;">
                      <option value="">Pilih Peserta 2...</option>
                      <?php foreach($registeredParticipants as $rp): ?>
                        <option value="<?= $rp['id'] ?>"><?= e($rp['p1_name']) ?> <?= $rp['p2_name'] ? '& '.e($rp['p2_name']) : '' ?></option>
                      <?php endforeach; ?>
                   </select>
                   <button type="submit" name="assign_player" onclick="this.form.slot.value='p2'; this.form.participant_sp_id.value=this.form.participant_sp_id_p2.value;" class="btn btn-primary" style="padding: 2px 8px; font-size: 10px;">Set</button>
                </div>
             <?php endif; ?>
          </div>

          <!-- Hidden inputs for manual assign helper -->
          <input type="hidden" name="slot" value="">
          <input type="hidden" name="participant_sp_id" value="">

          <?php 
          $is_bye = (!$b['player1_id'] && $b['winner_id'] == $b['player2_id']) || (!$b['player2_id'] && $b['winner_id'] == $b['player1_id']);
          if($b['player1_id'] && $b['player2_id'] && !$is_bye): ?>
                <button type="submit" name="save_score" class="btn btn-primary" style="width: 100%; padding: 10px; font-size: 12px; font-weight: 800; letter-spacing: 1px; margin-top: 10px;">UPDATE SKOR</button>
          <?php endif; ?>
          </form>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<?php include 'includes/footer.php'; ?>
