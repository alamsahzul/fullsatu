<?php
require 'config/db.php';
$pdo->query('UPDATE tournament_brackets SET player1_score=NULL, player2_score=NULL, winner_id=NULL');
$pdo->query('DELETE FROM tournament_brackets');
echo 'Reset!';
