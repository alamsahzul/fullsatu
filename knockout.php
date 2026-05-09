<?php
require 'config/db.php';
require 'includes/functions.php';

$allSeasons = getAllKnockoutSeasons($pdo);
$selectedSeasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;

if ($selectedSeasonId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM seasons WHERE id = ?");
    $stmt->execute([$selectedSeasonId]);
    $season = $stmt->fetch();
} else {
    $season = getCurrentKnockoutSeason($pdo);
    $selectedSeasonId = $season['id'] ?? 0;
}

$pageTitle = 'Knockout - Bracket';
include 'includes/header.php';
?>

<div style="padding-top: 100px;"></div>
<section class="hero-page">
  <div class="badge" style="display: inline-block; background: rgba(34, 197, 94, 0.1); color: var(--color-primary); padding: 8px 20px; border-radius: 30px; font-weight: 800; letter-spacing: 2px; margin-bottom: 20px; border: 1px solid rgba(34, 197, 94, 0.1);">
    KNOCKOUT
  </div>
  <h1>Knockout Bracket</h1>
  <p>Sistem gugur (Knockout) murni tanpa fase grup.</p>

  <div style="margin-top: 20px; display: flex; justify-content: center; gap: 10px; align-items: center;">
    <span style="color: var(--color-text-muted); font-size: 14px;">Pilih Knockout:</span>
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
    <div class="alert">Belum ada kompetisi Knockout.</div>
  <?php else: ?>
    <?php
    $stmt = $pdo->prepare("SELECT b.*, p1.name AS p1_name, p1p.name AS p1p_name, p2.name AS p2_name, p2p.name AS p2p_name
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

    if(empty($brackets)): ?>
      <div class="card" style="text-align: center; padding: 60px 20px;">
         <div style="font-size: 50px; margin-bottom: 20px;">⏳</div>
         <h2 style="margin-bottom: 10px;">Bagan Belum Dibuat</h2>
         <p style="color: var(--color-text-muted);">Admin belum menginisialisasi bagan untuk tournament ini.</p>
      </div>
    <?php else: ?>
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
                     <div class="name">
                       <a href="player?id=<?= $b['player1_id'] ?>" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='var(--color-primary)'" onmouseout="this.style.color='inherit'"><?= e($b['p1_name'] ?? 'TBD') ?></a>
                       <?php if($b['p1p_name']): ?>
                         <div class="partner-name">& <a href="player?id=<?= $b['p1_partner_id'] ?>" style="color: inherit; text-decoration: none; font-size: 11px;" onmouseover="this.style.color='var(--color-primary)'" onmouseout="this.style.color='inherit'"><?= e($b['p1p_name']) ?></a></div>
                       <?php endif; ?>
                     </div>
                  </div>
                  <span class="score"><?= $b['player1_score'] ?? '-' ?></span>
                </div>
                <div class="player-slot <?= ($b['winner_id'] && $b['winner_id'] == $b['player2_id'] && $b['player2_id']) ? 'winner' : '' ?> <?= ($roundName === 'Final') ? 'final-slot' : '' ?>">
                  <?php if($roundName === 'Final' && $b['winner_id'] && $b['winner_id'] == $b['player2_id']): ?><span class="trophy-icon">🏆</span><?php endif; ?>
                  <div class="name-box">
                     <div class="name">
                       <a href="player?id=<?= $b['player2_id'] ?>" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='var(--color-primary)'" onmouseout="this.style.color='inherit'"><?= e($b['p2_name'] ?? 'TBD') ?></a>
                       <?php if($b['p2p_name']): ?>
                         <div class="partner-name">& <a href="player?id=<?= $b['p2_partner_id'] ?>" style="color: inherit; text-decoration: none; font-size: 11px;" onmouseover="this.style.color='var(--color-primary)'" onmouseout="this.style.color='inherit'"><?= e($b['p2p_name']) ?></a></div>
                       <?php endif; ?>
                     </div>
                  </div>
                  <span class="score"><?= $b['player2_score'] ?? '-' ?></span>
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
          <?php if($roundName !== 'Final'): ?><div class="bracket-connector"></div><?php endif; ?>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<style>
  .bracket-container {
    display: flex;
    justify-content: center;
    align-items: stretch;
    padding: 40px 0;
    overflow-x: auto;
    gap: 30px;
  }
  .bracket-round {
    flex: none;
    width: 170px;
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
  .player-slot.winner .name, .player-slot.winner .partner-name { color: var(--color-primary); font-weight: 700; }
  .player-slot.winner .score { color: var(--color-primary); font-weight: 900; }
  
  .name-box { flex: 1; display: flex; flex-direction: column; }
  .name, .partner-name { font-size: 14px; color: white; }
  .partner-name { margin-top: 0px; }
  .score { font-size: 16px; font-weight: 700; color: var(--color-text-muted); }

  /* FINAL STYLING */
  .bracket-match-item.final { border: 2px solid #facc15; box-shadow: 0 0 30px rgba(250, 204, 21, 0.2); }
  .final-slot { padding: 12px 15px; }
  .final-slot .name { font-size: 14px; font-weight: 700; }
  .trophy-icon { margin-right: 8px; font-size: 16px; }
  .final-glow {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(45deg, transparent, rgba(250, 204, 21, 0.05), transparent);
    pointer-events: none;
  }

  @media (max-width: 768px) {
    .bracket-container { 
      flex-direction: row !important; 
      overflow-x: auto !important; 
      padding-bottom: 20px;
      -webkit-overflow-scrolling: touch;
      display: flex !important;
      justify-content: flex-start !important;
      gap: 10px !important;
    }
    .bracket-round { 
      width: 170px !important; 
      flex-shrink: 0;
    }
  }
</style>

<?php include 'includes/footer.php'; ?>
