<?php
require 'config/db.php';
require 'includes/functions.php';

$allSeasons = getAllCupSeasons($pdo);
$selectedSeasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;

if ($selectedSeasonId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM seasons WHERE id = ?");
    $stmt->execute([$selectedSeasonId]);
    $season = $stmt->fetch();
} else {
    $season = getCurrentCupSeason($pdo);
    $selectedSeasonId = $season['id'] ?? 0;
}

$pageTitle = 'FullSatu Knockout - Bracket';
include 'includes/header.php';

// For Hybrid: fetch group standings to display as reference
$groupStandings = [];
if ($season && $season['format'] === 'hybrid') {
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
    $gsStmt->execute([$selectedSeasonId]);
    $allGsRows = $gsStmt->fetchAll();
    foreach ($allGsRows as $row) {
        $groupStandings[$row['group_name']][] = $row;
    }
}
?>

<div style="padding-top: 100px;"></div>
<section class="hero-page">
  <div class="badge" style="display: inline-block; background: rgba(34, 197, 94, 0.1); color: var(--color-primary); padding: 8px 20px; border-radius: 30px; font-weight: 800; letter-spacing: 2px; margin-bottom: 20px; border: 1px solid rgba(34, 197, 94, 0.1);">
    <?= ($season['format'] === 'league' ? 'LIGA' : ($season['format'] === 'cup' ? 'KNOCKOUT' : 'HYBRID')) ?> - <?= strtoupper(e($season['category'])) ?>
  </div>
  <h1>Knockout Bracket</h1>
  <p>Sistem gugur (Knockout) paling bergengsi musim ini.</p>

  <!-- TOURNAMENT SWITCHER -->
  <div style="margin-top: 20px; display: flex; justify-content: center; gap: 10px; align-items: center;">
    <span style="color: var(--color-text-muted); font-size: 14px;">Pilih Tournament:</span>
    <form method="get" id="seasonCupForm">
      <select name="season_id" onchange="document.getElementById('seasonCupForm').submit()" style="background: var(--color-bg-light); border: 1px solid var(--color-border); color: var(--color-primary); padding: 8px 15px; border-radius: 30px; font-weight: 700; cursor: pointer; min-width: 150px;">
        <?php foreach($allSeasons as $s): ?>
          <option value="<?= $s['id'] ?>" <?= $s['id'] == $selectedSeasonId ? 'selected' : '' ?>>
            <?= e($s['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
</section>

<div class="container">
  <?php if (!$season): ?>
    <div class="alert">Belum ada tournament aktif.</div>
  <?php else: ?>
    
    <?php if($season['format'] === 'hybrid' && !empty($groupStandings)): ?>
    <div class="card" style="margin-bottom: 40px; padding: 0; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
      <div style="background: var(--color-bg-light); padding: 15px 25px; border-bottom: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between;">
        <h2 style="margin: 0; font-size: 18px; letter-spacing: 1px; color: var(--color-primary);">📊 HASIL FASE GRUP</h2>
      </div>
      <div style="padding: 25px; display: flex; flex-wrap: wrap; gap: 30px;">
      <?php 
      $qualN = max(1, (int)($season['qualifiers_per_group'] ?? 2));
      foreach ($groupStandings as $grp => $rows): ?>
        <div style="flex: 1; min-width: 300px;">
          <h3 style="font-size: 14px; letter-spacing: 2px; color: var(--color-text-muted); margin-bottom: 15px; text-transform: uppercase; border-left: 3px solid var(--color-primary); padding-left: 10px;">Grup <?= e($grp) ?></h3>
          <div class="table-wrap" style="border: none; border-radius: 0;">
            <table style="width: 100%; font-size: 13px;">
              <thead>
                <tr style="border-bottom: 1px solid var(--color-border);">
                  <th style="text-align: left; padding: 10px 8px;">#</th>
                  <th style="text-align: left; padding: 10px 8px;">Tim</th>
                  <th style="text-align: center; padding: 10px 8px;">M</th>
                  <th style="text-align: center; padding: 10px 8px;">M</th>
                  <th style="text-align: center; padding: 10px 8px;">K</th>
                  <th style="text-align: center; padding: 10px 8px;">Poin</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($rows as $ri => $row): 
                  $lolos = ($ri < $qualN);
                ?>
                <tr style="border-bottom: 1px solid rgba(255,255,255,0.03); background: <?= $lolos ? 'rgba(34, 197, 94, 0.03)' : 'transparent' ?>;">
                  <td style="padding: 12px 8px; font-weight: 800; color: var(--color-text-muted);"><?= $ri+1 ?></td>
                  <td style="padding: 12px 8px; font-weight: 700;">
                    <div style="color: white;"><?= e($row['p1_name']) ?></div>
                    <?php if($row['p2_name']): ?><div style="font-size: 11px; color: var(--color-text-muted);">& <?= e($row['p2_name']) ?></div><?php endif; ?>
                  </td>
                  <td style="padding: 12px 8px; text-align: center;"><?= $row['played'] ?></td>
                  <td style="padding: 12px 8px; text-align: center; color: #4ade80;"><?= $row['wins'] ?></td>
                  <td style="padding: 12px 8px; text-align: center; color: #f87171;"><?= $row['losses'] ?></td>
                  <td style="padding: 12px 8px; text-align: center; font-weight: 900; color: var(--color-primary);"><?= $row['points'] ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
    
    <?php
    // Fetch bracket data with partners
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
    $stmt->execute([$season['id']]);
    $brackets = $stmt->fetchAll();
    
    $rounds = [];
    foreach($brackets as $b) {
        $rounds[$b['round_name']][] = $b;
    }
    ?>

    <?php if(empty($brackets)): ?>
      <div class="card" style="text-align: center; padding: 60px 20px;">
         <div style="font-size: 50px; margin-bottom: 20px;">⏳</div>
         <h2 style="margin-bottom: 10px;">Bagan Belum Dibuat</h2>
         <p style="color: var(--color-text-muted);">Admin belum menginisialisasi bagan untuk tournament ini.</p>
      </div>
    <?php else: ?>
      <!-- BRACKET UI -->
      <div class="bracket-container">
        
        <?php foreach ($rounds as $roundName => $roundBrackets): ?>
          <div class="bracket-round">
            <h3 class="round-title"><?= strtoupper(e($roundName)) ?></h3>
            <div class="bracket-matches">
              <?php foreach($roundBrackets as $b): ?>
              <div class="bracket-match-item <?= ($roundName === 'Final') ? 'final' : '' ?>">
                <?php if($roundName === 'Final'): ?><div class="final-glow"></div><?php endif; ?>
                
                <div class="player-slot <?= ($b['winner_id'] && $b['winner_id'] == $b['player1_id'] && $b['player1_id']) ? 'winner' : '' ?> <?= ($roundName === 'Final') ? 'final-slot' : '' ?>">
                  <?php if($roundName === 'Final' && $b['winner_id'] && $b['winner_id'] == $b['player1_id']): ?><span class="trophy-icon">🏆</span><?php endif; ?>
                  <div class="name-box">
                     <div class="name"><?= e($b['p1_name'] ?? ($b['player1_id'] ? 'Player 1' : 'TBD')) ?></div>
                     <?php if($b['p1p_name']): ?><div class="partner-name">& <?= e($b['p1p_name']) ?></div><?php endif; ?>
                  </div>
                  <span class="score"><?= $b['player1_score'] ?? '-' ?></span>
                </div>

                <div class="player-slot <?= ($b['winner_id'] && $b['winner_id'] == $b['player2_id'] && $b['player2_id']) ? 'winner' : '' ?> <?= ($roundName === 'Final') ? 'final-slot' : '' ?>">
                  <?php if($roundName === 'Final' && $b['winner_id'] && $b['winner_id'] == $b['player2_id']): ?><span class="trophy-icon">🏆</span><?php endif; ?>
                  <div class="name-box">
                     <div class="name"><?= e($b['p2_name'] ?? ($b['player2_id'] ? 'Player 2' : 'TBD')) ?></div>
                     <?php if($b['p2p_name']): ?><div class="partner-name">& <?= e($b['p2p_name']) ?></div><?php endif; ?>
                  </div>
                  <span class="score"><?= $b['player2_score'] ?? '-' ?></span>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php if($roundName !== 'Final'): ?>
            <div class="bracket-connector"></div>
          <?php endif; ?>
        <?php endforeach; ?>

      </div>
    <?php endif; ?>

    <style>
      .bracket-container {
        display: flex;
        justify-content: space-between;
        align-items: stretch;
        padding: 40px 0;
        overflow-x: auto;
        gap: 20px;
      }
      .bracket-round {
        flex: 1;
        min-width: 250px;
        display: flex;
        flex-direction: column;
        justify-content: space-around;
      }
      .round-title {
        text-align: center;
        font-size: 14px;
        color: var(--color-primary);
        letter-spacing: 3px;
        margin-bottom: 30px;
        font-weight: 900;
        text-transform: uppercase;
      }
      .bracket-matches {
        display: flex;
        flex-direction: column;
        justify-content: space-around;
        height: 100%;
        gap: 30px;
      }
      .bracket-match-item {
        background: var(--color-bg-light);
        border: 1px solid var(--color-border);
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        position: relative;
      }
      .player-slot {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 15px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
      }
      .player-slot:last-child { border-bottom: none; }
      .player-slot.winner { background: rgba(34, 197, 94, 0.05); }
      .player-slot.winner .name { color: var(--color-primary); font-weight: 700; }
      .player-slot.winner .score { color: var(--color-primary); font-weight: 900; }
      
      .name-box { flex: 1; display: flex; flex-direction: column; }
      .name { font-size: 14px; color: white; }
      .partner-name { font-size: 10px; color: var(--color-text-muted); }
      .score { font-size: 16px; font-weight: 700; color: var(--color-text-muted); }

      /* FINAL STYLING */
      .bracket-match-item.final { border: 2px solid #facc15; box-shadow: 0 0 30px rgba(250, 204, 21, 0.2); }
      .final-slot { padding: 20px; }
      .final-slot .name { font-size: 18px; font-weight: 900; }
      .trophy-icon { margin-right: 10px; font-size: 20px; }
      .final-glow {
        position: absolute;
        top: 0; left: 0; right: 0; bottom: 0;
        background: linear-gradient(45deg, transparent, rgba(250, 204, 21, 0.05), transparent);
        pointer-events: none;
      }

      @media (max-width: 768px) {
        .bracket-container { flex-direction: column; align-items: center; }
        .bracket-round { width: 100%; margin-bottom: 50px; }
      }
    </style>

  <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
