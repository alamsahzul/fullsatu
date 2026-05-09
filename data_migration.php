<?php
require 'config/db.php';

echo "Starting Data Migration...\n";

try {
    echo "Disabling Foreign Key Checks...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0");

    echo "Cleaning local tables...\n";
    $pdo->exec("TRUNCATE TABLE matches");
    $pdo->exec("TRUNCATE TABLE season_players");
    $pdo->exec("TRUNCATE TABLE seasons");
    $pdo->exec("TRUNCATE TABLE players");

    $sql = file_get_contents('scratch/server_dump.sql');
    $lines = explode("\n", $sql);
    
    echo "Importing server records...\n";
    $count = 0;
    $currentQuery = "";
    foreach ($lines as $line) {
        $trimmed = trim($line);
        if (empty($trimmed) || strpos($trimmed, '--') === 0 || strpos($trimmed, '/*') === 0) {
            continue;
        }

        $currentQuery .= $line . "\n";

        if (substr($trimmed, -1) === ';') {
            if (stripos($currentQuery, 'INSERT INTO') !== false) {
                $pdo->exec($currentQuery);
                $count++;
            }
            $currentQuery = "";
        }
    }
    echo "Import successful. Total $count batch INSERTs executed.\n";

    echo "Updating new columns with default values...\n";
    $pdo->exec("UPDATE seasons SET category = 'single', format = 'league', group_count = 1, qualifiers_per_group = 2");
    $pdo->exec("UPDATE season_players SET group_name = 'A'");
    $pdo->exec("UPDATE matches SET group_name = 'A'");

    echo "Enabling Foreign Key Checks...\n";
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1");

    $sCount = $pdo->query("SELECT COUNT(*) FROM seasons")->fetchColumn();
    $mCount = $pdo->query("SELECT COUNT(*) FROM matches")->fetchColumn();
    echo "Summary: $sCount seasons and $mCount matches imported.\n";

    echo "Migration completed successfully!\n";

} catch (Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage() . "\n";
}
