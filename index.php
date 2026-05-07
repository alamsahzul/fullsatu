<?php
require 'config/db.php';
require 'includes/functions.php';
$season = getCurrentSeason($pdo);
$pageTitle = 'FullSatu Single Man League';
include 'includes/header.php';

// Get top 5 standings for the real-time section
$topStandings = [];
if ($season) {
    $allStandings = calculateStandings($pdo, $season['id']);
    $topStandings = array_slice($allStandings, 0, 5);
}
?>

<!-- HERO SECTION -->
<section class="hero-section">
  <div class="hero-content">
    <h1>FULLSATU</h1>
    <h2>SINGLE MAN LEAGUE</h2>
    <p>Liga pickleball single player pertama yang mengutamakan sportivitas, persaingan sehat, dan keseruan di setiap match.</p>
    <div class="hero-buttons">
      <a href="<?= base_url('standings.php') ?>" class="btn btn-primary"><span class="icon-trophy">🏆</span> LIHAT KLASEMEN</a>
      <a href="<?= base_url('matches.php') ?>" class="btn btn-outline"><span class="icon-calendar">📅</span> JADWAL MATCH</a>
    </div>
  </div>
  <div class="hero-image">
    <!-- Hero image is managed via CSS or img tag, let's use a large background or img -->
    <img src="<?= base_url('assets/img/hero_logo.png') ?>" alt="Fullsatu Shield" class="hero-shield">
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
              <th>#</th>
              <th>Player</th>
              <th>Matches</th>
              <th>W</th>
              <th>L</th>
              <th>Points</th>
            </tr>
          </thead>
          <tbody>
            <?php if(empty($topStandings)): ?>
              <tr><td colspan="6" style="text-align:center;">Belum ada data</td></tr>
            <?php else: ?>
              <?php foreach ($topStandings as $i => $row): ?>
              <tr>
                <td><?= $i + 1 ?></td>
                <td>
                  <div style="display: flex; align-items: center; gap: 8px;">
                    <img src="<?= base_url('assets/img/player_avatar.png') ?>" alt="Avatar" style="width: 24px; height: 24px; border-radius: 50%; border: 1px solid var(--color-border); object-fit: cover;">
                    <span><?= e($row['name']) ?></span>
                  </div>
                </td>
                <td><?= $row['main'] ?></td>
                <td><?= $row['w'] ?></td>
                <td><?= $row['l'] ?></td>
                <td><strong><?= $row['pts'] ?></strong></td>
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
      <div class="match-date">NEXT MATCH<br><span>25 MEI 2024 | 19:00</span></div>
      <div class="match-players">
        <div class="player">
          <img src="<?= base_url('assets/img/player_avatar.png') ?>" alt="Player 1" class="player-img">
          <h5>ANDI SETIAWAN</h5>
          <span class="rank">RANK #1</span>
        </div>
        <div class="vs">Vs</div>
        <div class="player">
          <img src="<?= base_url('assets/img/player_avatar.png') ?>" alt="Player 2" class="player-img">
          <h5>BUDI KURNIAWAN</h5>
          <span class="rank">RANK #2</span>
        </div>
      </div>
      <div class="realtime-card-footer">
        <h4>JADWAL MATCH</h4>
        <p>Lihat jadwal pertandingan berikutnya dengan mudah.</p>
      </div>
    </div>

    <!-- ACTION CARD -->
    <div class="realtime-card action-card">
      <div class="action-img-wrap">
        <img src="<?= base_url('assets/img/pickleball_action.png') ?>" alt="Action Shot">
      </div>
      <div class="realtime-card-footer">
        <h4>PERTANDINGAN SERU</h4>
        <p>Tanding dengan semangat, menang dengan sportivitas.</p>
      </div>
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
      <a href="#" class="btn btn-primary"><span class="icon-user">👤</span> JOIN LEAGUE</a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>
