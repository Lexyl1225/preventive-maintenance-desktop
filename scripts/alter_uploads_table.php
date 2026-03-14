<?php
require_once __DIR__ . '/../db-config.php';

// Check and add columns if they don't exist
$cols = [
    'deleted' => "TINYINT(1) DEFAULT 0",
    'deleted_at' => "DATETIME DEFAULT NULL",
    'deleted_by' => "VARCHAR(255) DEFAULT NULL",
];

foreach($cols as $col => $definition){
    $stmt = $pdo->prepare("SELECT COUNT(*) AS cnt FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'maintenance_uploads' AND column_name = ?");
    $stmt->execute([$col]);
    $row = $stmt->fetch();
    if($row && intval($row['cnt']) === 0){
        $sql = "ALTER TABLE maintenance_uploads ADD COLUMN $col $definition";
        try{
            $pdo->exec($sql);
            echo "OK: added column $col\n";
        }catch(PDOException $e){
            echo "ERROR adding $col: " . $e->getMessage() . "\n";
        }
    } else {
        echo "SKIP: column $col already exists\n";
    }
}

echo "Done.\n";
?>
