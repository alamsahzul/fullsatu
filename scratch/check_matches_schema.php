<?php
require 'config/db.php';
$stmt = $pdo->query("DESCRIBE matches");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
