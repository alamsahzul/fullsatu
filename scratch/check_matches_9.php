<?php
require 'config/db.php';
$stmt = $pdo->query("SELECT status, count(*) as c FROM matches WHERE season_id=9 GROUP BY status");
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
