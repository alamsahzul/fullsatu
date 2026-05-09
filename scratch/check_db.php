<?php
require 'config/db.php';
$stmt = $pdo->query("DESCRIBE seasons");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt = $pdo->query("SELECT * FROM seasons WHERE id = 1");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
