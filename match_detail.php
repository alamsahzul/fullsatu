<?php
require 'config/db.php';
require 'includes/functions.php';

$matchId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($matchId <= 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("SELECT m.*, 
    p1.name AS player1, p1.photo AS photo1,
    pa1.name AS partner1, pa1.photo AS photo1p,
    p2.name AS player2, p2.photo AS photo2,
    pa2.name AS partner2, pa2.photo AS photo2p,
    s.name AS season_name, s.format, s.category
    FROM matches m
    JOIN seasons s ON m.season_id = s.id
    JOIN players p1 ON m.player1_id = p1.id
    LEFT JOIN players pa1 ON m.p1_partner_id = pa1.id
    JOIN players p2 ON m.player2_id = p2.id
    LEFT JOIN players pa2 ON m.p2_partner_id = pa2.id
    WHERE m.id = ?");
$stmt->execute([$matchId]);
$match = $stmt->fetch();

if (!$match) {
    header('Location: index.php');
    exit;
}

$pageTitle = 'Detail Pertandingan - ' . e($match['player1']) . ' vs ' . e($match['player2']);

// Dynamic Meta Tags for Sharing
$ogTitle = "Match: " . $match['player1'] . " vs " . $match['player2'];
$ogDescription = !empty($match['match_notes']) ? mb_strimwidth(strip_tags($match['match_notes']), 0, 100, "...") : "Lihat detail skor dan dokumentasi pertandingan FullSatu.";
$ogImage = $match['match_photo'] ? base_url('assets/uploads/matches/'.$match['match_photo']) : base_url('assets/uploads/players/'.$match['photo1']);

include 'includes/header.php';
?>

<div style="padding-top: 100px;"></div>

<div class="container">
    <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
        <a href="javascript:history.back()" class="btn btn-outline" style="padding: 8px 20px; border-radius: 30px; font-size: 14px; display: inline-flex; align-items: center; gap: 8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"></line><polyline points="12 19 5 12 12 5"></polyline></svg>
            Kembali
        </a>

        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <?php
            $currentUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $shareText = "🏆 HASIL MATCH 🏆\n\n" . $match['player1'] . " vs " . $match['player2'] . "\nSkor: " . ($match['player1_score'] ?? 0) . " - " . ($match['player2_score'] ?? 0) . "\n\nCek di sini:\n" . $currentUrl;
            
            $waLink = "https://wa.me/?text=" . urlencode($shareText);
            $fbLink = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($currentUrl);
            ?>
            
            <!-- WhatsApp -->
            <a href="<?= $waLink ?>" target="_blank" class="btn-share" title="Bagikan ke WhatsApp" style="background: #25D366; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 10px rgba(37, 211, 102, 0.3);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L0 24l6.335-1.662c1.72.937 3.659 1.432 5.631 1.433h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
            </a>

            <!-- Facebook -->
            <a href="<?= $fbLink ?>" target="_blank" class="btn-share" title="Bagikan ke Facebook" style="background: #1877F2; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; text-decoration: none; box-shadow: 0 4px 10px rgba(24, 119, 242, 0.3);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.469h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.469h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>

            <!-- TikTok (Copies Link) -->
            <button onclick="copyMatchLink()" class="btn-share" title="Bagikan ke TikTok" style="background: #000000; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); position: relative; overflow: hidden;">
                <div style="position: absolute; width: 100%; height: 100%; background: linear-gradient(45deg, #ff0050 0%, #00f2ea 100%); opacity: 0.15;"></div>
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" style="position: relative; z-index: 1;"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.17-2.89-.6-4.09-1.47V15.5c0 1.93-.45 3.84-1.63 5.37-1.31 1.78-3.41 2.87-5.59 2.87-2.18 0-4.28-1.09-5.59-2.87C4.45 19.34 4 17.43 4 15.5c0-1.93.45-3.84 1.63-5.37C6.94 8.35 9.04 7.26 11.22 7.26c.42 0 .84.04 1.25.12V11.5c-.41-.08-.83-.12-1.25-.12-1.29 0-2.54.64-3.32 1.69-.71.93-.98 2.08-.98 3.23 0 1.15.27 2.3.98 3.23.78 1.05 2.03 1.69 3.32 1.69 1.29 0 2.54-.64 3.32-1.69.71-.93.98-2.08.98-3.23V0l.02.02z"/></svg>
            </button>

            <!-- Copy Link -->
            <button onclick="copyMatchLink()" class="btn-share" title="Salin Link" style="background: #6366f1; color: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; box-shadow: 0 4px 10px rgba(99, 102, 241, 0.3);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
            </button>
        </div>
    </div>

    <div class="card" style="padding: 0; overflow: hidden; border: 1px solid rgba(255,255,255,0.05); background: var(--color-bg-light);">
        <!-- MATCH HEADER / SCORE -->
        <div style="background: linear-gradient(135deg, rgba(34, 197, 94, 0.1) 0%, rgba(0,0,0,0.4) 100%); padding: 60px 40px; text-align: center; border-bottom: 1px solid var(--color-border); position: relative;">
            <div style="position: absolute; top: 20px; left: 50%; transform: translateX(-50%); font-size: 11px; letter-spacing: 3px; color: var(--color-primary); font-weight: 900; text-transform: uppercase;">
                <?= e($match['season_name']) ?> - <?= strtoupper(e($match['format'])) ?>
            </div>

            <div style="display: flex; align-items: center; justify-content: center; gap: 40px; flex-wrap: wrap;">
                <!-- TEAM 1 -->
                <div style="flex: 1; min-width: 200px;">
                    <div style="position: relative; display: inline-block; margin-bottom: 25px; width: 160px; height: 110px;">
                        <!-- Partner 1 Photo (Back) -->
                        <?php if($match['partner1']): ?>
                        <div style="position: absolute; right: 0; top: 5px; z-index: 1;">
                             <img src="<?= $match['photo1p'] ? base_url('assets/uploads/players/'.$match['photo1p']) : base_url('assets/img/player_avatar.png') ?>" style="width: 95px; height: 95px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,0.2); box-shadow: 0 10px 20px rgba(0,0,0,0.4);">
                        </div>
                        <?php endif; ?>

                        <!-- Player 1 Photo (Front) -->
                        <div style="position: absolute; left: 0; top: 0; z-index: 2;">
                             <img src="<?= $match['photo1'] ? base_url('assets/uploads/players/'.$match['photo1']) : base_url('assets/img/player_avatar.png') ?>" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid <?= $match['winner_id'] == $match['player1_id'] ? 'var(--color-primary)' : 'white' ?>; box-shadow: 0 10px 30px rgba(0,0,0,0.5); background: var(--color-bg-light);">
                        </div>

                        <?php if($match['winner_id'] == $match['player1_id']): ?>
                            <div style="position: absolute; bottom: 5px; left: 65px; background: var(--color-primary); color: #000; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; border: 3px solid #1a1a1a; z-index: 5;">🏆</div>
                        <?php endif; ?>
                    </div>
                    <h2 style="margin: 0; font-size: 24px; font-weight: 900; color: white;"><?= e($match['player1']) ?></h2>
                    <?php if($match['partner1']): ?>
                        <div style="color: var(--color-text-muted); font-size: 14px; font-weight: 600; margin-top: 5px;">& <?= e($match['partner1']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- SCORE BOARD -->
                <div style="display: flex; align-items: center; gap: 20px;">
                    <div style="font-size: 80px; font-weight: 900; color: <?= $match['winner_id'] == $match['player1_id'] ? 'var(--color-primary)' : 'white' ?>; line-height: 1; text-shadow: 0 5px 20px rgba(0,0,0,0.5);"><?= $match['player1_score'] ?? '-' ?></div>
                    <div style="font-size: 24px; font-weight: 900; color: var(--color-text-muted); opacity: 0.5;">VS</div>
                    <div style="font-size: 80px; font-weight: 900; color: <?= $match['winner_id'] == $match['player2_id'] ? 'var(--color-primary)' : 'white' ?>; line-height: 1; text-shadow: 0 5px 20px rgba(0,0,0,0.5);"><?= $match['player2_score'] ?? '-' ?></div>
                </div>

                <!-- TEAM 2 -->
                <div style="flex: 1; min-width: 200px;">
                    <div style="position: relative; display: inline-block; margin-bottom: 25px; width: 160px; height: 110px;">
                        <!-- Partner 2 Photo (Back) -->
                        <?php if($match['partner2']): ?>
                        <div style="position: absolute; left: 0; top: 5px; z-index: 1;">
                             <img src="<?= $match['photo2p'] ? base_url('assets/uploads/players/'.$match['photo2p']) : base_url('assets/img/player_avatar.png') ?>" style="width: 95px; height: 95px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,0.2); box-shadow: 0 10px 20px rgba(0,0,0,0.4);">
                        </div>
                        <?php endif; ?>

                        <!-- Player 2 Photo (Front) -->
                        <div style="position: absolute; right: 0; top: 0; z-index: 2;">
                             <img src="<?= $match['photo2'] ? base_url('assets/uploads/players/'.$match['photo2']) : base_url('assets/img/player_avatar.png') ?>" style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover; border: 4px solid <?= $match['winner_id'] == $match['player2_id'] ? 'var(--color-primary)' : 'white' ?>; box-shadow: 0 10px 30px rgba(0,0,0,0.5); background: var(--color-bg-light);">
                        </div>

                        <?php if($match['winner_id'] == $match['player2_id']): ?>
                            <div style="position: absolute; bottom: 5px; right: 65px; background: var(--color-primary); color: #000; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; border: 3px solid #1a1a1a; z-index: 5;">🏆</div>
                        <?php endif; ?>
                    </div>
                    <h2 style="margin: 0; font-size: 24px; font-weight: 900; color: white;"><?= e($match['player2']) ?></h2>
                    <?php if($match['partner2']): ?>
                        <div style="color: var(--color-text-muted); font-size: 14px; font-weight: 600; margin-top: 5px;">& <?= e($match['partner2']) ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <div style="margin-top: 40px; color: var(--color-text-muted); font-size: 13px; letter-spacing: 1px;">
                DIMAINKAN PADA: <span style="color: white; font-weight: 700;"><?= date('d F Y', strtotime($match['created_at'])) ?></span>
            </div>
        </div>

        <!-- MATCH DOCUMENTATION -->
        <div style="padding: 50px 40px;">
            <div style="max-width: 800px; margin: 0 auto;">
                
                <?php if($match['match_photo']): ?>
                    <div style="margin-bottom: 40px; border-radius: 20px; overflow: hidden; box-shadow: 0 20px 50px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.05);">
                        <img src="<?= base_url('assets/uploads/matches/'.$match['match_photo']) ?>" style="width: 100%; height: auto; display: block;" alt="Dokumentasi Pertandingan">
                    </div>
                <?php endif; ?>

                <div style="background: rgba(255,255,255,0.02); padding: 40px; border-radius: 20px; border-left: 5px solid var(--color-primary);">
                    <h3 style="margin-top: 0; margin-bottom: 20px; font-size: 14px; letter-spacing: 2px; color: var(--color-primary); text-transform: uppercase;">Ringkasan Pertandingan</h3>
                    <div style="color: #cbd5e1; font-size: 18px; line-height: 1.8; font-family: 'Inter', sans-serif;">
                        <?= nl2br(e(trim($match['match_notes']))) ?: 'Belum ada ringkasan untuk pertandingan ini.' ?>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
function copyMatchLink() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        alert('Link pertandingan berhasil disalin ke clipboard!');
    }).catch(err => {
        console.error('Gagal menyalin link: ', err);
    });
}
</script>

<?php include 'includes/footer.php'; ?>
