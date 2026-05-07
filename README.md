# FullSatu Single Man League - PHP Native MVP

Aplikasi liga pickleball single player berbasis PHP native + MySQL.

## Fitur
- CRUD pemain
- Buat season half/full league
- Pilih pemain per season
- Generate jadwal round robin
- Input skor 1 game sampai 11 poin
- Klasemen otomatis
- Halaman publik klasemen dan jadwal

## Setup Lokal XAMPP/Laragon
1. Copy folder ini ke `htdocs/fullsatu-php-mvp` atau folder web server kamu.
2. Buat database dengan menjalankan `sql/schema.sql` di phpMyAdmin.
3. Edit `config/db.php` sesuai username/password MySQL kamu.
4. Buka `http://localhost/fullsatu-php-mvp`.

## Urutan Pakai
1. Buka `admin/players.php` untuk tambah pemain.
2. Buka `admin/seasons.php` untuk buat season.
3. Buka `admin/season_players.php` untuk pilih pemain season.
4. Buka `admin/generate.php` untuk generate jadwal.
5. Buka `admin/matches.php` untuk input skor.
6. Buka `index.php` untuk lihat klasemen.

## Rules Liga
- Single player, bukan tim.
- 1 pertandingan = 1 game.
- Skor minimal pemenang 11.
- Skor tidak boleh seri.
- Menang = 1 poin.
- Kalah = 0 poin.
- Tie-break: poin, selisih poin, point for, nama.
