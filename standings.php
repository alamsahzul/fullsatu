<?php
require 'config/db.php';
require 'includes/functions.php';
$allSeasons = getAllLeagueSeasons($pdo);
$selectedSeasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;

// If no season selected or invalid, get current season
if ($selectedSeasonId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM seasons WHERE id = ?");
    $stmt->execute([$selectedSeasonId]);
    $season = $stmt->fetch();
} else {
    $season = getCurrentLeagueSeason($pdo);
    $selectedSeasonId = $season['id'] ?? 0;
}

$pageTitle = 'Klasemen - FullSatu';
include 'includes/header.php';
?>
<div style="padding-top: 100px;"></div>
<section class="hero-page">
  <div style="display: inline-block; background: rgba(34, 197, 94, 0.1); color: var(--color-primary); padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: 800; letter-spacing: 2px; margin-bottom: 15px; border: 1px solid rgba(34, 197, 94, 0.2);">LIVE STANDINGS</div>
  <h1>Klasemen Liga</h1>
  <p>Peringkat terbaru dari kompetisi Liga yang sedang berjalan.</p>
  
  <!-- TOURNAMENT SWITCHER -->
  <div style="margin-top: 20px; display: flex; justify-content: center; gap: 10px; align-items: center;">
    <span style="color: var(--color-text-muted); font-size: 14px;">Pilih Tournament:</span>
    <form method="get" id="seasonForm">
      <select name="season_id" onchange="document.getElementById('seasonForm').submit()" style="background: var(--color-bg-light); border: 1px solid var(--color-border); color: var(--color-primary); padding: 8px 15px; border-radius: 30px; font-weight: 700; cursor: pointer; min-width: 150px;">
        <?php foreach($allSeasons as $s): ?>
          <option value="<?= $s['id'] ?>" <?= $s['id'] == $selectedSeasonId ? 'selected' : '' ?>>
            <?= e($s['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
</section>
<?php if (!$season): ?>
  <div class="alert">Belum ada season. Buat season dari halaman admin.</div>
<?php else: ?>
  <?php if ($season['format'] === 'cup'): ?>
    <div class="card" style="text-align: center; padding: 60px 20px;">
       <div style="font-size: 50px; margin-bottom: 20px;">🏆</div>
       <h2 style="margin-bottom: 10px;">Turnamen Cup Murni</h2>
       <p style="color: var(--color-text-muted); margin-bottom: 30px;">Turnamen ini menggunakan sistem gugur langsung tanpa fase grup/klasemen.</p>
       <a href="<?= base_url('cup?season_id=' . $season['id']) ?>" class="btn btn-primary" style="padding: 12px 30px;">Lihat Bagan Pertandingan &rarr;</a>
    </div>
  <?php else: ?>
    <?php
    // Fetch participants with partners and group them
    $stmt = $pdo->prepare("SELECT sp.*, p1.name AS p1_name, p1.photo AS photo1, p2.name AS p2_name, p2.photo AS photo2 
                           FROM season_players sp 
                           JOIN players p1 ON p1.id = sp.player_id 
                           LEFT JOIN players p2 ON p2.id = sp.partner_id 
                           WHERE sp.season_id=?");
    $stmt->execute([$season['id']]);
    $allParticipants = $stmt->fetchAll();

    $groups = [];
    foreach ($allParticipants as $p) {
        $groups[$p['group_name']][] = $p;
    }
    ksort($groups);

    if (empty($groups)): ?>
      <div class="alert">Belum ada peserta di musim ini.</div>
    <?php else: ?>
      
      <div style="text-align: center; margin-bottom: 40px;">
         <span class="badge" style="background: rgba(34, 197, 94, 0.1); color: var(--color-primary); padding: 8px 20px; border-radius: 30px; font-weight: 800; letter-spacing: 2px;">
           <?= ($season['format'] === 'league' ? 'LIGA' : ($season['format'] === 'cup' ? 'KNOCKOUT' : 'HYBRID')) ?> - <?= strtoupper(e($season['category'])) ?>
         </span>
      </div>

      <?php foreach($groups as $groupName => $groupParticipants): 
          $participantIds = array_column($groupParticipants, 'id'); 
          $standings = calculateStandings($pdo, $season['id'], $participantIds); 
      ?>
      <div class="card" style="margin-bottom: 50px; border: 1px solid rgba(255,255,255,0.05); padding: 0;">
        <div style="background: var(--color-bg-light); padding: 15px 25px; border-bottom: 1px solid var(--color-border); display: flex; align-items: center; gap: 15px;">
          <div style="width: 40px; height: 40px; background: var(--color-primary); color: #000; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-weight: 900; font-size: 20px;">
            <?= e($groupName) ?>
          </div>
          <h2 style="margin: 0; font-size: 20px; letter-spacing: 1px;">GROUP <?= e($groupName) ?></h2>
        </div>
        <div class="table-wrap" style="border: none; border-radius: 0;">
          <table>
            <thead>
              <tr><th class="num">#</th><th>Pemain / Tim</th><th class="num">M</th><th class="num">M</th><th class="num">K</th><th class="num">PF</th><th class="num">PA</th><th class="num">Diff</th><th class="num">Poin</th></tr>
            </thead>
            <tbody>
              <?php foreach ($standings as $i => $row): ?>
              <tr class="<?= $i < 4 ? 'top' : '' ?>">
                <td class="num" style="font-weight: 700; color: <?= $i < 4 ? 'var(--color-primary)' : 'var(--color-text-muted)' ?>;"><?= $i + 1 ?></td>
                <td>
                    <div style="display: flex; align-items: center; gap: 12px; color: white;">
                      <div style="position: relative; flex-shrink: 0;">
                        <img src="<?= $row['photo'] ? base_url('assets/uploads/players/' . $row['photo']) : base_url('assets/img/player_avatar.png') ?>" style="width: 32px; height: 32px; border-radius: 50%; border: 2px solid <?= $i < 4 ? 'var(--color-primary)' : 'transparent' ?>; object-fit: cover;">
                        <?php if(isset($row['photo2']) && $row['photo2']): ?>
                          <img src="<?= base_url('assets/uploads/players/' . $row['photo2']) ?>" style="width: 20px; height: 20px; border-radius: 50%; border: 1px solid var(--color-border); position: absolute; bottom: -5px; right: -5px; background: #000;">
                        <?php endif; ?>
                      </div>
                      <div>
                        <div style="font-weight: 700;"><?= e($row['name']) ?></div>
                        <?php if(isset($row['name2']) && $row['name2']): ?>
                          <div style="font-size: 11px; color: var(--color-text-muted);">& <?= e($row['name2']) ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                </td>
                <td class="num"><?= $row['main'] ?></td>
                <td class="num" style="color: #4ade80;"><?= $row['w'] ?></td>
                <td class="num" style="color: #ef4444;"><?= $row['l'] ?></td>
                <td class="num"><?= $row['pf'] ?></td>
                <td class="num"><?= $row['pa'] ?></td>
                <td class="num" style="color: <?= $row['diff'] >= 0 ? '#4ade80' : '#ef4444' ?>;"><?= ($row['diff'] > 0 ? '+' : '') . $row['diff'] ?></td>
                <td class="num"><strong style="color: var(--color-primary); font-size: 16px;"><?= $row['pts'] ?></strong></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
      <?php endforeach; ?>
    <?php endif; ?>
  <?php endif; ?>
<?php endif; ?>
<?php include 'includes/footer.php'; ?>
