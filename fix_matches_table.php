<?php
require 'config/db.php';
echo "<h2>Auto-Fix Database Structure</h2>";

try {
    // 1. Check round_name in matches
    $stmt = $pdo->query("DESC matches");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (!in_array('round_name', $columns)) {
        echo "<p>Menambahkan kolom 'round_name' ke tabel 'matches'...</p>";
        $pdo->exec("ALTER TABLE matches ADD COLUMN round_name VARCHAR(50) DEFAULT 'Group Stage' AFTER season_id");
        echo "<p style='color:green'>BERHASIL: Kolom 'round_name' telah ditambahkan.</p>";
    } else {
        echo "<p style='color:blue'>Kolom 'round_name' sudah ada.</p>";
    }

    // 2. Check match_photo and match_notes (for quick view)
    if (!in_array('match_photo', $columns)) {
        echo "<p>Menambahkan kolom 'match_photo'...</p>";
        $pdo->exec("ALTER TABLE matches ADD COLUMN match_photo VARCHAR(255) DEFAULT NULL");
    }
    if (!in_array('match_notes', $columns)) {
        echo "<p>Menambahkan kolom 'match_notes'...</p>";
        $pdo->exec("ALTER TABLE matches ADD COLUMN match_notes TEXT DEFAULT NULL");
    }

    echo "<h3>Semua perbaikan selesai!</h3>";
    echo "<p><a href='hybrid.php'>Klik di sini untuk kembali ke halaman Hybrid</a></p>";

} catch (Exception $e) {
    echo "<p style='color:red'>GAGAL: " . $e->getMessage() . "</p>";
}
?>
