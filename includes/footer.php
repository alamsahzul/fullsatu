</main>
<footer class="site-footer">
  <div class="container">
    <div class="footer-grid">
      <!-- Column 1: Brand -->
      <div class="footer-col">
        <div class="footer-brand">
          <img src="<?= base_url('assets/img/hero_logo.png') ?>" alt="FullSatu Logo" class="footer-logo-img">
          <div class="brand-text">
            <strong>FULLSATU</strong>
            <span>SINGLE MAN LEAGUE</span>
          </div>
        </div>
        <p style="margin-top: 20px; color: var(--color-text-muted); font-size: 14px; line-height: 1.6;">
          Liga pickleball single player pertama yang mengutamakan sportivitas dan persaingan sehat di setiap pertandingan.
        </p>
      </div>

      <!-- Column 2: Quick Links -->
      <div class="footer-col">
        <h4 class="footer-title">Navigasi</h4>
        <ul class="footer-links">
          <li><a href="<?= base_url('index') ?>">Beranda</a></li>
          <li><a href="<?= base_url('standings') ?>">Klasemen Liga</a></li>
          <li><a href="<?= base_url('matches') ?>">Jadwal & Hasil</a></li>
          <li><a href="<?= base_url('rules') ?>">Aturan Liga</a></li>
        </ul>
      </div>

      <!-- Column 3: About -->
      <div class="footer-col">
        <h4 class="footer-title">Informasi</h4>
        <ul class="footer-links">
          <li><a href="<?= base_url('about') ?>">Tentang Kami</a></li>
          <li><a href="https://wa.me/6285298963132" target="_blank">Hubungi Admin</a></li>
          <li><a href="<?= base_url('admin/index') ?>">Portal Admin</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <div class="copyright">
        &copy; <?= date('Y') ?> <strong>FullSatu</strong>. Built for Champions.
      </div>
    </div>
  </div>
</footer>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.querySelector('.mobile-toggle');
    const headerMenu = document.querySelector('.header-menu');

    if (mobileToggle && headerMenu) {
      mobileToggle.addEventListener('click', function() {
        mobileToggle.classList.toggle('active');
        headerMenu.classList.toggle('active');
      });
    }
  });
</script>
</body>
</html>
