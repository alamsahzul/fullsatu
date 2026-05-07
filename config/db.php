<?php
$host = 'localhost';
$dbname = 'fullsatu_league';
$username = 'root';
$password = '';

define('BASE_URL', '/fullsatu/'); // Ubah sesuai dengan path di server Anda (contoh: '/' jika di root domain)
define('ADMIN_USER', 'admin');
define('ADMIN_PASS', 'password123'); // Ganti dengan password yang lebih kuat
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
?>
