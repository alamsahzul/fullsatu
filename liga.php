<?php
require 'config/db.php';
require 'includes/functions.php';
$allSeasons = getAllLigaSeasons($pdo);
$selectedSeasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;

if ($selectedSeasonId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM seasons WHERE id = ?");
    $stmt->execute([$selectedSeasonId]);
    $season = $stmt->fetch();
} else {
    $season = getCurrentLigaSeason($pdo);
    $selectedSeasonId = $season['id'] ?? 0;
}

$pageTitle = 'Liga - Klasemen';
include 'includes/header.php';
?>
$pageTitle = 'Liga - Klasemen';
include 'includes/header.php';
?>
<style>
    @media (max-width: 768px) {
        .tab-container {
            flex-direction: column !important;
            gap: 10px !important;
            padding: 0 20px;
        }
        .tab-container .btn {
            width: 100% !important;
            padding: 15px !important;
            justify-content: center !important;
            font-size: 14px !important;
        }
        .search-filter-container {
            flex-direction: column !important;
            gap: 10px !important;
            padding: 20px !important;
        }
        .search-filter-container input {
            width: 100% !important;
            text-align: center !important;
        }
        .search-filter-container div {
            margin: 5px 0 !important;
        }
        .match-main-flex {
            gap: 8px !important;
            flex-wrap: nowrap !important;
            justify-content: space-between !important;
        }
        .team-box {
            min-width: 0 !important;
            flex: 1 !important;
            gap: 5px !important;
        }
        .team-box img {
            width: 25px !important;
            height: 25px !important;
        }
        .team-box div div:first-child {
            font-size: 11px !important;
        }
        .team-box div div:last-child {
            font-size: 9px !important;
        }
        .score-box {
            gap: 3px !important;
        }
        .score-box > div:first-child {
            padding: 5px 10px !important;
            gap: 5px !important;
        }
        .score-box div div {
            font-size: 16px !important;
        }
        .score-box div span {
            font-size: 10px !important;
        }
        .quick-view-btn {
            font-size: 7px !important;
            padding: 2px 6px !important;
        }
        .detail-row-inner {
            flex-direction: column !important;
            align-items: center !important;
            text-align: center !important;
            padding: 20px !important;
            gap: 20px !important;
        }
        .detail-photo-wrapper {
            width: 100% !important;
            display: flex !important;
            justify-content: center !important;
        }
        .detail-photo-wrapper img {
            width: 100% !important;
            max-width: 280px !important;
            height: auto !important;
        }
        .detail-notes {
            display: -webkit-box !important;
            -webkit-line-clamp: 3 !important;
            -webkit-box-orient: vertical !important;
            overflow: hidden !important;
            font-size: 13px !important;
            line-height: 1.6 !important;
        }
        .detail-footer {
            justify-content: center !important;
            width: 100% !important;
        }
    }
</style>

<div style="padding-top: 100px;"></div>
<section class="hero-page">
  <div style="display: inline-block; background: rgba(34, 197, 94, 0.1); color: var(--color-primary); padding: 5px 15px; border-radius: 20px; font-size: 12px; font-weight: 800; letter-spacing: 2px; margin-bottom: 15px; border: 1px solid rgba(34, 197, 94, 0.2);">LIGA MURNI</div>
  <h1>Klasemen Liga</h1>
  <p>Peringkat terbaru dari kompetisi Liga yang sedang berjalan.</p>
  
  <div style="margin-top: 20px; display: flex; justify-content: center; gap: 10px; align-items: center;">
    <span style="color: var(--color-text-muted); font-size: 14px;">Pilih Liga:</span>
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
  <div class="container"><div class="alert">Belum ada kompetisi Liga.</div></div>
<?php else: ?>
    <?php
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
      <div class="container"><div class="alert">Belum ada peserta di liga ini.</div></div>
    <?php else: ?>
      <!-- TAB NAVIGATION -->
      <div class="tab-container container" style="display: flex; gap: 15px; justify-content: center; margin-bottom: 40px;">
        <button id="btn-tab-klasemen" onclick="switchTab('tab-klasemen')" class="tab-btn btn btn-primary" style="padding: 10px 30px; border-radius: 30px; font-weight: 800; display: flex; align-items: center; gap: 10px; border: 2px solid var(--color-primary);">
          <span style="font-size: 20px;">📊</span> KLASEMEN
        </button>
        <button id="btn-tab-jadwal" onclick="switchTab('tab-jadwal')" class="tab-btn btn btn-outline" style="padding: 10px 30px; border-radius: 30px; font-weight: 800; display: flex; align-items: center; gap: 10px; border: 2px solid var(--color-primary);">
          <span style="font-size: 20px;">📅</span> JADWAL & HASIL
        </button>
      </div>

      <!-- TAB KLASEMEN -->
      <div id="tab-klasemen" class="tab-content">
        <div class="container">
          <div style="text-align: center; margin-bottom: 40px;">
             <span class="badge" style="background: rgba(34, 197, 94, 0.1); color: var(--color-primary); padding: 8px 20px; border-radius: 30px; font-weight: 800; letter-spacing: 2px;">
               LIGA - <?= strtoupper(e($season['category'])) ?>
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
              <h2 style="margin: 0; font-size: 20px; letter-spacing: 1px;">KLASEMEN</h2>
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
        </div>
      </div>

      <!-- TAB JADWAL -->
      <div id="tab-jadwal" class="tab-content" style="display: none;">
        <?php
        $stmt = $pdo->prepare("SELECT m.*, 
            p1.name AS player1, p1.photo AS photo1,
            pa1.name AS partner1, pa1.photo AS photo1p,
            p2.name AS player2, p2.photo AS photo2,
            pa2.name AS partner2, pa2.photo AS photo2p
            FROM matches m
            JOIN players p1 ON m.player1_id = p1.id
            LEFT JOIN players pa1 ON m.p1_partner_id = pa1.id
            JOIN players p2 ON m.player2_id = p2.id
            LEFT JOIN players pa2 ON m.p2_partner_id = pa2.id
            WHERE m.season_id = ? 
            ORDER BY (m.status = 'completed') DESC, m.created_at DESC, m.id DESC");
        $stmt->execute([$season['id']]);
        $matches = $stmt->fetchAll();
        $limitInitial = 10;
        ?>
        <div class="container">
          <div class="card search-filter-container" style="margin-bottom: 24px; padding: 15px 25px; display: flex; align-items: center; justify-content: center; gap: 15px; border: 1px solid var(--color-border); flex-wrap: wrap;">
            <input type="text" id="matchSearch1" placeholder="Pemain 1..." onkeyup="searchMatches()" style="width: 180px; background: rgba(0,0,0,0.2); border: 1px solid var(--color-border); color: white; padding: 10px 20px; border-radius: 30px; outline: none; font-size: 14px;">
            <div style="font-weight: 900; color: var(--color-text-muted); font-size: 12px;">VS</div>
            <input type="text" id="matchSearch2" placeholder="Pemain 2..." onkeyup="searchMatches()" style="width: 180px; background: rgba(0,0,0,0.2); border: 1px solid var(--color-border); color: white; padding: 10px 20px; border-radius: 30px; outline: none; font-size: 14px;">
          </div>

          <div style="background: var(--color-bg-light); padding: 15px 25px; border-radius: 12px 12px 0 0; border: 1px solid rgba(255,255,255,0.05); border-bottom: none;">
            <h2 style="margin: 0; font-size: 18px; letter-spacing: 1px; color: var(--color-primary);">📅 JADWAL & HASIL LIGA</h2>
          </div>
          <div class="card" style="border-radius: 0 0 12px 12px; border: 1px solid rgba(255,255,255,0.05); padding: 0;">
            <div class="table-wrap" style="border: none; border-radius: 0;">
              <table id="matchesTable">
                <tbody id="matchesTableBody">
                  <?php if(empty($matches)): ?>
                    <tr><td colspan="3" style="text-align: center; padding: 40px; color: var(--color-text-muted);">Belum ada jadwal pertandingan.</td></tr>
                  <?php else: ?>
                    <?php foreach($matches as $index => $m): ?>
                    <tr class="match-row <?= ($index >= $limitInitial) ? 'hidden-match' : '' ?> <?= ($m['match_photo'] || $m['match_notes']) ? 'has-detail' : '' ?>" 
                        data-team1="<?= strtolower(e($m['player1'] . ' ' . $m['partner1'])) ?>" 
                        data-team2="<?= strtolower(e($m['player2'] . ' ' . $m['partner2'])) ?>"
                        style="<?= ($index >= $limitInitial) ? 'display: none;' : '' ?> <?= ($m['match_photo'] || $m['match_notes']) ? 'cursor: pointer;' : '' ?>"
                        <?= ($m['match_photo'] || $m['match_notes']) ? 'onclick="toggleDetail(\'m-'.$m['id'].'\')"' : '' ?>>
                      <td style="padding: 20px;">
                        <div class="match-main-flex" style="display: flex; align-items: center; gap: 20px; justify-content: space-between; flex-wrap: wrap;">
                          
                          <div class="team-box" style="flex: 1; min-width: 150px; display: flex; align-items: center; gap: 12px; justify-content: flex-end; text-align: right;">
                            <div>
                              <div style="font-weight: 700; color: <?= $m['winner_id'] == $m['player1_id'] ? 'var(--color-primary)' : 'white' ?>;"><?= e($m['player1']) ?></div>
                              <?php if($m['partner1']): ?><div style="font-size: 11px; color: var(--color-text-muted);">& <?= e($m['partner1']) ?></div><?php endif; ?>
                            </div>
                            <img src="<?= $m['photo1'] ? base_url('assets/uploads/players/'.$m['photo1']) : base_url('assets/img/player_avatar.png') ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid <?= $m['winner_id'] == $m['player1_id'] ? 'var(--color-primary)' : 'var(--color-border)' ?>;">
                          </div>

                          <div class="score-box" style="display: flex; flex-direction: column; align-items: center; gap: 5px;">
                            <div style="display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.2); padding: 8px 15px; border-radius: 8px;">
                                <div style="font-size: 20px; font-weight: 900; color: <?= $m['winner_id'] == $m['player1_id'] ? 'var(--color-primary)' : 'white' ?>;"><?= $m['status'] === 'completed' ? $m['player1_score'] : '-' ?></div>
                                <div style="font-size: 12px; color: var(--color-text-muted);">VS</div>
                                <div style="font-size: 20px; font-weight: 900; color: <?= $m['winner_id'] == $m['player2_id'] ? 'var(--color-primary)' : 'white' ?>;"><?= $m['status'] === 'completed' ? $m['player2_score'] : '-' ?></div>
                            </div>
                            <?php if($m['match_photo'] || $m['match_notes']): ?>
                                <div class="quick-view-btn" style="font-size: 8px; color: #000; background: var(--color-primary); font-weight: 900; letter-spacing: 1px; display: flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 10px; margin-top: 3px; box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3);">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="M7 13l5 5 5-5M7 6l5 5 5-5"/></svg>
                                    QUICK VIEW
                                </div>
                            <?php endif; ?>
                          </div>

                          <div class="team-box" style="flex: 1; min-width: 150px; display: flex; align-items: center; gap: 12px; justify-content: flex-start; text-align: left;">
                            <img src="<?= $m['photo2'] ? base_url('assets/uploads/players/'.$m['photo2']) : base_url('assets/img/player_avatar.png') ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid <?= $m['winner_id'] == $m['player2_id'] ? 'var(--color-primary)' : 'var(--color-border)' ?>;">
                            <div>
                              <div style="font-weight: 700; color: <?= $m['winner_id'] == $m['player2_id'] ? 'var(--color-primary)' : 'white' ?>;"><?= e($m['player2']) ?></div>
                              <?php if($m['partner2']): ?><div style="font-size: 11px; color: var(--color-text-muted);">& <?= e($m['partner2']) ?></div><?php endif; ?>
                            </div>
                          </div>
                        </div>
                      </td>
                    </tr>
                    
                    <?php if($m['match_photo'] || $m['match_notes']): ?>
                    <tr id="m-<?= $m['id'] ?>" class="detail-row" style="display: none; background: rgba(0,0,0,0.3);">
                        <td style="padding: 0;">
                            <div class="detail-row-inner" style="padding: 30px; border-top: 1px solid rgba(34, 197, 94, 0.2); border-bottom: 1px solid rgba(34, 197, 94, 0.2); display: flex; gap: 30px; flex-wrap: wrap; background: linear-gradient(to right, rgba(34, 197, 94, 0.05), transparent);">
                                <?php if($m['match_photo']): ?>
                                    <div class="detail-photo-wrapper" style="flex-shrink: 0;">
                                        <img src="<?= base_url('assets/uploads/matches/'.$m['match_photo']) ?>" style="width: 200px; height: 200px; border-radius: 20px; object-fit: cover; border: 2px solid rgba(255,255,255,0.1); box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
                                    </div>
                                <?php endif; ?>
                                <div style="flex: 1; min-width: 250px; display: flex; flex-direction: column; justify-content: space-between;">
                                    <div>
                                        <div class="detail-notes" style="color: #e2e8f0; font-size: 15px; line-height: 1.8; font-family: 'Inter', sans-serif;">
                                            <?= nl2br(e(trim($m['match_notes']))) ?>
                                        </div>
                                    </div>
                                    <div class="detail-footer" style="margin-top: 25px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                                        <a href="match_detail?id=<?= $m['id'] ?>" class="btn btn-primary" style="padding: 8px 25px; font-size: 12px; border-radius: 30px; font-weight: 800; display: inline-flex; align-items: center; gap: 8px;">
                                            LIHAT HALAMAN PENUH <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>
                                        </a>

                                        <div style="display: flex; gap: 10px; align-items: center;">
                                            <?php 
                                                $fullBase = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                                                $mUrl = $fullBase . base_url('match_detail?id='.$m['id']);
                                                $mText = "🔥 HASIL PERTANDINGAN SERU! 🔥\n\n" . $m['player1'] . " vs " . $m['player2'] . "\nSkor: " . ($m['player1_score'] ?? 0) . " - " . ($m['player2_score'] ?? 0);
                                                $mWa = "https://wa.me/?text=" . urlencode($mText . "\n\nCek di sini:\n" . $mUrl);
                                                $mFb = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($mUrl);
                                            ?>
                                            <a href="<?= $mWa ?>" target="_blank" style="color: #25D366;"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.937 3.659 1.432 5.631 1.433h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg></a>
                                            <a href="<?= $mFb ?>" target="_blank" style="color: #1877F2;"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                                            <button onclick="copyMatchLink('<?= $mUrl ?>')" style="background: none; border: none; padding: 0; color: #000; cursor: pointer; position: relative;"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.17-2.89-.6-4.09-1.47V15.5c0 1.93-.45 3.84-1.63 5.37-1.31 1.78-3.41 2.87-5.59 2.87-2.18 0-4.28-1.09-5.59-2.87C4.45 19.34 4 17.43 4 15.5c0-1.93.45-3.84 1.63-5.37C6.94 8.35 9.04 7.26 11.22 7.26c.42 0 .84.04 1.25.12V11.5c-.41-.08-.83-.12-1.25-.12-1.29 0-2.54.64-3.32 1.69-.71.93-.98 2.08-.98 3.23 0 1.15.27 2.3.98 3.23.78 1.05 2.03 1.69 3.32 1.69 1.29 0 2.54-.64 3.32-1.69.71-.93.98-2.08.98-3.23V0l.02.02z"/></svg></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
            
            <?php if(count($matches) > $limitInitial): ?>
            <div id="loadMoreContainer" style="padding: 20px; text-align: center; border-top: 1px solid rgba(255,255,255,0.05);">
                <button onclick="loadMoreMatches()" class="btn btn-outline" style="padding: 10px 30px; border-radius: 30px; font-size: 13px; font-weight: 700;">LIHAT PERTANDINGAN LAINNYA</button>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
    <?php endif; ?>
<?php endif; ?>

<script>
function switchTab(tabId) {
    // Hide all contents
    document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
    // Show target
    document.getElementById(tabId).style.display = 'block';
    
    // Update buttons
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('btn-primary');
        b.classList.add('btn-outline');
    });
    document.getElementById('btn-' + tabId).classList.add('btn-primary');
    document.getElementById('btn-' + tabId).classList.remove('btn-outline');
}

function searchMatches() {
    const q1 = document.getElementById('matchSearch1').value.toLowerCase();
    const q2 = document.getElementById('matchSearch2').value.toLowerCase();
    const rows = document.querySelectorAll('.match-row');
    const loadMoreBtn = document.getElementById('loadMoreContainer');
    
    rows.forEach(row => {
        const team1 = row.getAttribute('data-team1');
        const team2 = row.getAttribute('data-team2');
        
        let show = false;
        if (q1 === "" && q2 === "") {
            show = true;
        } else if (q1 !== "" && q2 !== "") {
            show = (team1.includes(q1) && team2.includes(q2)) || (team1.includes(q2) && team2.includes(q1));
        } else {
            const query = q1 || q2;
            show = team1.includes(query) || team2.includes(query);
        }
        
        // If searching, we ignore the 'hidden-match' class for visibility but searching hides/shows
        row.style.display = show ? 'table-row' : 'none';
    });

    // Hide Load More during search
    if (q1 !== "" || q2 !== "") {
        if(loadMoreBtn) loadMoreBtn.style.display = 'none';
        // Hide all detail rows during search to keep it clean
        document.querySelectorAll('.detail-row').forEach(dr => dr.style.display = 'none');
    } else {
        if(loadMoreBtn && document.querySelectorAll('.hidden-match').length > 0) loadMoreBtn.style.display = 'block';
    }
}

function toggleDetail(id) {
    const row = document.getElementById(id);
    if (row.style.display === 'none') {
        row.style.display = 'table-row';
    } else {
        row.style.display = 'none';
    }
}

function copyMatchLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        alert('Link pertandingan berhasil disalin!');
    });
}

function loadMoreMatches() {
    const hidden = document.querySelectorAll('.hidden-match');
    const batchSize = 10;
    
    let count = 0;
    hidden.forEach(row => {
        if (count < batchSize) {
            row.style.display = 'table-row';
            row.classList.remove('hidden-match');
            count++;
        }
    });
    
    if (document.querySelectorAll('.hidden-match').length === 0) {
        const btn = document.getElementById('loadMoreContainer');
        if(btn) btn.style.display = 'none';
    }
}
</script>

<?php include 'includes/footer.php'; ?>
