<?php
require 'config/db.php';
$m1 = $pdo->query("SELECT * FROM matches WHERE id=28")->fetch(PDO::FETCH_ASSOC);
$m2 = $pdo->query("SELECT * FROM matches WHERE id=789")->fetch(PDO::FETCH_ASSOC);
echo "Match 28 Status: {$m1['status']}\n";
echo "Match 789 Status: {$m2['status']}\n";
