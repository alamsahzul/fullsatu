<?php
require 'config/db.php';
try {
    $pdo->query('ALTER TABLE seasons ADD COLUMN qualifiers_per_group INT DEFAULT 2 AFTER group_count');
    echo 'Column added!';
} catch(Exception $e) {
    echo $e->getMessage();
}
