<?php
require_once __DIR__ . '/../db-config.php';

try{
    $stmt = $pdo->query('SELECT id, file_name, file_path, uploaded_at, uploader FROM maintenance_uploads ORDER BY id DESC LIMIT 10');
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(!$rows){ echo "(no rows)\n"; exit; }
    foreach($rows as $r){
        echo sprintf("%s | %s | %s | %s | %s\n", $r['id'], $r['file_name'], $r['file_path'], $r['uploaded_at'], $r['uploader']);
    }
}catch(PDOException $e){
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}

?>
