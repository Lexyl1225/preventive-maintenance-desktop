<?php
require_once __DIR__ . '/../db-config.php';

$sql = <<<'SQL'
CREATE TABLE IF NOT EXISTS maintenance_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    record_id INT DEFAULT NULL,
    file_name VARCHAR(255) DEFAULT NULL,
    file_path VARCHAR(512) DEFAULT NULL,
    file_type VARCHAR(100) DEFAULT NULL,
    file_size BIGINT DEFAULT NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    uploader VARCHAR(255) DEFAULT NULL,
    KEY idx_record (record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

try{
    $pdo->exec($sql);
    echo "OK: maintenance_uploads table created or already exists\n";
}catch(PDOException $e){
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

?>
