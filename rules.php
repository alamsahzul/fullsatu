<?php
require 'config/db.php';
require 'includes/functions.php';

$pageTitle = 'Peraturan - FullSatu';
include 'includes/header.php';
?>

<section class="hero-page">
  <h1>Peraturan Liga</h1>
  <p>Berikut adalah aturan resmi yang berlaku di FullSatu Single Man League.</p>
</section>

<div class="card" style="margin-bottom: 60px;">
  <div style="background: var(--color-bg-dark); border: 1px solid var(--color-border); padding: 30px; border-radius: 16px;">
    
    <h2 style="color: var(--color-primary); margin-bottom: 15px; font-size: 24px;">1. Sistem Pertandingan</h2>
    <ul style="color: var(--color-text-main); margin-bottom: 30px; padding-left: 20px; line-height: 1.8;">
      <li>Pertandingan menggunakan sistem skor tradisional (side-out). Poin hanya bisa didapat oleh pemain yang sedang melakukan servis.</li>
      <li>Setiap pertandingan (match) terdiri dari 1 game. Pemenang adalah yang lebih dulu mencapai 11 poin.</li>
      <li>Tidak ada aturan selisih 2 poin (no deuce). Pemain yang pertama kali menyentuh angka 11 dinyatakan sebagai pemenang (Sudden Death 11).</li>
    </ul>

    <h2 style="color: var(--color-primary); margin-bottom: 15px; font-size: 24px;">2. Aturan Servis & Pelanggaran</h2>
    <ul style="color: var(--color-text-main); margin-bottom: 30px; padding-left: 20px; line-height: 1.8;">
      <li>Servis dilakukan secara diagonal menyilang ke area servis lawan.</li>
      <li><strong>Foot Fault:</strong> Saat servis, kedua kaki harus di belakang baseline. Kaki tidak boleh menyentuh garis baseline atau masuk lapangan sebelum bola dipukul.</li>
      <li><strong>Double Bounce:</strong> Bola harus memantul satu kali di masing-masing sisi (area lawan lalu area sendiri) sebelum boleh melakukan pukulan volley.</li>
      <li><strong>The Kitchen:</strong> Dilarang melakukan volley (memukul bola tanpa pantul) jika posisi kaki menyentuh atau berada di dalam area Non-Volley Zone.</li>
      <li><strong>Aksesoris & Kitchen:</strong> Jika aksesoris (topi, kacamata, botol, dll) yang dipakai atau dibawa pemain jatuh dan menyentuh area Kitchen atau garis Kitchen, maka dianggap sebagai pelanggaran (Fault).</li>
    </ul>

    <h2 style="color: var(--color-primary); margin-bottom: 15px; font-size: 24px;">3. Klasemen & Poin</h2>
    <ul style="color: var(--color-text-main); margin-bottom: 30px; padding-left: 20px; line-height: 1.8;">
      <li>Pemenang akan mendapatkan <strong>1 Poin (Pts)</strong> di klasemen. Pemain yang kalah tidak mendapatkan poin (0 Pts).</li>
      <li>Peringkat klasemen ditentukan secara berurutan berdasarkan: Poin Tertinggi, Selisih Skor (Diff) Tertinggi, Skor Memasukkan (PF) Tertinggi, dan Alfabet nama.</li>
    </ul>

    <h2 style="color: var(--color-primary); margin-bottom: 15px; font-size: 24px;">4. Wewenang Wasit</h2>
    <ul style="color: var(--color-text-main); margin-bottom: 30px; padding-left: 20px; line-height: 1.8;">
      <li>Keputusan wasit di lapangan bersifat <strong>MUTLAK</strong> dan tidak dapat diganggu gugat.</li>
      <li>Wasit berhak memberikan teguran, pengurangan poin, hingga diskualifikasi jika pemain melanggar aturan sportivitas.</li>
    </ul>

    <h2 style="color: var(--color-primary); margin-bottom: 15px; font-size: 24px;">5. Sportivitas & Etika</h2>
    <ul style="color: var(--color-text-main); margin-bottom: 0; padding-left: 20px; line-height: 1.8;">
      <li><strong>No Provocation:</strong> Dilarang keras melakukan provokasi berlebihan, intimidasi, atau mengeluarkan kata-kata kasar (bacot) kepada lawan maupun wasit.</li>
      <li>Semua pemain wajib menjunjung tinggi kejujuran. Jika tidak ada wasit, pemain harus jujur menyatakan bola out/in di areanya sendiri.</li>
      <li>Pemain wajib bersalaman dengan lawan dan wasit setelah pertandingan berakhir.</li>
    </ul>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
