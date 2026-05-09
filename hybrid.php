<?php
require 'config/db.php';
require 'includes/functions.php';

$allSeasons = getAllHybridSeasons($pdo);
$selectedSeasonId = isset($_GET['season_id']) ? (int)$_GET['season_id'] : 0;

if ($selectedSeasonId > 0) {
    $stmt = $pdo->prepare("SELECT * FROM seasons WHERE id = ?");
    $stmt->execute([$selectedSeasonId]);
    $season = $stmt->fetch();
} else {
    $season = getCurrentHybridSeason($pdo);
    $selectedSeasonId = $season['id'] ?? 0;
}

$pageTitle = 'Hybrid - Klasemen & Jadwal';
include 'includes/header.php';

// Hybrid: fetch group standings
$groupStandings = [];
if ($season) {
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
        .match-main-flex {
            gap: 10px !important;
            flex-wrap: wrap !important;
            justify-content: center !important;
            flex-direction: column !important;
        }
        .team-box {
            justify-content: center !important;
            text-align: center !important;
            width: 100% !important;
            flex-direction: column !important;
        }
        .team-box div {
            text-align: center !important;
        }
    }
</style>

<div style="padding-top: 100px;"></div>
<section class="hero-page">
  <div class="badge" style="display: inline-block; background: rgba(34, 197, 94, 0.1); color: var(--color-primary); padding: 8px 20px; border-radius: 30px; font-weight: 800; letter-spacing: 2px; margin-bottom: 20px; border: 1px solid rgba(34, 197, 94, 0.1);">
    HYBRID TOURNAMENT
  </div>
  <h1>Hybrid: Grup & Knockout</h1>
  <p>Fase grup yang berlanjut ke sistem gugur otomatis.</p>

  <div style="margin-top: 20px; display: flex; justify-content: center; gap: 10px; align-items: center;">
    <span style="color: var(--color-text-muted); font-size: 14px;">Pilih Hybrid:</span>
    <form method="get" id="seasonHybridForm">
      <select name="season_id" onchange="document.getElementById('seasonHybridForm').submit()" style="background: var(--color-bg-light); border: 1px solid var(--color-border); color: var(--color-primary); padding: 8px 15px; border-radius: 30px; font-weight: 700; cursor: pointer; min-width: 150px;">
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
    <div class="alert">Belum ada kompetisi Hybrid.</div>
  <?php else: ?>
    
    <!-- TAB NAVIGATION -->
    <div class="tab-container" style="display: flex; justify-content: center; gap: 15px; margin-bottom: 40px;">
      <button id="btn-tab-klasemen" onclick="switchTab('tab-klasemen')" class="tab-btn btn btn-primary" style="padding: 10px 30px; border-radius: 30px; font-weight: 800; display: flex; align-items: center; gap: 10px; border: 2px solid var(--color-primary);">
        <span style="font-size: 20px;">📊</span> KLASEMEN
      </button>
      <button id="btn-tab-jadwal" onclick="switchTab('tab-jadwal')" class="tab-btn btn btn-outline" style="padding: 10px 30px; border-radius: 30px; font-weight: 800; display: flex; align-items: center; gap: 10px; border: 2px solid var(--color-primary);">
        <span style="font-size: 20px;">📅</span> HASIL
      </button>
      <button id="btn-tab-bagan" onclick="switchTab('tab-bagan')" class="tab-btn btn btn-outline" style="padding: 10px 30px; border-radius: 30px; font-weight: 800; display: flex; align-items: center; gap: 10px; border: 2px solid var(--color-primary);">
        <span style="font-size: 20px;">🏆</span> BAGAN
      </button>
    </div>

    <!-- TAB KLASEMEN GRUP -->
    <div id="tab-klasemen" class="tab-content">
      <?php if(!empty($groupStandings)): ?>
      <div class="card" style="margin-bottom: 40px; padding: 0; overflow: hidden; border: 1px solid rgba(255,255,255,0.05);">
        <div style="background: var(--color-bg-light); padding: 15px 25px; border-bottom: 1px solid var(--color-border);">
          <h2 style="margin: 0; font-size: 18px; letter-spacing: 1px; color: var(--color-primary);">📊 KLASEMEN FASE GRUP</h2>
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
                      <?php if($row['p2_name']): ?><div style="font-weight: 700;">& <?= e($row['p2_name']) ?></div><?php endif; ?>
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
    </div>

    <!-- TAB HASIL PERTANDINGAN GRUP -->
    <div id="tab-jadwal" class="tab-content" style="display: none;">
      <?php
      $stmt_m = $pdo->prepare("SELECT m.*, 
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
          ORDER BY (m.status = 'completed') DESC, m.id DESC");
      $stmt_m->execute([$season['id']]);
      $groupMatches = $stmt_m->fetchAll();
      $limitInitial = 10;

      if(!empty($groupMatches)): ?>
        <div class="card search-filter-container" style="margin-bottom: 24px; padding: 15px 25px; display: flex; align-items: center; justify-content: center; gap: 15px; border: 1px solid var(--color-border); flex-wrap: wrap;">
            <input type="text" id="matchSearch1" placeholder="Pemain 1..." onkeyup="searchMatches()" style="width: 180px; background: rgba(0,0,0,0.2); border: 1px solid var(--color-border); color: white; padding: 10px 20px; border-radius: 30px; outline: none; font-size: 14px;">
            <div style="font-weight: 900; color: var(--color-text-muted); font-size: 12px;">VS</div>
            <input type="text" id="matchSearch2" placeholder="Pemain 2..." onkeyup="searchMatches()" style="width: 180px; background: rgba(0,0,0,0.2); border: 1px solid var(--color-border); color: white; padding: 10px 20px; border-radius: 30px; outline: none; font-size: 14px;">
          </div>

        <div style="background: var(--color-bg-light); padding: 15px 25px; border-radius: 12px 12px 0 0; border: 1px solid rgba(255,255,255,0.05); border-bottom: 1px solid var(--color-border);">
          <h2 style="margin: 0; font-size: 18px; letter-spacing: 1px; color: var(--color-primary);">📅 HASIL PERTANDINGAN GRUP</h2>
        </div>
        <div class="card" style="background: transparent; border: none; box-shadow: none; padding: 0; margin-bottom: 40px;">
          <div class="table-wrap" style="border: none; border-radius: 0; background: transparent;">
            <table id="matchesTable" style="width: 100%; border-collapse: separate; border-spacing: 0 12px;">
              <tbody>
                <?php foreach($groupMatches as $index => $m): ?>
                <tr class="match-row <?= ($index >= $limitInitial) ? 'hidden-match' : '' ?> <?= ($m['match_photo'] || $m['match_notes']) ? 'has-detail' : '' ?>" 
                    data-team1="<?= strtolower(e($m['player1'] . ' ' . $m['partner1'])) ?>" 
                    data-team2="<?= strtolower(e($m['player2'] . ' ' . $m['partner2'])) ?>"
                    style="<?= ($index >= $limitInitial) ? 'display: none;' : '' ?> <?= ($m['match_photo'] || $m['match_notes']) ? 'cursor: pointer;' : '' ?> background: var(--color-bg-light); border: 1px solid rgba(255,255,255,0.05);"
                    <?= ($m['match_photo'] || $m['match_notes']) ? 'onclick="toggleDetail(\'h-'.$m['id'].'\')"' : '' ?>
                    onmouseover="this.style.filter='brightness(1.2)'" 
                    onmouseout="this.style.filter='none'">
                  <td style="padding: 20px;">
  <div class="match-main-flex" style="display: flex; align-items: center; gap: 15px; justify-content: space-between; flex-wrap: wrap;">
    
    <!-- TEAM 1 -->
    <div class="team-box" style="flex: 1; min-width: 150px; display: flex; align-items: center; gap: 12px; justify-content: flex-end; text-align: right;">
                        <div>
                          <div style="font-weight: 700; color: <?= $m['winner_id'] == $m['player1_id'] ? 'var(--color-primary)' : 'white' ?>;"><?= e($m['player1']) ?></div>
                          <?php if($m['partner1']): ?><div style="font-weight: 700;">& <?= e($m['partner1']) ?></div><?php endif; ?>
                        </div>
                        <img src="<?= $m['photo1'] ? base_url('assets/uploads/players/'.$m['photo1']) : base_url('assets/img/player_avatar.png') ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid <?= $m['winner_id'] == $m['player1_id'] ? 'var(--color-primary)' : 'var(--color-border)' ?>;">
                      </div>

                      <!-- SCORE & DETAIL -->
                      <div class="score-box" style="text-align: center; min-width: 80px;">
                        <div style="display: flex; align-items: center; gap: 10px; background: rgba(0,0,0,0.5); padding: 8px 15px; border-radius: 8px; justify-content: center;">
                            <div style="font-size: 20px; font-weight: 900; color: <?= $m['winner_id'] == $m['player1_id'] ? 'var(--color-primary)' : 'white' ?>;"><?= $m['status'] === 'completed' ? $m['player1_score'] : '-' ?></div>
                            <span style="font-size: 10px; color: var(--color-text-muted); font-weight: 900;">VS</span>
                            <div style="font-size: 20px; font-weight: 900; color: <?= $m['winner_id'] == $m['player2_id'] ? 'var(--color-primary)' : 'white' ?>;"><?= $m['status'] === 'completed' ? $m['player2_score'] : '-' ?></div>
                        </div>
                        <?php if($m['match_photo'] || $m['match_notes']): ?>
                            <div class="quick-view-btn" style="font-size: 8px; color: #000; background: var(--color-primary); font-weight: 900; letter-spacing: 1px; display: flex; align-items: center; gap: 4px; padding: 2px 8px; border-radius: 10px; margin-top: 2px; box-shadow: 0 4px 10px rgba(34, 197, 94, 0.3);">
                                <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"><path d="M7 13l5 5 5-5M7 6l5 5 5-5"/></svg>
                                QUICK VIEW
                            </div>
                        <?php endif; ?>
                      </div>

                      <!-- TEAM 2 -->
                      <div class="team-box" style="flex: 1; min-width: 150px; display: flex; align-items: center; gap: 12px; justify-content: flex-start; text-align: left;">
  <img src="<?= $m['photo2'] ? base_url('assets/uploads/players/'.$m['photo2']) : base_url('assets/img/player_avatar.png') ?>" style="width: 35px; height: 35px; border-radius: 50%; object-fit: cover; border: 1px solid <?= $m['winner_id'] == $m['player2_id'] ? 'var(--color-primary)' : 'var(--color-border)' ?>;">
                        <div>
                          <div style="font-weight: 700; color: <?= $m['winner_id'] == $m['player2_id'] ? 'var(--color-primary)' : 'white' ?>;"><?= e($m['player2']) ?></div>
                          <?php if($m['partner2']): ?><div style="font-weight: 700;">& <?= e($m['partner2']) ?></div><?php endif; ?>
                        </div>
                      </div>

                    </div>
                  </td>
                </tr>

                <?php if($m['match_photo'] || $m['match_notes']): ?>
                <tr id="h-<?= $m['id'] ?>" class="detail-row" style="display: none; background: rgba(0,0,0,0.3);">
                    <td style="padding: 0;">
                        <div class="detail-row-inner" style="padding: 25px; border-top: 1px solid rgba(34, 197, 94, 0.2); border-bottom: 1px solid rgba(34, 197, 94, 0.2); display: flex; gap: 25px; flex-wrap: wrap; background: linear-gradient(to right, rgba(34, 197, 94, 0.05), transparent);">
                            <?php if($m['match_photo']): ?>
                                <div class="detail-photo-wrapper" style="flex-shrink: 0;">
                                    <img src="<?= base_url('assets/uploads/matches/'.$m['match_photo']) ?>" style="width: 150px; height: 150px; border-radius: 15px; object-fit: cover; border: 2px solid rgba(255,255,255,0.1); box-shadow: 0 10px 20px rgba(0,0,0,0.4);">
                                </div>
                            <?php endif; ?>
                            <div style="flex: 1; min-width: 200px; display: flex; flex-direction: column; justify-content: space-between;">
                                <div>
                                    <div class="detail-notes" style="color: #e2e8f0; font-size: 13px; line-height: 1.7;">
                                        <?= nl2br(e(trim($m['match_notes']))) ?>
                                    </div>
                                </div>
                                <div class="detail-footer" style="margin-top: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                                    <a href="match_detail?id=<?= $m['id'] ?>" class="btn btn-primary" style="padding: 6px 18px; font-size: 11px; border-radius: 25px; font-weight: 800; display: inline-flex; align-items: center; gap: 6px;">
                                        FULL DETAIL <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6M15 3h6v6M10 14L21 3"/></svg>
                                    </a>

                                    <div style="display: flex; gap: 8px; align-items: center;">
                                        <?php 
                                            $fullBase = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
                                            $mUrl = $fullBase . base_url('match_detail?id='.$m['id']);
                                            $mText = "🔥 HASIL PERTANDINGAN SERU! 🔥\n\n" . $m['player1'] . " vs " . $m['player2'] . "\nSkor: " . ($m['player1_score'] ?? 0) . " - " . ($m['player2_score'] ?? 0);
                                            $mWa = "https://wa.me/?text=" . urlencode($mText . "\n\nCek di sini:\n" . $mUrl);
                                            $mFb = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($mUrl);
                                        ?>
                                        <a href="<?= $mWa ?>" target="_blank" style="color: #25D366;"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.937 3.659 1.432 5.631 1.433h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg></a>
                                        <a href="<?= $mFb ?>" target="_blank" style="color: #1877F2;"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg></a>
                                        <button onclick="copyMatchLink('<?= $mUrl ?>')" style="background: none; border: none; padding: 0; color: #000; cursor: pointer;"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.17-2.89-.6-4.09-1.47V15.5c0 1.93-.45 3.84-1.63 5.37-1.31 1.78-3.41 2.87-5.59 2.87-2.18 0-4.28-1.09-5.59-2.87C4.45 19.34 4 17.43 4 15.5c0-1.93.45-3.84 1.63-5.37C6.94 8.35 9.04 7.26 11.22 7.26c.42 0 .84.04 1.25.12V11.5c-.41-.08-.83-.12-1.25-.12-1.29 0-2.54.64-3.32 1.69-.71.93-.98 2.08-.98 3.23 0 1.15.27 2.3.98 3.23.78 1.05 2.03 1.69 3.32 1.69 1.29 0 2.54-.64 3.32-1.69.71-.93.98-2.08.98-3.23V0l.02.02z"/></svg></button>
                                        <button onclick="copyMatchLink('<?= $mUrl ?>')" style="background: none; border: none; padding: 0; color: #6366f1; cursor: pointer;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php if(count($groupMatches) > $limitInitial): ?>
            <div id="loadMoreContainer" style="padding: 15px; text-align: center; border-top: 1px solid rgba(255,255,255,0.05);">
                <button onclick="loadMoreMatches()" class="btn btn-outline" style="padding: 8px 25px; border-radius: 30px; font-size: 12px; font-weight: 700;">LIHAT HASIL LAINNYA</button>
            </div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>

    <!-- TAB BAGAN KNOCKOUT -->
    <div id="tab-bagan" class="tab-content" style="display: none;">
      <?php
      $stmt_b = $pdo->prepare("SELECT b.*, p1.name AS p1_name, p1p.name AS p1p_name, p2.name AS p2_name, p2p.name AS p2p_name
                             FROM tournament_brackets b 
                             LEFT JOIN players p1 ON p1.id = b.player1_id 
                             LEFT JOIN players p1p ON p1p.id = b.p1_partner_id
                             LEFT JOIN players p2 ON p2.id = b.player2_id 
                             LEFT JOIN players p2p ON p2p.id = b.p2_partner_id
                             WHERE b.season_id = ? 
                             ORDER BY FIELD(round_name, 'Round of 16', 'Quarterfinal', 'Semifinal', 'Final'), position_index");
      $stmt_b->execute([$season['id']]);
      $brackets = $stmt_b->fetchAll();
      $rounds = [];
      foreach($brackets as $b) { $rounds[$b['round_name']][] = $b; }

      if(!empty($brackets)): ?>
        <div style="background: var(--color-bg-light); padding: 15px 25px; border-radius: 12px 12px 0 0; border: 1px solid rgba(255,255,255,0.05); border-bottom: none; margin-top: 20px;">
          <h2 style="margin: 0; font-size: 18px; letter-spacing: 1px; color: var(--color-primary);">🏆 BAGAN KNOCKOUT</h2>
        </div>
        <div class="card" style="padding: 40px 20px; border-radius: 0 0 12px 12px; border: 1px solid rgba(255,255,255,0.05); overflow-x: auto;">
          <div class="bracket-container" style="min-width: 800px;">
            <?php foreach ($rounds as $roundName => $roundBrackets): ?>
              <div class="bracket-round">
                <h3 class="round-title"><?= strtoupper(e($roundName)) ?></h3>
                <div class="bracket-matches">
                  <?php foreach($roundBrackets as $b): ?>
                  <div class="bracket-match-item <?= ($roundName === 'Final') ? 'final' : '' ?>">
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
              <?php if($roundName !== 'Final'): ?><div class="bracket-connector"></div><?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>
    </div>

<script>
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(c => c.style.display = 'none');
    document.getElementById(tabId).style.display = 'block';
    
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
        row.style.display = show ? 'table-row' : 'none';
    });

    if (q1 !== "" || q2 !== "") {
        if(loadMoreBtn) loadMoreBtn.style.display = 'none';
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
  .player-slot.winner .name { color: var(--color-primary); font-weight: 700; }
  .player-slot.winner .score { color: var(--color-primary); font-weight: 900; }
  
  .name-box { flex: 1; display: flex; flex-direction: column; }
  .name { font-size: 14px; color: white; font-weight: 700; }
  .partner-name { font-size: 14px; color: var(--color-text-muted); font-weight: 700; }
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
