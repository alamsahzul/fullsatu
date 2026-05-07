<?php
require 'config/db.php';
require 'includes/functions.php';

$pageTitle = 'Tentang Kami - FullSatu';
include 'includes/header.php';
?>

<section class="hero-page">
  <h1>Tentang FullSatu</h1>
  <p>Lebih dari sekadar olahraga, ini adalah komunitas dan semangat juang.</p>
</section>

<div class="card" style="margin-bottom: 60px;">
  <div style="background: var(--color-bg-dark); border: 1px solid var(--color-border); padding: 40px; border-radius: 16px; display: flex; flex-direction: column; gap: 40px; align-items: center; text-align: center;">
    
    <img src="<?= base_url('assets/img/hero_logo.png') ?>" alt="FullSatu Shield" style="max-width: 250px; filter: drop-shadow(0 10px 20px rgba(0,0,0,0.5));">
    
    <div style="max-width: 800px;">
      <h2 style="color: var(--color-primary); margin-bottom: 20px; font-size: 32px;">SEJARAH & VISI</h2>
      <p style="color: var(--color-text-main); font-size: 18px; line-height: 1.8; margin-bottom: 20px;">
        <strong>FullSatu Single Man League</strong> didirikan dari semangat untuk memasyarakatkan olahraga pickleball, khususnya pada kategori tunggal putra (single). Kami menyadari bahwa bermain single membutuhkan ketahanan fisik, fokus penuh, dan mental juara yang luar biasa.
      </p>
      <p style="color: var(--color-text-muted); font-size: 16px; line-height: 1.8;">
        Visi kami adalah menjadikan FullSatu sebagai wadah kompetisi yang sehat, mengedepankan asas *fair play*, serta mempererat tali persaudaraan antar pemain. Kami berkomitmen menyelenggarakan liga yang transparan dan terorganisir, di mana setiap keringat dihargai dengan sebuah sistem ranking *real-time*.
      </p>
    </div>
    
    <div style="width: 100%; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 40px;">
      <h2 style="color: var(--color-text-main); margin-bottom: 30px;">HUBUNGI KAMI</h2>
      <div style="display: flex; gap: 20px; justify-content: center; flex-wrap: wrap;">
        <div style="background: var(--color-bg-light); padding: 20px; border-radius: 12px; border: 1px solid var(--color-border); width: 250px;">
          <h3 style="color: var(--color-primary); font-size: 18px; margin-bottom: 10px;">Email</h3>
          <p style="color: var(--color-text-muted);">hello@fullsatu.com</p>
        </div>
        <div style="background: var(--color-bg-light); padding: 20px; border-radius: 12px; border: 1px solid var(--color-border); width: 250px;">
          <h3 style="color: var(--color-primary); font-size: 18px; margin-bottom: 10px;">WhatsApp</h3>
          <p style="color: var(--color-text-muted);">+62 812-3456-7890</p>
        </div>
        <div style="background: var(--color-bg-light); padding: 20px; border-radius: 12px; border: 1px solid var(--color-border); width: 250px;">
          <h3 style="color: var(--color-primary); font-size: 18px; margin-bottom: 10px;">Lokasi</h3>
          <p style="color: var(--color-text-muted);">FullSatu Pickleball Court</p>
        </div>
      </div>
    </div>
    
  </div>
</div>

<?php include 'includes/footer.php'; ?>
