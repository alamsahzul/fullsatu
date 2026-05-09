<?php
require 'config/db.php';
require 'includes/functions.php';

$pageTitle = "Global Player Ranking - FullSatu";
include 'includes/header.php';

// Calculate Global Rankings
// Points: Win +2, Loss -1
$stmt = $pdo->query("
    SELECT p.*,
        (SELECT COUNT(*) FROM matches m 
         WHERE (m.player1_id = p.id OR m.player2_id = p.id OR m.p1_partner_id = p.id OR m.p2_partner_id = p.id) 
         AND m.status = 'completed') as total_matches,
        
        (SELECT COUNT(*) FROM matches m 
         WHERE ((m.winner_id = m.player1_id AND (m.player1_id = p.id OR m.p1_partner_id = p.id)) OR
                (m.winner_id = m.player2_id AND (m.player2_id = p.id OR m.p2_partner_id = p.id)))
         AND m.status = 'completed') as wins,
         
        (SELECT COUNT(*) FROM matches m 
         WHERE ((m.winner_id = m.player2_id AND (m.player1_id = p.id OR m.p1_partner_id = p.id)) OR
                (m.winner_id = m.player1_id AND (m.player2_id = p.id OR m.p2_partner_id = p.id)))
         AND m.status = 'completed') as losses
    FROM players p
    ORDER BY p.id ASC
");
$players = $stmt->fetchAll();

// Calculate points and win rate for each player
foreach ($players as &$p) {
    $p['points'] = ($p['wins'] * 2) - ($p['losses'] * 1);
    $p['win_rate'] = $p['total_matches'] > 0 ? round(($p['wins'] / $p['total_matches']) * 100) : 0;
}

// Sort by points descending, then win rate, then wins
usort($players, function($a, $b) {
    if ($a['points'] !== $b['points']) return $b['points'] <=> $a['points'];
    if ($a['win_rate'] !== $b['win_rate']) return $b['win_rate'] <=> $a['win_rate'];
    return $b['wins'] <=> $a['wins'];
});

// Take top players for the leaderboard
$topPlayers = array_slice($players, 0, 50); // Show top 50
?>

<div style="padding-top: 100px;"></div>

<section class="rank-hero" style="background: linear-gradient(135deg, #064e3b 0%, #022c22 100%); padding: 80px 0; text-align: center; border-bottom: 1px solid rgba(255,255,255,0.05); position: relative; overflow: hidden;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: url('assets/img/pattern.png'); opacity: 0.1;"></div>
    <div class="container" style="position: relative; z-index: 2;">
        <div style="display: inline-block; background: rgba(250, 204, 21, 0.1); color: var(--color-primary); padding: 6px 20px; border-radius: 30px; font-size: 12px; font-weight: 900; letter-spacing: 3px; margin-bottom: 20px; border: 1px solid rgba(250, 204, 21, 0.2);">OFFICIAL LEADERBOARD</div>
        <h1 style="font-size: 56px; font-family: 'Oswald', sans-serif; letter-spacing: 2px; margin-bottom: 10px;">GLOBAL <span style="color: var(--color-primary);">RANKING</span></h1>
        <p style="color: rgba(255,255,255,0.7); max-width: 600px; margin: 0 auto; font-size: 16px;">Sistem peringkat prestisius berdasarkan performa sejati. Menang memberikan 2 poin, sementara kekalahan memberikan penalti -1 poin.</p>
    </div>
</section>

<div class="container" style="margin-top: -40px; position: relative; z-index: 5;">
    <div class="card" style="padding: 0; border: 1px solid rgba(255,255,255,0.05); background: rgba(15, 23, 42, 0.9); backdrop-filter: blur(20px); border-radius: 16px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);">
        <div class="table-wrap" style="border: none; border-radius: 0;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: rgba(255,255,255,0.02);">
                        <th style="padding: 20px; text-align: center; width: 80px; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Pos</th>
                        <th style="padding: 20px; text-align: left; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Player</th>
                        <th style="padding: 20px; text-align: center; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Played</th>
                        <th style="padding: 20px; text-align: center; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">W - L</th>
                        <th style="padding: 20px; text-align: center; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Win Rate</th>
                        <th style="padding: 20px; text-align: center; color: var(--color-text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Total Points</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($topPlayers as $i => $p): 
                        $isTop3 = ($i < 3);
                        $badgeColor = '#94a3b8';
                        if ($i == 0) $badgeColor = '#fbbf24'; // Gold
                        if ($i == 1) $badgeColor = '#e2e8f0'; // Silver
                        if ($i == 2) $badgeColor = '#cd7f32'; // Bronze
                    ?>
                        <tr style="border-bottom: 1px solid rgba(255,255,255,0.03); transition: all 0.3s;" class="rank-row">
                            <td style="padding: 20px; text-align: center;">
                                <?php if($isTop3): ?>
                                    <div style="width: 35px; height: 35px; background: <?= $badgeColor ?>; color: #000; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto; font-weight: 900; font-size: 18px; box-shadow: 0 0 15px <?= $badgeColor ?>44;">
                                        <?= $i + 1 ?>
                                    </div>
                                <?php else: ?>
                                    <span style="font-weight: 800; color: var(--color-text-muted); font-size: 16px;"><?= $i + 1 ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 20px;">
                                <a href="player?id=<?= $p['id'] ?>" style="display: flex; align-items: center; gap: 15px; text-decoration: none; color: inherit;" onmouseover="this.querySelector('.p-name').style.color='var(--color-primary)'" onmouseout="this.querySelector('.p-name').style.color='white'">
                                    <div style="position: relative; flex-shrink: 0;">
                                        <?php if($p['photo']): ?>
                                            <img src="<?= base_url('assets/uploads/players/' . $p['photo']) ?>" style="width: 45px; height: 45px; border-radius: 50%; object-fit: cover; border: 2px solid <?= $isTop3 ? $badgeColor : 'rgba(255,255,255,0.1)' ?>;">
                                        <?php else: ?>
                                            <div style="width: 45px; height: 45px; border-radius: 50%; background: #2d3748; display: flex; align-items: center; justify-content: center; font-size: 20px; border: 2px solid rgba(255,255,255,0.1);">👤</div>
                                        <?php endif; ?>
                                        <?php if($i == 0): ?>
                                            <div style="position: absolute; -10px; left: 50%; transform: translateX(-50%); font-size: 14px;">👑</div>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div class="p-name" style="font-weight: 800; font-size: 16px; color: white; transition: color 0.3s;"><?= strtoupper(e($p['name'])) ?></div>
                                        <div style="font-size: 10px; color: var(--color-text-muted); letter-spacing: 1px; text-transform: uppercase;">Professional Player</div>
                                    </div>
                                </a>
                            </td>
                            <td style="padding: 20px; text-align: center; font-weight: 700; color: #cbd5e1;"><?= $p['total_matches'] ?></td>
                            <td style="padding: 20px; text-align: center;">
                                <div style="display: flex; justify-content: center; align-items: center; gap: 8px;">
                                    <span style="color: #4ade80; font-weight: 800;"><?= $p['wins'] ?></span>
                                    <span style="color: rgba(255,255,255,0.1);">/</span>
                                    <span style="color: #f87171; font-weight: 800;"><?= $p['losses'] ?></span>
                                </div>
                            </td>
                            <td style="padding: 20px; text-align: center;">
                                <div style="width: 80px; height: 8px; background: rgba(255,255,255,0.05); border-radius: 4px; margin: 0 auto 5px; overflow: hidden;">
                                    <div style="width: <?= $p['win_rate'] ?>%; height: 100%; background: <?= $p['win_rate'] >= 50 ? 'var(--color-primary)' : '#ef4444' ?>;"></div>
                                </div>
                                <span style="font-size: 12px; font-weight: 800; color: <?= $p['win_rate'] >= 50 ? 'var(--color-primary)' : '#f87171' ?>;"><?= $p['win_rate'] ?>%</span>
                            </td>
                            <td style="padding: 20px; text-align: center;">
                                <span style="font-size: 24px; font-weight: 900; color: <?= $p['points'] >= 0 ? 'white' : '#f87171' ?>;"><?= ($p['points'] > 0 ? '+' : '') . $p['points'] ?></span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if(empty($topPlayers)): ?>
                        <tr><td colspan="6" style="padding: 100px; text-align: center; color: var(--color-text-muted);">Belum ada data ranking tersedia.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
.rank-row:hover {
    background: rgba(255, 255, 255, 0.02);
}
</style>

<?php include 'includes/footer.php'; ?>
