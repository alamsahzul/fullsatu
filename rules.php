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

    <h2 style="color: var(--color-primary); margin-bottom: 15px; font-size: 24px;">2. Aturan Servis</h2>
    <ul style="color: var(--color-text-main); margin-bottom: 30px; padding-left: 20px; line-height: 1.8;">
      <li>Servis harus dilakukan dengan ayunan bawah (underhand) dengan kontak raket berada di bawah pinggang.</li>
      <li>Bola harus dipantulkan ke tanah satu kali (drop serve) atau dipukul di udara sebelum menyentuh tanah (volley serve).</li>
      <li>Servis dilakukan secara diagonal menyilang ke area servis lawan.</li>
      <li>Jika poin genap (0, 2, 4), servis dilakukan dari sisi kanan. Jika ganjil (1, 3, 5), servis dilakukan dari sisi kiri.</li>
    </ul>

    <h2 style="color: var(--color-primary); margin-bottom: 15px; font-size: 24px;">3. Klasemen & Poin</h2>
    <ul style="color: var(--color-text-main); margin-bottom: 30px; padding-left: 20px; line-height: 1.8;">
      <li>Pemenang akan mendapatkan <strong>1 Poin (Pts)</strong> di klasemen. Pemain yang kalah tidak mendapatkan poin (0 Pts).</li>
      <li>Peringkat klasemen ditentukan secara berurutan berdasarkan: Poin Tertinggi, Selisih Skor (Diff) Tertinggi, Skor Memasukkan (PF) Tertinggi, dan Alfabet nama.</li>
    </ul>

    <h2 style="color: var(--color-primary); margin-bottom: 15px; font-size: 24px;">4. Sportivitas</h2>
    <ul style="color: var(--color-text-main); margin-bottom: 0; padding-left: 20px; line-height: 1.8;">
      <li>Semua pemain diharapkan menjunjung tinggi sportivitas dan kejujuran dalam menghitung poin atau menyatakan bola out/in.</li>
      <li>Keputusan pemain bersifat mengikat dalam permainan tanpa wasit (honor system).</li>
    </ul>

  </div>
</div>

<?php include 'includes/footer.php'; ?>
