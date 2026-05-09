<?php
require 'config/db.php';
echo "<h3>Daftar Kolom Tabel 'matches':</h3><ul>";
try {
    $stmt = $pdo->query("DESC matches");
    while ($row = $stmt->fetch()) {
        echo "<li>" . $row['Field'] . "</li>";
    }
} catch (Exception $e) {
    echo "<li style='color:red'>Gagal akses tabel: " . $e->getMessage() . "</li>";
}
echo "</ul>";
?>
