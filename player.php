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

// Get current rank in latest season
$allStandings = $season ? calculateStandings($pdo, $season['id']) : [];
$currentRank = '-';
foreach ($allStandings as $i => $row) {
    if ($row['p1_id'] == $id || (isset($row['p2_id']) && $row['p2_id'] == $id)) {
        $currentRank = $i + 1;
        break;
    }
}

// Get Global Rank
$stmt_g = $pdo->query("
    SELECT p.id,
        ((SELECT COUNT(*) FROM matches m WHERE ((m.winner_id = m.player1_id AND (m.player1_id = p.id OR m.p1_partner_id = p.id)) OR (m.winner_id = m.player2_id AND (m.player2_id = p.id OR m.p2_partner_id = p.id))) AND m.status = 'completed') * 2) - 
        ((SELECT COUNT(*) FROM matches m WHERE ((m.winner_id = m.player2_id AND (m.player1_id = p.id OR m.p1_partner_id = p.id)) OR (m.winner_id = m.player1_id AND (m.player2_id = p.id OR m.p2_partner_id = p.id))) AND m.status = 'completed') * 1) as points
    FROM players p
");
$allGlobal = $stmt_g->fetchAll();
usort($allGlobal, function($a, $b) { return $b['points'] <=> $a['points']; });
$globalRank = '-';
foreach($allGlobal as $i => $gr) {
    if($gr['id'] == $id) { $globalRank = $i + 1; break; }
}

// Get comprehensive match history including as partner
$stmt = $pdo->prepare("SELECT m.*, s.category AS season_category, s.name AS season_name,
                       p1.name AS p1_name, p2.name AS p2_name,
                       pa1.name AS pa1_name, pa2.name AS pa2_name
                       FROM matches m
                       JOIN seasons s ON s.id = m.season_id
                       JOIN players p1 ON p1.id = m.player1_id
                       JOIN players p2 ON p2.id = m.player2_id
                       LEFT JOIN players pa1 ON pa1.id = m.p1_partner_id
                       LEFT JOIN players pa2 ON pa2.id = m.p2_partner_id
                       WHERE (m.player1_id = ? OR m.player2_id = ? OR m.p1_partner_id = ? OR m.p2_partner_id = ?)
                       AND m.status = 'completed'
                       ORDER BY m.id DESC");
$stmt->execute([$id, $id, $id, $id]);
$allMatches = $stmt->fetchAll();

// Calculate Career Stats
$stats = [
    'single' => ['main' => 0, 'w' => 0, 'l' => 0],
    'double' => ['main' => 0, 'w' => 0, 'l' => 0],
];

foreach ($allMatches as $m) {
    $cat = ($m['season_category'] === 'double') ? 'double' : 'single';
    $stats[$cat]['main']++;
    
    // Check if win
    $isP1Side = ($m['player1_id'] == $id || $m['p1_partner_id'] == $id);
    $isWinner = ($m['winner_id'] == ($isP1Side ? $m['player1_id'] : $m['player2_id']));
    
    if ($isWinner) $stats[$cat]['w']++;
    else $stats[$cat]['l']++;
}

$totalMain = $stats['single']['main'] + $stats['double']['main'];
$totalW = $stats['single']['w'] + $stats['double']['w'];
$winRate = $totalMain > 0 ? round(($totalW / $totalMain) * 100) : 0;
?>

<div style="padding-top: 100px;"></div>

<section class="player-profile-hero" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); position: relative; overflow: hidden; padding: 60px 0 120px;">
    <!-- Abstract Background Elements -->
    <div style="position: absolute; top: -100px; right: -100px; width: 400px; height: 400px; background: var(--color-primary); opacity: 0.05; border-radius: 50%; filter: blur(80px);"></div>
    <div style="position: absolute; bottom: -50px; left: -50px; width: 300px; height: 300px; background: #3b82f6; opacity: 0.05; border-radius: 50%; filter: blur(60px);"></div>

    <div class="container">
        <div class="profile-header" style="display: flex; align-items: center; gap: 40px; flex-wrap: wrap;">
            <div class="profile-photo-wrap" style="position: relative; width: 180px; height: 180px;">
                <div style="position: absolute; inset: -10px; border: 2px solid rgba(250, 204, 21, 0.2); border-radius: 50%; animation: pulse 3s infinite;"></div>
                <?php if($player['photo']): ?>
                    <img src="<?= base_url('assets/uploads/players/' . $player['photo']) ?>" alt="<?= e($player['name']) ?>" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; border: 4px solid var(--color-primary); box-shadow: 0 10px 40px rgba(0,0,0,0.5); position: relative; z-index: 2;">
                <?php else: ?>
                    <div style="width: 100%; height: 100%; border-radius: 50%; background: #2d3748; display: flex; align-items: center; justify-content: center; font-size: 60px; border: 4px solid var(--color-primary); position: relative; z-index: 2;">👤</div>
                <?php endif; ?>
                <div style="position: absolute; bottom: 5px; right: 5px; background: var(--color-primary); color: #000; font-weight: 900; padding: 6px 12px; border-radius: 20px; font-size: 11px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); z-index: 3;">SEASON #<?= $currentRank ?></div>
                <div style="position: absolute; top: 5px; left: 5px; background: #3b82f6; color: #fff; font-weight: 900; padding: 6px 12px; border-radius: 20px; font-size: 11px; box-shadow: 0 4px 10px rgba(0,0,0,0.3); z-index: 3;">GLOBAL #<?= $globalRank ?></div>
            </div>

            <div class="profile-info" style="flex: 1; min-width: 300px;">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                    <h1 style="font-size: 42px; margin: 0; font-family: 'Oswald', sans-serif; letter-spacing: 1px;"><?= strtoupper(e($player['name'])) ?></h1>
                    <?php if($winRate >= 70): ?>
                        <span style="background: rgba(250, 204, 21, 0.1); color: var(--color-primary); padding: 4px 10px; border-radius: 4px; font-size: 10px; font-weight: 900; border: 1px solid rgba(250, 204, 21, 0.2);">ELITE PLAYER</span>
                    <?php endif; ?>
                </div>
                <p style="color: var(--color-text-muted); font-size: 16px; margin-bottom: 25px;">Professional Player of FullSatu E-Sport League</p>
                
                <div style="display: flex; gap: 40px;">
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-size: 11px; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 2px;">Win Rate</span>
                        <span style="font-size: 28px; font-weight: 900; color: var(--color-primary);"><?= $winRate ?>%</span>
                    </div>
                    <div style="display: flex; flex-direction: column;">
                        <span style="font-size: 11px; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 2px;">Total Matches</span>
                        <span style="font-size: 28px; font-weight: 900; color: white;"><?= $totalMain ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<div class="container" style="margin-top: -50px; position: relative; z-index: 10;">
    <!-- Stats Grid -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 50px;">
        <!-- Single Stats -->
        <div class="card" style="padding: 25px; border: 1px solid var(--color-border); background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(10px);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px;">
                <h3 style="margin: 0; font-size: 16px; letter-spacing: 1px; color: var(--color-primary);">SINGLE STATS</h3>
                <span style="font-size: 12px; color: var(--color-text-muted);"><?= $stats['single']['main'] ?> Matches</span>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="background: rgba(34, 197, 94, 0.05); padding: 15px; border-radius: 8px; border: 1px solid rgba(34, 197, 94, 0.1); text-align: center;">
                    <div style="font-size: 10px; color: #4ade80; margin-bottom: 5px; font-weight: 700;">WIN</div>
                    <div style="font-size: 24px; font-weight: 900; color: white;"><?= $stats['single']['w'] ?></div>
                </div>
                <div style="background: rgba(239, 68, 68, 0.05); padding: 15px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.1); text-align: center;">
                    <div style="font-size: 10px; color: #f87171; margin-bottom: 5px; font-weight: 700;">LOSE</div>
                    <div style="font-size: 24px; font-weight: 900; color: white;"><?= $stats['single']['l'] ?></div>
                </div>
            </div>
        </div>

        <!-- Double Stats -->
        <div class="card" style="padding: 25px; border: 1px solid var(--color-border); background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(10px);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 15px;">
                <h3 style="margin: 0; font-size: 16px; letter-spacing: 1px; color: var(--color-primary);">DOUBLE STATS</h3>
                <span style="font-size: 12px; color: var(--color-text-muted);"><?= $stats['double']['main'] ?> Matches</span>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="background: rgba(34, 197, 94, 0.05); padding: 15px; border-radius: 8px; border: 1px solid rgba(34, 197, 94, 0.1); text-align: center;">
                    <div style="font-size: 10px; color: #4ade80; margin-bottom: 5px; font-weight: 700;">WIN</div>
                    <div style="font-size: 24px; font-weight: 900; color: white;"><?= $stats['double']['w'] ?></div>
                </div>
                <div style="background: rgba(239, 68, 68, 0.05); padding: 15px; border-radius: 8px; border: 1px solid rgba(239, 68, 68, 0.1); text-align: center;">
                    <div style="font-size: 10px; color: #f87171; margin-bottom: 5px; font-weight: 700;">LOSE</div>
                    <div style="font-size: 24px; font-weight: 900; color: white;"><?= $stats['double']['l'] ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Match History -->
    <div class="history-section" style="margin-bottom: 80px;">
        <h2 style="margin-bottom: 30px; font-family: 'Oswald', sans-serif; letter-spacing: 2px;">
            <span style="color: var(--color-primary);">MATCH</span> HISTORY
        </h2>

        <div class="table-wrap">
            <table style="width: 100%; border-collapse: separate; border-spacing: 0 10px;">
                <thead>
                    <tr style="background: transparent;">
                        <th style="padding: 15px; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Result</th>
                        <th style="padding: 15px; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Tournament & Match</th>
                        <th style="padding: 15px; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;" class="num">Score</th>
                        <th style="padding: 15px; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($allMatches as $m): 
                        $isP1Side = ($m['player1_id'] == $id || $m['p1_partner_id'] == $id);
                        $isWinner = ($m['winner_id'] == ($isP1Side ? $m['player1_id'] : $m['player2_id']));
                        
                        $myTeam = e($m['p1_name']) . ($m['pa1_name'] ? ' & ' . e($m['pa1_name']) : '');
                        $opponentTeam = e($m['p2_name']) . ($m['pa2_name'] ? ' & ' . e($m['pa2_name']) : '');
                        
                        if (!$isP1Side) {
                            $temp = $myTeam;
                            $myTeam = $opponentTeam;
                            $opponentTeam = $temp;
                        }
                    ?>
                        <tr class="match-row-item">
                            <td style="padding: 15px; background: var(--color-bg-light); border-radius: 12px 0 0 12px; border: 1px solid var(--color-border); border-right: none;">
                                <span style="background: <?= $isWinner ? 'rgba(34, 197, 94, 0.1)' : 'rgba(239, 68, 68, 0.1)' ?>; color: <?= $isWinner ? '#4ade80' : '#f87171' ?>; padding: 4px 12px; border-radius: 4px; font-weight: 900; font-size: 11px; border: 1px solid <?= $isWinner ? 'rgba(34, 197, 94, 0.2)' : 'rgba(239, 68, 68, 0.2)' ?>;">
                                    <?= $isWinner ? 'WIN' : 'LOSE' ?>
                                </span>
                            </td>
                            <td style="padding: 15px; background: var(--color-bg-light); border: 1px solid var(--color-border); border-left: none; border-right: none;">
                                <div style="font-size: 11px; color: var(--color-text-muted); margin-bottom: 5px; text-transform: uppercase; letter-spacing: 1px;">
                                    <?= e($m['season_name']) ?> <span style="color: rgba(255,255,255,0.1);">|</span> <?= strtoupper($m['season_category']) ?>
                                </div>
                                <div style="display: flex; align-items: center; gap: 10px; font-weight: 700;">
                                    <span style="color: <?= $isWinner ? 'white' : 'var(--color-text-muted)' ?>;"><?= $myTeam ?></span>
                                    <span style="color: rgba(255,255,255,0.1); font-size: 10px;">VS</span>
                                    <span style="color: <?= !$isWinner ? 'white' : 'var(--color-text-muted)' ?>;"><?= $opponentTeam ?></span>
                                </div>
                            </td>
                            <td style="padding: 15px; background: var(--color-bg-light); border: 1px solid var(--color-border); border-left: none; border-right: none;" class="num">
                                <div style="background: rgba(0,0,0,0.3); padding: 5px 12px; border-radius: 6px; display: inline-block; font-weight: 900; letter-spacing: 1px;">
                                    <span style="color: <?= $isWinner ? 'var(--color-primary)' : 'white' ?>;"><?= $isP1Side ? $m['player1_score'] : $m['player2_score'] ?></span>
                                    <span style="color: rgba(255,255,255,0.2);">:</span>
                                    <span style="color: <?= !$isWinner ? 'var(--color-primary)' : 'white' ?>;"><?= !$isP1Side ? $m['player1_score'] : $m['player2_score'] ?></span>
                                </div>
                            </td>
                            <td style="padding: 15px; background: var(--color-bg-light); border-radius: 0 12px 12px 0; border: 1px solid var(--color-border); border-left: none; text-align: right;">
                                <a href="match_detail?id=<?= $m['id'] ?>" class="btn-quick-view" style="width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05); border-radius: 50%; color: var(--color-primary); transition: all 0.3s;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
@keyframes pulse {
    0% { transform: scale(1); opacity: 0.2; }
    50% { transform: scale(1.05); opacity: 0.4; }
    100% { transform: scale(1); opacity: 0.2; }
}

.match-row-item:hover .btn-quick-view {
    background: var(--color-primary) !important;
    color: #000 !important;
    transform: translateX(3px);
}

.match-row-item:hover td {
    background: rgba(255,255,255,0.02) !important;
}

@media (max-width: 768px) {
    .profile-header { justify-content: center; text-align: center; }
    .profile-info { flex: none; width: 100%; }
    .profile-info div { justify-content: center; }
}
</style>

<?php include 'includes/footer.php'; ?>
