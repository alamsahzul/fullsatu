<?php
require 'config/db.php';
$stmt = $pdo->query("DESCRIBE seasons");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
