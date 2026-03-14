<?php
// One-time migration: adds missing columns to load_balancing_records
// Delete this file from the server after running.
require_once __DIR__ . '/db-config.php';

$alters = [
    "ALTER TABLE `load_balancing_records` ADD COLUMN IF NOT EXISTS `kvaTotal`  decimal(12,3) DEFAULT NULL",
    "ALTER TABLE `load_balancing_records` ADD COLUMN IF NOT EXISTS `vl1l2Ind`  decimal(6,2)  DEFAULT NULL",
    "ALTER TABLE `load_balancing_records` ADD COLUMN IF NOT EXISTS `vl1l3Ind`  decimal(6,2)  DEFAULT NULL",
    "ALTER TABLE `load_balancing_records` ADD COLUMN IF NOT EXISTS `vl2l3Ind`  decimal(6,2)  DEFAULT NULL",
    "ALTER TABLE `load_balancing_records` ADD COLUMN IF NOT EXISTS `vl1nInd`   decimal(6,2)  DEFAULT NULL",
    "ALTER TABLE `load_balancing_records` ADD COLUMN IF NOT EXISTS `vl2nInd`   decimal(6,2)  DEFAULT NULL",
    "ALTER TABLE `load_balancing_records` ADD COLUMN IF NOT EXISTS `vl3nInd`   decimal(6,2)  DEFAULT NULL",
];

echo "<pre>\n";
foreach ($alters as $sql) {
    try {
        $pdo->exec($sql);
        echo "OK: $sql\n";
    } catch (PDOException $e) {
        echo "ERR: " . $e->getMessage() . "\n  SQL: $sql\n";
    }
}

// Show current columns to confirm
$cols = $pdo->query("SHOW COLUMNS FROM `load_balancing_records`")->fetchAll(PDO::FETCH_COLUMN);
echo "\nCurrent columns:\n" . implode(", ", $cols) . "\n";
echo "</pre>\nDone. Delete this file from the server.\n";
