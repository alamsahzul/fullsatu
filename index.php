<?php
require 'config/db.php';
require 'includes/functions.php';
$season = getCurrentLigaSeason($pdo);
$pageTitle = 'FullSatu Single Man League';
include 'includes/header.php';

// Get top 5 standings for the real-time section
$topStandings = [];
$nextMatch = null;
$randomMatch = null;

if ($season) {
    $allStandings = calculateStandings($pdo, $season['id']);
    $topStandings = array_slice($allStandings, 0, 5);
    
    // Get next scheduled match
    $stmt = $pdo->prepare("SELECT m.*, p1.name AS p1_name, p2.name AS p2_name, p1.photo AS p1_photo, p2.photo AS p2_photo 
                           FROM matches m 
                           JOIN players p1 ON p1.id=m.player1_id 
                           JOIN players p2 ON p2.id=m.player2_id 
                           WHERE m.season_id=? AND m.status='scheduled' 
                           ORDER BY m.round_number, m.id LIMIT 1");
    $stmt->execute([$season['id']]);
    $nextMatch = $stmt->fetch();
    
    // Get a random completed match for "Pertandingan Seru"
    $stmt = $pdo->prepare("SELECT m.*, p1.name AS p1_name, p2.name AS p2_name, p1.photo AS p1_photo, p2.photo AS p2_photo 
                           FROM matches m 
                           JOIN players p1 ON p1.id=m.player1_id 
                           JOIN players p2 ON p2.id=m.player2_id 
                           WHERE m.season_id=? AND m.status='completed' 
                           ORDER BY RAND() LIMIT 1");
    $stmt->execute([$season['id']]);
    $randomMatch = $stmt->fetch();
}

?>

<!-- HERO SECTION -->
<section class="hero-section">
  <div class="hero-content">
    <h1>FULLSATU</h1>
    <h2>SINGLE MAN LEAGUE</h2>
    <p>Liga pickleball single player pertama yang mengutamakan sportivitas, persaingan sehat, dan keseruan di setiap match.</p>
    <div class="hero-buttons">
      <a href="<?= base_url('liga') ?>" class="btn btn-primary"><span class="icon-trophy">🏆</span> KLASEMEN LIGA</a>
      <a href="<?= base_url('knockout') ?>" class="btn btn-outline"><span class="icon-calendar">📅</span> BAGAN KNOCKOUT</a>
    </div>
  </div>
  <div class="hero-image">
    <!-- Hero image is managed via CSS or img tag, let's use a large background or img -->
    <img src="<?= base_url('assets/img/logo.png') ?>" alt="Fullsatu Shield" class="hero-shield">
  </div>
</section>

<!-- FEATURES BANNER -->
<section class="features-banner">
  <div class="feature-item">
    <div class="feature-icon">👥</div>
    <h3>1</h3>
    <h4>SINGLE LEAGUE</h4>
    <p>Setiap pemain berjuang untuk jadi yang terbaik</p>
  </div>
  <div class="feature-item">
    <div class="feature-icon">🏅</div>
    <h3>100%</h3>
    <h4>SPORTIVITAS</h4>
    <p>Fair play adalah prioritas utama</p>
  </div>
  <div class="feature-item">
    <div class="feature-icon">📈</div>
    <h3>RANKING</h3>
    <h4>REAL-TIME</h4>
    <p>Klasemen selalu update setiap selesai match</p>
  </div>
  <div class="feature-item">
    <div class="feature-icon">🏆</div>
    <h3>CHAMPION</h3>
    <h4>ONLY ONE</h4>
    <p>Hanya 1 juara di setiap musim liga</p>
  </div>
</section>

<!-- ABOUT SECTION -->
<section class="about-section">
  <div class="about-text">
    <h2>APA ITU<br><span>FULLSATU?</span></h2>
    <p>FullSatu Single Man League adalah liga pickleball tunggal putra (single) yang menggunakan sistem ranking. Semua pemain akan bertanding, poin akan diakumulasikan, dan hanya yang konsisten yang akan menjadi juara.</p>
  </div>
  <div class="about-grid">
    <div class="about-card">
      <div class="about-icon">🎯</div>
      <div class="about-card-text">
        <h4>SISTEM RANKING</h4>
        <p>Poin dihitung berdasarkan hasil match. Menang = Naik Poin.</p>
      </div>
    </div>
    <div class="about-card">
      <div class="about-icon">📅</div>
      <div class="about-card-text">
        <h4>MATCH TERJADWAL</h4>
        <p>Jadwal jelas, tertata, dan diumumkan sebelumnya.</p>
      </div>
    </div>
    <div class="about-card">
      <div class="about-icon">🛡️</div>
      <div class="about-card-text">
        <h4>ATURAN JELAS</h4>
        <p>Semua pemain mengikuti aturan yang sama. Fair & transparan.</p>
      </div>
    </div>
    <div class="about-card">
      <div class="about-icon">🤝</div>
      <div class="about-card-text">
        <h4>KOMUNITAS SOLID</h4>
        <p>Bangun koneksi, komongtsi, dan kebersamaan.</p>
      </div>
    </div>

  </div>
</section>

<!-- REALTIME SECTION -->
<section class="realtime-section">
  <div class="realtime-header">
    <h2>PANTAU LIGA SECARA <span>REAL-TIME</span></h2>
  </div>
  
  <div class="realtime-grid">
    <!-- KLASEMEN CARD -->
    <div class="realtime-card">
      <div class="realtime-table-wrap">
        <table>
          <thead>
            <tr>
              <th style="width: 40px;">#</th>
              <th>Player</th>
              <th class="num">M</th>
              <th class="num">M</th>
              <th class="num">K</th>
              <th class="num">Poin</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($topStandings)): ?>
              <tr><td colspan="6" style="text-align:center; padding: 30px;">Belum ada data</td></tr>
            <?php else: ?>
              <?php foreach ($topStandings as $i => $row): ?>
              <tr>
                <td style="color: var(--color-text-muted);"><?= $i + 1 ?></td>
                <td>
                  <a href="<?= base_url('player?id=' . $row['p1_id']) ?>" style="display: flex; align-items: center; gap: 10px; color: white;" onmouseover="this.style.color='var(--color-primary)'" onmouseout="this.style.color='white'">
                    <?php if($row['photo']): ?>
                      <img src="<?= base_url('assets/uploads/players/' . $row['photo']) ?>" alt="<?= e($row['name']) ?>" style="width: 30px; height: 30px; border-radius: 50%; object-fit: cover; border: 1px solid var(--color-border);">
                    <?php else: ?>
                      <img src="<?= base_url('assets/img/player_avatar.png') ?>" alt="Avatar" style="width: 30px; height: 30px; border-radius: 50%; opacity: 0.5;">
                    <?php endif; ?>
                    <span style="font-weight: 600;"><?= e($row['name']) ?></span>
                  </a>
                  <?php if(isset($row['p2_id']) && $row['p2_id']): ?>
                    <div style="font-size: 10px; color: var(--color-text-muted); margin-left: 40px; margin-top: -5px;">
                      & <a href="<?= base_url('player?id=' . $row['p2_id']) ?>" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='var(--color-primary)'" onmouseout="this.style.color='inherit'"><?= e($row['name2']) ?></a>
                    </div>
                  <?php endif; ?>
                </td>
                <td class="num"><?= $row['main'] ?></td>
                <td class="num"><?= $row['w'] ?></td>
                <td class="num"><?= $row['l'] ?></td>
                <td class="num"><strong style="color: var(--color-primary);"><?= $row['pts'] ?></strong></td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <div class="realtime-card-footer">
        <h4>KLASEMEN TERBARU</h4>
        <p>Update poin dan posisi pemain secara real-time.</p>
      </div>
    </div>

    <!-- NEXT MATCH CARD -->
    <div class="realtime-card match-card">
      <div class="match-date" style="text-align: center;">NEXT MATCH<br><span>LEG <?= e($nextMatch['leg_number'] ?? '-') ?></span></div>
      <div class="match-players">
        <a href="<?= $nextMatch ? base_url('player?id=' . $nextMatch['player1_id']) : '#' ?>" class="player" style="color: inherit;">
          <?php if($nextMatch && $nextMatch['p1_photo']): ?>
            <img src="<?= base_url('assets/uploads/players/' . $nextMatch['p1_photo']) ?>" alt="Player 1" class="player-img" style="object-fit: cover;">
          <?php else: ?>
            <img src="<?= base_url('assets/img/player_avatar.png') ?>" alt="Player 1" class="player-img">
          <?php endif; ?>
          <h5><?= e($nextMatch['p1_name'] ?? 'TBA') ?></h5>
          <span class="rank">RANK #<?= $nextMatch ? getPlayerRank($nextMatch['p1_name'], $allStandings) : '-' ?></span>
        </a>
        <div class="vs">Vs</div>
        <a href="<?= $nextMatch ? base_url('player?id=' . $nextMatch['player2_id']) : '#' ?>" class="player" style="color: inherit;">
          <?php if($nextMatch && $nextMatch['p2_photo']): ?>
            <img src="<?= base_url('assets/uploads/players/' . $nextMatch['p2_photo']) ?>" alt="Player 2" class="player-img" style="object-fit: cover;">
          <?php else: ?>
            <img src="<?= base_url('assets/img/player_avatar.png') ?>" alt="Player 2" class="player-img">
          <?php endif; ?>
          <h5><?= e($nextMatch['p2_name'] ?? 'TBA') ?></h5>
          <span class="rank">RANK #<?= $nextMatch ? getPlayerRank($nextMatch['p2_name'], $allStandings) : '-' ?></span>
        </a>
      </div>
      <div class="realtime-card-footer">
        <h4>JADWAL MATCH</h4>
        <p>Lihat jadwal pertandingan berikutnya dengan mudah.</p>
      </div>
    </div>

    <!-- ACTION CARD -->
    <div class="realtime-card action-card">
      <?php if($randomMatch): ?>
        <div class="match-date" style="text-align: center; padding-top: 30px;">RECENT RESULT<br><span>LEG <?= e($randomMatch['leg_number']) ?></span></div>
        <div class="match-players">
          <a href="<?= base_url('player?id=' . $randomMatch['player1_id']) ?>" class="player" style="color: inherit;">
            <?php if($randomMatch['p1_photo']): ?>
              <img src="<?= base_url('assets/uploads/players/' . $randomMatch['p1_photo']) ?>" alt="Player 1" class="player-img" style="object-fit: cover;">
            <?php else: ?>
              <img src="<?= base_url('assets/img/player_avatar.png') ?>" alt="Player 1" class="player-img">
            <?php endif; ?>
            <h5><?= e($randomMatch['p1_name']) ?></h5>
            <div style="font-size: 28px; font-weight: 900; color: var(--color-primary); margin-top: 5px;"><?= e($randomMatch['player1_score']) ?></div>
          </a>
          <div class="vs">Vs</div>
          <a href="<?= base_url('player?id=' . $randomMatch['player2_id']) ?>" class="player" style="color: inherit;">
            <?php if($randomMatch['p2_photo']): ?>
              <img src="<?= base_url('assets/uploads/players/' . $randomMatch['p2_photo']) ?>" alt="Player 2" class="player-img" style="object-fit: cover;">
            <?php else: ?>
              <img src="<?= base_url('assets/img/player_avatar.png') ?>" alt="Player 2" class="player-img">
            <?php endif; ?>
            <h5><?= e($randomMatch['p2_name']) ?></h5>
            <div style="font-size: 28px; font-weight: 900; color: var(--color-primary); margin-top: 5px;"><?= e($randomMatch['player2_score']) ?></div>
          </a>
        </div>
        <div class="realtime-card-footer">
          <h4>HIGHLIGHTS HASIL</h4>
          <p>Momen seru antara <?= e($randomMatch['p1_name']) ?> vs <?= e($randomMatch['p2_name']) ?>.</p>
        </div>
      <?php else: ?>
        <div class="action-img-wrap">
          <img src="<?= base_url('assets/img/pickleball_action.png') ?>" alt="Action Shot">
        </div>
        <div class="realtime-card-footer">
          <h4>PERTANDINGAN SERU</h4>
          <p>Tanding dengan semangat, menang dengan sportivitas.</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- CTA SECTION -->
<section class="cta-section" style="background-image: url('<?= base_url('assets/img/pickleball_cta_bg.png') ?>');">
  <div class="cta-overlay"></div>
  <div class="cta-content">
    <div class="cta-text">
      <h2>SIAP JADI YANG TERBAIK?<br><span>GABUNG SEKARANG!</span></h2>
      <p>Daftar dan buktikan kemampuanmu di FullSatu Single Man League!</p>
    </div>
    <div class="cta-action">
      <a href="https://wa.me/6285298963132" class="btn btn-primary" target="_blank"><span class="icon-user">👤</span> JOIN LEAGUE</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
