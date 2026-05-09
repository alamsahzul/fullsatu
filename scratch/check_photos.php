<?php
require 'config/db.php';
$stmt = $pdo->query("SELECT id, name, photo FROM players WHERE photo IS NOT NULL LIMIT 5");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
