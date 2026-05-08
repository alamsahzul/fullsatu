<?php
require 'config/db.php';
require 'includes/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$season = getCurrentSeason($pdo);

// Get player details
$stmt = $pdo->prepare("SELECT * FROM players WHERE id = ?");
$stmt->execute([$id]);
$player = $stmt->fetch();

if (!$player) {
    header("Location: " . base_url('index'));
    exit;
}

$pageTitle = e($player['name']) . " - Profil Pemain";
include 'includes/header.php';

// Calculate individual stats for current season
$allStandings = $season ? calculateStandings($pdo, $season['id']) : [];
$playerStats = null;
$rank = '-';
foreach ($allStandings as $i => $row) {
    if ($row['id'] == $id) {
        $playerStats = $row;
        $rank = $i + 1;
        break;
    }
}

// Get match history
$stmt = $pdo->prepare("SELECT m.*, p1.name AS p1_name, p2.name AS p2_name, p1.photo AS p1_photo, p2.photo AS p2_photo, w.name AS winner_name
                       FROM matches m
                       JOIN players p1 ON p1.id = m.player1_id
                       JOIN players p2 ON p2.id = m.player2_id
                       LEFT JOIN players w ON w.id = m.winner_id
                       WHERE (m.player1_id = ? OR m.player2_id = ?) AND m.status = 'completed'
                       ORDER BY m.id DESC LIMIT 10");
$stmt->execute([$id, $id]);
$matchHistory = $stmt->fetchAll();

// Win rate calculation
$winRate = ($playerStats && $playerStats['main'] > 0) ? round(($playerStats['w'] / $playerStats['main']) * 100) : 0;
?>

<div style="padding-top: 100px;"></div>

<section class="player-profile-hero">
  <div class="container">
    <div class="profile-header">
      <div class="profile-photo-wrap">
        <?php if($player['photo']): ?>
          <img src="<?= base_url('assets/uploads/players/' . $player['photo']) ?>" alt="<?= e($player['name']) ?>" class="profile-photo">
        <?php else: ?>
          <img src="<?= base_url('assets/img/player_avatar.png') ?>" alt="Avatar" class="profile-photo" style="opacity: 0.3;">
        <?php endif; ?>
        <div class="profile-rank-badge">RANK #<?= $rank ?></div>
      </div>
      <div class="profile-info">
        <h1><?= e($player['name']) ?></h1>
        <p>Pemain FullSatu Single Man League</p>
        <div class="profile-stats-summary">
          <div class="stat-item">
            <span class="label">Win Rate</span>
            <span class="value"><?= $winRate ?>%</span>
          </div>
          <div class="stat-item">
            <span class="label">Points</span>
            <span class="value"><?= $playerStats['pts'] ?? 0 ?></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<div class="container" style="margin-top: -40px; position: relative; z-index: 5;">
  <div class="stats-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 40px;">
    <div class="stat-card" style="background: var(--color-bg-light); padding: 25px; border-radius: 16px; border: 1px solid var(--color-border); text-align: center;">
      <h4 style="color: var(--color-text-muted); font-size: 14px; margin-bottom: 10px;">Total Match</h4>
      <div style="font-size: 32px; font-weight: 900; color: white;"><?= $playerStats['main'] ?? 0 ?></div>
    </div>
    <div class="stat-card" style="background: var(--color-bg-light); padding: 25px; border-radius: 16px; border: 1px solid var(--color-border); text-align: center;">
      <h4 style="color: var(--color-text-green); font-size: 14px; margin-bottom: 10px;">Menang (W)</h4>
      <div style="font-size: 32px; font-weight: 900; color: #4ade80;"><?= $playerStats['w'] ?? 0 ?></div>
    </div>
    <div class="stat-card" style="background: var(--color-bg-light); padding: 25px; border-radius: 16px; border: 1px solid var(--color-border); text-align: center;">
      <h4 style="color: #f87171; font-size: 14px; margin-bottom: 10px;">Kalah (L)</h4>
      <div style="font-size: 32px; font-weight: 900; color: #f87171;"><?= $playerStats['l'] ?? 0 ?></div>
    </div>
    <div class="stat-card" style="background: var(--color-bg-light); padding: 25px; border-radius: 16px; border: 1px solid var(--color-border); text-align: center;">
      <h4 style="color: var(--color-primary); font-size: 14px; margin-bottom: 10px;">Point Diff</h4>
      <div style="font-size: 32px; font-weight: 900; color: var(--color-primary);"><?= ($playerStats['diff'] ?? 0) > 0 ? '+' : '' ?><?= $playerStats['diff'] ?? 0 ?></div>
    </div>
  </div>

  <div class="history-section" style="margin-bottom: 60px;">
    <h2 style="margin-bottom: 30px; display: flex; align-items: center; gap: 15px;">
      <span style="color: var(--color-primary);">MATCH</span> TERAKHIR
    </h2>
    
    <div class="table-wrap">
      <table>
        <thead>
          <tr>
            <th>Hasil</th>
            <th>Pertandingan</th>
            <th class="num">Skor</th>
            <th style="text-align: center;">Opponent Rank</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($matchHistory as $m): ?>
            <?php 
              $isP1 = ($m['player1_id'] == $id);
              $isWinner = ($m['winner_id'] == $id);
              $opponentName = $isP1 ? $m['p2_name'] : $m['p1_name'];
              $opponentRank = getPlayerRank($opponentName, $allStandings);
            ?>
            <tr>
              <td>
                <span class="badge" style="background: <?= $isWinner ? 'rgba(34, 197, 94, 0.1)' : 'rgba(248, 113, 113, 0.1)' ?>; color: <?= $isWinner ? '#22c55e' : '#f87171' ?>; border: 1px solid <?= $isWinner ? 'rgba(34, 197, 94, 0.2)' : 'rgba(248, 113, 113, 0.2)' ?>;">
                  <?= $isWinner ? 'WIN' : 'LOSE' ?>
                </span>
              </td>
              <td>
                <div style="display: flex; align-items: center; gap: 10px;">
                  <span><?= e($m['p1_name']) ?></span>
                  <span style="color: var(--color-text-muted); font-size: 11px;">VS</span>
                  <span><?= e($m['p2_name']) ?></span>
                </div>
                <?php if($m['match_notes']): ?>
                  <div style="font-size: 11px; color: var(--color-text-muted); margin-top: 5px; font-style: italic; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                    "<?= e($m['match_notes']) ?>"
                  </div>
                <?php endif; ?>
              </td>
              <td class="num">
                <strong style="color: white;"><?= $m['player1_score'] ?> - <?= $m['player2_score'] ?></strong>
              </td>
              <td style="text-align: center; color: var(--color-primary); font-weight: 700;">
                #<?= $opponentRank ?>
                <?php if($m['match_photo']): ?>
                  <div style="margin-top: 5px;"><span title="Ada Foto" style="cursor:help;">📸</span></div>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          <?php if(empty($matchHistory)): ?>
            <tr><td colspan="4" style="text-align: center; padding: 40px; color: var(--color-text-muted);">Belum ada riwayat pertandingan.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<style>
.player-profile-hero {
  background: linear-gradient(to bottom, #042f1a, var(--color-bg-dark));
  padding: 60px 0 100px;
  border-bottom: 1px solid var(--color-border);
}

.profile-header {
  display: flex;
  align-items: center;
  gap: 40px;
  flex-wrap: wrap;
}

.profile-photo-wrap {
  position: relative;
  width: 180px;
  height: 180px;
}

.profile-photo {
  width: 100%;
  height: 100%;
  border-radius: 50%;
  object-fit: cover;
  border: 5px solid var(--color-primary);
  box-shadow: 0 10px 30px rgba(0,0,0,0.5);
}

.profile-rank-badge {
  position: absolute;
  bottom: 0;
  right: 0;
  background: var(--color-primary);
  color: #000;
  font-weight: 900;
  padding: 8px 15px;
  border-radius: 30px;
  font-size: 14px;
  box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.profile-info h1 {
  font-size: 48px;
  margin-bottom: 5px;
}

.profile-info p {
  color: var(--color-text-muted);
  margin-bottom: 25px;
}

.profile-stats-summary {
  display: flex;
  gap: 30px;
}

.stat-item {
  display: flex;
  flex-direction: column;
}

.stat-item .label {
  font-size: 12px;
  color: var(--color-text-muted);
  text-transform: uppercase;
  letter-spacing: 1px;
}

.stat-item .value {
  font-size: 24px;
  font-weight: 900;
  color: var(--color-primary);
}

@media (max-width: 640px) {
  .profile-header { justify-content: center; text-align: center; }
  .profile-stats-summary { justify-content: center; }
}
</style>

<?php include 'includes/footer.php'; ?>
